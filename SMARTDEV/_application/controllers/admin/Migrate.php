<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include_once dirname(__FILE__).'/Base_admin.php';

class Migrate extends Base_admin {


    public function labMigration() {

        for($i=1; $i<9; $i++) {
            $req = array(
                'offset' => $i
            ); 
            $this->labDataTransfer($req);
            exit;
        }

    }


    public function labDataTransfer($req=array()) {

        if( ! isset($req['offset']) ) {
            $req['offset'] = 0;
        }


		$this->load->model(array(
            'lab_tb_model',
            'models_tb_model',
            'assets_model_tb_model',
            'ip_class_tb_model',
            'ip_tb_model',
            'rack_tb_model',
            'fieldset_tb_model',
            'models_custom_fields_tb_model',
            'direct_ip_map_tb_model',
            'assets_ip_map_tb_model',
            'vmservice_tb_model',
            'vmservice_ip_map_tb_model',
        ));

		$this->load->business(array(
            'ip_class_tb_business',
            'custom_value_tb_business'
        ));
        $this->load->library('CIDR');
        $this->load->helper('file');


        // 실패 IDS 제외
        $invalid_ids = get_filenames(LOG_FILE_PATH.'/lab_migration/');
        //echo print_r($invalid_ids); //exit;
        $not_in_ids = array();
        foreach($invalid_ids as $file) {
            $id = str_replace('.info','',$file);
            $not_in_ids[] = $id;
        }
        //echo print_r($not_in_ids); exit;


        // sw_% => 스위치 일단 제외
        $raw_query = '';
        $raw_query = 'lab_owner != "E" AND ';               // 에누리 제외
        $raw_query .= 'lab_server != "DL360 Gen9" AND ';    // 에누리 장비 제외
        $raw_query .= 'lab_rack <> "" AND ';
        $raw_query .= 'lab_servicetag <> ""';
        //$raw_query .= 'lab_ip NOT LIKE "58.%"';

        $params = array();
        //$params['=']['lab_owner'] = '';
        if(sizeof($not_in_ids) > 0) {
            $params['not in']['lab_id'] = $not_in_ids;
        }
        $params['raw'] = array($raw_query);
        //$params['=']['lab_id'] = 5550;

        $extras = array();
        /*
        $extras['fields'] = array(
            'lab_id', 'lab_ip', 'lab_remoteip', 'lab_name', 'lab_servicetag', 'lab_server', 'lab_memo' 
        );
        */
        $extras['order_by'] = array('lab_id ASC');
        //$extras['offset'] = $req['offset'];
        //$extras['offset'] = 0;
        //$extras['limit'] = 100;


        //$tt_cnt = $this->lab_tb_model->getCount($params)->getData();
        //echo 'Total Count : '.$tt_cnt.PHP_EOL;

        $st_data = $this->lab_tb_model->getList($params, $extras)->getData();
        //echo $this->lab_tb_model->getLastQuery();
        //echo print_r($st_data); exit;
        echo '[lab] Count : '.sizeof($st_data).PHP_EOL;
        $st_data = $this->common->getDataByPK($st_data, 'lab_servicetag');
        $lab_tags = array_keys($st_data);
        //echo print_r($lab_tags); //exit;
        //echo print_r(array_keys($this->common->getDataByPK($st_data, 'lab_id'))); exit;

        $params = array();
        $params['in']['am_serial_no'] = $lab_tags;
        $extras = array();
        $extras['fields'] = array('am_serial_no');
        $am_data = $this->assets_model_tb_model->getList($params, $extras)->getData();

        $am_tags = array_keys($this->common->getDataByPK($am_data, 'am_serial_no'));
        $tags = array_diff($lab_tags, $am_tags);
        //echo print_r($tags);
        echo 'DIFF Servicetag : '.sizeof($tags).PHP_EOL.PHP_EOL; //exit;

        //return;
        //exit;
        //echo 'TEST'; exit;

        $company_data = array(
            ''  => 1,           // NULL 도 KOREACENTER
            'K' => 1,
            'S' => 2,
            'M' => 3,
            'T' => 4
        );



        foreach($tags as $tag) {


            $chkVal = array();

            $lab_data = $st_data[$tag];

            echo '[DATA] LAB1 DATA :';
            echo print_r($lab_data).PHP_EOL.PHP_EOL; //exit;
            

            $lab_data['lab_remoteip'] = str_replace('*','',$lab_data['lab_remoteip']);
            $lab_data['lab_remoteip'] = str_replace('(o)','',$lab_data['lab_remoteip']);
            $lab_data['lab_remoteip'] = str_replace('(o','',$lab_data['lab_remoteip']);



            // servicetag 검증=========================================================
            echo '[VALIDATION] servicetag :';
            $params = array();
            $params['=']['am_serial_no'] = strtoupper($lab_data['lab_servicetag']);
            $cnt_servicetag = $this->assets_model_tb_model->getCount($params)->getData();
            if($cnt_servicetag > 0) {
                $chkVal['servicetag'] = false;
                echo 'Exists Service Tag'.PHP_EOL.PHP_EOL;
                $this->_write_invalid_log($lab_data, $chkVal);
                continue;
            }
            echo 'OK'.PHP_EOL.PHP_EOL;


            // company_id MAP =========================================================
            echo '[VALIDATION] company :';
            if( ! in_array($lab_data['lab_owner'], array_keys($company_data)) ) {
                $chkVal['company'] = false;
                echo 'NO Exists Company ID'.PHP_EOL.PHP_EOL;
                $this->_write_invalid_log($lab_data, $chkVal);
                continue;
            }
            echo 'OK'.PHP_EOL.PHP_EOL; //exit;


            // iDrac IP=========================================================
            echo '[VALIDATION] iDrac IP :';
            $params = array(
                'ip'        => $lab_data['lab_remoteip'],
                'set_valid' => 'idrac'
            );
            $valid_idrac = array();
            $valid_idrac = $this->ip_class_tb_business->checkIPinClass($params);
            //echo print_r($valid_idrac); exit;
            if( $valid_idrac['is_success'] == FALSE ) {
                $chkVal['idrac_ip'] = $valid_idrac;
                echo 'Invalid iDrac IP'.PHP_EOL.PHP_EOL;
                $this->_write_invalid_log($lab_data, $chkVal);
                continue;
            }
            $valid_idrac = $valid_idrac['msg']; 
            echo print_r($valid_idrac).PHP_EOL.PHP_EOL; 
            //exit;


            // IP 확인=========================================================
            echo '[VALIDATION] IP :';

            $raw_query = 'INET_ATON(ipc_start) <= INET_ATON("'.$lab_data['lab_ip'].'")';
            $raw_query .= ' AND INET_ATON(ipc_end) >= INET_ATON("'.$lab_data['lab_ip'].'")';
            $params = array();
            $params['raw'] = array($raw_query);
            $extras = array();
            $ipc_data = $this->ip_class_tb_model->getList($params, $extras)->getData();
            if( sizeof($ipc_data) < 1 || sizeof($ipc_data) > 1 ) {
                $chkVal['lab_ip'] = false;
                echo 'NO Exists IP CLASS'.PHP_EOL.PHP_EOL;
                $this->_write_invalid_log($lab_data, $chkVal);
                continue;

            }else {
                $ipc_data = array_shift($ipc_data);
            }
            echo print_r($ipc_data).PHP_EOL.PHP_EOL;



            // RACK 확인=========================================================
            echo '[VALIDATION] RACK :';

            if( strlen(trim($lab_data['lab_rack'])) < 1 ) {
                $chkVal['rack'] = false;
                echo 'NO Exists RACK'.PHP_EOL.PHP_EOL;
                $this->_write_invalid_log($lab_data, $chkVal);
                continue;
            }

            $rack = explode('-', $lab_data['lab_rack']);
            if(sizeof($rack) > 2) {
                $section = $rack[1];
                $frame = $rack[2];
            }else {
                $section = $rack[0];
                $frame = $rack[1];
            }


            $params = array();
            $params['=']['r_location_id'] = $ipc_data['ipc_location_id'];
            $params['=']['r_section'] = $section;
            $params['=']['r_frame'] = $frame;
            $rack_data = $this->rack_tb_model->getList($params, array())->getData();
            echo print_r($rack_data); //exit;
            if(sizeof($rack_data) < 1) {
                $chkVal['rack'] = false;
                echo 'NO Exists RACK'.PHP_EOL.PHP_EOL;
                $this->_write_invalid_log($lab_data, $chkVal);
                continue;
            }else {
                $rack_data = array_shift($rack_data);
            }
            echo print_r($rack_data).PHP_EOL.PHP_EOL;



            // MODEL 확인=========================================================
            echo '[VALIDATION] Assets Model :';
            $params = array();
            $params['=']['m_model_name'] = trim($lab_data['lab_server']);
            $model_data = $this->models_tb_model->getList($params)->getData();
            echo "MODEL DATA".PHP_EOL;
            if(sizeof($model_data) < 1) {
                $chkVal['model'] = false;
                echo 'NO Exists MODEL'.PHP_EOL.PHP_EOL;
                $this->_write_invalid_log($lab_data, $chkVal);
                continue;
            }else {
                $model_data = array_shift($model_data);
            }
            echo print_r($model_data).PHP_EOL.PHP_EOL;



            // model_custom_fields_tb=========================================================
            echo '[VALIDATION] Model Custom Fields :';
            $params = array();
            $params['=']['mcf_models_id'] = $model_data['m_id'];
            $extras = array();
            $extras['order_by'] = array('mcf_id ASC');
            $mcf_data = $this->models_custom_fields_tb_model->getList($params, $extras)->getData();
            //echo print_r($mcf_data); exit;

            if(sizeof($mcf_data) < 1) {
                $chkVal['model_custom_fields'] = false;
                echo 'NO Exists MODEL CUSTOM FIELDS'.PHP_EOL.PHP_EOL;
                $this->_write_invalid_log($lab_data, $chkVal);
                continue;
            }
            //echo print_r($mdf_data).PHP_EOL.PHP_EOL;


            $raid_type = $this->_get_raid_type($lab_data);
            $disk_type = $this->_get_disk_type($lab_data);

            
            $field_params = array();
            $field_params['fieldset_id'] = $model_data['m_fieldset_id'];
            foreach($mcf_data as $mcf) {

                $mcf_value = $mcf['mcf_value'];
                switch($mcf['mcf_name']) {
                    case 'CPU':
                        $mcf_value = $lab_data['lab_cpu'];
                        break;
                    case 'MEMORY':
                        $mcf_value = $lab_data['lab_ram'];
                        break;
                    case 'DISK':
                        $mcf_value = $disk_type.' '.$lab_data['lab_hdd'];
                        break;
                    case 'RAID INFO':
                        $mcf_value = $raid_type;
                        break;
                    default:
                        break;
                }
                $field_params['fields'][$mcf['mcf_id']] = trim($mcf_value); 
            } 
            echo "FIELD PARAMS".PHP_EOL;
            echo print_r($field_params).PHP_EOL.PHP_EOL;
            //exit;




            // IS VMWare========================================================= 
            echo '[DATA] WMWare :';
            $params = array();
            $params['=']['lab_remoteip'] = $lab_data['lab_ip'];
            $extras = array();
            $extras['fields'] = array(
                'lab_id', 'lab_ip', 'lab_remoteip', 'lab_name', 'lab_servicetag', 'lab_server', 'lab_memo' 
            );
            $extras['order_by'] = array('INET_ATON(lab_ip) ASC');

            $vm_data = $this->lab_tb_model->getList($params, $extras)->getData();
            //$vm_data = $this->common->getDataByDuplPK($vm_data, 'lab_remoteip');
            echo print_r($vm_data); //exit;



            if(sizeof($vm_data) < 1) {

                echo 'Direct'.PHP_EOL;

                // Direct IP=========================================================
                if($ipc_data['ipc_category'] == 'VMWARE') {

                    echo '[VALIDATION] ZERO VMWARE IP :';
                    $params = array(
                        'ip'        => $lab_data['lab_ip'],
                        'set_valid' => 'vmware'
                    );
                    $valid_zero_vmware= array();
                    $valid_zero_vmware = $this->ip_class_tb_business->checkIPinClass($params);
                    if( $valid_zero_vmware['is_success'] == FALSE ) {
                        $chkVal['direct_ip'] = false;
                        echo 'Invalid Zero VMWare IP'.PHP_EOL.PHP_EOL;
                        $this->_write_invalid_log($lab_data, $chkVal);
                        continue;
                    }
                    $valid_zero_vmware = $valid_zero_vmware['msg'];
                    echo print_r($valid_zero_vmware).PHP_EOL.PHP_EOL; //exit;

                } else {
                    echo '[VALIDATION] Direct IP :';
                    $params = array(
                        'ip'        => $lab_data['lab_ip'],
                        'set_valid' => 'direct'
                    );
                    $valid_direct = array();
                    $valid_direct = $this->ip_class_tb_business->checkIPinClass($params);
                    if( $valid_direct['is_success'] == FALSE ) {
                        $chkVal['direct_ip'] = false;
                        echo 'Invalid Direct IP'.PHP_EOL.PHP_EOL;
                        $this->_write_invalid_log($lab_data, $chkVal);
                        continue;
                    }
                    $valid_direct = $valid_direct['msg'];
                    echo print_r($valid_direct).PHP_EOL.PHP_EOL; //exit;
                }

            }else {

                echo 'VMWare'.PHP_EOL;
            
                // VMware IP=========================================================
                echo '[VALIDATION] VMWARE IP :';
                $params = array(
                    'ip'        => $lab_data['lab_ip'],
                    'set_valid' => 'vmware'
                );
                $valid_vmware = array();
                $valid_vmware = $this->ip_class_tb_business->checkIPinClass($params);
                //echo print_r($valid_vmware); exit;
                if( $valid_vmware['is_success'] == FALSE ) {
                    $chkVal['vmware_ip'] = false;
                    echo 'Invalid VMWARE IP'.PHP_EOL.PHP_EOL;
                    $this->_write_invalid_log($lab_data, $chkVal);
                    continue;
                }
                $valid_vmware = $valid_vmware['msg'];
                echo print_r($valid_vmware).PHP_EOL.PHP_EOL; //exit;



                echo '[VALIDATION] VMSERVICE IP :';
                $valid_vmservice = array();
                $false_valid = TRUE;
                foreach($vm_data as $vm) {
                    $params = array(
                        'ip'        => $vm['lab_ip'],
                        'set_valid' => 'vmservice'
                    );
                    $valid_rea = array();
                    $valid_res = $this->ip_class_tb_business->checkIPinClass($params);

                    if($valid_res['is_success'] == false) {
                        $false_valid = FALSE;
                        $valid_res['msg'] = '['.$vm['lab_ip'].'] '.$valid_res['msg'];
                    }
                    $valid_vmservice[$vm['lab_id']] = $valid_res['msg'];
                }

                if( $false_valid == FALSE ) {
                    $chkVal['vmservice_ip'] = $valid_vmservice;
                    echo 'Invalid VMSERVICE IP'.PHP_EOL.PHP_EOL;
                    $this->_write_invalid_log($lab_data, $chkVal);
                    continue;

                }
                echo print_r($valid_vmservice).PHP_EOL.PHP_EOL; //exit;
            }
            //exit;


            // 입력을 위한 Validation 완료
            // =============================================================================

            // 데이터에 대한 검증 완료하에 아래는 INSERT 수행
            $res_name = $this->_get_name($lab_data);
            $replace_words = array('<br />','<br>','<br/>','/');
            foreach($replace_words as $word) {
                $lab_data['lab_memo'] = str_replace($word, PHP_EOL,$lab_data['lab_memo']);
            }
            
            $memo = '';
            //$memo .= '[RemoteIP] '.$lab_data['lab_remoteip'].PHP_EOL;
            $memo .= $lab_data['lab_memo'].PHP_EOL.$res_name['memo'];



            // INSERT [assets_model_tb]
            echo '[INSERT] assets_model_tb :';
            $in_params = array(
                'am_company_id'     => $company_data[$lab_data['lab_owner']],
                'am_models_id'      => $model_data['m_id'], 
                'am_models_name'    => $lab_data['lab_server'], 
                'am_status_id'      => 4,                                                 // 입고
                'am_location_id'    => $ipc_data['ipc_location_id'], 
                'am_rack_id'        => $rack_data['r_id'],
                'am_rack_code'      => $rack_data['r_code'],
                'am_rack_order'     => 0,                                                 // TODO. 
                'am_assets_type_id' => $model_data['m_category_id'],
                'am_serial_no'      => $lab_data['lab_servicetag'], 
                'am_name'           => $res_name['name'],
                'am_memo'           => $memo, 
                'am_vmware_name'    => (sizeof($vm_data) > 0) ? $res_name['name'] : '',
                'am_migration'      => 'LAB1',
                'am_migration_at'   => date('Y-m-d H:i:s', strtotime($lab_data['lab_regdate'])),
                'am_created_at'     => date('Y-m-d H:i:s'), 
                'am_updated_at'     => date('Y-m-d H:i:s'), 
            );
            //echo print_r($in_params); exit;
            if( ! $this->assets_model_tb_model->doInsert($in_params)->isSuccess()) {
                echo $this->assets_model_tb_model->getErrorMsg();
                exit;
            }
			$am_id = $this->assets_model_tb_model->getData();
            echo '[am_id]: '.$am_id.PHP_EOL.PHP_EOL;


            // INSERT [custom_value_tb]
            $res = $this->custom_value_tb_business->insertCustomValue($field_params, $in_params, $am_id);


            // INSERT [idrac ip]  
            echo '[INSERT] ip_tb(iDrac) :';
            $params = array(
                'ip_class_id'       => $valid_idrac['ipc_id'],
                'ip_class_type'     => $valid_idrac['ipc_type'],
                'ip_class_category' => $valid_idrac['ipc_category'],
                'ip_address'        => $lab_data['lab_remoteip'],
                'ip_memo'           => '',
                'ip_created_at'     => date('Y-m-d H:i:s'), 
                'ip_updated_at'     => date('Y-m-d H:i:s')
            );
            if( ! $this->ip_tb_model->doInsert($params)->isSuccess() ) {
                echo $this->ip_tb_model->getErrorMsg().PHP_EOL.PHP_EOL; 
                exit;
            }else {
                $idrac_ip_id = $this->ip_tb_model->getData();
                $params = array(
                    'aim_assets_model_id'   => $am_id, 
                    'aim_ip_id'             => $idrac_ip_id,
                );
                $this->assets_ip_map_tb_model->doInsert($params);
                echo '[idrac_ip_id]: '.$idrac_ip_id.PHP_EOL;
                echo '[aim_id]: '.$this->assets_ip_map_tb_model->getData().PHP_EOL;
            }
            echo PHP_EOL;


            if(sizeof($vm_data) < 1) {


                if($ipc_data['ipc_category'] == 'VMWARE') {

                    // INSERT ZERO MWARE IP
                    echo '[INSERT] ip_tb(VMWARE) :'.PHP_EOL;
                    $params = array(
                        'ip_class_id'       => $valid_zero_vmware['ipc_id'],
                        'ip_class_type'     => $valid_zero_vmware['ipc_type'],
                        'ip_class_category' => $valid_zero_vmware['ipc_category'],
                        'ip_address'        => $lab_data['lab_ip'],
                        'ip_memo'           => '',
                        'ip_created_at'     => date('Y-m-d H:i:s'), 
                        'ip_updated_at'     => date('Y-m-d H:i:s')
                    );
                    if( ! $this->ip_tb_model->doInsert($params)->isSuccess() ) {
                        echo $this->ip_tb_model->getErrorMsg().PHP_EOL.PHP_EOL; 
                        exit;
                    }else {
                        $vmware_ip_id = $this->ip_tb_model->getData();
                        $params = array(
                            'aim_assets_model_id'   => $am_id, 
                            'aim_ip_id'             => $vmware_ip_id,
                        );
                        $this->assets_ip_map_tb_model->doInsert($params);
                        echo '[vmware_ip_id]: '.$vmware_ip_id.PHP_EOL;
                        echo '[aim_id]: '.$this->assets_ip_map_tb_model->getData().PHP_EOL;
                    }

                }else {
                    // INSERT Direct IP
                    echo '[INSERT] ip_tb(Direct) :';
                    $params = array(
                        'ip_class_id'       => $valid_direct['ipc_id'],
                        'ip_class_type'     => $valid_direct['ipc_type'],
                        'ip_class_category' => $valid_direct['ipc_category'],
                        'ip_address'        => $lab_data['lab_ip'],
                        'ip_memo'           => '',
                        'ip_created_at'     => date('Y-m-d H:i:s'), 
                        'ip_updated_at'     => date('Y-m-d H:i:s')
                    );
                    if( ! $this->ip_tb_model->doInsert($params)->isSuccess() ) {
                        echo $this->ip_tb_model->getErrorMsg().PHP_EOL.PHP_EOL; 
                        exit;
                    }else {
                        $direct_ip_id = $this->ip_tb_model->getData();
                        $params = array(
                            'dim_assets_model_id'   => $am_id, 
                            'dim_ip_id'             => $direct_ip_id,
                        );
                        $this->direct_ip_map_tb_model->doInsert($params);
                        echo '[direct_ip_id]: '.$direct_ip_id.PHP_EOL;
                        echo '[dim_id]: '.$this->direct_ip_map_tb_model->getData().PHP_EOL;
                    }
                }
                echo PHP_EOL;

            }else {
                // INSERT VMWARE IP
                echo '[INSERT] ip_tb(VMWARE) :'.PHP_EOL;
                $params = array(
                    'ip_class_id'       => $valid_vmware['ipc_id'],
                    'ip_class_type'     => $valid_vmware['ipc_type'],
                    'ip_class_category' => $valid_vmware['ipc_category'],
                    'ip_address'        => $lab_data['lab_ip'],
                    'ip_memo'           => '',
                    'ip_created_at'     => date('Y-m-d H:i:s'), 
                    'ip_updated_at'     => date('Y-m-d H:i:s')
                );
                if( ! $this->ip_tb_model->doInsert($params)->isSuccess() ) {
                    echo $this->ip_tb_model->getErrorMsg().PHP_EOL.PHP_EOL; 
                    exit;
                }else {
                    $vmware_ip_id = $this->ip_tb_model->getData();
                    $params = array(
                        'aim_assets_model_id'   => $am_id, 
                        'aim_ip_id'             => $vmware_ip_id,
                    );
                    $this->assets_ip_map_tb_model->doInsert($params);
                    echo '[vmware_ip_id]: '.$vmware_ip_id.PHP_EOL;
                    echo '[aim_id]: '.$this->assets_ip_map_tb_model->getData().PHP_EOL;
                }
                echo PHP_EOL;


                // INSERT VMService
                foreach($vm_data as $vm) {

                    echo '[INSERT] vmservice_tb :';

                    $vm['lab_memo'] = str_replace('<br>',PHP_EOL,$vm['lab_memo']);
                    $name = explode(' ', $vm['lab_name']);
                    $memo = $vm['lab_memo'].PHP_EOL.implode(array_slice($name, 1));
                    $params = array(
                        'vms_assets_model_id' => $am_id,
                        'vms_alias_id' => 0,
                        'vms_name' => $name[0],
                        'vms_memo' => $memo, 
                        'vms_created_at' => date('Y-m-d H:i:s'),
                        'vms_updated_at' => date('Y-m-d H:i:s'),
                    );

                    if( ! $this->vmservice_tb_model->doInsert($params)->isSuccess() ) {
                        echo $this->vmservice_tb_model->getErrorMsg().PHP_EOL.PHP_EOL; 
                        exit;
                    }else {

                        $vmservice_id = $this->vmservice_tb_model->getData();
                        echo '[VMSERVICE ID] '.$vmservice_id.PHP_EOL.PHP_EOL;

                        echo '[INSERT] ip_tb(VMSERVICE) :';
                        $params = array(
                            'ip_class_id'       => $valid_vmservice[$vm['lab_id']]['ipc_id'],
                            'ip_class_type'     => $valid_vmservice[$vm['lab_id']]['ipc_type'],
                            'ip_class_category' => $valid_vmservice[$vm['lab_id']]['ipc_category'],
                            'ip_address'        => $vm['lab_ip'],
                            'ip_memo'           => '',
                            'ip_created_at'     => date('Y-m-d H:i:s'), 
                            'ip_updated_at'     => date('Y-m-d H:i:s')
                        );
                        if( ! $this->ip_tb_model->doInsert($params)->isSuccess() ) {
                            echo $this->ip_tb_model->getErrorMsg().PHP_EOL.PHP_EOL; 
                            exit;
                        }else {
                            $vmservice_ip_id = $this->ip_tb_model->getData();
                            $params = array(
                                'vim_vmservice_id'      => $vmservice_id, 
                                'vim_ip_id'             => $vmservice_ip_id,
                            );
                            $this->vmservice_ip_map_tb_model->doInsert($params);
                            echo '[vmservice_ip_id]: '.$vmservice_ip_id.PHP_EOL;
                            echo '[vim_id]: '.$this->vmservice_ip_map_tb_model->getData().PHP_EOL;
                        }

                    }
                    echo PHP_EOL;
                }

            }
            echo PHP_EOL.'======================='.PHP_EOL;
            exit;
        }

    }



    public function joong() {

		$this->load->business(array(
            'ip_class_tb_business',
            'custom_value_tb_business'
        ));

        $params = array(
            //'ip'        => '14.129.113.31',
            'ip'        => '121.78.72.148',
            //'set_valid' => 'vmservice'
        );

        $valid_res = $this->ip_class_tb_business->checkIPinClass($params);
        echo print_r($valid_res);

    }


    private function _get_name($lab_data) {

        $res = array(
            'name' => '',
            'memo' => '',
        );

        $replace_words = array('<br />','<br>','<br/>','/','(',')');
        foreach($replace_words as $word) {
            $lab_data['lab_name'] = str_replace($word,' ',$lab_data['lab_name']);
        }

        $match_name = array();
        $pattern = '/vmware[-|_][A-z]*[0-9]*/';
        preg_match($pattern, $lab_data['lab_name'], $match_name);
        //echo print_r($match_name);

        if( sizeof($match_name) > 0 ) {
            $res['name'] = $match_name[0]; 
            $res['memo'] = $lab_data['lab_name']; 
        }else {
             
            $name = explode(' ', $lab_data['lab_name']);
            $name = array_filter($name);
            $res['name'] = $name[0];
            $res['memo'] = implode(PHP_EOL, $name);
        }
        return $res;
    }


    private function _get_raid_type($lab_data) {

        $raid = '';

        $lab_data['lab_hddtype'] = str_replace(' ', '', $lab_data['lab_hddtype']);
        $lab_data['lab_hddtype'] = strtoupper($lab_data['lab_hddtype']);

        $match_raid = array();
        $pattern = '/RAID[0-9]{1,2}/';
        preg_match($pattern, $lab_data['lab_hddtype'], $match_raid);


        $match_hot = array();
        $pattern = '/HOT/';
        preg_match($pattern, $lab_data['lab_hddtype'], $match_hot);

        if( sizeof($match_raid) > 0 ) {
            $raid = $match_raid[0];
            if( sizeof($match_hot) > 0 ) {
                $raid .= '+'.$match_hot[0];
            } 
        }else {
            if( sizeof($match_hot) > 0 ) {
                $raid .= $match_hot[0];
            }
        }
        return $raid;
    }


    private function _get_disk_type($lab_data) {

        $disk = '';

        $lab_data['lab_hddtype'] = str_replace(' ', '', $lab_data['lab_hddtype']);
        $lab_data['lab_hddtype'] = strtoupper($lab_data['lab_hddtype']);

        $match_disk = array();
        $pattern = '/S[A-z]{2,3}/';
        preg_match_all($pattern, $lab_data['lab_hddtype'], $match_disk);
        if( sizeof($match_disk) > 0 ) {
            $match_disk = array_shift($match_disk);
            $disk = implode(',',$match_disk);
        }
        return $disk;
    }




    private function _write_invalid_log($lab_data, $chkVal) {

        $this->load->helper('file');

        $logdata = array(
            'lab'       => $lab_data,
            'invalid'   => $chkVal
        );                 
        $path = LOG_FILE_PATH.'/lab_migration/';
        $filename = $lab_data['lab_id'].'.info';
        write_file($path.$filename, print_r($logdata, true));

        echo PHP_EOL.'============ ['.$filename.'] Write Invalid Log =============='.PHP_EOL.PHP_EOL;
        //exit;

    }


}
