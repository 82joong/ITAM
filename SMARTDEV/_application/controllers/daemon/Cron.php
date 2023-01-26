<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Cron extends CI_Controller {

    public function __construct() {
        parent::__construct();

        if( IS_REAL_SERVER ) {
            if($this->input->is_cli_request() == false) {
                return;
            }
        }

    }

    public function index() {
        echo 'COMMON INDEX'.PHP_EOL;
    }



    // 주기적 SNAPSHOT 생성 및 정리 
    public function elastic_snapshot_and_clean() {

        $this->load->library('Elastic');

        // 과거 snaphost 정리
        $this->elastic->clean_snapshot();


        // 신규 snapshot 생성
        $res = $this->elastic->store_snapshot(ELASTIC_REPOSITORY_NAME, ELASTIC_MARKET_GOODS_VVIC_INDEX);
	//echo print_r($res);
        $this->common->logWrite('snapshot', print_r($res, true), 'vvic');



        // 신규 snapshot 생성
        $res = $this->elastic->store_snapshot(ELASTIC_REPOSITORY_NAME, ELASTIC_MARKET_GOODS_LAFAYETTE_INDEX);
	//echo print_r($res);
        $this->common->logWrite('snapshot', print_r($res, true), 'lafayette');
    }




    // 하루전 vvic_cache 데이터 삭제
    public function clean_cache_and_csv() {

        // VVIC Cache
        $filepath = APPPATH.'../appdata/vvic_cache';
        $data = scandir($filepath);

        foreach($data as $file) {
            if($file == '.' || $file == '..') continue;
            if( filemtime($filepath.'/'.$file) < time()- 86400 ) {
		        //echo $filepath.'/'.$file.PHP_EOL;
                @unlink($filepath.'/'.$file);
            }
        }

        // VVIC
        $this->_clean_csv(ELASTIC_CSV_PATH, 30);


        // LAFAYETTE
        $_LAFAYETTE_BULK_PATH = APPPATH.'../appdata/elastic_bulk';
        $this->_clean_csv($_LAFAYETTE_BULK_PATH, 10);


        // QEEBOO CSV
        $this->_clean_csv(APPDATA_PATH.'/market_file/qeeboo/csvs', 10);
    }


    // csv 파일 주기적으로 삭제
    private function _clean_csv($path, $st_date) {

        //$st_date = 30; // 30일 이전 데이터 유지
        $data = scandir($path);

        foreach($data as $file) {
            if($file == '.' || $file == '..') continue;
            if( filemtime($path.'/'.$file) < (time() - (86400 * $st_date)) ) {
		        //echo $filepath.'/'.$file.PHP_EOL;
                @unlink($path.'/'.$file);
            }
        }
    }


    public function ipinfo() {


        $ips = array(
            '101.201.208.194',
            '43.249.37.183',  
            '209.216.230.229', 
            '84.16.234.194',   
            '103.254.154.21',  
            '217.196.146.37',  
            '77.93.208.23',    
            '31.204.145.174',  
            '71.19.249.62',    
            '81.93.149.230',   
            '173.0.59.114',    
            '188.94.27.156',   
            '199.223.252.230', 
            '98.143.158.202',  
            '134.19.161.36',   
            '172.107.248.114', 
            '158.58.173.8',    
            '201.131.127.49',  
            '212.38.169.106',  
            '89.46.72.60', 
            '103.28.15.182',   
            '172.107.248.114', 
            '80.239.175.136',  
            '66.232.106.200',  
            '131.100.2.22',    
            '188.241.178.82',  
            '185.3.233.48',    
            '195.154.28.177',  
            '213.227.181.18', 
            '142.44.137.201',  
            '221.121.154.217', 
            '37.252.253.22',   
            '49.50.250.78',    
            '168.205.94.10',   
            '164.52.195.81',   
            '185.50.105.199',  
            '46.182.221.24',   
            '213.206.252.57',  
            '95.211.176.129',  
            '54.36.123.176',   
            '5.182.210.227',   
            '77.243.191.61',   
        );


        $url = 'ipinfo.io';
        foreach($ips as $ip) {
            $res = $this->common->restful_curl_get($url.'/'.$ip);
            $res = json_decode($res, true);

            echo 'IP: '.$ip.',    ';
            echo 'Country: '.$res['country'].',    ';
            echo 'City: '.$res['city'].',    ';
            echo 'Timezone: '.$res['timezone'].'<br/>'.PHP_EOL;
            exit;

            sleep(1);
        }
    }
        

    public function up_am() {


		$this->load->model(array(
            'order_tb_model',
            'assets_model_tb_model'
        ));


        $params = array();
        $params['raw'] = array('am_order_id > 0');
        $extras = array();
        $extras['fields'] = array('am_id', 'am_order_id');
        $extras['get_query_only'] = TRUE;
        $sql = $this->assets_model_tb_model->getList($params, $extras);
        //echo $sql; exit;
        $query = $this->db->query($sql);

        while($row = $query->unbuffered_row('array')) {
            $order_data = $this->order_tb_model->get($row['am_order_id'])->getData();

            $data_params = array();
            $data_params['am_estimatenum'] = $order_data['o_estimatenum'];
            //echo print_r($data_params); exit; 
            //$this->assets_model_tb_model->doUpdate($row['am_id'], $data_params);
        }

    }


    public function insertCustomFields() {

		$this->load->model(array(
            'fieldset_tb_model',
            'custom_field_map_tb_model',
            'models_custom_fields_tb_model',
            'custom_field_tb_model',
            'custom_value_tb_model'
        ));

        $cf_id = 10;
        $cf_data = $this->custom_field_tb_model->get($cf_id)->getData();
        echo print_r($cf_data); 


        $params = array();
        $params['=']['cfm_custom_field_id'] = $cf_id;
        
        $cfm_data = $this->custom_field_map_tb_model->getList($params)->getData();
        $cfm_data = $this->common->getDataByPK($cfm_data, 'cfm_fieldset_id');
        echo print_r($cfm_data);


        $fs_ids = array_keys($cfm_data);
        echo print_r($fs_ids);
        

        $params = array();
        $params['in']['mcf_fieldset_id'] = $fs_ids;
        $extras = array();
        $mcf_data = $this->models_custom_fields_tb_model->getList($params, $extras)->getData();
        $mcf_data = $this->common->getDataByDuplPK($mcf_data, 'mcf_models_id');
        //echo print_r($mcf_data);


        exit;


        /*
        foreach($mcf_data as $models_id=>$mcf) {

            $mcf_names = array_keys($this->common->getDataByPK($mcf, 'mcf_name'));
            //echo print_r($mcf_names); //exit;

            if( ! in_array($cf_data['cf_name'], $mcf_names) ) {


                $in_params = array();
                $in_params['mcf_models_id']         = $models_id;
                $in_params['mcf_fieldset_id']       = $mcf[0]['mcf_fieldset_id'];
                $in_params['mcf_fieldset_name']     = $mcf[0]['mcf_fieldset_name'];
                $in_params['mcf_name']              = $cf_data['cf_name'];
                $in_params['mcf_format']            = $cf_data['cf_format'];
                $in_params['mcf_format_element']    = $cf_data['cf_format_element'];
                $in_params['mcf_help_text']         = $cf_data['cf_help_text'];
                $in_params['mcf_element_value']     = $cf_data['cf_element_value'];
                $in_params['mcf_value']             = '';
                $in_params['mcf_encrypt']           = $cf_data['cf_encrypt'];
                $in_params['mcf_required']          = $cf_data['cf_required'];
                $in_params['mcf_order']             = sizeof($mcf) + 1;
                $in_params['mcf_created_at']        = date('Y-m-d H:i:s');
                $in_params['mcf_udpated_at']        = date('Y-m-d H:i:s');

                //echo print_r($in_params); exit;


                if( ! $this->models_custom_fields_tb_model->doInsert($in_params)->isSuccess()) {
                    $res[] = array(
                        'in_params' => $in_params,
                        'error_msg' => $this->getErrorMsg()
                    );
                }
                //exit;
            }
        } // END_FOREACH @mcf_data
        */


        $params = array();
        $params['in']['cv_fieldset_id'] = $fs_ids;
        $extras = array();
        $cv_data = $this->custom_value_tb_model->getList($params, $extras)->getData();
        $cv_data = $this->common->getDataByDuplPK($cv_data, 'cv_assets_model_id');

        foreach($cv_data as $assets_model_id=>$cv) {

            $cv_names = array_keys($this->common->getDataByPK($cv, 'cv_name'));
            //echo print_r($cv_names); //exit;

            if( ! in_array($cf_data['cf_name'], $cv_names) ) {
                $in_params = array();
                $in_params['cv_assets_model_id']    = $assets_model_id;
                $in_params['cv_models_id']          = $cv[0]['cv_models_id'];
                $in_params['cv_fieldset_id']        = $cv[0]['cv_fieldset_id'];
                $in_params['cv_name']               = $cf_data['cf_name'];
                $in_params['cv_format']             = $cf_data['cf_format'];
                $in_params['cv_format_element']     = $cf_data['cf_format_element'];
                $in_params['cv_help_text']          = $cf_data['cf_help_text'];
                $in_params['cv_element_value']      = $cf_data['cf_element_value'];
                $in_params['cv_value']              = '';
                $in_params['cv_encrypt']            = $cf_data['cf_encrypt'];
                $in_params['cv_required']           = $cf_data['cf_required'];
                $in_params['cv_order']              = sizeof($cv) + 1;
                $in_params['cv_created_at']         = date('Y-m-d H:i:s');
                $in_params['cv_udpated_at']         = date('Y-m-d H:i:s');

                //echo print_r($in_params); exit;
                if( ! $this->custom_value_tb_model->doInsert($in_params)->isSuccess()) {
                    $res[] = array(
                        'in_params' => $in_params,
                        'error_msg' => $this->custom_value_tb_model->getErrorMsg()
                    );

                    echo print_r($res);
                }
                //exit;
            }
        } // END_FOREACH @cv_data
    } 




    /*
    |--------------------------------------------------------------------------
    | ELK syslog-* Index 로 부터 주기적으로 host 업데이트 
    |--------------------------------------------------------------------------
    |
    | ITAM 내 서비스명과 ELK syslog-* Index 내에 수집되는 host 매핑 위한 
    | [vmservice_host_map_tb] host 항목 주기적인 업데이트 
    | 
    |
    */
    public function upsertHost() {


        $this->load->model(array(
            'vmservice_host_map_tb_model',
            'vmservice_tb_model',
        ));


        $this->load->library('elastic');
        $el_header = $this->elastic->get_auth_header();

        $sub_aggs = array(
            'agg' => array(
                'top_hits' => array(
                    '_source' => array('host', 'mysql_version', '@timestamp'),
                    'size' => 1,
                    'sort' => array(
                        '@timestamp' => array('order' => 'desc')
                    )
                )
            )
        );

        $parmas = array();
        $params['size'] = 0;
        $params['aggs'] = array(
            'group_by_host' => array(
                'terms' => array(
                    'field' => 'host',
                    'order' => array(
                        '_key' => 'ASC'
                    ),
                    'size'  => 2000
                    //'size'  => 10 // DEBUG
                ),
                'aggs' => $sub_aggs
            )
        );
        //echo print_r($params);
        $json_params = json_encode($params, JSON_NUMERIC_CHECK);
        //echo $json_params; exit;
            
        $el_url = ELASTIC_SYSLOG_HOST.'/'.ELASTIC_SYSLOG_INDEX.'/_search?size=0';
        $el_result = $this->elastic->restful_curl($el_url, $json_params, 'GET', $timeout=10, $el_header);
        $el_result = json_decode($el_result, true);
        //echo print_r($el_result); exit;


        if( !isset($el_result['aggregations']) || sizeof($el_result['aggregations']['group_by_host']['buckets']) < 1 ) {
            echo 'Data No Exists! <br/>'.PHP_EOL;
            exit;
        }


        $data = $el_result['aggregations']['group_by_host']['buckets'];

        $chunk_cnt = 30;
        $insert_cnt = 0;
        foreach(array_chunk($data, $chunk_cnt)  as $rows) {

            $rows = $this->common->getDataByPK($rows, 'key');
            //echo print_r($rows); exit;
            $host_names = array_keys($rows); 
            if( sizeof($host_names) < 1 ) continue;

            $params = array();
            $params['in']['vhm_elk_host_name'] = $host_names;
            $extras = array();
            $extras['order_by'] = array('vhm_elk_host_name ASC');
            $vhm_data = $this->vmservice_host_map_tb_model->getList($params, $extras)->getData();


            $vhm_map = array_keys($this->common->getDataByPK($vhm_data, 'vhm_elk_host_name'));
            $diff = array_diff($host_names, $vhm_map);
            //echo print_r($diff); exit;

            foreach($diff as $host) {


                // vms_name 에서 직관적으로 동일한 host name 있으면 해당 name으로 일단 매핑해보자.
                $params = array();
                $params['=']['vms_name'] = strtolower($host);
                $vms_data = $this->vmservice_tb_model->getList($params)->getData();

                
                if(sizeof($vms_data) > 0 ) {
                    $vms_data = array_shift($vms_data);
                    $vms_id = $vms_data['vms_id'];
                    $vms_name = $vms_data['vms_name'];
                }else {
                    $vms_id = 0;
                    $vms_name = '';
                }

                $params = array(
                    'vhm_vmservice_id'  => $vms_id,
                    'vhm_vmservice_name'=> $vms_name,
                    'vhm_elk_host_name' => $host,
                    'vhm_mysql_version' => $rows[$host]['agg']['hits']['hits'][0]['_source']['mysql_version'],
                    'vhm_created_at'    => date('Y-m-d H:i:s'),
                    'vhm_updated_at'    => date('Y-m-d H:i:s'),
                );
                //echo print_r($params); exit;

                if( ! $this->vmservice_host_map_tb_model->doInsert($params)->isSuccess() ) {
                    echo $this->vmservice_host_map_tb_model->getErrorMsg();
                    continue;
                }
                $insert_cnt = $insert_cnt + 1;
            }
            //exit;
        }
        echo 'INSERT COUNT : '.$insert_cnt;
    }




    // [lab1.makeshop.co.kr] ipmanager 테이블 최초 1회 Batch
    /*
        
        mysql> SELECT COUNT(*) FROM ipmanager WHERE name <> '' LIMIT 0,10\G;
        *************************** 1. row ***************************
        COUNT(*): 5836
        1 row in set (0.00 sec)

    */
    public function lab_batch_sync() {


		$this->load->model(array(
            'lab_tb_model',
        ));

        $this->load->library('CIDR');

        for($i=1; $i<60; $i++) {

            echo 'PAGE: '. $i.'<br />'.PHP_EOL;

            $params = array('page' => $i);
            $url = 'http://lab1.makeshop.co.kr/server/api/getService.html';
            $res = $this->common->restful_curl($url, $params, 'POST');
            //echo $res;
            $res = json_decode($res, true);
            //echo print_r($res);

            if($res['is_success'] == FALSE) {
                echo $res['msg'];
                exit;
            }

            //echo print_r($res['msg']); exit;
            foreach($res['msg'] as $data) {


                if( $this->cidr->validIP($data['ip']) == FALSE ) {
                    continue;
                }


                $db_params = array();
                $db_params['=']['lab_ip'] = $data['ip'];
                $lab_row = $this->lab_tb_model->getList($db_params)->getData();


                $in_params = array();
                $in_params['lab_remoteip'] = trim($data['remoteip']);
                $in_params['lab_name'] = trim($data['name']);
                $in_params['lab_rack'] = trim($data['rack']);
                $in_params['lab_owner'] = trim($data['owner']);
                $in_params['lab_sw'] = trim($data['sw']);
                $in_params['lab_seq'] = trim($data['seq']);
                $in_params['lab_os'] = trim($data['os']);
                $in_params['lab_server'] = trim($data['server']);
                $in_params['lab_servicetag'] = trim($data['servicetag']);
                $in_params['lab_cpu'] = trim($data['cpu']);
                $in_params['lab_ram'] = trim($data['ram']);
                $in_params['lab_hdd'] = trim($data['hdd']);
                $in_params['lab_hddtype'] = trim($data['hddtype']);
                $in_params['lab_regdate'] = trim($data['regdate']);
                $in_params['lab_memo'] = trim($data['memo']);
                $in_params['lab_ping'] = trim($data['ping']);
                $in_params['lab_backup'] = trim($data['backup']);
                $in_params['lab_asset_code'] = trim($data['assets_code']);


                if(sizeof($lab_row) > 0) {

                    //echo print_r($db_params);
                    //echo print_r($lag_row); exit;

                    // Update
                    $in_params['lab_updated_at'] = date('Y-m-d H:i:s');
                    if($this->lab_tb_model->doUpdate($lab_row[0]['lab_ip'], $in_params)->isSuccess() == TRUE) {
                        echo $lab_row[0]['id'].' : UPDATED. <br />'.PHP_EOL;
                    }else {
                        echo $this->lab_tb_model->getErrorMsg().'<br />'.PHP_EOL;
                    }

                }else {

                    // Insert
                    $in_params['lab_ip'] = trim($data['ip']);
                    $in_params['lab_created_at'] = date('Y-m-d H:i:s');
                    if($this->lab_tb_model->doInsert($in_params)->isSuccess() == TRUE) {
                        echo $this->lab_tb_model->getData().' : INSERTED. <br />'.PHP_EOL;
                    }else {
                        echo $this->lab_tb_model->getErrorMsg().'<br />'.PHP_EOL;
                    }
                } // END if

                //exit;
            } // END foreach
        } // END for
    } 



    // 다우그룹웨어 사용자 데이터 싱크
    public function sync_daou($mode='ACCOUNT') {

        $this->load->helper('file');
        $this->load->library('encryption');

        //$mode = 'DEPT';

        switch($mode) {
            case 'ACCOUNT':
                $_API_URL = 'https://api.daouoffice.com/public/v1/account';         // 계정정보조회
                break;

            case 'DEPT':
                $_API_URL = 'https://api.daouoffice.com/public/v1/dept';            // 부서정보조회
                break;

            case 'MEMBER':
                $_API_URL = 'https://api.daouoffice.com/public/v1/dept/member';     // 부서원정보조회
                break;

            case 'DUTY':
                $_API_URL = 'https://api.daouoffice.com/public/v1/dept/duty';       // 직책정보조회
                break;
        }

        $_API_ID  = 'eee5dedae2d5f1f5';
        $_API_SECRET = 'acbcd0aba8e4e7bec5fce2fdbaaef5a1';

        $cmd = "curl -XPOST '".$_API_URL."' ";
        $cmd .= "-d '{\"clientId\":\"".$_API_ID."\",\"clientSecret\":\"".$_API_SECRET."\"}' ";
        $cmd .= "-H 'content-type: application/json'";

        $res = @shell_exec($cmd);
        $data = json_decode($res, true); 
        //echo print_r($data); exit;

        /*
           Array(
               [code] => 200
               [message] => OK
               [data] => Array(
                   [0] => Array(
                    ...
        */

        if($data['code'] == 200) {
            
            $ciphertext = $this->encryption->encrypt($res);
            @write_file(SYNC_FILE_PATH.'/groupware_'.strtolower($mode).'.info', $ciphertext);

        }else {

        }

        $this->load->model(array(
            'history_tb_model'
        ));

        unset($data['data']);
        $serial = array(
            'params' => array(
                'request' => $cmd,
                'response' => $data
            ) 
        );

        $params = array();
        $params['h_loginid'] = 'SYSTEM';
        $params['h_name'] = 'SYSTEM';
        $params['h_act_mode'] = 'DAEMON';
        $params['h_act_key'] = 0;
        $params['h_serialize'] = serialize($serial);
        $params['h_act_table'] = 'sync_daou';
        $this->history_tb_model->doInsert($params);
    }



    /*
    |--------------------------------------------------------------------------
    | Sync Member 
    |--------------------------------------------------------------------------
    | - 다우 그룹웨어 회원 정보 Sync
    | - 재직 여부 및 퇴사 처리 : ip(자산) 회수& ip_history 저장
    |
    */
    public function sync_member() {

		$this->load->model(array(
            'company_tb_model',
            'people_tb_model',
            'people_ip_map_tb_model',
            'ip_tb_model', 
            'history_tb_model'
        ));

        $log_data = array(
            'NEW_MEMBER' => 0,
            'OUT_MEMBER' => 0,
        );

        $this->load->library(array(
            'DaouData'
        ));

		$this->load->business('company_tb_business');
        $company_code = $this->company_tb_business->getMap('c_code', 'c_id');

        $account_data = $this->daoudata->getData('MEMBER');
        $account_data = array_chunk($account_data, 20);
        $dept_data = $this->daoudata->getData('DEPT');
        

        if( ! is_array($account_data) || sizeof($account_data) < 1 ) {
            // GET FILE DATA FAIL;
            echo 'Get File data Fail!!';
            exit;
        }


        foreach($account_data as $rows) {

            foreach($rows as $row) {

                $root_code = $this->daoudata->getCompany($dept_data, $row['orgCode']);
                $company_id = isset($company_code[$root_code]) ? $company_code[$root_code] : 0;

                if( strlen($row['dutyName']) < 1 ) {
                    $pp_title = ' ';
                }else {
                    $pp_title = $row['dutyName'];
                }

                $set_params = array(
                    'pp_name'       => trim($row['userName']),
                    //'pp_emp_number' => trim($row['employeeNumber']),  // sync_empno() 에서 해당 데이터 기준으로 함. 
                    'pp_login_id'   => trim($row['loginId']),
                    'pp_email'      => trim($row['loginId']).'@cocen.com',
                    'pp_dept'       => $row['orgName'],
                    'pp_title'      => $pp_title,
                    'pp_company_id' => $company_id,
                    'pp_is_upsert'  => 1,
                );

                $params = array();
                $params['=']['pp_login_id'] = trim($row['loginId']);
                $extras = array();
                $pp_data = $this->people_tb_model->getList($params, $extras)->getData();

                if( sizeof($pp_data) < 1 ) {
                    // @INSERT
                    //echo 'INSERT'.PHP_EOL;
                    if( ! $this->people_tb_model->doInsert($set_params)->isSuccess() ) {
                        echo $this->people_tb_model->getErrorMsg().PHP_EOL;
                    }
                    $log_data['NEW_MEMBER'] = $log_data['NEW_MEMBER'] + 1;

                } else {
                    // @UPDATE
                    //echo 'UPDATE'.PHP_EOL;
                    $pp_data = array_shift($pp_data);
                    if( ! $this->people_tb_model->doUpdate($pp_data['pp_id'], $set_params)->isSuccess() ) {
                        echo $this->people_tb_model->getErrorMsg().PHP_EOL;
                    }
                }
            } // END_FOREACH @rows
        } // END_FOREACH @account_data


        //  pp_upsert == 0 인것들 OUTMEMBER 처리
        //echo 'OUTMEMBER Process'.PHP_EOL;

        $params = array();
        $params['<']['pp_is_upsert'] = 1;
        $params['=']['pp_status'] = 'ACTIVE';
        $extras = array();
        $outmembers = $this->people_tb_model->getList($params, $extras)->getData();
        //echo print_r($outmembers); //exit;

        foreach($outmembers as $mem) {
            $params = array();
            $params['=']['pim_people_id'] = $mem['pp_id'];
            $params['join']['ip_tb'] = 'ip_id = pim_ip_id';
            $extras = array();
            $extras['fields'] = array('ip_tb.*', 'people_ip_map_tb.*');

            $pim_data = array();
            $pim_data = $this->people_ip_map_tb_model->getList($params, $extras)->getData();
            //echo print_r($pim_data);  //exit;

            // @UPDATE pp_status = 'OUTMEMBER'
            $params = array();
            $params['pp_ip_history'] = serialize($pim_data);
            $params['pp_status'] = 'OUTMEMBER';
            $params['pp_outed_at'] = date('Y-m-d H:i:s');
            $this->people_tb_model->doUpdate($mem['pp_id'], $params);

            // @DELETE ip_tb, people_ip_map_tb
            foreach($pim_data as $pim) {
                $this->ip_tb_model->doDelete($pim['ip_id']);
                $this->people_ip_map_tb_model->doDelete($pim['pim_id']);
            }

            $log_data['OUT_MEMBER'] = $log_data['NEW_MEMBER'] + 1;
        }


        // @UPDATE is_upsert = 0;
        $where_params = array();
        $where_params['=']['pp_is_upsert'] = 1;
        $data_params = array();
        $data_params['pp_is_upsert'] = 0;
        $this->people_tb_model->doMultiUpdate($data_params, $where_params);


        // @HISTTORY
        $serial = array('params' => $log_data);

        $params = array();
        $params['h_loginid'] = 'SYSTEM';
        $params['h_name'] = 'SYSTEM';
        $params['h_act_mode'] = 'DAEMON';
        $params['h_act_key'] = 0;
        $params['h_serialize'] = serialize($serial);
        $params['h_act_table'] = 'sync_member';
        $this->history_tb_model->doInsert($params);
    }




    // http://mantis.joong.co.kr/view.php?id=135
    // IP 밀어넣기
    public function bulk_insert_member_ip() {

        $_FILE_PATH = "/home/team/82joong/html/itam/SMARTDEV/appdata/bulk_data/member_ip_bulk.csv";


		$this->load->model(array(
            'people_tb_model',
            'people_ip_map_tb_model',
            'ip_tb_model'
        ));


        $file = fopen($_FILE_PATH, 'r');
        exit;
 
        $cnt = 0;
        while (($row = fgetcsv($file)) !== false) {


            $cnt = $cnt + 1;

            /*
            Array(
                [0] => rock                 // Username
                [1] => rock@cocen.com       // Email
                [2] => cocen                // Groups
                [3] => 10.0.1.3             // SSLVPN PRI IP
                [4] =>                      // VDI PRI IP
                [5] => 14.129.1.3           // SSLVPN PUB IP
                [6] =>                      // VDI PUB IP
                [7] => 210.217.16.28        // GASAN
                [8] => 218.154.69.28        // PARC1
            )
            */

            echo print_r($row).PHP_EOL; //exit;


            if( strlen($row[0]) < 1 ) continue;


            $pp_data = array();
            $pp_data = $this->people_tb_model->get(array('pp_login_id' => $row[0]))->getData();
            //echo print_r($pp_data); //exit;

            if( ! is_array($pp_data) || sizeof($pp_data) < 1 ) {
                echo '[NOEXISTS] MEMBER '.$row[0].PHP_EOL; 
                continue;
            }

            
            // SSLVVPN PRI IP
            if( strlen(trim($row[3])) > 0 ) { 
            
                $params = array();
                $params['=']['ip_address'] = trim($row[3]);
                $vpn_ip_cnt = $this->ip_tb_model->getCount($params)->getData();
                if( $vpn_ip_cnt > 0 ) {
                    echo '[EXISTS] SSLVPN IP '.$row[3].PHP_EOL; 
                }else {

                    // @INSERT SSLVPN IP [ip_tb]
                    $params = array(
                        'ip_class_id'           => 197,
                        'ip_class_type'         => 'LOCAL',
                        'ip_class_category'     => 'PRIVATE',
                        'ip_address'            => trim($row[3]),
                        'ip_memo'               => '#SSLVPN_PRI',
                    );
                    if( ! $this->ip_tb_model->doInsert($params)->isSuccess() ) {
                        echo '[ERROR SSLVPN IP] '.$this->ip_tb_model->getErrorMsg().PHP_EOL;
                    }
                    $sslvpn_ip_id = $this->ip_tb_model->getData();
                    
                    // @INSERT SSLVPN IP [people_ip_map_tb]
                    $params = array(
                        'pim_people_id' => $pp_data['pp_id'],
                        'pim_ip_id'     => $sslvpn_ip_id
                    );
                    if( ! $this->people_ip_map_tb_model->doInsert($params)->isSuccess() ) {
                        echo '[ERROR SSLVPN IP MAP] '.$this->people_ip_map_tb_model->getErrorMsg().PHP_EOL;
                    }
                }
            } // END_IF SSLVPN



            // VDI PRI IP
            if( strlen(trim($row[4])) > 0 ) { 

                $params = array();
                $params['=']['ip_address'] = trim($row[4]);
                $vpn_ip_cnt = $this->ip_tb_model->getCount($params)->getData();
                if( $vpn_ip_cnt > 0 ) {
                    echo '[EXISTS] VDI IP '.$row[4].PHP_EOL; 
                }else {

                    // @INSERT VDI IP [ip_tb]
                    $params = array(
                        'ip_class_id'           => 200,
                        'ip_class_type'         => 'LOCAL',
                        'ip_class_category'     => 'PRIVATE',
                        'ip_address'            => trim($row[4]),
                        'ip_memo'               => '#VDI_PRI',
                    );
                    if( ! $this->ip_tb_model->doInsert($params)->isSuccess() ) {
                        echo '[ERROR VDI IP] '.$this->ip_tb_model->getErrorMsg().PHP_EOL;
                    }
                    $vdi_ip_id = $this->ip_tb_model->getData();
                    
                    // @INSERT VDI IP [people_ip_map_tb]
                    $params = array(
                        'pim_people_id' => $pp_data['pp_id'],
                        'pim_ip_id'     => $vdi_ip_id
                    );
                    if( ! $this->people_ip_map_tb_model->doInsert($params)->isSuccess() ) {
                        echo '[ERROR VDI IP MAP] '.$this->people_ip_map_tb_model->getErrorMsg().PHP_EOL;
                    }
                }
            } // END_IF VPN

            if( $cnt > 600 ) {
                break;
            }

        } // END WHILE @row
        fclose($file);
    }





    public function trasferISMStoArray() {

        $this->load->library(array(
            'CIDR',
        ));

        // hostname : 663 EA
        //$_FILE_PATH = "/home/team/82joong/html/itam/SMARTDEV/appdata/sync_data/20220712/ISMS_vm_guest_DB.txt";

        // hostname : 130 EA
        //$_FILE_PATH = "/home/team/82joong/html/itam/SMARTDEV/appdata/sync_data/20220712/ISMS_vm_guest_FIREWALL.txt";

        // hostname : 1444 EA 
        //$_FILE_PATH = "/home/team/82joong/html/itam/SMARTDEV/appdata/sync_data/20220712/ISMS_vm_guest_RADIUS.txt";

        // hostname : 133 EA 
        //$_FILE_PATH = "/home/team/82joong/html/itam/SMARTDEV/appdata/sync_data/20220712/ISMS_solo_RADIUS.txt";

        // hostname : 102 EA 
        $_FILE_PATH = "/home/team/82joong/html/itam/SMARTDEV/appdata/sync_data/20220712/ISMS_solo_FIREWALL.txt";




        //$data = file_get_contents($_FILE_PATH);
        //echo print_r($data);

        $data = array();
        $data = preg_split('/============================/', trim(file_get_contents($_FILE_PATH)));
        $data = array_filter($data);
        //echo print_r($data);

        $res = array();
        foreach($data as $row) {
            $r = preg_split('/\r\n|\r|\n/', trim($row));
            //echo print_r($r).PHP_EOL; 
            //exit;
            
            $tmp = array(
                'IP'    => '',
                'HOST'  => '',
                'OS'    => '',
                'WAS'   => '',
                'PHP'   => '',
                'MYSQL' => '',
                'POST'  => '',
            );
            foreach($r as $k=>$v) {

                $val = explode(': ', $v);
                if( sizeof($val) > 1 ) {
                    switch($val[0]) {
                        case 'hostname':
                            $tmp['HOST'] = $val[1];
                            break;
                        case 'php':
                            $tmp['PHP'] = 'PHP '.$val[1];
                            break;
                        case 'nginx':
                        case 'nginx version':
                            $tmp['WAS'] = $val[1];
                            break;
                        case 'mysql':
                            $tmp['MYSQL'] = ($val[1] == 'No MySQL') ? '' : 'MySQL '.$val[1];
                            break;
                        default:
                            break;
                    }

                }else {

                    if( $k == 0 ) {
                        if( $this->cidr->validIP(trim($v)) ) {
                            $tmp['IP'] = $v;
                        }else {
                            $tmp['IP'] = 'ERROR';
                        }
                    }
                    if( $k == 2 ) {
                        if( strpos($v, 'CentOS') !== false ) {
                            $tmp['OS'] = $v;
                        }else {
                            $tmp['OS'] = 'FreeBSD '.$v;
                        }
                    }
                    if( $k == 3 ) {
                        if( $v == 'No Web' ) {
                            $tmp['WAS'] = ''; 
                        }
                    }
                    if( strpos($v, 'postgres') !== false ) {
                        if( $v == 'No postgres' || $v == 'No postgress'  ) {
                            $tmp['POST'] = '';
                        }else {
                            $tmp['POST'] = 'PostgreSQL'.substr($v, strrpos($v, ' '));
                        }
                    }
                }
                
            } // END_FOREACH @r


            //echo print_r($tmp); exit;
            $res[] = $tmp;

        } // END_FOREACH @data


        //echo sizeof($res).PHP_EOL;
        //echo print_r($res);
        //exit;

        return $res;
    }



    public function upsertVMService() {

        exit;

		$this->load->model(array(
            'assets_model_tb_model',
            'direct_ip_map_tb_model',
            'ip_tb_model',
            'service_manage_tb_model',
        ));



        // DB 내에 존재 하지 않음 
        $no_exists = array();
        $no_ip = array();
        $find_host = array();

        $data = $this->trasferISMStoArray();
        $data = array_chunk($data, 10);


        foreach($data as $key=>$rows) {

            foreach($rows as $k=>$v) {
                echo print_r($v).PHP_EOL;


                // 1. Find IP
                $params = array();
                $params['=']['ip_address'] = $v['IP'];
                $params['join']['direct_ip_map_tb'] = 'ip_id = dim_ip_id';
                $params['join']['assets_model_tb'] = 'dim_assets_model_id = am_id';
                $extras = array();
                $extras['fields'] = array('am_id', 'am_name', 'ip_tb.*');
                $dim_data = $this->ip_tb_model->getList($params, $extras)->getData();
                //echo $this->ip_tb_model->getLastQuery(); exit;
                echo print_r($dim_data);
                //exit;


                if( sizeof($dim_data) > 0 ) {

                    $dim_data = array_shift($dim_data);
                    if( md5($v['HOST']) != md5($dim_data['am_name']) ) {
                        $params = array();
                        $params['am_name'] = $v['HOST'];
                        if( ! $this->assets_model_tb_model->doUpdate($dim_data['am_id'], $params)->isSuccess() ) {
                            echo '[HOST UPDATE] ERROR : '.$this->assets_model_tb_model->getError().PHP_EOL;
                        }else {
                            echo '[HOST UPDATE] : '.$dim_data['am_name'].' -> '.$v['HOST'].PHP_EOL;
                        }
                    }

                    echo '[IP SERVICE UPSERT]'.PHP_EOL;
                    $this->_upsertServiceManage($dim_data['am_id'], 0, $v); 

                }else {
                    $no_ip[] = $v;
                }

                //exit;
            } // END_FOREACH @rows
        } // END_FOREACH @@data

        echo '[NO IP ASSETS]'.PHP_EOL;
        echo print_r($no_ip);
    }



    // VM Guest UPDATE
    public function upsertVMGuestService() {

        exit;

		$this->load->model(array(
            'vmservice_tb_model',
            'vmservice_ip_map_tb_model',
            'ip_tb_model',
            'service_manage_tb_model',
        ));


        /* @data
        Array
        (
            [IP] => 10.100.82.237
            [HOST] => bank-db
            [OS] => FreeBSD 10.3-STABLE
            [WAS] =>
            [PHP] => PHP 5.2.16
            [MYSQL] => MySQL 5.0.51a-log
            [POST] =>
        )
        */


        // DB 내에 존재 하지 않음 
        $no_exists = array();
        $find_host = array();

        $data = $this->trasferISMStoArray();
        $data = array_chunk($data, 10);


        foreach($data as $key=>$rows) {

            foreach($rows as $k=>$v) {
                echo print_r($v).PHP_EOL;

                // 1. Find IP
                $params = array();
                $params['=']['ip_address'] = $v['IP'];
                $params['join']['vmservice_ip_map_tb'] = 'ip_id = vim_ip_id';
                $params['join']['vmservice_tb'] = 'vim_vmservice_id = vms_id';
                $extras = array();
                $extras['fields'] = array('vms_id', 'vms_assets_model_id', 'vms_name', 'ip_tb.*');
                $vim_data = $this->ip_tb_model->getList($params, $extras)->getData();
                //echo $this->ip_tb_model->getLastQuery(); exit;
                //echo print_r($vim_data);

                if( sizeof($vim_data) > 0 ) {

                    $vim_data = array_shift($vim_data);
                    if( md5($v['HOST']) != md5($vim_data['vms_name']) ) {
                        $params = array();
                        $params['vms_name'] = $v['HOST'];
                        if( ! $this->vmservice_tb_model->doUpdate($vim_data['vms_id'], $params)->isSuccess() ) {
                            echo '[HOST UPDATE] ERROR : '.$this->vmservice_tb_model->getError().PHP_EOL;
                        }else {
                            echo '[HOST UPDATE] : '.$vim_data['vms_name'].' -> '.$v['HOST'].PHP_EOL;
                        }
                    }

                    echo '[IP SERVICE UPSERT]'.PHP_EOL;
                    //$this->_upsertServiceManage($vim_data['vms_assets_model_id'], $vim_data['vms_id'], $v); 

                }else {


                    $vim_data = array();

                    // 1. Find vms_name 
                    $params = array();
                    $params['=']['vms_name'] = $v['HOST'];
                    $params['join']['vmservice_ip_map_tb'] = 'vms_id = vim_vmservice_id';
                    $params['join']['ip_tb'] = 'vim_ip_id = ip_id';
                    $extras = array();
                    $extras['fields'] = array('vms_id', 'vms_assets_model_id', 'vms_name', 'ip_tb.*');
                    $vim_data = $this->vmservice_tb_model->getList($params, $extras)->getData();

                    //echo $this->ip_tb_model->getLastQuery(); exit;
                    //echo print_r($vim_data);

                    if( sizeof($vim_data) >  1) {

                        // 중복 HOST 
                        $find_host[] = $v;

                    }else if( sizeof($vim_data) == 1 ) {

                        // NAT IP 확인
                        $vim_data = array_shift($vim_data);
                        $ips = explode('.', $vim_data['ip_address']);
                        $v_ips = explode('.', $v['IP']);

                        // @NAT 조건
                        if($ips[2] == $v_ips[2]) {

                            echo '[NAT IP SERVICE UPSERT]'.PHP_EOL;
                            //echo print_r($v).PHP_EOL;
                            //exit;
                            //$this->_upsertServiceManage($vim_data['vms_assets_model_id'], $vim_data['vms_id'], $v); 

                        }else {
                            $find_host[] = $v;
                        }

                    }else {
                        $no_exists[] = $v;
                    }
                } // END_IF @vim_data
            } // END_FOREACH @rows
        } // END_FOREACH @data 

                        
        echo '[FIND HOST NAME] : '.$v['HOST'].PHP_EOL;
        echo print_r($find_host).PHP_EOL;

        echo '[NO EXISTS ASSETS]'.PHP_EOL;
        echo print_r($no_exists).PHP_EOL;


        
        $this->insertNoExists($find_host);
    }



    private function _upsertServiceManage($assets_model_id, $vmservice_id, $data) {

        $params = array();
        $params['=']['sm_assets_model_id'] = $assets_model_id;
        $params['=']['sm_vmservice_id'] = $vmservice_id;
        $extras = array();
        $extras['fields'] = array('sm_id');
        $sm_data = $this->service_manage_tb_model->getList($params, $extras)->getData();
        //echo print_r($sm_data);


        // set service data
        $params = array();

        $db_data = array();
        foreach($data as $k=>$v) {

            switch($k) {
                case 'OS':
                    $params['sm_os_info'] = $v;
                    break;
                case 'WAS':
                    $params['sm_was_info'] = $v;
                    break;
                case 'PHP':
                    $params['sm_lang_info'] = $v;
                    break;
                case 'MYSQL':
                case 'POST':
                    $db_data[] = $v;
                    break;
                case 'USAGE':
                    $params['sm_usage'] = $v;
                    break;
                case 'DEPART':
                    $params['sm_manage_team'] = $v;
                    break;
                case 'MAIN':
                    $params['sm_master_manager'] = $v;
                    break;
                case 'SUB':
                    $params['sm_sub_manager'] = $v;
                    break;
                case 'KI':
                    $params['sm_secure_conf'] = $v;
                    break;
                case 'MU':
                    $params['sm_secure_inte'] = $v;
                    break;
                case 'GA':
                    $params['sm_secure_avail'] = $v;
                    break;
            } // END SWITCH
        }


        if( sizeof($db_data) > 0 ) {
            $params['sm_db_info'] = implode(', ', array_filter($db_data));
        }
        //echo print_r($params); exit;

        // @UPDATE [service_manage_tb]
        if( sizeof($sm_data) > 0 ) {
            $sm_data = array_shift($sm_data);
            if( ! $this->service_manage_tb_model->doUpdate($sm_data['sm_id'], $params)->isSuccess() ) {
                echo print_r($params).PHP_EOL;
                echo '[SERVICE UPDATE] ERROR : '.$this->service_manage_tb_model->getErrorMsg().PHP_EOL;
            }else {
                echo '[SERVICE UPDATE] : SUCCESS'.PHP_EOL;
            }

        // @INSERT [service_manage_tb]
        }else {

            $params['sm_assets_model_id'] = $assets_model_id;
            $params['sm_vmservice_id'] = $vmservice_id;
            if( ! $this->service_manage_tb_model->doInsert($params)->isSuccess() ) {
                echo print_r($params).PHP_EOL;
                echo '[SERVICE INSERT] ERROR : '.$this->service_manage_tb_model->getErrorMsg().PHP_EOL;
            }else {
                echo '[SERVICE INSERT] : SUCCESS'.PHP_EOL;
            }
        }
    }


    //private function _insertNoExists($data) {
    public function insertNoExists($nodata=array()) {


		$this->load->model(array(
            'ip_class_tb_model',
            'ip_tb_model',
            'assets_model_tb_model',
            'vmservice_tb_model',
            'vmservice_ip_map_tb_model',
            'service_manage_tb_model',
        ));

        $this->load->business(array(
            'ip_class_tb_business',
        ));


        /*
        $nodata = array(
            0 => array(
                'IP'    => '10.100.83.114',
                'HOST'  => 'premium214-db',
                'OS'    => 'FreeBSD 13.0-STABLE',
                'WAS'   => '',
                'PHP'   => 'PHP 5.3.29',
                'MYSQL' => 'MySQL 5.7.33-log',
                'POST'  => ''
            ),
            1 => array(
                'IP'    => '10.100.83.99',
                'HOST'  => 'premium199-db',
                'OS'    => 'FreeBSD 13.0-STABLE',
                'WAS'   => '',
                'PHP'   => 'PHP 5.3.29',
                'MYSQL' => 'MySQL 5.7.33-log',
                'POST'  => '',
            ),
        );
        */
        $nodata = $this->common->getDataByPK($nodata, 'IP');
        //echo print_r($nodata); exit;


        // VMWare to Assets : 109 EA 
        $_FILE_PATH = "/home/team/82joong/html/itam/SMARTDEV/appdata/sync_data/20220712/vm_to_assets_list.txt";


        $data = array();
        $data = preg_split('/\r\n|\r|\n/', trim(file_get_contents($_FILE_PATH)));
        $data = array_filter($data);
        //echo print_r($data); exit;

        foreach($data as $row) {
            $val = explode(', ', $row);
            //echo print_r($val); exit;

            //echo $row.PHP_EOL;

            $ip = trim($val[0]);
            $tag = trim($val[1]);

            if( ! isset($nodata[$ip]) ) {
                echo '[NO IP] '.$row.PHP_EOL;
                //exit;
                continue;
            }else {
                $vm = $nodata[$ip];
            }

            /*
            $ip_data = $this->ip_tb_model->get(array('ip_address' => $ip))->getData();
            if( is_array($ip_data) && sizeof($ip_data) > 0 ) {

                //echo '[REGISTERED] '.$row.PHP_EOL;
                //echo $row.PHP_EOL;

            }else {
                echo $row.PHP_EOL;
            }
            */


            $ip_class = $this->ip_class_tb_business->checkIPinClass(array('ip' => $ip));
            //echo print_r($ip_class).PHP_EOL;
            if( $ip_class['is_success'] == FALSE ) {
                echo '[NO IPCLASS] '.$row.PHP_EOL;
                //exit;
                continue;
            }
            $ip_class = $ip_class['msg'];
            

            $am_data = $this->assets_model_tb_model->get(array('am_serial_no' => $tag))->getData();
            //echo print_r($am_data).PHP_EOL;
            if( is_array($am_data) && sizeof($am_data) > 0 ) {

                // UNIQUE 
                $cnt = $this->vmservice_tb_model->getCount(array('=' => array('vms_name' => $vm['HOST'])))->getData();
                //echo $this->vmservice_tb_model->getLastQuery(); exit;
                if($cnt > 0) {
                    echo '[EXISTS vms_name] '.$row.PHP_EOL;
                    //exit;
                    continue;  
                }

                $params = array(
                    'vms_assets_model_id'   => $am_data['am_id'],
                    'vms_name'              => $vm['HOST'],
                );
                if( ! $this->vmservice_tb_model->doInsert($params)->isSuccess()) {
                    echo '[INSERT vmservice] ERROR'.$row.PHP_EOL;
                    echo $this->vmservice_tb_model->getErrorMsg().PHP_EOL;
                    //exit;
                    continue;  
                }else {
                    $act_key = $this->vmservice_tb_model->getData();

                    $params = array(
                        'ip_class_id'           => $ip_class['ipc_id'],
                        'ip_class_type'         => $ip_class['ipc_type'],
                        'ip_class_category'     => $ip_class['ipc_category'],
                        'ip_address'            => $ip,
                        'ip_memo'               => '',
                        'ip_created_at'         => date('Y-m-d H:i:s'),
                        'ip_updated_at'         => date('Y-m-d H:i:s'),
                    );
                    $ip_id = $this->ip_tb_model->doInsert($params)->getData();

                    $params = array(
                        'vim_vmservice_id'  => $act_key,
                        'vim_ip_id'         => $ip_id,
                    );
                    $log_array['params']['vmservice_ip_map_tb'] = $params;
                    $this->vmservice_ip_map_tb_model->doInsert($params);


                    echo '[SERVICE UPSERT]'.PHP_EOL;
                    echo print_r($vm).PHP_EOL;
                    //exit;
                    $this->_upsertServiceManage($am_data['am_id'], $act_key, $vm); 

                }

                
            }else {
                echo '[NO ASSETS] '.$row.PHP_EOL;
            }
            //exit;
        }
    }



    public function updateServiceInfo2021() {

        exit;

		$this->load->model(array(
            'assets_model_tb_model',
            'service_manage_tb_model',
            'vmservice_tb_model',
            'direct_ip_map_tb_model',
            'vmservice_ip_map_tb_model',
            'ip_tb_model',
        ));


        //$_FILE_PATH = "/home/team/82joong/html/itam/SMARTDEV/appdata/sync_data/20220718/2021_assets_bulk_update.CSV";
        //$_FILE_PATH = "/home/team/82joong/html/itam/SMARTDEV/appdata/sync_data/20220718/add_host_matching_0726.csv";
        //$_FILE_PATH = "/home/team/82joong/html/itam/SMARTDEV/appdata/sync_data/20220718/test.csv";

        $file = fopen($_FILE_PATH, 'r');
 
        $cnt = 0;
        $no_service = array();
        while (($row = fgetcsv($file)) !== false) {

            $cnt = $cnt + 1;
            $exists = FALSE;

            /*
            Array
            (
                [0] => 129.168.13.14        // IP 
                [1] => '080'                // 호스트명
                [2] => '080ARS연동서버'     // 사용용도
                [3] => '기획지원팀'         // 담당부서 및 팀
                [4] => '이한미과장'         // 자산운영자 (정)
                [5] => '김슬비대리'         // 자산운영자 (부)
                [6] => 2                    // 기밀성
                [7] => 2                    // 무결성
                [8] => 2                    // 가용성
            )
            */

            $row = array_map('trim', $row);

            foreach($row as $k=>&$v) {
                if($k > 0 & $k <= 5) {
                    $v= iconv("euc-kr", "utf-8", $v);
                }
            }

            $service_params = array(
                'USAGE'     => $row[2],
                'DEPART'    => $row[3],
                'MAIN'      => mb_substr($row[4], 0, 3),
                'SUB'       => mb_substr($row[5], 0, 3),
                'KI'        => $row[6],
                'MU'        => $row[7],
                'GA'        => $row[8],
            );
 

            echo print_r($row).PHP_EOL; //exit;
            echo print_r($service_params).PHP_EOL; //exit;

            // [assets_model_tb] 존재 확인
            $params = array();
            $params['=']['am_name'] = $row[1];
            $extras = array();
            $am_data = $this->assets_model_tb_model->getList($params, $extras)->getData();
            //echo print_r($am_data).PHP_EOL;

            if( sizeof($am_data) > 0 ) {
                
                $exists = TRUE;

                echo '[assets_model_tb]'.PHP_EOL;
                $am_data = array_shift($am_data);
                $this->_upsertServiceManage($am_data['am_id'], 0, $service_params);


            }else {

                echo '[vmservice_tb]'.PHP_EOL;

                // [vmservice_tb] 존재 확인
                $params = array();
                $params['=']['vms_name'] = $row[1];
                $extras = array();
                $vms_data = $this->vmservice_tb_model->getList($params, $extras)->getData();
                //echo print_r($vms_data).PHP_EOL;

                if( sizeof($vms_data) > 0 ) {

                    $exists = TRUE;
                    $vms_data = array_shift($vms_data);
                    $this->_upsertServiceManage($vms_data['vms_assets_model_id'], $vms_data['vms_id'], $service_params);
                }else {
                    /*
                    //echo '[NO SERVICE] : '.print_r($row).PHP_EOL;
                    $no_service[] = $row;
                    */
                }
            }

            if($exists == FALSE) {

                $hostname = 'NO_HOST';

                $params = array();
                $params['=']['ip_address'] = $row[0];
                $params['join']['direct_ip_map_tb'] = "ip_id = dim_ip_id";
                $params['join']['assets_model_tb'] = "am_id = dim_assets_model_id";
                $extras = array();
                $extras['fields'] = array('am_name');
                $dim_data = $this->ip_tb_model->getList($params, $extras)->getData();

                if(sizeof($dim_data) > 0) {
                    $dim_data = array_shift($dim_data);
                    $hostname = $dim_data['am_name'];
                }else {
            
                    $params = array();
                    $params['=']['ip_address'] = $row[0];
                    $params['join']['vmservice_ip_map_tb'] = "ip_id = vim_ip_id";
                    $params['join']['vmservice_tb'] = "vms_id = vim_vmservice_id";
                    $extras = array();
                    $extras['fields'] = array('vms_name');
                    $vim_data = $this->ip_tb_model->getList($params, $extras)->getData();

                    if(sizeof($vim_data) > 0) {
                        $vim_data = array_shift($vim_data);
                        $hostname = $vim_data['vms_name'];
                    }
                }

                $row[9] = $hostname;
                $no_service[] = $row;
        
            }
            //exit;
            //if( $cnt >= 5 ) break;

        } // END While

        echo 'TOTAL ROWS : '.$cnt,PHP_EOL;
        echo PHP_EOL.PHP_EOL;
        echo '[NO SERVICE] : '.PHP_EOL;
        //echo print_r($no_service).PHP_EOL;


        foreach($no_service as $srv) {
            echo implode(',', $srv).PHP_EOL;
        }

    }


    public function updateManager() {

        exit;

		$this->load->model(array(
            'assets_model_tb_model',
            'vmservice_tb_model',
            'service_manage_tb_model',
        ));


        /*
        $params = array();
        $params['join']['service_manage_tb'] = 'sm_assets_model_id = am_id';
        $params['raw'] = array('am_name LIKE "premium%" OR am_name LIKE "special%"');
        $extras = array();
        $extras['fields'] = array('am_id','am_name','service_manage_tb.*');
        $am_data = $this->assets_model_tb_model->getList($params, $extras)->getData();

        foreach($am_data as $am) {

            echo $am['am_name'].PHP_EOL;
            echo $am['sm_id'].PHP_EOL;

            $params = array();
            $params['sm_usage'] = '메이크샵쇼핑몰서비스';
            $params['sm_manage_team'] = '메이크샵개발실';
            $params['sm_master_manager'] = '안채준';
            $params['sm_sub_manager'] = '유성종';
            $params['sm_secure_conf'] = 3;
            $params['sm_secure_inte'] = 3;
            $params['sm_secure_avail'] = 3;
            $params['sm_important_score'] = 9;
            $params['sm_important_level'] = 1;
            $this->service_manage_tb_model->doUpdate($am['sm_id'], $params);
            //exit;

            echo PHP_EOL;
        }
        */



        $params = array();
        $params['join']['service_manage_tb'] = 'sm_vmservice_id = vms_id';
        $params['raw'] = array('(vms_name LIKE "premium%" OR vms_name LIKE "special%") AND sm_manage_team = ""');
        $extras = array();
        $extras['fields'] = array('vms_id','vms_name','service_manage_tb.*');
        $am_data = $this->vmservice_tb_model->getList($params, $extras)->getData();
        //echo $this->vmservice_tb_model->getLastQuery(); exit;

        foreach($am_data as $am) {

            echo $am['vms_name'].PHP_EOL;
            //echo $am['sm_id'].PHP_EOL;

            $params = array();
            $params['sm_usage'] = '메이크샵쇼핑몰서비스';
            $params['sm_manage_team'] = '메이크샵개발실';
            $params['sm_master_manager'] = '안채준';
            $params['sm_sub_manager'] = '유성종';
            $params['sm_secure_conf'] = 3;
            $params['sm_secure_inte'] = 3;
            $params['sm_secure_avail'] = 3;
            $params['sm_important_score'] = 9;
            $params['sm_important_level'] = 1;

            if( 
                strpos($am['vms_name'], 'slave') !== FALSE || 
                strpos($am['vms_name'], 'proxy') !== FALSE || 
                strpos($am['vms_name'], 'event') !== FALSE || 
                strpos($am['vms_name'], 'dbgate') !== FALSE 
            ) {
                $params['sm_secure_conf'] = 2;
                $params['sm_secure_inte'] = 2;
                $params['sm_secure_avail'] = 2;
                $params['sm_important_score'] = 6;
                $params['sm_important_level'] = 2;
            }
            //echo print_r($params).PHP_EOL;
            //$this->service_manage_tb_model->doUpdate($am['sm_id'], $params);
            //exit;
            //echo PHP_EOL;
        }
    }



    public function check_ip_map() {

		$this->load->model(array(
            'assets_model_tb_model',
            'assets_ip_map_tb_model',
            'direct_ip_map_tb_model',
            'people_tb_model',
            'people_ip_map_tb_model',
            'vmservice_tb_model',
            'vmservice_ip_map_tb_model',
            'ip_tb_model'
        ));


        for($offset=0; $offset<=10000; $offset = $offset + 100) {

            echo $offset.PHP_EOL.PHP_EOL;

            $params = array();
            //$params['=']['ip_id'] = 142;
            $extras = array();
            //$extras['fields'] = array('ip_id', 'ip_address');
            $extras['order_by'] = array('ip_id ASC');
            $extras['offset'] = $offset;
            $extras['limit'] = 100;

            $ip_data = $this->ip_tb_model->getList($params, $extras)->getData();
            //echo print_r($ip_data);

            foreach($ip_data as $row) {

                $params = array();
                $params['join']['assets_model_tb'] = 'am_id = aim_assets_model_id';
                $params['=']['aim_ip_id'] = $row['ip_id'];
                $aim_cnt = $this->assets_ip_map_tb_model->getCount($params)->getData();
                //echo '[assets_ip_map_tb_model] : '.$aim_cnt.PHP_EOL;


                $params = array();
                $params['join']['assets_model_tb'] = 'am_id = dim_assets_model_id';
                $params['=']['dim_ip_id'] = $row['ip_id'];
                $dim_cnt = $this->direct_ip_map_tb_model->getCount($params)->getData();
                //echo '[direct_ip_map_tb_model] : '.$dim_cnt.PHP_EOL;


                $params = array();
                $params['join']['people_tb'] = 'pp_id = pim_people_id';
                $params['=']['pim_ip_id'] = $row['ip_id'];
                $pim_cnt = $this->people_ip_map_tb_model->getCount($params)->getData();
                //echo '[people_ip_map_tb_model] : '.$pim_cnt.PHP_EOL;


                $params = array();
                $params['join']['vmservice_tb'] = 'vms_id = vim_vmservice_id';
                $params['=']['vim_ip_id'] = $row['ip_id'];
                $vim_cnt = $this->vmservice_ip_map_tb_model->getCount($params)->getData();
                //echo '[vmservice_ip_map_tb_model] : '.$vim_cnt.PHP_EOL;


                if( ($aim_cnt + $dim_cnt + $pim_cnt + $vim_cnt) == 0 ) {
                    echo print_r($row).PHP_EOL;
                    $this->ip_tb_model->doDelete($row['ip_id']);
                }
            } // END_FOREACH

        } // END_FOR
    }



    public function sync_empno() {


		$this->load->model(array(
            'people_tb_model',
            'history_tb_model'
        ));

        $GW_DB = $this->load->database('groupware', TRUE);
        $count_query = 'SELECT * FROM v_sso';

        if ($GW_DB->query($count_query)) {
            $query = $GW_DB->query($count_query);
            $row_cnt = $query->num_rows();
        }else {
            echo 'FAIL DB';
            $GW_DB->close();
            exit;
        }

        if($row_cnt < 1) {
            $GW_DB->close();
            exit;
        }

        $limit = 50;
        for($i = 0; $i <= $row_cnt; $i = $i + $limit) {

            //echo 'CNT : '.$i.PHP_EOL.PHP_EOL;

            $list_query = 'SELECT userid, userno FROM v_sso ORDER BY guid ASC LIMIT '.$i.', '.$limit;
            $query = $GW_DB->query($list_query);
            $data = array();
            foreach($query->result_array() as $row) {
                $data[$row['userid']] = $row['userno'];
            }

            $pp_data = array();
            if( sizeof($data) > 0 ) {
                $params = array();
                $params['in']['pp_login_id'] = array_keys($data);
                $extras = array();
                $extras['fields'] = array('pp_id','pp_login_id', 'pp_emp_number');
                $pp_data = $this->people_tb_model->getList($params, $extras)->getData();

                
                foreach($pp_data as $pp) {

                    if( $pp['pp_emp_number'] != $data[$pp['pp_login_id']] ) {

                        $params = array();
                        $params['pp_emp_number'] = $data[$pp['pp_login_id']];
                        $this->people_tb_model->doUpdate($pp['pp_id'], $params)->getData();
                        //echo $pp['pp_login_id'].PHP_EOL;

                        $serial = array(
                            'prev_data'  => $pp,
                            'params'    => $params
                        );
                        $params = array();
                        $params['h_loginid'] = 'SYSTEM';
                        $params['h_name'] = 'SYSTEM';
                        $params['h_act_mode'] = 'UPDATE';
                        $params['h_act_key'] = $pp['pp_id'];
                        $params['h_serialize'] = serialize($serial);
                        $params['h_act_table'] = 'people_tb';
                        $this->history_tb_model->doInsert($params);

                    } // END_IF
                } // END_FOREACH @pp_data
            } // END_IF
        } // END_FOR @i


        $GW_DB->close();
    }
}
