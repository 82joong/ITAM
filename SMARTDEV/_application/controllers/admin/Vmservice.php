<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include_once dirname(__FILE__).'/Base_admin.php';

class Vmservice extends Base_admin {

    public function lists() {

        $data = array();

		$this->load->model(array(
            'assets_model_tb_model',
            'ip_tb_model',
            'vmservice_tb_model',
            'vmservice_ip_map_tb_model',
            'assets_ip_map_tb_model',
            'service_manage_tb_model',
        ));
        $this->load->business(array(
            'company_tb_business',
            'location_tb_business',
            'assets_model_tb_business',
        ));
        
        $req = $this->input->post();
        //echo print_r($req); exit;
        $data = array();

        $company_data = $this->company_tb_business->getNameMap();
        $location_data = $this->location_tb_business->getNameMap();
        $status_data = $this->vmservice_tb_model->getStatusText();


        if(isset($req['mode']) && $req['mode'] == 'list') {

            $params = array();
            $extras = array();

            $fields = array(
                'vms_name', 'vms_memo', 'vms_status', 'sm_os_info', 'sm_db_info', 'sm_lang_info', 'sm_was_info', 
                'am_id', 'am_name', 'am_models_name', 'am_serial_no', 'am_location_id', 
                'am_vmware_name', 'am_rack_code', 'am_company_id', 'ip_address', 'ip_memo'
            ); 

            $params = $this->common->transDataTableFiltersToParams($req, $fields);
            $extras = $this->common->transDataTableFiltersToExtras($req);
            //echo print_r($params); //exit;
            //echo print_r($extras); exit;

            // Alias 제외
            $params['=']['ip_class_category'] = 'VMWARE';
            //$params['<']['vms_alias_id'] = 1;

            $params['join']['assets_model_tb'] = 'aim_assets_model_id = am_id';
            $params['join']['ip_tb'] = 'aim_ip_id = ip_id';
            $params['left_join']['vmservice_tb'] = 'vms_assets_model_id = am_id'; 
            $params['left_join']['vmservice_ip_map_tb'] = 'vim_vmservice_id = vms_id'; 
            $params['left_join']['service_manage_tb'] = 'sm_vmservice_id = vms_id';

            if(isset($params['or_raw'])) {
                $params['or_raw'] = array('('.$params['or_raw'][0].')');
            }

            $extras['fields'] = array_merge(array('vms_id', 'ip_id', 'ip_memo', 'am_id', 'service_manage_tb.*'), $fields);
            //$extras['order_by'] = array('am_serial_no ASC');
            $count = $this->assets_ip_map_tb_model->getCount($params)->getData();
            $rows = $this->assets_ip_map_tb_model->getList($params, $extras)->getData();
            //echo $this->assets_ip_map_tb_model->getLastQuery(); exit;
            //echo print_r($rows); exit;


            $am_ids = array_keys($this->common->getDataByPK($rows, 'am_id'));
            $vms_ids = array_keys($this->common->getDataByPK($rows, 'vms_id'));


            // [IDRAC] IP
            $aim_ips = array();
            if(sizeof($am_ids) > 0) {
                $params = array();
                $params['in']['aim_assets_model_id'] = $am_ids;
                $params['=']['ip_class_category'] = 'IDRAC';
                $params['join']['ip_tb'] = 'aim_ip_id = ip_id';
                $extras = array();
                $extras['fields'] = array('ip_tb.*', 'assets_ip_map_tb.*');
                $aim_ips = $this->assets_ip_map_tb_model->getList($params, $extras)->getData();
                $aim_ips = $this->common->getDataByPK($aim_ips, 'aim_assets_model_id');
                //echo print_r($aim_ips);
                //exit;
            }

            // [VMService IP]
            $vim_ips = array();
            if(sizeof($vms_ids) > 0) {
                $params = array();
                $params['in']['vim_vmservice_id'] = $vms_ids;
                $params['join']['ip_tb'] = 'vim_ip_id = ip_id';
                $extras = array();
                $extras['fields'] = array('ip_tb.*', 'vmservice_ip_map_tb.*');
                $vim_ips = $this->vmservice_ip_map_tb_model->getList($params, $extras)->getData();
                $vim_ips = $this->common->getDataByPK($vim_ips, 'vim_vmservice_id');
            }



            $data = array();
            foreach($rows as $k=>$r){

                //echo print_r($r); exit;

                //$link = '/admin/assets/detail/servers/'.$r['am_id'].'#tab_assets';
                //$r['am_name'] = nameToLinkHtml($link, $r['am_name']);

                $link = '/admin/assets/detail/servers/'.$r['am_id'].'#tab_vmware';
                $r['vms_name'] = nameToLinkHtml($link, $r['vms_name']);
                $r['vms_status'] = $this->vmservice_tb_model->getStatusBadge($r['vms_status']);
                

                $r['am_company_id'] = $company_data[$r['am_company_id']];
                $r['am_location_id'] = $location_data[$r['am_location_id']];

                $r['vms_ip'] = '';
                if(strlen($r['vms_id']) > 0 && $r['vms_id'] > 0) {
                    $r['vms_ip'] = $vim_ips[$r['vms_id']]['ip_address'];
                }

                $r['remote_ip'] = isset($aim_ips[$r['am_id']]) ? $aim_ips[$r['am_id']]['ip_address'] : '';

                if(strlen($r['am_vmware_name']) < 1) {
                    $r['am_vmware_name'] = $r['am_name'];
                }

                $data[] = $r;
            }

            $json_data = new stdClass;
            $json_data->draw = $req['draw'];
            $json_data->recordsTotal = $count;
            $json_data->recordsFiltered = $count;
            $json_data->data = $data;

            //echo print_r($json_data); exit;
            echo json_encode($json_data);
            return;
        }


        $data['location_type'] = $this->common->genJqgridOption($location_data, false);
        $data['company_type'] = $this->common->genJqgridOption($company_data, false);
        $data['status_type'] = $this->common->genJqgridOption($status_data, false);

		$this->_view('vmservice/lists', $data);
    }





    public function detail($id=0) {

		$this->load->model(array(
            'assets_model_tb_model',
            'vmservice_tb',
            'vmservice_ip_map_tb_model',
            'vmservice_host_map_tb_model',
            'ip_tb',
        ));

        $data = array();
        $row = array();
		$id = intval($id);

        if( $id < 1 ) {
            $this->common->alert(getAlertMsg('INVALID_ACCESS', 'kr'));
            $this->common->historyback();
            return;
        }


        $params = array();
        $params['=']['vms_id'] = $id;
        $params['join']['vmservice_ip_map_tb'] = 'vim_vmservice_id = vms_id';
        $params['join']['ip_tb'] = 'ip_id = vim_ip_id';

        $extras['fields'] = array(
                'vms_id', 'vms_name', 'vms_memo', 
                'ip_address as vms_ip_address',
                'ip_id as vms_ip_id',
                'ip_memo'
        );
        $row = $this->vmservice_tb_model->getList($params, $extras)->getData();
        if( sizeof($row) < 1 ) {
            $this->common->alert(getAlertMsg('INVALID_ACCESS', 'kr'));
            $this->common->historyback();
            return;
        }

        $row = array_shift($row);
        //echo print_r($row);


        $this->load->config('bootstrap_color');
        $data['color_set'] = $this->config->item('color_set');

        $data['row'] = $row;


        // Elastic Data
        $params = array();
        $params['=']['vhm_vmservice_id'] = $id;
        $vhm_data = $this->vmservice_host_map_tb_model->getList($params)->getData();
        if( sizeof($vhm_data) < 1 ) {
            $data['sys'] = array();
            $this->common->alert(getAlertMsg('EMPTY_HOST_DATA', 'kr'));
            $this->common->historyback();
            return;

        }else {
            $vhm_data = array_shift($vhm_data);
            $data['sys'] = $this->getSysData($vhm_data['vhm_id']);
		    $this->_view('vmservice/detail', $data);
        }
    }



    public function getSysData($vhm_id=0) {

        $size = 20;

		$this->load->model(array(
            'vmservice_tb',
            'vmservice_host_map_tb_model',
        ));

        if( intVal($vhm_id) < 1 ) return;
        $vhm_data = $this->vmservice_host_map_tb_model->get($vhm_id)->getData();
        if(sizeof($vhm_data) < 1) {
            return;
        }

        $host = $vhm_data['vhm_elk_host_name'];

        $this->load->library('elastic');
        $el_header = $this->elastic->get_auth_header();


        $params = array();
        $params['from'] = 0;
        $params['size'] = $size;
        $params['sort'] = array(
            array('@timestamp' => array('order' => 'desc')) 
        );
        $params['query'] = array(
            'bool' => array(
                'must' => array(
                    'term' => array(
                        'host'  => array('value' => $host)
                    )
                )
            )
        );

        //echo print_r($params);
        $json_params = json_encode($params, JSON_NUMERIC_CHECK);
        //echo $json_params; exit;
            
        $el_url = ELASTIC_SYSLOG_HOST.'/'.ELASTIC_SYSLOG_INDEX.'/_search';
        $el_result = $this->elastic->restful_curl($el_url, $json_params, 'GET', $timeout=10, $el_header);
        $el_result = json_decode($el_result, true);
        //echo print_r($el_result); exit;
            
        if( ! isset($el_result['hits']) ||  ! isset($el_result['hits']['hits']) || sizeof($el_result['hits']['hits']) < 1 ) {
            return array();
        }

        $data = array();
        foreach($el_result['hits']['hits'] as $k=>$r) {

            $key = $r['_id'];
            $row = $r['_source'];
            $data[$key] = $row;
        }
        //echo print_r($data);

        return $data;
    }

}
