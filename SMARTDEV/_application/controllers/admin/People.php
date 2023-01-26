<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include_once dirname(__FILE__).'/Base_admin.php';

class People extends Base_admin {


    public function employee() {

		$this->load->model(array(
            'people_tb_model',
            'ip_tb_model',
            'people_ip_map_tb_model'
        ));
		$this->load->business('company_tb_business');

        $company_data = $this->company_tb_business->getNameMap();
        $status_data = $this->people_tb_model->getStatusMap();
        $is_admin_data = array('YES' => 'YES', 'NO' => 'NO');

        $req = $this->input->post();
        $data = array();

        if(isset($req['mode']) && $req['mode'] == 'list') {

            $out_data = array();
            $params = array();
            $extras = array();

            //echo print_r($req);
            $fields = array('pp_name', 'pp_login_id', 'pp_email', 'pp_dept', 'pp_title'); 
            $params = $this->common->transDataTableFiltersToParams($req, $fields);
            $extras = $this->common->transDataTableFiltersToExtras($req);

            if( isset($params['=']['pp_admin_id']) ) {
                if( $params['=']['pp_admin_id'] == 'YES' ) {
                    $params['>=']['pp_admin_id'] = 1;
                }else {
                    $params['<']['pp_admin_id'] = 1;
                }
                unset($params['=']['pp_admin_id']);
            }

            //$extras['order_by'] = array('pp_company_id ASC', 'pp_created_at DESC');
            $count = $this->people_tb_model->getCount($params)->getData();
            $rows = $this->people_tb_model->getList($params, $extras)->getData();
            $pp_ids = array_keys($this->common->getDataByPK($rows, 'pp_id'));
            //echo print_r($pp_ids); //exit;

            if( sizeof($pp_ids) > 0 ) {
                $params = array();
                $params['in']['pim_people_id'] = $pp_ids;
                $params['join']['ip_tb'] = 'ip_id = pim_ip_id';
                $extras = array();
                $extras['fields'] = array('pim_ip_id', 'pim_people_id', 'ip_address');
                $pim_data = $this->people_ip_map_tb_model->getList($params, $extras)->getData();
                $pim_data = $this->common->getDataByDuplPK($pim_data, 'pim_people_id'); 
            }

            $data = array();
            foreach($rows as $k=>$r){
                $company_id = $r['pp_company_id'];
                $r['pp_company_id'] = $company_data[$company_id];

                $status_class = 'info';
                if( $r['pp_status'] == 'OUTMEMBER' ) {
                    $status_class = 'danger';
                }
                $r['pp_status'] = $status_data[$r['pp_status']];
                $r['pp_status'] = '<span class="badge border border-'.$status_class.' text-'.$status_class.'">'.$r['pp_status'].'</span>';

                $new_icon = '<span class="badge badge-danger mr-1">New</span>';
                $diff_date = $this->common->diffDate(time(), $r['pp_created_at']);
                if( $diff_date < 7 ) {
                    $r['pp_login_id'] = $new_icon.$r['pp_login_id'];
                }

                $ips = ''; 
                if( isset($pim_data[$r['pp_id']]) ) {
                    foreach($pim_data[$r['pp_id']] as $pim) {
                        $ips .= '<div class="badge border border-primary text-primary mb-1">'.$pim['ip_address'].'</div>';
                    } 
                }
                $r['pp_ips'] = $ips;


                $r['pp_admin_id'] = ($r['pp_admin_id'] > 0) ? 'YES' : 'NO';

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

        $data['company_data'] = $this->common->genJqgridOption($company_data, false);
        $data['status_data'] = $this->common->genJqgridOption($status_data, false);
        $data['is_admin_data'] = $this->common->genJqgridOption($is_admin_data, false);
		$this->_view('people/employee', $data);
    }



	public function employee_detail($id=0) {

		$this->load->model('people_tb_model');
		$this->load->business(array(
            'company_tb_business',
            'supplier_tb_business',
            'admin_tb_business'
        ));

        $row = array();
		$id = intval($id);

        if($id > 0) {
            $mode = 'update'; 
            $row = $this->people_tb_model->get($id)->getdata();
            $row['pp_ip_history'] = unserialize($row['pp_ip_history']);
        
        }else {

            // set 초기화 및 기본값 
            $mode = 'insert'; 
            $fields = $this->people_tb_model->getfields();
            foreach($fields as $f) {
                if( $f == 'pp_status' ) {
                    $row[$f] = 'ACTIVE'; 
                }else {
                    $row[$f] = ''; 
                }
            }
        }

        $data = array();
        $data['mode'] = $mode;
        $data['row'] = $row;

        $company_data = $this->company_tb_business->getNameMap();
        $data['select_company'] = getSearchSelect($company_data, 'pp_company_id', $row['pp_company_id']);

        $supplier_data = $this->supplier_tb_business->getOptionMap();
        $data['select_supplier'] = getGroupSearchSelect($supplier_data, 'pp_supplier_id', $row['pp_supplier_id']); 

        $loginid_data = $this->admin_tb_business->getLoginIDMap();
        $data['select_loginid'] = getSearchSelect($loginid_data, 'pp_admin_id', $row['pp_admin_id']);

        $status_data = $this->people_tb_model->getStatusMap();
        $data['select_status'] = getSearchSelect($status_data, 'pp_status', $row['pp_status']);

		$this->_view('people/employee_detail', $data);
    }




    public function employee_process() {

		$this->load->model('people_tb_model');
        $req = $this->input->post();
        if($this->input->is_ajax_request()) {
            $req['request'] = 'ajax';
        }

        $sess = array();
        $log_array = array();
        $row_data = array();

        //echo print_r($req).PHP_EOL;

        $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/people/employee';
        
        $field_list = $this->people_tb_model->getFields();
        $data_params = array();
        foreach($field_list as $key) {
			if(array_key_exists($key, $req)) {
				$data_params[$key] = $req[$key];
				continue;
			}

			if(array_key_exists($key.'_date', $req) && array_key_exists($key.'_time', $req)) {
				$data_params[$key] = $req[$key.'_date'].' '.$req[$key.'_time'];
			}
		}

        // 삭제 & 업데이트 시 기존데이터 검증
        $row_data = array();
        if($req['mode'] == 'update' || $req['mode'] == 'delete') {
            if( ! $this->people_tb_model->get($req['pp_id'])->isSuccess()) {
                $log_array['msg'] = getAlertMsg('INVALID_SUBMIT');
                $this->common->alert($log_array['msg']);
                $this->common->locationhref($rtn_url);
                $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['pp_id'], $log_array, 'people_tb');
                return;
            }
            $row_data = $this->people_tb_model->getData();
            $log_array['prev_data'] = $row_data;
        }
        


        // 중복값 처리
        if($req['mode'] != 'delete') {
            
        }



        //echo print_r($data_params).PHP_EOL; exit;
        switch($req['mode']) {

            case 'delete':
                
                /* TODO.
                $data_params = array();
                $data_params['m_is_active'] = 'NO';
                $log_array['update_data'] = $data_params;

                if( ! $this->people_tb_model->doUpdate($row_data['m_id'], $data_params)->isSuccess()) {
                    $log_array['msg'] = $this->people_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['m_id'], $log_array, 'people_tb');
                    return;
                }
                $this->common->write_history_log($sess, 'EXPIRE', $req['m_id'], $log_array, 'people_tb');
                $this->common->locationhref($rtn_url);
                return;
                */

                break;

            case 'update':

                $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/people/employee_detail/'.$req['pp_id'];

                // UNIQUE KEY 
                $params = array();
                $params['!=']['pp_id'] = $req['pp_id'];
                $params['=']['pp_email'] = $data_params['pp_email'];
                $cnt = $this->people_tb_model->getCount($params)->getData();
                if($cnt > 0) {
                    $this->common->alert(getAlertMsg('DUPLICATE_VALUES').' [Email]');
                    $this->common->historyback();
                    return;
                }

                $log_array['params'] = $data_params;
                if( ! $this->people_tb_model->doUpdate($req['pp_id'], $data_params)->isSuccess()) {
                    $log_array['msg'] = $this->people_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['pp_id'], $log_array, 'people_tb');
                    return;
                }
                $this->common->write_history_log($sess, 'UPDATE', $req['pp_id'], $log_array, 'people_tb');
                break;
            
            case 'insert':
                
                if( ! isset($data_params['pp_email'])) {
                    $log_array['msg'] = getAlertMsg('REQUIRED_VALUES'); 
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    return;
                }
			
                // UNIQUE KEY
                $cnt = $this->people_tb_model->getCount(array('=' => array('pp_email' => $data_params['pp_email'])))->getData();
                if($cnt > 0) {
                    $log_array['msg'] = getAlertMsg('DUPLICATE_VALUES').' [Email]'; 
                    $this->common->alert($log_array['msg']);
                    $this->common->historyback();
                    return;
                }

                $data_params['pp_created_at'] = date('Y-m-d H:i:s');
                unset($data_params['pt_id']);

                $log_array['params'] = $data_params;
                if( ! $this->people_tb_model->doInsert($data_params)->isSuccess()) {
                    $log_array['msg'] = $this->people_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    return;
                }
				$act_key = $this->people_tb_model->getData();
                $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/people/employee_detail/'.$act_key;
                $this->common->write_history_log($sess, 'INSERT', $act_key, $log_array, 'people_tb');
                break;
        }

        $this->common->locationhref($rtn_url);
    }





    public function partners() {

		$this->load->model('partner_tb_model');
		$this->load->business('supplier_tb_business');

        $supplier_data = $this->supplier_tb_business->getNameMap();

        $req = $this->input->post();
        $data = array();

        if(isset($req['mode']) && $req['mode'] == 'list') {

            $out_data = array();
            $params = array();
            $extras = array();

            //echo print_r($req);
            $fields = array('pt_name', 'pt_title', 'pt_dept', 'pt_email', 'pt_tel', 'pt_mobile'); 
            $params = $this->common->transDataTableFiltersToParams($req, $fields);
            $extras = $this->common->transDataTableFiltersToExtras($req);
            //echo print_r($params); exit;

            $count = $this->partner_tb_model->getCount($params)->getData();
            $rows = $this->partner_tb_model->getList($params, $extras)->getData();

            $data = array();
            foreach($rows as $k=>$r){
                if( isset($supplier_data[$r['pt_supplier_id']]) ) {
                    $r['pt_supplier_id'] = $supplier_data[$r['pt_supplier_id']];
                }else {
                    $r['pt_supplier_id'] = '';
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

        $data['supplier_data'] = $this->common->genJqgridOption($supplier_data, false);

		$this->_view('people/partners', $data);
    }



	public function partner_detail($id=0) {

		$this->load->model('partner_tb_model');
		$this->load->business(array(
            'company_tb_business',
            'supplier_tb_business',
            'admin_tb_business'
        ));

        $row = array();
		$id = intval($id);

        if($id > 0) {
            $mode = 'update'; 
            $row = $this->partner_tb_model->get($id)->getdata();
        
        }else {

            // set 초기화 및 기본값 
            $mode = 'insert'; 
            $fields = $this->partner_tb_model->getfields();
            foreach($fields as $f) {
                $row[$f] = ''; 
            }
        }

        $data = array();
        $data['mode'] = $mode;
        $data['row'] = $row;

        $supplier_data = $this->supplier_tb_business->getOptionMap();
        $data['select_supplier'] = getGroupSearchSelect($supplier_data, 'pt_supplier_id', $row['pt_supplier_id']); 

		$this->_view('people/partner_detail', $data);
    }




    public function partner_process() {

		$this->load->model('partner_tb_model');
        $req = $this->input->post();
        if($this->input->is_ajax_request()) {
            $req['request'] = 'ajax';
        }

        $sess = array();
        $log_array = array();
        $row_data = array();

        //echo print_r($req).PHP_EOL;

        $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/people/partners';
        
        $field_list = $this->partner_tb_model->getFields();
        $data_params = array();
        foreach($field_list as $key) {
			if(array_key_exists($key, $req)) {
				$data_params[$key] = $req[$key];
				continue;
			}

			if(array_key_exists($key.'_date', $req) && array_key_exists($key.'_time', $req)) {
				$data_params[$key] = $req[$key.'_date'].' '.$req[$key.'_time'];
			}
		}

        // 삭제 & 업데이트 시 기존데이터 검증
        $row_data = array();
        if($req['mode'] == 'update' || $req['mode'] == 'delete') {
            if( ! $this->partner_tb_model->get($req['pt_id'])->isSuccess()) {
                $log_array['msg'] = getAlertMsg('INVALID_SUBMIT');
                $this->common->alert($log_array['msg']);
                $this->common->locationhref($rtn_url);
                $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['pt_id'], $log_array, 'partner_tb');
                return;
            }
            $row_data = $this->partner_tb_model->getData();
            $log_array['prev_data'] = $row_data;
        }
        


        // 중복값 처리
        if($req['mode'] != 'delete') {
            
        }



        //echo print_r($data_params).PHP_EOL; exit;
        switch($req['mode']) {

            case 'delete':
                
                /* TODO.
                $data_params = array();
                $data_params['m_is_active'] = 'NO';
                $log_array['update_data'] = $data_params;

                if( ! $this->partner_tb_model->doUpdate($row_data['m_id'], $data_params)->isSuccess()) {
                    $log_array['msg'] = $this->partner_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['m_id'], $log_array, 'partner_tb');
                    return;
                }
                $this->common->write_history_log($sess, 'EXPIRE', $req['m_id'], $log_array, 'partner_tb');
                $this->common->locationhref($rtn_url);
                return;
                */

                break;

            case 'update':

                $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/people/partner_detail/'.$req['pt_id'];

                // UNIQUE KEY 
                $params = array();
                $params['!=']['pt_id'] = $req['pt_id'];
                $params['=']['pt_email'] = $data_params['pt_email'];
                $cnt = $this->partner_tb_model->getCount($params)->getData();
                if($cnt > 0) {
                    $this->common->alert(getAlertMsg('DUPLICATE_VALUES').' [Email]');
                    $this->common->historyback();
                    return;
                }

                $log_array['params'] = $data_params;
                if( ! $this->partner_tb_model->doUpdate($req['pt_id'], $data_params)->isSuccess()) {
                    $log_array['msg'] = $this->partner_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['pt_id'], $log_array, 'partner_tb');
                    return;
                }
                $this->common->write_history_log($sess, 'UPDATE', $req['pt_id'], $log_array, 'partner_tb');
                break;
            
            case 'insert':
                
                if( ! isset($data_params['pt_email'])) {
                    $log_array['msg'] = getAlertMsg('REQUIRED_VALUES'); 
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    return;
                }
			
                // UNIQUE KEY
                $cnt = $this->partner_tb_model->getCount(array('=' => array('pt_email' => $data_params['pt_email'])))->getData();
                if($cnt > 0) {
                    $log_array['msg'] = getAlertMsg('DUPLICATE_VALUES').' [Email]'; 
                    $this->common->alert($log_array['msg']);
                    $this->common->historyback();
                    return;
                }

                $data_params['pt_created_at'] = date('Y-m-d H:i:s');
                unset($data_params['pt_id']);

                $log_array['params'] = $data_params;
                if( ! $this->partner_tb_model->doInsert($data_params)->isSuccess()) {
                    $log_array['msg'] = $this->partner_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    return;
                }
				$act_key = $this->partner_tb_model->getData();
                $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/people/partner_detail/'.$act_key;
                $this->common->write_history_log($sess, 'INSERT', $act_key, $log_array, 'partner_tb');
                break;
        }

        $this->common->locationhref($rtn_url);
    }



    public function ip_list() {

		$this->load->model(array(
            'ip_tb_model',
            'ip_class_tb_model',
            'assets_model_tb_model',
            'people_tb_model',
        ));
		$this->load->business(array(
            'ip_class_tb_business',
        ));

        $req = $this->input->post();
        $data = array();

        if(isset($req['mode']) && $req['mode'] == 'list') {

            //$ipc_data = $this->ip_class_tb_business->getNameMap();

            $params = array();
            $extras = array();

            $fields = array(
                'ip_address', 'ip_memo', 'ipc_name', 'ipc_cidr', 'ip_class_type', 'ip_class_category'
            );
            $params = $this->common->transDataTableFiltersToParams($req, $fields);
            $extras = $this->common->transDataTableFiltersToExtras($req);


            $params['join']['ip_class_tb'] = 'ip_class_id = ipc_id';

            $extras['fields'] = array(
                'ip_id', 'ip_address', 'ip_class_id', 'ip_class_type', 'ip_class_category', 'ip_memo', 'ip_updated_at', 'ip_created_at',
                'ipc_id', 'ipc_type', 'ipc_name', 'ipc_cidr'
            );
            $extras['order_by'] = array('ip_id DESC');



            $count = $this->ip_tb_model->getCount($params)->getData();
            $rows = $this->ip_tb_model->getList($params, $extras)->getData();
            //echo $this->ip_tb_model->getLastQuery(); exit;


            $data = array();
            foreach($rows as $k=>$r){

                $link = '/'.SHOP_INFO_ADMIN_DIR.'/assets/ip_total/'.$r['ipc_id'];
                $icon = '<i class="fal fa-link mr-1"></i>';
                $r['ipc_cidr'] = '<a href="'.$link.'" class="btn btn-xs btn-danger waves-effect waves-themed">'.$icon.$r['ipc_cidr'].'</a>';

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


        $type_data = $this->ip_class_tb_model->getTypeMap();
        $data['type_data'] = $this->common->genJqgridOption($type_data, false);
 
        $category_data = $this->ip_class_tb_model->getCategoryMap();
        $data['category_data'] = $this->common->genJqgridOption($category_data, false);
             
		$this->_view('people/ip_list', $data);
    }



	public function ip_detail($id=0, $mode='insert') {

		$this->load->model(array(
            'ip_tb_model',
            'ip_class_tb_model'
        ));
		$this->load->business(array(
            'people_tb_business',
            'location_tb_business'
        ));

        $row = array();
		$id = intval($id);

        if($id > 0) {
            if($mode != 'clone') {
                $mode = 'update';
            }
            $row = $this->ip_tb_model->get($id)->getData();

            $location_data = $this->location_tb_business->getNameMap();
            $class_data = $this->ip_class_tb_model->get($row['ip_class_id'])->getData();

            $row['ipc_location_id'] = $location_data[$class_data['ipc_location_id']]; 
            $row['ipc_cidr'] = $class_data['ipc_cidr'];
        
        }else {

            // set 초기화 및 기본값 
            $mode = 'insert'; 
            $fields = $this->ip_tb_model->getFields();
            foreach($fields as $f) {
                $row[$f] = ''; 
            }
        }

        $data = array();
        $data['mode'] = $mode;
        $data['row'] = $row;

        $people_data = $this->people_tb_business->getNameMap();
        $data['select_people'] = getSearchSelect($people_data, 'ip_people_id', $row['ip_people_id'], 'required');

		$this->_view('people/ip_detail', $data);
    }



    public function ip_process() {

		$this->load->model(array(
            'ip_tb_model',
            'assets_model_tb_model',
            'people_tb_model'
        ));

        $req = $this->input->post();
        //echo print_r($req); exit;
        if($this->input->is_ajax_request()) {
            $req['request'] = 'ajax';
        }

        $sess = array();
        $log_array = array(
            'is_success' => FALSE,
            'msg'       => ''
        );
        $row_data = array();
        //echo print_r($req).PHP_EOL; exit;

        $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/people/ip_list';
        
        $field_list = $this->ip_tb_model->getFields();
        $data_params = array();
        foreach($field_list as $key) {
			if(array_key_exists($key, $req)) {
				$data_params[$key] = $req[$key];
				continue;
			}
			if(array_key_exists($key.'_date', $req) && array_key_exists($key.'_time', $req)) {
				$data_params[$key] = $req[$key.'_date'].' '.$req[$key.'_time'];
			}
		}

        // 삭제 & 업데이트 시 기존데이터 검증
        $row_data = array();
        if($req['mode'] == 'update' || $req['mode'] == 'delete') {
            if( ! $this->ip_tb_model->get($req['ip_id'])->isSuccess()) {
                $log_array['msg'] = getAlertMsg('INVALID_SUBMIT');
                if($req['request'] == 'ajax') {
                     echo json_encode($json_data);
                }else {
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['ip_id'], $log_array, 'ip_tb');
                }
                return;
            }
            $row_data = $this->ip_tb_model->getData();
            $log_array['prev_data'] = $row_data;
        }

        switch($req['mode']) {

            case 'delete':
                if( ! $this->ip_tb_model->doDelete($req['ip_id'])->isSuccess()) {
                    $log_array['msg'] = $this->ip_tb_model->getErrorMsg();
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['ip_id'], $log_array, 'ip_tb');
                }
                    
                $this->common->write_history_log($sess, 'DELETE', $req['ip_id'], $log_array, 'ip_tb');
                break;


            case 'update':
                $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/people/ip_detail/'.$req['ip_id'];

                // UNIQUE KEY 
                $params = array();
                $params['!=']['ip_id'] = $req['ip_id'];
                $params['=']['ip_address'] = $data_params['ip_address'];
                $cnt = $this->ip_tb_model->getCount($params)->getData();
                if($cnt > 0) {
                    $msg = getAlertMsg('DUPLICATE_VALUES').' [IP Address]';
                    $this->_resultFalse($mode, $msg);
                }

                $data_params['ip_class_type'] = strtoupper($data_params['ip_class_type']);

                $log_array['params'] = $data_params;
                if( ! $this->ip_tb_model->doUpdate($req['ip_id'], $data_params)->isSuccess()) {
                    $log_array['msg'] = $this->ip_tb_model->getErrorMsg();
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['ip_id'], $log_array, 'ip_tb');
                }
                $this->common->write_history_log($sess, 'UPDATE', $req['ip_id'], $log_array, 'ip_tb');
                break;
            

            case 'insert':
            case 'clone':

                // UNIQUE KEY 
                $cnt = $this->ip_tb_model->getCount(array('=' => array('ip_address' => $data_params['ip_address'])))->getData();
                if($cnt > 0) {
                    $msg = getAlertMsg('DUPLICATE_VALUES').' [IP Address]';
                    $this->_resultFalse($mode, $msg);
                }

                unset($data_params['ip_id']);
                $data_params['ip_created_at'] = date('Y-m-d H:i:s');
                $data_params['ip_class_type'] = strtoupper($data_params['ip_class_type']);

                $log_array['params'] = $data_params;
                if( ! $this->ip_tb_model->doInsert($data_params)->isSuccess()) {
                    $log_array['msg'] = $this->ip_tb_model->getErrorMsg();
                    $this->common->locationhref($rtn_url);
                }
                $act_key = $this->ip_tb_model->getData();
                $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/people/ip_detail/'.$act_key;
                $this->common->write_history_log($sess, 'INSERT', $act_key, $log_array, 'ip_tb');
                break;

        } // END_SWITCH




        // :::: 결과처리 :::::

        // 실패 Case.
        if(strlen($json_encode['msg']) > 0) {

            if($req['request'] == 'ajax') {
                $json_data['msg'] = getAlertMsg('INVALID_SUBMIT');    
                echo json_encode($json_data);
                return;
            }else {
                $this->common->alert($log_array['msg']);
            }

        // 성공 Case.
        }else {

            if($req['request'] == 'ajax') {
                $json_data['is_success'] = true;
                echo json_encode($json_data);
                return;
            }
        }
        $this->common->locationhref($rtn_url);
    }


    private function _resultFalse($mode, $msg) {
        switch($mode) {
            case 'ajax':
                $json_data['is_success'] = false;
                $json_data['msg'] = $msg;    
                echo json_encode($json_data);
                break;

            default:
                $this->common->alert($msg);
                $this->common->historyback();
                break;
        }
        return;
    }



    // People IP MAP List
    public function pim_list() {

		$this->load->model(array(
            'ip_tb_model',
            'ip_class_tb_model',
            'people_ip_map_tb_model',
        ));
		$this->load->business('ip_class_tb_business');

        $req = $this->input->post();
        //echo print_r($req);
        $data = array();

        if(isset($req['mode']) && $req['mode'] == 'list') {

            $out_data = array();
            $params = array();
            $extras = array();

            //echo print_r($req);
            $fields = $this->ip_tb_model->getFields();
            $params = $this->common->transDataTableFiltersToParams($req, $fields);
            $extras = $this->common->transDataTableFiltersToExtras($req);
            //echo print_r($params); exit;

            $params['=']['pim_people_id'] = $req['pim_people_id'];
            $params['join']['people_ip_map_tb'] = 'pim_ip_id = ip_id';
            $extras['fields'] = array(
                'ip_tb.*', 'pim_id'
            );

            $count = $this->ip_tb_model->getCount($params)->getData();
            $rows = $this->ip_tb_model->getList($params, $extras)->getData();


            // Class Name
            $ipc_ids = array_keys($this->common->getDataByPK($rows, 'ip_class_id'));
            $ipc_data = $this->ip_class_tb_business->getNameMap($ipc_ids);


            $data = array();
            foreach($rows as $k=>$r){
                $r['ip_class_id'] = $ipc_data[$r['ip_class_id']];
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

    }


    public function pim_process() {

		$this->load->model(array(
            'people_tb_model',
            'ip_tb_model',
            'ip_class_tb_model',
            'people_ip_map_tb_model',
        ));
        $req = $this->input->post();
        if($this->input->is_ajax_request()) {
            $req['request'] = 'ajax';
        }
        //echo print_r($req); exit;

        $sess = array();
        $log_array = array();
        $row_data = array();

        $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/people/pim_list';
        
        $field_list = $this->ip_tb_model->getFields();
        $data_params = array();
        foreach($field_list as $key) {
			if(array_key_exists($key, $req)) {
				$data_params[$key] = $req[$key];
				continue;
			}

			if(array_key_exists($key.'_date', $req) && array_key_exists($key.'_time', $req)) {
				$data_params[$key] = $req[$key.'_date'].' '.$req[$key.'_time'];
			}
		}

        // 삭제 & 업데이트 시 기존데이터 검증
        $row_data = array();
        if($req['mode'] == 'update' || $req['mode'] == 'delete') {
            if( ! $this->ip_tb_model->get($req['ip_id'])->isSuccess()) {
                $$log_array['msg'] = getAlertMsg('INVALID_SUBMIT');
                if($req['request'] == 'ajax') {
                     echo json_encode($log_array);
                }else {
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['ip_id'], $log_array, 'ip_tb');
                }
                return;
            }
            $row_data = $this->ip_tb_model->getData();
            $log_array['prev_data'] = $row_data;
        }
        
        switch($req['mode']) {

            case 'delete':
                
                $log_array['params']['ip_tb'] = $req['ip_id'];
                if( ! $this->ip_tb_model->doDelete($req['ip_id'])->isSuccess()) {
                    $log_array['msg'] = $this->ip_tb_model->getErrorMsg();
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['ip_id'], $log_array, 'ip_tb');
                }else {
                    $log_array['params']['people_ip_map_tb'] = $req['pim_id'];
                    $this->people_ip_map_tb_model->doDelete($req['pim_id']);
                    $this->common->write_history_log($sess, $req['mode'], $req['ip_id'], $log_array, 'ip_tb');
                }
                break;

            case 'update':

                $data_params = array(
                    'ip_memo'   => $req['ip_memo']
                );

                $log_array['params']['ip_tb'] = $data_param;
                if( ! $this->ip_tb_model->doUpdate($req['ip_id'], $data_params)->isSuccess()) {
                    $log_array['msg'] = $this->ip_tb_model->getErrorMsg();
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['ip_id'], $log_array, 'ip_tb');
                }else {
                    $data_params = array(
                        'pim_people_id'  => $req['pim_people_id']
                    );
                    $log_array['params']['people_ip_map_tb'] = $data_param;
                    $this->people_ip_map_tb_model->doUpdate($req['pim_id'], $data_params);
                    $this->common->write_history_log($sess, $req['mode'], $req['ip_id'], $log_array, 'ip_tb');
                }
                break;
            
            case 'insert':
                
                if( ! isset($data_params['ip_address'])) {
                    $log_array['msg'] = getAlertMsg('REQUIRED_VALUES').' [IP Address]';
                    if($req['request'] == 'ajax') {
                         echo json_encode($log_array);
                    }else {
                        $this->common->alert($log_array['msg']);
                        $this->common->locationhref($rtn_url);
                    }
                    return;
                }
			
                // UNIQUE KEY
                $cnt = $this->ip_tb_model->getCount(array('=' => array('ip_address' => $data_params['ip_address'])))->getData();
                if($cnt > 0) {
                    $log_array['msg'] = getAlertMsg('DUPLICATE_VALUES').' [IP Address]'; 
                    if($req['request'] == 'ajax') {
                         echo json_encode($log_array);
                    }else {
                        $this->common->alert($log_array['msg']);
                        $this->common->locationhref($rtn_url);
                    }
                    return;
                }


                $data_params['ip_created_at'] = date('Y-m-d H:i:s');
                unset($data_params['ip_id']);
                //echo print_r($data_params); exit;
                $log_array['params']['ip_tb'] = $data_params;
                if( ! $this->ip_tb_model->doInsert($data_params)->isSuccess()) {
                    $log_array['msg'] = $this->ip_tb_model->getErrorMsg();
                }else {
				    $act_key = $this->ip_tb_model->getData();
                    
                    $params = array(
                        'pim_people_id' => $req['pim_people_id'],
                        'pim_ip_id'     => $act_key
                    );

                    $log_array['params']['people_ip_map_tb'] = $params;
                    $this->people_ip_map_tb_model->doInsert($params);

                    $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/people/aim_list/'.$act_key;
                    $this->common->write_history_log($sess, 'INSERT', $act_key, $log_array, 'ip_tb');
                }
                break;
        }


        // :::: 결과처리 :::::

        // 실패 Case.
        if(strlen($log_array['msg']) > 0) {
            if($req['request'] == 'ajax') {
                $log_array['msg'] = getAlertMsg('INVALID_SUBMIT');    
                echo json_encode($log_array);
                return;
            }else {
                $this->common->alert($log_array['msg']);
            }

        // 성공 Case.
        }else {

            if($req['request'] == 'ajax') {
                $json_data['is_success'] = true;
                echo json_encode($json_data);
                return;
            }
        }

        $this->common->locationhref($rtn_url);
    }


}
