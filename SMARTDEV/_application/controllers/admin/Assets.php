<?php

defined('BASEPATH') OR exit('No direct script access allowed');

include_once dirname(__FILE__).'/Base_admin.php';

class Assets extends Base_admin {


    public function type($at_name='') {

		$this->load->model(array(
            'assets_model_tb_model',
            'assets_type_tb_model',
            'models_tb_model',
            'custom_value_tb_model',
        ));
        $this->load->business(array(
            'status_tb_business',
            'company_tb_business',
            'supplier_tb_business',
            'location_tb_business',
            'category_tb_business',
        ));

        if(strlen($at_name) < 1) {
            $this->common->alert(getAlertMsg('INVALID_ACCESS', 'kr'));
            $this->common->historyback();
            return;
        }


        $params = array();
        $params['=']['at_name'] = ucfirst(strtolower($at_name));
        $type_data = $this->assets_type_tb_model->getList($params)->getData();
       
        if(sizeof($type_data) < 1) {
            $this->common->alert(getAlertMsg('INVALID_ACCESS', 'kr'));
            $this->common->historyback();
            return;
        }
        $type_data = array_shift($type_data);

        $config = array(
            'assets_type_id'    => $type_data['at_id'],
            'assets_type_uri'   => strtolower($at_name),
        );

        $this->_lists($config);
    }


    private function _lists($config = array()) {

        $req = $this->input->post();
        $data = $config;

        $status_map = $this->status_tb_business->getRowMap();
        $location_data = $this->location_tb_business->getNameMap();
        $category_data = $this->category_tb_business->getNameMap();
        $company_data = $this->company_tb_business->getNameMap();


        // Extra(custom_value) Fields
        $ex_fields = array();
        $params = array();
        $params['join']['assets_model_tb'] = 'am_id = cv_assets_model_id';
        $params['=']['am_assets_type_id'] = $config['assets_type_id'];
        $extras = array();
        $extras['fields'] = array('cv_name');
        $extras['group_by'] = array('cv_name');
        $ex_fields = $this->custom_value_tb_model->getList($params, $extras)->getData();
        $data['ex_fields'] = $ex_fields;


        if(isset($req['mode']) && $req['mode'] == 'list') {

            $out_data = array();
            $params = array();
            $extras = array();

            //echo print_r($req);
            $fields = array('am_name', 'am_vmware_name', 'am_tags', 'am_serial_no', 'am_rack_code');
            $params = $this->common->transDataTableFiltersToParams($req, $fields);
            $extras = $this->common->transDataTableFiltersToExtras($req);
            //echo print_r($params); //exit;


            // EX_[fields] 검색일때,
            if( isset($params['like']) ) {

                $is_ex_search = FALSE;
                foreach($params['like'] as $k=>$v) {
                    if(strpos($k, 'ex_') === 0) {
                        $is_ex_search = TRUE;
                        $field = explode('ex_', $k);
                        $params['=']['cv_name'] = $field[1];
                        $params['like']['cv_value'] = $v;

                        unset($params['like'][$k]);
                    }
                }
                if($is_ex_search == TRUE) {
                    $params['join']['custom_value_tb'] = 'am_id = cv_assets_model_id';
                }
            }
            //echo print_r($params); exit;


            $params['=']['am_assets_type_id'] = $config['assets_type_id'];
            $params['join']['models_tb'] = 'am_models_id = m_id';
            $extras['fields'] = array('assets_model_tb.*', 'm_id', 'm_category_id');
            //echo print_r($params); exit;

            $count = $this->assets_model_tb_model->getCount($params)->getData();
            $rows = $this->assets_model_tb_model->getList($params, $extras)->getData();
            //echo $this->assets_model_tb_model->getLastQuery(); exit;

            // Custom Value
            $am_ids = array_keys($this->common->getDataByPK($rows, 'am_id'));
            //echo print_r($am_ids); exit;
            $params = array();
            $params['in']['cv_assets_model_id'] = $am_ids;

            $extras = array();
            $extras['fields'] = array('cv_id', 'cv_assets_model_id', 'cv_name', 'cv_format', 'cv_format_element', 'cv_value');
            $cv_data = $this->custom_value_tb_model->getList($params, $extras)->getData();
            $cv_data = $this->common->getDataByDuplPK($cv_data, 'cv_assets_model_id');
            //echo print_r($cv_data); exit;

            $data = array();
            foreach($rows as $k=>$r){

                $html_tag = '';
                $tags = explode(',', $r['am_tags']);
                foreach($tags as $tag) {
                    if(strlen($tag) > 0 ) {
                        $html_tag .= '<span class="badge border border-info text-info mr-1">#'.$tag.'</span>';
                    }
                }
                $r['am_tags'] = $html_tag; 

                $status_color = $status_map[$r['am_status_id']]['opt_color'];
                $status_name = $status_map[$r['am_status_id']]['opt_name'];
                $r['am_status_id'] = $this->status_tb_business->iconStatusName($status_color, $status_name);

                $r['am_location_id'] = $location_data[$r['am_location_id']];
                $r['category'] = $category_data[$r['m_category_id']];
                $r['am_company_id'] = $company_data[$r['am_company_id']];


                // CV Fields
                foreach($ex_fields as $ex) {
                    $ex_name = 'ex_'.$ex['cv_name'];    // !!![주의] DataTable Column 명과 일치 

                    $ex_value = array();
                    if( isset($cv_data[$r['am_id']]) ) {
                        $ex_value = $this->common->getDataByPK($cv_data[$r['am_id']], 'cv_name');
                    }

                    if( isset($ex_value[$ex['cv_name']]) ) {
                        $r[$ex_name] = nl2br($ex_value[$ex['cv_name']]['cv_value']);
                    }else {
                        $r[$ex_name] = '';
                    }
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

        $status_data = $this->status_tb_business->getNameMap();
        $data['status_type'] = $this->common->genJqgridOption($status_data, false);
        $data['location_type'] = $this->common->genJqgridOption($location_data, false);
        $data['category_type'] = $this->common->genJqgridOption($category_data, false);
        $data['company_type'] = $this->common->genJqgridOption($company_data, false);


		$this->_view('assets/type_lists', $data);
    }



    public function detail() {

		$this->load->model(array(
            'assets_model_tb_model',
            'assets_type_tb_model',
            'models_tb_model',
            'custom_value_tb_model',
            'category_tb_model',
            'ip_tb_model',
            'assets_ip_map_tb_model',
            'direct_ip_map_tb_model',
            'vmservice_tb_model',
            'maintenance_tb_model',
            'service_manage_tb_model',
        ));

		$this->load->business(array(
            'assets_type_tb_business',
            'assets_model_tb_business',
            'company_tb_business',
            'supplier_tb_business',
            'models_tb_business',
            'order_item_tb_business',
            'location_tb_business',
            'status_tb_business',
            'rack_tb_business',
            'custom_field_tb_business',
            'category_tb_business',
            'direct_ip_map_tb_business',
        ));


        $at_name = $this->uri->segment(4);
        if(strlen($at_name) < 1) {
            $this->common->alert(getAlertMsg('INVALID_ACCESS', 'kr'));
            $this->common->historyback();
            return;
        }


        $params = array();
        $params['=']['at_name'] = ucfirst(strtolower($at_name));
        $type_data = $this->assets_type_tb_model->getList($params)->getData();
        if(sizeof($type_data) < 1) {
            $this->common->alert(getAlertMsg('INVALID_ACCESS', 'kr'));
            $this->common->historyback();
            return;
        }
        
        $type_data = array_shift($type_data);
        $assets_model_id = $this->uri->segment(5,0);

        $config = array(
            'assets_type_id'    => $type_data['at_id'],
            'assets_type_uri'   => strtolower($at_name),
            'assets_model_id'   => $assets_model_id,
        );

        $this->_detail($config);
    }


    private function _detail($config = array()) {

        $data = $config;
        $row = array();
		$id = intval($data['assets_model_id']);
        $disabled = '';
        $ct_id = '';


        // VMWare Init
        $data['vmware'] = array(
            'mode'                => 'insert',
            'aim_id'              => '',
            'aim_assets_model_id' => '',
            'ip_id'               => '',
            'ip_memo'             => ''
        );
        // date set 
        $data['idrac'] = $data['vmware'];

        $data['vmware_cnt'] = 0;
        $data['alias_cnt'] = 0;
        $data['works_cnt'] = 0;

        if($id > 0) {
            $mode = 'update'; 
            $disabled = 'disabled';
            $row = $this->assets_model_tb_model->get($id)->getData();

            if(sizeof($row) < 1) {
                $this->common->alert(getAlertMsg('INVALID_ACCESS', 'kr'));
                $this->common->historyback();
                return;
            }

            // Get category_id 
            $params = array();
            $params['=']['ct_type_id'] = $row['am_assets_type_id'];
            $ct_data= $this->category_tb_model->getList($params)->getData();
            $ct_data = array_shift($ct_data);
            $ct_id = $ct_data['ct_id'];


            // Get custom_value_tb 데이터 : fieldset area 내에 Elements 
            $params = array();
            $params['=']['cv_assets_model_id'] = $id;
            $extras = array();
            $extras['order_by'] = array('cv_order ASC');
            $cv_data = $this->custom_value_tb_model->getList($params, $extras)->getData();

            $format_map = $this->custom_field_tb_business->getElementFormatMap();
            $temp_data = array(
                'prefix'        => 'cv_',
                'mode'          => 'update',
                'fieldset_data' => array('fs_id' => $cv_data[0]['cv_fieldset_id']),
                'custom_data'   => $cv_data,
                'format_map'    => $format_map
            );
            $row['view_data'] = $this->load->view('/admin/default_template/assets/custom_fields_template.php', $temp_data, true);


            // VMWare & iDrac & Driret (PUB/PRI)
            $res = $this->assets_model_tb_business->getAssetsIPType($row['am_id']);
            //echo print_r(array_keys($res));  exit;


            $vmware_keys = array('VMWARE', 'PUBLIC', 'PRIVATE');
            $in_vmware = array_intersect($vmware_keys, array_keys($res));
            if( sizeof($in_vmware) > 0 ) {

                $key = array_shift(array_values($in_vmware));
                $data['vmware'] = $res[$key];
                $data['vmware']['mode'] = 'update';

            }else {
                //echo 'NO EXISTS';
            }

            if( isset($res['IDRAC']) ) {
                $data['idrac'] = $res['IDRAC'];
                $data['idrac']['mode'] = 'update';
            }


            // Direct IP
            $dim_data = $this->direct_ip_map_tb_business->getDirectIP($row['am_id']);
            if(sizeof($dim_data) > 0) {
                $data['direct'] = $dim_data;
                $data['direct']['mode'] = 'update';
            }else {
                $data['direct'] = array(
                    'mode'                => 'insert',
                    'dim_id'              => '',
                    'dim_assets_model_id' => '',
                    'ip_id'               => '',
                    'ip_memo'             => ''
                );
            }


            // Service Data
            $params = array();
            $params['=']['sm_assets_model_id'] = $row['am_id'];
            $params['=']['sm_vmservice_id'] = 0;
            $service_data = $this->service_manage_tb_model->getList($params)->getData();
            if(sizeof($service_data) > 0) {
                $data['service'] = array_shift($service_data);
                $data['service']['mode'] = 'update';
            }else {

                $fields = $this->service_manage_tb_model->getFields();
                foreach($fields as $f) {
                    switch($f) {
                        case 'sm_secure_conf':
                        case 'sm_secure_inte':
                        case 'sm_secure_avail':
                            $data['service'][$f] = 1;
                            break;
                        case 'sm_important_score':
                        case 'sm_important_level':
                            $data['service'][$f] = 3;
                            $data['service'][$f] = 3;
                            break;
                        default:
                            $data['service'][$f] = ''; 
                            break;
                    }
                }
                $data['service']['mode'] = 'insert';
            }

            // COUNT
            $params = array();
            $params['=']['vms_assets_model_id'] = $row['am_id'];
            $params['<']['vms_alias_id'] = 1;
            $data['vmware_cnt'] = $this->vmservice_tb_model->getCount($params)->getData();

            $params = array();
            $params['=']['vms_assets_model_id'] = $row['am_id'];
            $params['>=']['vms_alias_id'] = 1;
            $data['alias_cnt'] = $this->vmservice_tb_model->getCount($params)->getData();


            $params = array();
            $params['=']['mtn_assets_model_id'] = $row['am_id'];
            $data['works_cnt'] = $this->maintenance_tb_model->getCount($params)->getData();
            

        }else {

            // SET 초기화 및 기본값 
            $mode = 'insert'; 
            $fields = $this->assets_model_tb_model->getFields();
            foreach($fields as $f) {
                $row[$f] = ''; 
            }

            $row['am_assets_type_id'] = $data['assets_type_id'];
        }

        $data['mode'] = $mode;
        $data['row'] = $row;

        $oi_data = $this->order_item_tb_business->getOrdersGroup($row['am_order_item_id']);
        $data['select_order'] = getGroupSearchSelect($oi_data, 'am_order_item_id', $row['am_order_item_id'], $disabled);

        $company_data = $this->company_tb_business->getNameMap();
        //$data['select_company'] = getSearchSelect($company_data, 'am_company_id', $row['am_company_id'], $disabled);
        $data['select_company'] = getSearchSelect($company_data, 'am_company_id', $row['am_company_id'], '');

        $supplier_data = $this->supplier_tb_business->getOptionMap();
        $data['select_supplier'] = getGroupSearchSelect($supplier_data, 'am_supplier_id', $row['am_supplier_id'], $disabled); 

        $model_data = $this->models_tb_business->getNameMap();
        $data['select_model'] = getSearchSelect($model_data, 'am_models_id', $row['am_models_id'], $disabled);

        $type_data = $this->assets_type_tb_business->getOptionMap();
        $data['select_type'] = getSearchWithIconSelect($type_data, 'am_assets_type_id', $row['am_assets_type_id'], '');

        $category_data = $this->category_tb_business->getGroupMap();
        $data['select_category'] = getGroupSearchSelect($category_data, 'm_category_id', $ct_id, $disabled); 

        $status_data = $this->status_tb_business->getRowMap();
        $data['select_status'] = getSearchWithIconSelect($status_data, 'am_status_id', $row['am_status_id'], 'required');

        $location_data = $this->location_tb_business->getNameMap();
        $data['select_location'] = getSearchSelect($location_data, 'am_location_id', $row['am_location_id']);


   
        $data['out_comments'] = $this->status_tb_business->outStatusText(); 
        $data['out_comments'] .= '<span class="text-danger">로 상태 변경시 자산(렉, IP) 회수 처리</span><br />';
        $data['out_comments'] .= '- 렉정보 매칭 => Blank (삭제)<br />';
        $data['out_comments'] .= '- iDrac/Direct/Vmware IP => Blank (삭제)';

        //echo print_r($data); exit;
		$this->_view('assets/type_detail', $data);
    }


    public function ajax_get_oi() {

        $json_data = array(
            'is_success' => FALSE,
            'msg'       => ''
        );
        $req = $this->input->post();

        if( ! isset($req['oi_id']) || $req['oi_id'] < 1 ) {
            $json_data['msg'] = getAlertMsg('INVALID_SUBMIT');    
             echo json_encode($json_data);
            return;
        }


		$this->load->model(array(
            'order_item_tb_model',
            'order_tb_model',
            'models_tb_model',
            'category_tb_model'
        ));
        $params = array();

        $params['join']['order_tb'] = 'oi_order_id = o_id';


        $parmas['=']['o_order_status'] = 'DELIVERED';  // CHECK. 주문완료된 주문서만 제공 
        $params['=']['oi_id'] = $req['oi_id'];
        $extras = array();
        $extras['fields'] = array(
            'o_id', 'o_company_id', 'o_supplier_id', 'o_estimatenum', 'o_ordernum', 'o_order_status', 'o_ordered_at', 
            'oi_id', 'oi_model_id', 'oi_model_name', 'oi_quantity', 'oi_total_price', 'oi_service_tag'
        );
        $extras['order_by'] = array('oi_id ASC');

        $oi_data = $this->order_item_tb_model->getList($params, $extras)->getData();
        //echo print_r($oi_data); //exit;
        
        if( sizeof($oi_data) < 1 ) {
            $json_data['msg'] = getAlertMsg('INVALID_SUBMIT');    
        }else {

            $oi_data = array_shift($oi_data);

            // Get category_id && assets_type_id
            $params = array();
            $params['=']['m_id'] = $oi_data['oi_model_id'];
            $params['join']['category_tb'] = 'm_category_id = ct_id';
            $extras = array();
            $extras['fields'] = array('m_id', 'ct_id', 'ct_type_id');
            $extras['order_by'] = array('m_id ASC');
            $model_data = $this->models_tb_model->getList($params, $extras)->getData();
            $model_data = array_shift($model_data);

            $oi_data['ct_id'] = $model_data['ct_id'];
            $oi_data['ct_type_id'] = $model_data['ct_type_id'];

            $json_data['is_success'] = true;
            $json_data['data'] = $oi_data;
        }
        echo json_encode($json_data);
        return;
    } 



    public function type_process() {

		$this->load->model(array(
            'assets_model_tb_model',
            'models_tb_model',
            'rack_tb_model',
            'custom_value_tb_model',
        ));

		$this->load->business(array(
            'custom_value_tb_business',
            'assets_type_tb_business',
            'assets_model_tb_business',
        ));
        $req = $this->input->post();

        if($this->input->is_ajax_request()) {
            $req['request'] = 'ajax';
        }


        // 반출 처리 로직
        if( isset($req['am_status_id']) && in_array($req['am_status_id'], OUT_STATUS) ) {
            if( $this->_assets_out_process($req) === TRUE ) {
                $req['am_rack_id'] = 0;
                $msg = '['.$req['am_name'].'] : 반출처리 '. date('Y-m-d H:i:s');
                $req['am_memo'] = $req['am_memo'].PHP_EOL.$msg;
                $req['am_name'] = '';
                $req['am_vmware_name'] = '';
            }
        }

        $at_map = $this->assets_type_tb_business->getNameMap();
        $at_name = strtolower($at_map[$req['am_assets_type_id']]);

        $sess = array();
        $log_array = array();
        $row_data = array();

        //echo print_r($req).PHP_EOL;        
        $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/assets/type/'.$at_name;
        
        $field_list = $this->assets_model_tb_model->getFields();
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
        //echo print_r($data_params); exit;


        // 삭제 & 업데이트 시 기존데이터 검증
        $row_data = array();
        if($req['mode'] == 'update' || $req['mode'] == 'delete') {
            if( ! $this->assets_model_tb_model->get($req['am_id'])->isSuccess()) {
                $log_array['msg'] = getAlertMsg('INVALID_SUBMIT');
                $this->common->alert($log_array['msg']);
                $this->common->locationhref($rtn_url);
                $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['am_id'], $log_array, 'assets_model_tb');
                return;
            }
            $row_data = $this->assets_model_tb_model->getData();
            $log_array['prev_data'] = $row_data;
        }

        //echo print_r($data_params); //exit;
        if($req['mode'] == 'insert' || $req['mode'] == 'update') {
            $model_data = $this->models_tb_model->get($data_params['am_models_id'])->getData();
            $rack_data = $this->rack_tb_model->get($data_params['am_rack_id'])->getData();
            $data_params['am_models_name'] = $model_data['m_model_name'];
            $data_params['am_rack_code'] = isset($rack_data['r_code']) ? $rack_data['r_code'] : '';
            $data_params['am_location_id'] = isset($rack_data['r_location_id']) ? $rack_data['r_location_id'] : '';
            //echo print_r($data_params); exit;
        }
        //echo print_r($data_params).PHP_EOL; exit;

        switch($req['mode']) {

            case 'delete':
                if( ! $this->assets_model_tb_model->doDelete($row_data['am_id'])->isSuccess()) {
                    $log_array['msg'] = $this->asset_model_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['am_id'], $log_array, 'assets_model_tb');
                    return;
                }

                // 연관데이터 삭제 처리
                $re_data = $this->assets_model_tb_business->getRelationTblData($row_data);
                foreach($re_data['custom'] as $cv) {
                    $this->custom_value_tb_model->doDelete($cv['cv_id']);
                }
                foreach($re_data['vmservice'] as $vms) {
                    $this->vmservice_tb_model->doDelete($vms['vms_id']);
                }
                foreach($re_data['ip'] as $ip) {
                    $this->ip_tb_model->doDelete($ip['ip_id']);
                }
                foreach($re_data['map']['aim'] as $aim) {
                    $this->assets_ip_map_tb_model->doDelete($aim['aim_id']);
                }
                foreach($re_data['map']['dim'] as $dim) {
                    $this->direct_ip_map_tb_model->doDelete($dim['dim_id']);
                }
                foreach($re_data['map']['vim'] as $vim) {
                    $this->vmservice_ip_map_tb_model->doDelete($vim['vim_id']);
                }

                $log_array['relation_tbl_data'] = $re_data; 
                $log_array['del_msg'] = $req['del_msg']; 
                $this->common->write_history_log($sess, 'DELETE', $req['am_id'], $log_array, 'assets_model_tb');
                $this->common->locationhref($rtn_url);
                break;

            case 'update':

                $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/assets/detail/'.$at_name.'/'.$req['am_id'];

                $log_array['params'] = $data_params;
                if( ! $this->assets_model_tb_model->doUpdate($req['am_id'], $data_params)->isSuccess()) {
                    $log_array['msg'] = $this->assets_model_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['am_id'], $log_array, 'assets_model_tb');
                    return;
                }

                // Update custom_value_tb
                $params = array();
                $params['=']['cv_assets_model_id'] = $req['am_id'];
                $cnt = $this->custom_value_tb_model->getCount($params)->getData();
                if($cnt < 1) {
                    $res = $this->custom_value_tb_business->insertCustomValue($req, $data_params, $req['am_id']);
                }else {
                    $res = $this->custom_value_tb_business->updateCustomValue($req);
                }

                $this->common->write_history_log($sess, 'UPDATE', $req['am_id'], $log_array, 'assets_model_tb');
                break;
            
            case 'insert':

                if( ! isset($data_params['am_name'])) {
                    $log_array['msg'] = getAlertMsg('REQUIRED_VALUES'); 
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    return;
                }
			

                // UNIQUE KEY 
                $params = array();
                $params['=']['am_serial_no'] = $req['am_serial_no'];
                $cnt = $this->assets_model_tb_model->getCount($params)->getData();
                if($cnt > 0) {
                    $log_array['msg'] = getAlertMsg('DUPLICATE_VALUES').' [Service Tag]';
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    return;
                }

                $data_params['am_created_at'] = date('Y-m-d H:i:s');
                unset($data_params['am_id']);

                $log_array['params'] = $data_params;
                //echo print_r($data_params); exit;
                if( ! $this->assets_model_tb_model->doInsert($data_params)->isSuccess()) {
                    $log_array['msg'] = $this->assets_model_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    return;
                }

				$act_key = $this->assets_model_tb_model->getData();


                // RACK Order
                $params = array();
                $params['=']['am_rack_id'] = $data_params['am_rack_id'];
                $cnt = $this->assets_model_tb_model->getCount($params)->getData();

                $params = array();
                $params['am_rack_order'] = $cnt + 1;
                $this->assets_model_tb_model->doUpdate($act_key, $params);
  

                // Insert custom_value_tb
                $res = $this->custom_value_tb_business->insertCustomValue($req, $data_params, $act_key);

                $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/assets/detail/'.$at_name.'/'.$act_key;
                $this->common->write_history_log($sess, 'INSERT', $act_key, $log_array, 'assets_model_tb');
                break;
        }

        $this->common->locationhref($rtn_url);
    }


    public function ajax_get_model() {

		$this->load->model(array(
            'models_tb_model',
            'custom_field_map_tb_model',
            'custom_field_tb_model',
            'models_custom_fields_tb_model',
            'fieldset_tb_model',
            'category_tb_model',
        ));

        $this->load->business(array(
            'custom_field_tb_business',
        ));

        $json_data = array(
            'is_success' => FALSE,
            'msg'        => '',
            'category_id'=> 0,
            'type_id'    => 0
        );
        $req = $this->input->post();

        $fieldset_id = 0;
        if(isset($req['models_id']) && strlen($req['models_id']) > 0) {
            $model_data = $this->models_tb_model->get($req['models_id'])->getData();
            if(sizeof($model_data) < 1) {
                echo json_encode($json_data);
                return;
            }
            $fieldset_id = $model_data['m_fieldset_id'];
            $json_data['category_id'] = $model_data['m_category_id'];

            $cate_data = $this->category_tb_model->get($model_data['m_category_id'])->getData();
            $json_data['type_id'] = $cate_data['ct_type_id'];
        }


        if($fieldset_id < 0) {
            $json_data['msg'] = getAlertMsg('INVALID_SUBMIT');    
            echo json_encode($json_data);
            return; 
        }

        $params = array();
        $params['=']['mcf_models_id'] = $req['models_id'];
        $extras = array();
        $extras['order_by'] = array('mcf_order ASC');
        $mcf_data = $this->models_custom_fields_tb_model->getList($params, $extras)->getData();
        if(sizeof($mcf_data) > 0) {
            $fs_data = array(
                'fs_id'     => $mcf_data[0]['mcf_fieldset_id'],
                'fs_name'   => $mcf_data[0]['mcf_fieldset_name'],
            );
            $format_map = $this->custom_field_tb_business->getElementFormatMap();

            $temp_data = array(
                'prefix'        => 'mcf_',
                'fieldset_data' => $fs_data,
                'custom_data'   => $mcf_data,
                'format_map'    => $format_map
            );
            $view_data = $this->load->view('/admin/default_template/assets/custom_fields_template.php', $temp_data, true);

            $json_data['is_success'] = true;
            $json_data['data'] = $view_data;
        }else {
            $json_data['is_success'] = true;
        }

        echo json_encode($json_data);
        return; 
    } 




    public function status() {


		$this->load->model(array(
            'assets_model_tb_model',
            'assets_type_tb_model',
        ));
        $this->load->business(array(
            'status_tb_business',
            'company_tb_business',
            'supplier_tb_business',
            'location_tb_business',
            'assets_type_tb_business',
        ));



        $req = $this->input->post();
        //$data = $config;
        $data = array();

        $status_map = $this->status_tb_business->getRowMap();
        $location_data = $this->location_tb_business->getNameMap();
        $assets_type_map = $this->assets_type_tb_business->getNameMap();
        $company_map = $this->company_tb_business->getNameMap();

        if(isset($req['mode']) && $req['mode'] == 'list') {

            $out_data = array();
            $params = array();
            $extras = array();

            //echo print_r($req);
            $fields = array('am_name', 'am_tags', 'am_serial_no', 'am_models_name', 'am_vmware_name', 'am_rack_code', 'am_estimatenum');
            $params = $this->common->transDataTableFiltersToParams($req, $fields);
            $extras = $this->common->transDataTableFiltersToExtras($req);
            //echo print_r($params); exit;


            $count = $this->assets_model_tb_model->getCount($params)->getData();
            $rows = $this->assets_model_tb_model->getList($params, $extras)->getData();

            $data = array();
            foreach($rows as $k=>$r){

                $r['am_tags'] = tagsToHtml($r['am_tags']); 

                $status_color = $status_map[$r['am_status_id']]['opt_color'];
                $status_name = $status_map[$r['am_status_id']]['opt_name'];
                $r['am_status_id'] = $this->status_tb_business->iconStatusName($status_color, $status_name);

                $r['am_location_id'] = $location_data[$r['am_location_id']];

                $at_name = $assets_type_map[$r['am_assets_type_id']]; 
                $r['am_assets_type_name'] = strtolower($at_name); 

                $r['am_company_id'] = $company_map[$r['am_company_id']];

                $data[] = $r;
            }

            $json_data = new stdClass;
            $json_data->draw = $req['draw'];
            $json_data->recordsTotal = $count;
            $json_data->recordsFiltered = $count;
            $json_data->data = $data;

            echo json_encode($json_data);
            return;
        }

        $status_data = $this->status_tb_business->getNameMap();
        $data['status_type'] = $this->common->genJqgridOption($status_data, false);
        $data['location_type'] = $this->common->genJqgridOption($location_data, false);
        $data['company_type'] = $this->common->genJqgridOption($company_map, false);


		$this->_view('assets/status', $data);
    }



    public function status_detail($id=0) {

        $data = array();
        $data['works_cnt'] = 0;


		$this->load->model(array(
            'assets_model_tb_model', 
            'custom_value_tb_model', 
            'models_tb_model', 
            'category_tb_model', 
            'status_tb_model', 
            'vendor_tb_model', 
            'location_tb_model', 
            'supplier_tb_model', 
            'company_tb_model', 
        ));
		$this->load->business(array(
            'status_tb_business',
            'vendor_tb_business',
            'category_tb_business',
        ));



        $row = array();
		$id = intval($id);


        if($id < 0) {
            $this->common->alert(getAlertMsg('INVALID_ACCESS', 'kr'));
            $this->common->historyback();
            return;
        }

        $status_map = $this->status_tb_business->getRowMap();
        $category_map = $this->category_tb_business->getNameMap();

        $row = $this->assets_model_tb_model->get($id)->getData();
        if( !is_array($row) || sizeof($row) < 1 ) {
            $this->common->alert(getAlertMsg('INVALID_ACCESS', 'kr'));
            $this->common->historyback();
            return;
        }

        // Status
        $status_color = $status_map[$row['am_status_id']]['opt_color'];
        $status_name = $status_map[$row['am_status_id']]['opt_name'];
        $row['status'] = $this->status_tb_business->iconStatusName($status_color, $status_name);


        // Assets Type
        $row['type'] = $this->assets_type_tb_business->iconTypeName($this->_ASSETS_TYPE[$row['am_assets_type_id']]);


        // Model
        $model = $this->models_tb_model->get($row['am_models_id'])->getData();
        $model['img_url'] = '';
        if( strlen($model['m_filename']) > 0 ) {
            $img_path = $this->common->getImgUrl('models', $model['m_id']);
            $model['img_url'] = $img_path.'/'.$model['m_filename'];
        }


        $row['category'] = $category_map[$model['m_category_id']];

        // Vendor
        $vendor = $this->vendor_tb_model->get($model['m_vendor_id'])->getData();
        $vendor['icon'] = $this->vendor_tb_business->getVendorIcon($vendor['vd_id'], $vendor['vd_filename']);


        // Location
        $location = $this->location_tb_model->get($row['am_location_id'])->getData();


        // Company
        $company = $this->company_tb_model->get($row['am_company_id'])->getData();


        // Supplier
        $supplier = $this->supplier_tb_model->get($row['am_supplier_id'])->getData();
        


        // Custom Value
        $params = array();
        $params['=']['cv_assets_model_id'] = $row['am_id'];
        $extras = array();
        $extras['fields'] = array('cv_name', 'cv_format_element', 'cv_value');
        $extras['order_by'] = array('cv_order ASC');
        $custom = $this->custom_value_tb_model->getList($params, $extras)->getData();
        //echo print_r($custom);


        $data['row'] = $row;
        $data['model'] = $model;
        $data['vendor'] = $vendor;
        $data['custom'] = $custom;
        $data['location'] = $location;
        $data['company'] = $company;
        $data['supplier'] = $supplier;

        $this->_view('assets/status_detail', $data);
    }



    public function maintenance() {

        $data = array();

		$this->load->model(array(
            'maintenance_tb_model',
            'supplier_tb_model',
            'assets_model_tb_model',
            'vmservice_tb_model',
        ));
        $this->load->business(array(
            'location_tb_business',
        ));

        $req = $this->input->post();
        $data = array();

        if(isset($req['mode']) && $req['mode'] == 'list') {

            $location_data = $this->location_tb_business->getNameMap();

            $out_data = array();
            $params = array();
            $extras = array();

            $fields = array(
                'am_models_name', 'am_name', 'am_vmware_name', 'mtn_type', 'mtn_title'
            );
            $params = $this->common->transDataTableFiltersToParams($req, $fields);
            $extras = $this->common->transDataTableFiltersToExtras($req);
            //echo print_r($params); exit;

            if(isset($req['assets_model_id']) && $req['assets_model_id'] > 0) {
                $params['=']['mtn_assets_model_id'] = $req['assets_model_id']; 
            }
            if( ! isset($extras['limit']) || $extras['limit'] < 0) {
                $extras['limit'] = 100;
            }

            //$params['join']['supplier_tb'] = 'mtn_supplier_id = sp_id';
            $params['join']['assets_model_tb'] = 'mtn_assets_model_id = am_id';
            $extras['fields'] = array('maintenance_tb.*', 'am_models_name', 'am_name', 'am_vmware_name');

            $count = $this->maintenance_tb_model->getCount($params)->getData();
            $rows = $this->maintenance_tb_model->getList($params, $extras)->getData();

            $data = array();
            foreach($rows as $k=>$r){
                //$r['r_location_id'] = $location_data[$r['r_location_id']];
                $link = '/admin/assets/detail/servers/'.$r['mtn_assets_model_id'];
                $r['am_name'] = nameToLinkHtml($link, $r['am_name'], '_blank');

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
		$this->_view('assets/maintenance', $data);
    }



    public function maintenance_detail($id=0) {

		$this->load->model(array(
            'maintenance_tb_model',
            'assets_model_tb_model',
            'vmservice_tb_model',
            'people_tb_model'
        ));
		$this->load->business(array(
            'supplier_tb_business',
            'people_tb_business',
            'assets_model_tb_business',
        ));




        $row = array();
		$id = intval($id);

        if($id > 0) {

            if(strlen($mode) < 1) {
                $mode = 'update';
            }else {
                // clone => insert 로 
                $mode = 'insert';
            }
            $row = $this->maintenance_tb_model->get($id)->getData();

            $row['mtn_assets_model_name'] = '';
            $row['mtn_people_name'] = '';

            if($row['mtn_vmservice_id'] > 0) {
                $vms_data = $this->vmservice_tb_model->get($row['mtn_vmservice_id'])->getData();
                $row['mtn_vmservice_name'] = $vms_data['vms_name']; 
            }
            if($row['mtn_people_id'] > 0) {
                $pp_data = $this->people_tb_model->get($row['mtn_people_id'])->getData();
                $row['mtn_people_name'] = $pp_data['pp_name']; 
            }
        }else {

            // SET 초기화 및 기본값 
            $mode = 'insert'; 
            $fields = $this->maintenance_tb_model->getFields();
            foreach($fields as $f) {
                $v = ''; 
                if( $f == 'mtn_start_at' ) {
                    $v = date('Y-m-d H:i:s');
                }
                $row[$f] = $v; 
            }
            if( isset($_GET['am_id']) && strlen($_GET['am_id']) > 0 ) {
                $row['mtn_assets_model_id'] = $_GET['am_id'];
            }

        }

        if($row['mtn_assets_model_id'] > 0) {
            $am_data = $this->assets_model_tb_model->get($row['mtn_assets_model_id'])->getData();
            $row['mtn_assets_model_name'] = $am_data['am_name'].' ( '.$am_data['am_serial_no'].' )'; 
        }

        $params = array();
        $params['=']['pp_admin_id'] = $this->_ADMIN_DATA['id'];
        $pp_data = $this->people_tb_model->getList($params)->getData();
        $pp_data = array_shift($pp_data);
        if( ! isset($row['mtn_people_id']) || strlen($row['mtn_people_id']) < 1 ) {
            $row['mtn_people_id'] = $pp_data['pp_id'];
            $row['mtn_people_name'] = $pp_data['pp_name'];
        }


        $data = array();
        $data['mode'] = $mode;
        $data['row'] = $row;

        $type_data = $this->maintenance_tb_model->getTypeMap();
        $data['select_type'] = getSearchSelect($type_data, 'mtn_type', $row['mtn_type'], 'required'); 

        $supplier_data = $this->supplier_tb_business->getOptionMap();
        $data['select_supplier'] = getGroupSearchSelect($supplier_data, 'mtn_supplier_id', $row['mtn_supplier_id']); 

		$this->_view('assets/maintenance_detail', $data);
    }


    public function maintenance_process() {

		$this->load->model('maintenance_tb_model');
        $req = $this->input->post();
        //echo print_r($req); exit;

        if($this->input->is_ajax_request()) {
            $req['request'] = 'ajax';
        }
        $sess = array();
        $log_array = array();
        $row_data = array();

        $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/assets/maintenance';
        
        $field_list = $this->maintenance_tb_model->getFields();
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
            if( ! $this->maintenance_tb_model->get($req['mtn_id'])->isSuccess()) {
                $log_array['msg'] = getAlertMsg('INVALID_SUBMIT');
                $this->common->alert($log_array['msg']);
                $this->common->locationhref($rtn_url);
                $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['mtn_id'], $log_array, 'maintenance_tb');
                return;
            }
            $row_data = $this->maintenance_tb_model->getData();
            $log_array['prev_data'] = $row_data;
        }
        

        //echo print_r($data_params).PHP_EOL; exit;

        switch($req['mode']) {

            case 'delete':
                
                $data_params = array();
                $log_array['update_data'] = $data_params;

                if( ! $maintenancek_tb_model->doDelete($row_data['myn_id'])->isSuccess()) {
                    $log_array['msg'] = $this->maintenance_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['mtn_id'], $log_array, 'maintenance_tb');
                    return;
                }
                $this->common->write_history_log($sess, 'DELETE', $req['mtn_id'], $log_array, 'maintenance_tb');
                $this->common->locationhref($rtn_url);
                return;

                break;

            case 'update':

                $tn_url = '/'.SHOP_INFO_ADMIN_DIR.'/assets/maintenance_detail/'.$req['mtn_id'];
                $log_array['params'] = $data_params;
                if( ! $this->maintenance_tb_model->doUpdate($req['mtn_id'], $data_params)->isSuccess()) {
                    $log_array['msg'] = $this->maintenance_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['mtn_id'], $log_array, 'maintenance_tb');
                    return;
                }
                $this->common->write_history_log($sess, 'UPDATE', $req['mtn_id'], $log_array, 'maintenance_tb');
                break;
            
            case 'insert':

                if( ! isset($data_params['mtn_assets_model_id'])) {
                    $log_array['msg'] = getAlertMsg('REQUIRED_VALUES'); 
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    return;
                }

                $admin_data = $this->_ADMIN_DATA;
                $data_params['mtn_admin_id'] = $admin_data['id'];
                $data_params['mtn_writer_name'] = $admin_data['name'];

			
                $data_params['mtn_created_at'] = date('Y-m-d H:i:s');
                unset($data_params['mtn_id']);
                //echo print_r($data_params).PHP_EOL; exit; 

                $log_array['params'] = $data_params;
                if( ! $this->maintenance_tb_model->doInsert($data_params)->isSuccess()) {
                    $log_array['msg'] = $this->maintenance_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    return;
                }
				$act_key = $this->maintenance_tb_model->getData();
                $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/assets/maintenance_detail/'.$act_key;
                $this->common->write_history_log($sess, 'INSERT', $act_key, $log_array, 'maintenance_tb');
                break;
        }

        $this->common->locationhref($rtn_url);
    }




    public function ip_total($id=0) {
        $data = array();

		$this->load->model(array(
            'ip_tb_model',
            'ip_class_tb_model'
        ));
        $this->load->business(array(
            'location_tb_business',
        ));

        $req = $this->input->post();
		$id = intval($id);

        $params = array();
        $extras = array();
        $extras['order_by'] = array('ipc_location_id ASC', 'ipc_start ASC');

        $count = $this->ip_class_tb_model->getCount($params)->getData();
        $rows = $this->ip_class_tb_model->getList($params, $extras)->getData();

        // 통합검색모드
        if(isset($req['is_search']) && $req['is_search'] == 'YES') {
        

        }else {
            if($id > 0) {
                $ipc_data = $this->ip_class_tb_model->get($id)->getData();
                $data['ipc_id'] = $ipc_data['ipc_id'];
                $data['ipc_type'] = $ipc_data['ipc_type'];
                $data['ipc_category'] = $ipc_data['ipc_category'];
            }else {
                $data['ipc_id'] = $rows[0]['ipc_id'];
                $data['ipc_type'] = $rows[0]['ipc_type'];
                $data['ipc_category'] = $rows[0]['ipc_category'];
            }
        }
        $rows = $this->common->getDataByDuplPK($rows, 'ipc_location_id');

        $data['req'] = $req;
        $data['rows'] = $rows;
        $data['location_map'] = $this->location_tb_business->getNameMap();

        //echo print_r($data);
		$this->_view('assets/ip_total', $data);
    }


    public function pre_aim_process() {

        $req = $this->input->post();
        //echo print_r($req); exit;

        $vm_data = array(
            'am_id'                 => $req['am_id'],
            'aim_id'                => $req['vm_aim_id'],
            'ip_id'                 => $req['vm_ip_id'], 
            'ip_class_id'           => $req['vm_ip_class_id'],
            'ip_class_type'         => $req['vm_ip_class_type'],
            'ip_class_category'     => $req['vm_ip_class_category'],
            'mode'                  => $req['vm_mode'],
            'ip_address'            => $req['vm_ip_address'],
            'ip_memo'               => $req['vm_ip_memo'],
            'am_vmware_name'        => $req['am_vmware_name'],
        );
        //echo print_r($vm_data); exit;
        $res = $this->aim_process($vm_data);
        //echo print_r($res);


        $idrac_data = array(
            'am_id'                 => $req['am_id'],
            'aim_id'                => $req['aim_id'],
            'ip_id'                 => $req['ip_id'], 
            'ip_class_id'           => $req['ip_class_id'],
            'ip_class_type'         => $req['ip_class_type'],
            'ip_class_category'     => $req['ip_class_category'],
            'mode'                  => $req['idrac_mode'],
            'ip_address'            => $req['ip_address'],
            'ip_memo'               => $req['ip_memo'],
        );
        //echo print_r($idrac_data); exit;
        $res = $this->aim_process($idrac_data);
        //echo print_r($res);


        // 일단 무조건 TRUE
        $json_data = array(
            'is_success' => TRUE,
            'msg'        => ''
        );

        echo json_encode($json_data);
        return;
    }

    public function del_aim_process() {

        $this->load->business(array(
            'assets_ip_map_tb_business',
        ));

        $json_data = array(
            'is_success' => FALSE,
            'msg'       => ''
        );

        $req = $this->input->post();

        // Delete IP Address
        $this->assets_ip_map_tb_business->deleteRowAndIP($req['aim_id']);
            
        // Delete iDrac IP Address
        $this->assets_ip_map_tb_business->deleteRowAndIP($req['idrac_aim_id']);
        
        $json_data['is_success'] = true;
        echo json_encode($json_data);
        return;
    }

    public function aim_process($params=array()) {

        $this->load->model(array(
            'assets_model_tb_model',
            'ip_tb_model',
            'assets_ip_map_tb_model',
        ));

        $json_data = array(
            'is_success' => FALSE,
            'msg'       => ''
        );


        if( sizeof($params) > 0 ) {
            $req = $params;
            $req['request'] = 'function';
        }else {
            $req = $this->input->post();
            if($this->input->is_ajax_request()) $req['request'] = 'ajax';
        }
        //echo print_r($req); exit;

        $sess = array();
        $log_array = array(
            'is_success'    => FALSE,
            'msg'           => ''
        );
        $row_data = array();

        // 삭제 & 업데이트 시 기존데이터 검증
        $row_data = array();
        if($req['mode'] == 'update' || $req['mode'] == 'delete') {
            if( ! $this->assets_ip_map_tb_model->get($req['aim_id'])->isSuccess()) {
                $json_data['msg'] = getAlertMsg('INVALID_SUBMIT');

                switch($req['request']) {
                    
                    case 'ajax':
                        echo json_encode($json_data);
                        return;
                        break;

                    case 'function':
                        return $json_data;
                        break;

                    default:
                        $this->common->alert($log_array['msg']);
                        $this->common->locationhref($rtn_url);
                        $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['aim_id'], $log_array, 'assets_ip_map_tb');
                        return;
                        break;
                
                }
            }
            $row_data = $this->assets_ip_map_tb_model->getData();
            $log_array['prev_data'] = $row_data;
        }


        switch($req['mode']) {

            case 'insert':

                if(isset($req['am_vmware_name'])) {
                    $data_params = array(
                        'am_vmware_name' => $req['am_vmware_name']
                    );
                    $this->assets_model_tb_model->doUpdate($req['am_id'], $data_params);
                }

                $params = array(
                    'ip_class_id'       => $req['ip_class_id'],
                    'ip_class_type'     => $req['ip_class_type'],
                    'ip_class_category' => $req['ip_class_category'],
                    'ip_address'        => $req['ip_address'],
                    'ip_memo'           => $req['ip_memo'],
                    'ip_created_at'     => date('Y-m-d H:i:s'), 
                    'ip_updated_at'     => date('Y-m-d H:i:s')
                );
                $log_array['params']['ip_tb'] = $params;
                if( ! $this->ip_tb_model->doInsert($params)->isSuccess() ) {
                    $log_array['msg'] = $this->ip_tb_model->getErrorMsg();    
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['ip_id'], $log_array, 'ip_tb');
                    $json_data['msg'] = getAlertMsg('FAILED_INSERT'); 
                }else {

                    $ip_id = $this->ip_tb_model->getData();
                    $params = array(
                        'aim_assets_model_id'   => $req['am_id'], 
                        'aim_ip_id'             => $ip_id,
                    );
                    $log_array['params']['assets_ip_map_tb'] = $params;
                    $this->assets_ip_map_tb_model->doInsert($params);
                    $json_data['is_success'] = TRUE;    
                }

                $this->common->write_history_log($sess, 'INSERT', $ip_id, $log_array, 'assets_ip_map_tb');
                break;


            case 'update':

                if(isset($req['am_vmware_name'])) {
                    $data_params = array(
                        'am_vmware_name' => $req['am_vmware_name']
                    );
                    $log_array['params']['assets_model_tb'] = $data_params;
                    $this->assets_model_tb_model->doUpdate($req['am_id'], $data_params);
                }

                $data_params = array(
                    'ip_class_id'       => $req['ip_class_id'],
                    'ip_class_type'     => $req['ip_class_type'],
                    'ip_class_category' => $req['ip_class_category'],
                    'ip_address'        => $req['ip_address'],
                    'ip_memo'           => $req['ip_memo'],
                    'ip_updated_at'     => date('Y-m-d H:i:s')
                );
                $log_array['params']['ip_tb'] = $data_params;
                if( ! $this->ip_tb_model->doUpdate($req['ip_id'], $data_params)->isSuccess() ) {
                    $log_array['msg'] = $this->ip_tb_model->getErrorMsg();    
                    $json_data['msg'] = getAlertMsg('FAILED_UPDATE'); 
                }

                $data_params = array(
                    'aim_assets_model_id'   => $req['am_id'], 
                    'aim_ip_id'             => $req['ip_id'],
                );
                $this->assets_ip_map_tb_model->doUpdate($req['aim_id'], $data_params);
                $json_data['is_success'] = TRUE;    

                $log_array['params']['assets_ip_map_tb'] = $data_params;
                $this->common->write_history_log($sess, 'UPDATE', $req['ip_id'], $log_array, 'assets_ip_map_tb');
                break;

            case 'delete':

                $this->assets_ip_map_tb_model->doDelete($req['aim_id']);
                $this->ip_tb_model->doDelete($row_data['aim_ip_id']);

                $json_data['is_success'] = TRUE;    

                $log_array['params']['assets_ip_map_tb'] = $req['aim_id'];
                $log_array['params']['ip_tb'] = $row_data['aim_ip_id'];
                $this->common->write_history_log($sess, 'DELETE', $row_data['aim_ip_id'], $log_array, 'assets_ip_map_tb');
                break;

        } // END switch


        // request 형태별 결과 정의 
        switch($req['request']) {
            
            case 'ajax':
                echo json_encode($json_data);
                return;
                break;

            case 'function':
                return $json_data;
                break;

            default:
                $this->common->alert($log_array['msg']);
                $this->common->locationhref($rtn_url);
                $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['aim_id'], $log_array, 'assets_ip_map_tb');
                return;
                break;
        }
    }



    public function dim_process($params=array()) {

        $this->load->model(array(
            'assets_model_tb_model',
            'ip_tb_model',
            'direct_ip_map_tb_model',
        ));

        $json_data = array(
            'is_success' => FALSE,
            'msg'       => ''
        );


        if( sizeof($params) > 0 ) {
            $req = $params;
            $req['request'] = 'function';
        }else {
            $req = $this->input->post();
            if($this->input->is_ajax_request()) $req['request'] = 'ajax';
        }

        $sess = array();
        $log_array = array(
            'is_success'    => FALSE,
            'msg'           => ''
        );
        $row_data = array();

        // 삭제 & 업데이트 시 기존데이터 검증
        $row_data = array();
        if($req['mode'] == 'update' || $req['mode'] == 'delete') {
            if( ! $this->direct_ip_map_tb_model->get($req['dim_id'])->isSuccess()) {
                $log_array['msg'] = getAlertMsg('INVALID_SUBMIT');

                switch($req['request']) {
                    
                    case 'ajax':
                        echo json_encode($log_array);
                        return;
                        break;

                    case 'function':
                        return $log_array;
                        break;

                    default:
                        $this->common->alert($log_array['msg']);
                        $this->common->locationhref($rtn_url);
                        $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['dim_id'], $log_array, 'direct_ip_map_tb');
                        return;
                        break;
                
                }
            }
            $row_data = $this->direct_ip_map_tb_model->getData();
            $log_array['prev_data'] = $row_data;
        }


        switch($req['mode']) {

            case 'insert':
                $params = array(
                    'ip_class_id'       => $req['ip_class_id'],
                    'ip_class_type'     => $req['ip_class_type'],
                    'ip_class_category' => $req['ip_class_category'],
                    'ip_address'        => $req['ip_address'],
                    'ip_memo'           => $req['ip_memo'],
                    'ip_created_at'     => date('Y-m-d H:i:s'), 
                    'ip_updated_at'     => date('Y-m-d H:i:s')
                );

                $log_array['params']['ip_tb'] = $params;
                if( ! $this->ip_tb_model->doInsert($params)->isSuccess() ) {
                    $json_data['msg'] = $this->ip_tb_model->getErrorMsg();    
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['ip_id'], $log_array, 'ip_tb');
                    $json_data['msg'] = getAlertMsg('FAILED_INSERT'); 
                }else {

                    $ip_id = $this->ip_tb_model->getData();
                    $params = array(
                        'dim_assets_model_id'   => $req['am_id'], 
                        'dim_ip_id'             => $ip_id,
                    );
                    $log_array['params']['direct_ip_map_tb'] = $params;
                    $this->direct_ip_map_tb_model->doInsert($params);
                    $json_data['is_success'] = TRUE;    
                }
                $this->common->write_history_log($sess, 'INSERT', $ip_id, $log_array, 'ip_tb');
                break;


            case 'update':

                $data_params = array(
                    'ip_class_id'       => $req['ip_class_id'],
                    'ip_class_type'     => $req['ip_class_type'],
                    'ip_class_category' => $req['ip_class_category'],
                    'ip_address'        => $req['ip_address'],
                    'ip_memo'           => $req['ip_memo'],
                    'ip_updated_at'     => date('Y-m-d H:i:s')
                );
                $log_array['params']['ip_tb'] = $data_params;
                if( ! $this->ip_tb_model->doUpdate($req['ip_id'], $data_params)->isSuccess() ) {
                    $json_data['msg'] = $this->ip_tb_model->getErrorMsg();    
                }

                $data_params = array(
                    'dim_assets_model_id'   => $req['am_id'], 
                    'dim_ip_id'             => $req['ip_id'],
                );
                $log_array['params']['direct_ip_map_tb'] = $data_params;
                $this->direct_ip_map_tb_model->doUpdate($req['dim_id'], $data_params);
                $json_data['is_success'] = TRUE;    

                $this->common->write_history_log($sess, 'UPDATE', $req['ip_id'], $log_array, 'ip_tb');
                break;

            case 'delete':

                $this->direct_ip_map_tb_model->doDelete($req['dim_id']);
                $this->ip_tb_model->doDelete($row_data['dim_ip_id']);

                $json_data['is_success'] = TRUE;    

                $log_array['params']['ip_tb'] = $row_data['dim_ip_id'];
                $log_array['params']['direct_ip_map_tb'] = $req['dim_id'];
                $this->common->write_history_log($sess, 'DELETE', $row_data['dim_ip_id'], $log_array, 'ip_tb');
                break;

        } // END switch


        // request 형태별 결과 정의 
        switch($req['request']) {
            
            case 'ajax':
                echo json_encode($json_data);
                return;
                break;

            case 'function':
                return $json_data;
                break;

            default:
                $this->common->alert($log_array['msg']);
                $this->common->locationhref($rtn_url);
                $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['dim_id'], $log_array, 'direct_ip_map_tb');
                return;
                break;
        }
    }




    public function vmservice() {


        $data = array();

		$this->load->model(array(
            'vmservice_tb_model',
            'vmservice_ip_map_tb_model',
            'service_manage_tb_model',
            'ip_tb_model',
        ));
		$this->load->business(array(
            'assets_model_tb_business',
        ));
       
        $req = $this->input->post();
        $data = array();

        if(isset($req['mode']) && $req['mode'] == 'list') {

            $out_data = array();
            $params = array();
            $extras = array();

            //echo print_r($req);
            $fields = $this->vmservice_tb_model->getFields();
            $params = $this->common->transDataTableFiltersToParams($req, $fields);
            $extras = $this->common->transDataTableFiltersToExtras($req);
            //echo print_r($params); exit;

            if( isset($req['assets_model_id']) && $req['assets_model_id'] > 0 ) {
                $params['=']['vms_assets_model_id'] = $req['assets_model_id'];
            }
            $params['<']['vms_alias_id'] = '1';
            $params['join']['vmservice_ip_map_tb'] = 'vim_vmservice_id = vms_id';
            $params['left_join']['service_manage_tb'] = 'vim_vmservice_id = sm_vmservice_id';
            $params['join']['ip_tb'] = 'ip_id = vim_ip_id';


            $extras['fields'] = array(
                'vms_id', 'vms_name', 'vms_memo', 'vms_status', 
                'ip_address as vms_ip_address', 'ip_id as vms_ip_id', 'ip_memo',
                'service_manage_tb.*'
            );
            $extras['order_by'] = array('INET_ATON(ip_address) ASC');

            $count = $this->vmservice_tb_model->getCount($params)->getData();
            $rows = $this->vmservice_tb_model->getList($params, $extras)->getData();


            $status_data = $this->vmservice_tb_model->getStatusText();

            // VMWare
            $am_ids = array($req['assets_model_id']);
            $res = $this->assets_model_tb_business->getVMWareIP($am_ids);
            $vmware_data = $res[$req['assets_model_id']];

            $data = array();
            foreach($rows as $k=>$r){
                $r['vms_vmware_ip'] = $vmware_data['ip_address'];
                $r['vms_status'] = $this->vmservice_tb_model->getStatusBadge($r['vms_status']);

                $data[] = $r;
            }

            $json_data = new stdClass;
            $json_data->draw = $req['draw'];
            $json_data->recordsTotal = $count;
            $json_data->recordsFiltered = $count;
            $json_data->data = $data;

            echo json_encode($json_data);
            return;
        }

		$this->_view('assets/vmservice', $data);
    }



    public function vmservice_process() {

		$this->load->model(array(
            'vmservice_tb_model',
            'vmservice_ip_map_tb_model',
            'ip_tb_model',
            'service_manage_tb_model',
        ));

        $this->load->business(array(
            'ip_class_tb_business',
        ));

        $req = $this->input->post();
        if($this->input->is_ajax_request()) {
            $req['request'] = 'ajax';
        }
        //echo print_r($req);
        //exit;

        $sess = array();
        $log_array = array(
            'is_success'    => FALSE,
            'msg'           => ''
        );
        $row_data = array();

        $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/assets/vmservice';
        
        $field_list = $this->vmservice_tb_model->getFields();
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


        // [service_manage_tb] Fields
        $sm_field_list = $this->service_manage_tb_model->getFields();
        $sm_params = array();
        foreach($sm_field_list as $key) {
            if(array_key_exists($key, $req)) {
                $sm_params[$key] = $req[$key];
                continue;
            } 
        }

        // 삭제 & 업데이트 시 기존데이터 검증
        $row_data = array();
        if($req['mode'] == 'update' || $req['mode'] == 'delete') {
            if( ! $this->vmservice_tb_model->get($req['vms_id'])->isSuccess()) {
                $log_array['msg'] = getAlertMsg('INVALID_SUBMIT');
                if($req['request'] == 'ajax') {
                     echo json_encode($log_array);
                }else {
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['vms_id'], $log_array, 'vmservice_tb');
                }
                return;
            }
            $row_data = $this->vmservice_tb_model->getData();
            $log_array['prev_data'] = $row_data;
        }


        switch($req['mode']) {

            case 'delete':

                $log_array['params']['vmservice_tb'] = $req['vms_id'];
                if( ! $this->vmservice_tb_model->doDelete($req['vms_id'])->isSuccess()) {
                    $log_array['msg'] = $this->vmservice_tb_model->getErrorMsg();
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['vms_id'], $log_array, 'vmservice_tb');
                }else {

                    $log_array['params']['ip_tb'] = $req['vms_ip_id'];
                    $this->ip_tb_model->doDelete($req['vms_ip_id']);

                    $where_params = array();
                    $where_params['=']['vim_vmservice_id'] = $req['vms_id'];

                    $log_array['params']['vmservice_ip_map_tb'] = $where_params;
                    $this->vmservice_ip_map_tb_model->doMultiDelete($where_params);


                    $where_params = array();
                    $where_params['=']['sm_vmservice_id'] = $req['vms_id'];
                    $log_array['params']['service_manage_map_tb'] = $where_params;
                    $this->service_manage_tb_model->doMultiDelete($where_params);

                    $this->common->write_history_log($sess, 'DELETE', $req['vms_id'], $log_array, 'vmservice_tb');
                }
                break;

            case 'update':

                $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/assets/vmservice_detail/'.$req['vms_id'];

                /*
                // UNIQUE KEY 
                $params = array();
                $params['!=']['vms_id'] = $req['vms_id'];
                $params['=']['vms_name'] = $data_params['vms_name'];
                $cnt = $this->vmservice_tb_model->getCount($params)->getData();
                if($cnt > 0) {
                    $log_array['msg'] = getAlertMsg('DUPLICATE_VALUES').' [Name]';
                    if($req['request'] == 'ajax') {
                         echo json_encode($log_array);
                    }else {
                        $this->common->alert($log_array['msg']);
                        $this->common->locationhref($rtn_url);
                    }
                    return;
                }
                */

                $log_array['params']['vmservice_tb'] = $data_params;
                //echo print_r($data_params); //exit;
                if( ! $this->vmservice_tb_model->doUpdate($req['vms_id'], $data_params)->isSuccess()) {
                    $log_array['msg'] = $this->vmservice_tb_model->getErrorMsg();
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['vms_id'], $log_array, 'vmservice_tb');
                }else {

                    // VMService 입력 성공시, ip 입력
                    $params = array(
                        'ip_id' => $req['vms_ip_id'],
                        'ip'    => $req['vms_ip_address'] 
                    );
                    $res = $this->ip_class_tb_business->checkIPinClass($params);
                    if( $res['is_success'] ) {
                        $params = array(
                            'ip_class_id'           => $res['msg']['ipc_id'],
                            'ip_class_type'         => $res['msg']['ipc_type'],
                            'ip_class_category'     => $res['msg']['ipc_category'],
                            'ip_address'            => $req['vms_ip_address'],
                            'ip_memo'               => $req['ip_memo'],
                            'ip_updated_at'         => date('Y-m-d H:i:s'),
                        );
                        $log_array['params']['ip_tb'] = $params;
                        $this->ip_tb_model->doUpdate($req['vms_ip_id'], $params);
                    }


                    // [service_manage_tb]
                    if(sizeof($sm_params) > 0) { 

                        $params = $sm_params;
                        $params['sm_updated_at'] = date('Y-m-d H:i:s');

                        $where_params = array();
                        $where_params['=']['sm_assets_model_id'] = $data_params['vms_assets_model_id'];
                        $where_params['=']['sm_vmservice_id'] = $data_params['vms_id'];

                        $sm_data = $this->service_manage_tb_model->getList($where_params)->getData();

                        $log_array['params']['service_manage_tb'] = $params;
                        if( sizeof($sm_data) > 0 ) {
                            $sm_data = array_shift($sm_data); 
                            $this->service_manage_tb_model->doUpdate($sm_data['sm_id'], $params);    
                        }else {
                            $params['sm_assets_model_id'] = $data_params['vms_assets_model_id'];
                            $params['sm_vmservice_id'] = $data_params['vms_id'];
                            $this->service_manage_tb_model->doInsert($params);    
                        }
                    }
                    $this->common->write_history_log($sess, 'UPDATE', $req['vms_id'], $log_array, 'vmservice_tb');
                }
                break;
            
            case 'insert':

                
                unset($data_params['vms_id']);
                /*
                if( ! isset($data_params['vms_name']) || strlen($data_params['vms_name']) < 1 ) {
                    $log_array['msg'] = getAlertMsg('REQUIRED_VALUES').' [Name]';
                    if($req['request'] == 'ajax') {
                         echo json_encode($log_array);
                    }else {
                        $this->common->alert($log_array['msg']);
                        $this->common->locationhref($rtn_url);
                    }
                    return;
                }
                */

                if( ! isset($req['vms_ip_address']) || strlen($req['vms_ip_address']) < 1 ) {
                    $log_array['msg'] = getAlertMsg('REQUIRED_VALUES').' [IP Address]';
                    if($req['request'] == 'ajax') {
                         echo json_encode($log_array);
                    }else {
                        $this->common->alert($log_array['msg']);
                        $this->common->locationhref($rtn_url);
                    }
                    return;
                }


                // IP Class Validation 
                $params = array(
                    'ip'    => $req['vms_ip_address'] 
                );
                $ip_res = $this->ip_class_tb_business->checkIPinClass($params);
                if( $ip_res['is_success'] == FALSE ) {
                    $log_array['msg'] = $ip_res['msg'];
                    if($req['request'] == 'ajax') {
                         echo json_encode($log_array);
                    }else {
                        $this->common->alert($log_array['msg']);
                        $this->common->locationhref($rtn_url);
                    }
                    return;
                }


                /*
                // UNIQUE 
                $cnt = $this->vmservice_tb_model->getCount(array('=' => array('vms_name' => $data_params['vms_name'])))->getData();
                if($cnt > 0) {
                    $log_array['msg'] = getAlertMsg('DUPLICATE_VALUES').' [Name]'; 
                    if($req['request'] == 'ajax') {
                         echo json_encode($log_array);
                    }else {
                        $this->common->alert($log_array['msg']);
                        $this->common->locationhref($rtn_url);
                    }
                    return;
                }
                */

                $data_params['vms_created_at'] = date('Y-m-d H:i:s');
                unset($data_params['vms_id']);

                //echo print_r($data_params); exit;
                $log_array['params']['vmservice_tb'] = $data_params;
                if( ! $this->vmservice_tb_model->doInsert($data_params)->isSuccess()) {
                    $log_array['msg'] = $this->vmservice_tb_model->getErrorMsg();
                }else {
                    $act_key = $this->vmservice_tb_model->getData();

                    $params = array(
                        'ip_class_id'           => $ip_res['msg']['ipc_id'],
                        'ip_class_type'         => $ip_res['msg']['ipc_type'],
                        'ip_class_category'     => $ip_res['msg']['ipc_category'],
                        'ip_address'            => $req['vms_ip_address'],
                        'ip_memo'               => $req['ip_memo'],
                        'ip_created_at'         => date('Y-m-d H:i:s'),
                        'ip_updated_at'         => date('Y-m-d H:i:s'),
                    );
                    $log_array['params']['ip_tb'] = $params;
                    $ip_id = $this->ip_tb_model->doInsert($params)->getData();

                    $params = array(
                        'vim_vmservice_id'  => $act_key,
                        'vim_ip_id'         => $ip_id,
                    );
                    $log_array['params']['vmservice_ip_map_tb'] = $params;
                    $this->vmservice_ip_map_tb_model->doInsert($params);


                    // [service_manage_tb]
                    if(sizeof($sm_params) > 0) { 
                        $sm_params['sm_assets_model_id'] = $req['vms_assets_model_id'];
                        $sm_params['sm_vmservice_id'] = $act_key;
                        $sm_params['sm_created_at'] = date('Y-m-d H:i:s');
                        $sm_params['sm_updated_at'] = date('Y-m-d H:i:s');

                        $log_array['params']['service_manage_tb'] = $sm_params;
                        $this->service_manage_tb_model->doInsert($sm_params);
                    }

                    $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/assets/vmservice_detail/'.$act_key;
                    $this->common->write_history_log($sess, 'INSERT', $act_key, $log_array, 'vmservice_tb');
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


    public function alias() {

		$this->load->model(array(
            'ip_tb_model',
            'vmservice_tb_model',
            'vmservice_ip_map_tb_model',
        ));

        $req = $this->input->post();

        if( ! isset($req['assets_model_id']) || $req['assets_model_id'] < 1 ) {
            return;
            exit;
        }

        $params = array();
        $params['>=']['vms_alias_id'] = '1';
        $params['=']['vms_assets_model_id'] = $req['assets_model_id'];
        $params['join']['vmservice_ip_map_tb'] = 'vim_vmservice_id = vms_id';
        $params['join']['ip_tb'] = 'vim_ip_id = ip_id';  
        $extras = array();
        $extras['fields'] = array('ip_tb.*', 'vms_id', 'vms_name', 'vms_memo', 'vms_alias_id');
        $rows = $this->vmservice_tb_model->getList($params, $extras)->getData();
        //echo $this->vmservice_tb_model->getLastQuery(); exit;

        $alias_ids = array_keys($this->common->getDataByPK($rows, 'vms_alias_id'));
        if(sizeof($alias_ids) > 0) {

            $params = array();
            $params['in']['vms_id'] = $alias_ids;
            $params['join']['vmservice_ip_map_tb'] = 'vim_vmservice_id = vms_id';
            $params['join']['ip_tb'] = 'vim_ip_id = ip_id';  
            $extras = array();
            $extras['fields'] = array('vms_id', 'vms_name', 'ip_id', 'ip_address', 'ip_memo' ,'vim_id');
            $parents = $this->vmservice_tb_model->getList($params, $extras)->getData();
            $parents = $this->common->getDataByPK($parents, 'vms_id');
        }

        $count = $this->vmservice_tb_model->getCount($params)->getData();

        $data = array();
        foreach($rows as $k=>$r){
            $r['vms_alias_ip'] = $parents[$r['vms_alias_id']]['ip_address'];
            $r['vms_alias_id'] = $parents[$r['vms_alias_id']]['vms_name'];
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


    public function alias_process() {

		$this->load->model(array(
            'ip_tb_model',
            'vmservice_tb_model',
            'vmservice_ip_map_tb_model',
            'assets_model_tb_model',
        ));

		$this->load->business(array(
        ));
        $req = $this->input->post();
        //echo print_r($req); exit;
        if($this->input->is_ajax_request()) {
            $req['request'] = 'ajax';
        }

        $at_map = $this->assets_type_tb_business->getNameMap();
        $at_name = strtolower($at_map[$req['am_assets_type_id']]);

        $sess = array();
        $ajax_res = array(
            'is_success'    => FALSE,
            'msg'           => ''
        );
        $log_array = array();
        $row_data = array();

        //echo print_r($req).PHP_EOL; //exit;
        $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/assets/detail/'.$at_name.'/'.$req['am_id'];
        //echo $rtn_url; exit;
        
        $field_list = $this->vmservice_tb_model->getFields();
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
            if( ! $this->vmservice_tb_model->get($req['vms_id'])->isSuccess()) {
                $log_array['msg'] = getAlertMsg('INVALID_SUBMIT');
                if($req['msg'] == 'ajax') {
                    $ajax_res['msg'] = $log_array['msg'];
                    echo json_encode($ajax_res);
                }else {
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['vms_id'], $log_array, 'vmservice_tb');
                }
                return;
            }
            $row_data = $this->vmservice_tb_model->getData();
            $log_array['prev_data'] = $row_data;
        }
        //echo print_r($data_params).PHP_EOL; exit;

        switch($req['mode']) {

            case 'delete':
                
                $data_params = array();
                $log_array['params'] = $data_params;
                if( ! $this->vmservice_tb_model->doDelete($req['vms_id'])->isSuccess()) {
                    $log_array['msg'] = $this->vmservice_tb_model->getErrorMsg();
                    if($req['request'] == 'ajax') {
                        $ajax_res['msg'] = $log_array['msg'];
                        echo json_encode($ajax_res);
                    }else {
                        $this->common->alert($log_array['msg']);
                        $this->common->locationhref($rtn_url);
                        $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['vms_id'], $log_array, 'vmservice_tb');
                    }
                    return;
                }

                $params = array();
                $params['=']['vim_vmservice_id'] = $req['vms_id'];
                $vim_data = $this->vmservice_ip_map_tb_model->getList($params)->getData();
                $vim_data = array_shift($vim_data);
                $log_array['prev_data'] = array_merge($log_array['prev_data'], $vim_data);

                // [ip_tb / vmservice_ip_map_tb] DELETE 
                $this->vmservice_ip_map_tb_model->doDelete($vim_data['vim_id']);
                $this->ip_tb_model->doDelete($vim_data['vim_ip_id']);
                $this->common->write_history_log($sess, 'DELETE', $req['vms_id'], $log_array, 'vmservice_tb');

                if($req['request'] == 'ajax') {
                    $ajax_res['is_success'] = true; 
                    echo json_encode($ajax_res);
                }else {
                    $am_data = $this->assets_model_tb_model->get($row_data['vms_assets_model_id'])->getData();
                    $at_name = strtolower($at_map[$am_data['am_assets_type_id']]);
                    $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/assets/detail/'.$at_name.'/'.$am_data['am_id'];
                    $this->common->locationhref($rtn_url);
                }
                return;
                break;

            case 'update':

                if( $req['type'] == 'alias' ) {
                    $alias_data = $this->vmservice_tb_model->get($data_params['vms_alias_id'])->getData();
                    $data_params['vms_assets_model_id'] = $alias_data['vms_assets_model_id'];
                }

                $log_array['params'] = $data_params;
                if( ! $this->vmservice_tb_model->doUpdate($req['vms_id'], $data_params)->isSuccess()) {
                    $log_array['msg'] = $this->vmservice_tb_model->getErrorMsg();

                    if($req['request'] == 'ajax') {
                        $ajax_res['msg'] = $log_array['msg'];
                        echo json_encode($ajax_res);
                    }else {
                        $this->common->alert($log_array['msg']);
                        $this->common->locationhref($rtn_url);
                        $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['vms_id'], $log_array, 'vmservice_tb');
                    }
                    return;
                }

                // ip_tb UPDATE
                $up = array();
                $up_params['ip_class_id']           = $req['ip_class_id'];
                $up_params['ip_class_type']         = $req['ip_class_type'];
                $up_params['ip_class_category']     = $req['ip_class_category'];
                $up_params['ip_address']            = $req['ip_address'];
                $up_params['ip_memo']               = $req['vms_memo'];
                $up_params['ip_updated_at']         = date('Y-m-d H:i:s');
                $this->ip_tb_model->doUpdate($req['ip_id'], $up_params);
                $log_array['params'] = array_merge($log_array['params'], $up_params);

                $this->common->write_history_log($sess, 'UPDATE', $req['vms_id'], $log_array, 'vmservice_tb');
                break;
            
            case 'insert':

                if( $req['type'] == 'alias' ) {
                    $alias_data = $this->vmservice_tb_model->get($data_params['vms_alias_id'])->getData();
                    $data_params['vms_assets_model_id'] = $alias_data['vms_assets_model_id'];
                }


                if( ! isset($data_params['vms_name'])) {
                    $log_array['msg'] = getAlertMsg('REQUIRED_VALUES'); 

                    if($req['request'] == 'ajax') {
                        $ajax_res['msg'] = $log_array['msg'];
                        echo json_encode($ajax_res);
                    }else {
                        $this->common->alert($log_array['msg']);
                        $this->common->locationhref($rtn_url);
                    }
                    return;
                }
			
                $data_params['vms_created_at'] = date('Y-m-d H:i:s');
                unset($data_params['vms_id']);

                $log_array['params'] = $data_params;
                //echo print_r($data_params); exit;
                if( ! $this->vmservice_tb_model->doInsert($data_params)->isSuccess()) {
                    $log_array['msg'] = $this->vmservice_tb_model->getErrorMsg();

                    if($req['request'] == 'ajax') {
                        $ajax_res['msg'] = $log_array['msg'];
                        echo json_encode($ajax_res);
                    }else {
                        $this->common->alert($log_array['msg']);
                        $this->common->locationhref($rtn_url);
                    }
                    return;
                }

				$act_key = $this->vmservice_tb_model->getData();

                // ip_tb INSERT
                $in_params = array();
                $in_params['ip_class_id']           = $req['ip_class_id'];
                $in_params['ip_class_type']         = $req['ip_class_type'];
                $in_params['ip_class_category']     = $req['ip_class_category'];
                $in_params['ip_address']            = $req['ip_address'];
                $in_params['ip_memo']               = $req['vms_memo'];
                $in_params['ip_created_at']         = date('Y-m-d H:i:s');
                $in_params['ip_updated_at']         = date('Y-m-d H:i:s');
                $ip_id = $this->ip_tb_model->doInsert($in_params)->getData();
                $log_array['params'] = array_merge($log_array['params'], $in_params);

                // vmservice_ip_map_tb INSERT
                $in_params = array();
                $in_params['vim_vmservice_id'] = $act_key;
                $in_params['vim_ip_id'] = $ip_id;
                $this->vmservice_ip_map_tb_model->doInsert($in_params)->getData();
                $log_array['params'] = array_merge($log_array['params'], $in_params);


                $this->common->write_history_log($sess, 'INSERT', $act_key, $log_array, 'vmservice_tb');
                break;
        }


        if($req['request'] == 'ajax') {
            $ajax_res['is_success'] = true;
            echo json_encode($ajax_res);
        }else {
            $this->common->locationhref($rtn_url);
        }
    }



    public function ajax_popup_alias() {

        $req = $this->input->post();
        //echo print_r($req); exit;

		$this->load->model(array(
            'ip_tb_model',
            'vmservice_tb_model',
            'vmservice_ip_map_tb_model',
            'assets_model_tb_model',
        ));
		$this->load->business(array(
            'vmservice_tb_business'
        ));


        if( ! isset($req['am_id']) || $req['am_id'] < 1 ) {
            $json_data['msg'] = getAlertMsg('INVALID_SUBMIT');    
             echo json_encode($json_data);
            return;
        }

        if( $req['mode'] == 'update' &&  ( ! isset($req['vms_id']) || $req['vms_id'] < 1) ) {
            $json_data['msg'] = getAlertMsg('INVALID_SUBMIT');    
             echo json_encode($json_data);
            return;
        }


        $json_data = array(
            'is_success' => FALSE,
            'msg'       => ''
        );


        $row = array();
        $fields = $this->vmservice_tb_model->getFields();
        foreach($fields as $f) {
            $row[$f] = ''; 
        }


        if( $req['mode'] == 'update' ) {
            $params = array();
            $params['=']['vms_id'] = $req['vms_id'];
            $params['join']['vmservice_ip_map_tb'] = 'vim_vmservice_id = vms_id';
            $params['join']['ip_tb'] = 'vim_ip_id = ip_id';
            $extras = array();
            $extras['fields'] = array('vms_id', 'vms_name', 'vms_memo', 'vms_alias_id', 'ip_id', 'ip_address', 'vim_id') ;

            $row = $this->vmservice_tb_model->getList($params, $extras)->getData();
            $row = array_shift($row);
        }

        $service_data = $this->vmservice_tb_business->get_service_for_alias($req['am_id']);
        $sel_services = getSearchSelect($service_data, 'vms_alias_id', $row['vms_alias_id'], 'required'); 

        $assign_data = $req; 
        $assign_data['sel_services'] = $sel_services;
        $assign_data['am_data'] = $this->assets_model_tb_model->get($req['am_id'])->getData();
        $assign_data['row'] = $row;


        $json_data['is_success'] = TRUE;
        $json_data['msg'] = $this->load->view('admin/default_template/assets/alias_template.php', $assign_data, true);
        echo json_encode($json_data);
    }


    public function ajax_popup_vmservice() {

        $req = $this->input->post();
        //echo print_r($req); exit;

		$this->load->model(array(
            'ip_tb_model',
            'vmservice_tb_model',
            'vmservice_ip_map_tb_model',
            'assets_model_tb_model',
            'service_manage_tb_model',
        ));
		$this->load->business(array(
            'vmservice_tb_business'
        ));


        if( ! isset($req['am_id']) || $req['am_id'] < 1 ) {
            $json_data['msg'] = getAlertMsg('INVALID_SUBMIT');    
             echo json_encode($json_data);
            return;
        }

        if( $req['mode'] == 'update' &&  ( ! isset($req['vms_id']) || $req['vms_id'] < 1) ) {
            $json_data['msg'] = getAlertMsg('INVALID_SUBMIT');    
             echo json_encode($json_data);
            return;
        }


        $json_data = array(
            'is_success' => FALSE,
            'msg'       => ''
        );


        $row = array();
        $fields = $this->vmservice_tb_model->getFields();
        foreach($fields as $f) {
            if($f == 'vms_status') {
                $row[$f] = 'ACTIVE'; 
            }else {
                $row[$f] = ''; 
            }
        }

        $sm_data = array();
        if( $req['mode'] == 'update' ) {
            $params = array();
            $params['=']['vms_id'] = $req['vms_id'];
            $params['join']['vmservice_ip_map_tb'] = 'vim_vmservice_id = vms_id';
            $params['join']['ip_tb'] = 'vim_ip_id = ip_id';
            $extras = array();
            $extras['fields'] = array('vms_id', 'vms_name', 'vms_memo', 'vms_status', 'vms_alias_id', 'vim_id', 'ip_tb.*') ;

            $row = $this->vmservice_tb_model->getList($params, $extras)->getData();
            $row = array_shift($row);


            $params = array();
            $params['=']['sm_assets_model_id'] = $req['am_id'];
            $params['=']['sm_vmservice_id'] = $req['vms_id'];
            $sm_data = $this->service_manage_tb_model->getList($params)->getData();
            $sm_data = array_shift($sm_data);
        }

        $service_data = $this->vmservice_tb_business->get_service_for_alias($req['am_id']);
        $sel_services = getSearchSelect($service_data, 'vms_alias_id', $row['vms_alias_id'], 'required'); 

        $assign_data = $req; 
        $assign_data['sel_services'] = $sel_services;
        $assign_data['am_data'] = $this->assets_model_tb_model->get($req['am_id'])->getData();
        $assign_data['row'] = $row;

        if( ! isset($sm_data['sm_secure_conf']) ) $sm_data['sm_secure_conf'] = 1;
        if( ! isset($sm_data['sm_secure_inte']) ) $sm_data['sm_secure_inte'] = 1;
        if( ! isset($sm_data['sm_secure_avail']) ) $sm_data['sm_secure_avail'] = 1;

        $assign_data['sm_data'] = $sm_data;
        $assign_data['status_type'] = $this->vmservice_tb_model->getStatusText();

        $json_data['is_success'] = TRUE;
        $json_data['msg'] = $this->load->view('admin/default_template/assets/vmservice_template.php', $assign_data, true);
        echo json_encode($json_data);
    }





    public function rackview() {

        $this->load->model(array(
            'rack_tb_model',
            'assets_model_tb_model',
            'assets_type_tb_model',
            'location_tb_model',
            'status_tb_model'
        ));

        $this->load->business(array(
            'location_tb_business',
            'assets_type_tb_business',
            'status_tb_business'
        ));



        $id = $this->uri->segment(4);

        // 기본값
        $rack_data = array(
            'r_location_id' => 1,    // KINX 가산
            'r_floor'        => '5F',
            'r_section'      => 'E',
        );
        if($id > 0) {
            //echo 'YES'; exit;
            $rack_data = $this->rack_tb_model->get($id)->getData();
        }

        $location_map = $this->location_tb_business->getNameMap();

        // TYPE
        $params = array();
        $extras = array();
        $extras['fields'] = array('at_id', 'at_name', 'at_icon', 'at_color');
        $type_data = $this->assets_type_tb_model->getList($params, $extras)->getData();
        $type_data = $this->common->getDataByPK($type_data, 'at_id');


        // STATUS
        $params = array();
        $extras = array();
        $extras['fields'] = array('s_id', 's_name', 's_color_code');
        $status_data = $this->status_tb_model->getList($params, $extras)->getData();
        $status_data = $this->common->getDataByPK($status_data, 's_id');


        // TOTAL RACK
        $params = array();
        $extras = array();
        $extras['fields'] = array('r_id', 'r_location_id', 'r_floor', 'r_section', 'COUNT(r_id) AS cnt');
        $extras['group_by'] = array('r_location_id', 'r_floor', 'r_section');
        $extras['order_by'] = array('r_location_id ASC', 'r_floor DESC', 'r_section ASC');
        $tt_rows = $this->rack_tb_model->getList($params, $extras)->getData();
        $tt_rows = $this->common->getDataByDuplPK($tt_rows, 'r_location_id');



        // RACK
        $params = array();
        $params['=']['r_location_id'] = $rack_data['r_location_id'];
        $params['=']['r_floor'] = $rack_data['r_floor'];
        $params['=']['r_section'] = $rack_data['r_section'];
        $extras = array();
        $extras['order_by'] = array('r_location_id ASC', 'r_floor DESC', 'r_section ASC', 'r_frame ASC');
        $rows = $this->rack_tb_model->getList($params, $extras)->getData();
        $rows = $this->common->getDataByDuplPK($rows, 'r_location_id');
        //echo print_r($rows); exit; 


        // ASSETS
        $params = array();
        $params['=']['r_location_id'] = $rack_data['r_location_id'];
        $params['=']['r_floor'] = $rack_data['r_floor'];
        $params['=']['r_section'] = $rack_data['r_section'];
        $params['join']['assets_model_tb'] = 'am_rack_id = r_id';
        $extras = array();
        $extras['fields'] = array(
            'am_id', 'am_models_name', 'am_rack_id', 'am_rack_code', 'am_rack_order', 
            'am_assets_type_id', 'am_name', 'am_status_id', 'am_vmware_name',
            'r_id', 'r_code'
        );
        $extras['order_by'] = array('am_rack_order DESC');
        $as_data = $this->rack_tb_model->getList($params, $extras)->getData();
        $as_data = $this->common->getDataByDuplPK($as_data, 'r_id');



        $data['rack_data'] = $rack_data;
        $data['tt_rows'] = $tt_rows;
        $data['rows'] = $rows;
        $data['as_data'] = $as_data;
        $data['location_map'] = $location_map;
        $data['type_data'] = $type_data;
        $data['status_data'] = $status_data;

		$this->_view('assets/rackview', $data);
    }



    public function update_rack_order() {

        $req = $this->input->post();
        //echo print_r($req); exit;

        $json_data = array(
            'is_success' => FALSE,
            'msg'       => ''
        );


        if( ! isset($req['data']) || strlen($req['data']) < 1 ) {
            $json_data['msg'] = getAlertMsg('INVALID_SUBMIT');    
             echo json_encode($json_data);
            return;
        }

        $data = json_decode($req['data'], true);
        if( ! is_array($data) || sizeof($data) < 1 ) {
            $json_data['msg'] = getAlertMsg('INVALID_SUBMIT');    
             echo json_encode($json_data);
            return;
        }


		$this->load->model(array(
            'assets_model_tb_model',
        ));


        $data = array_reverse($data);
        foreach($data as $k=>$r) {

            if(! isset($r['id']) || $r['id'] < 1) continue;

            $data_params = array();
            $data_params['am_rack_order'] = $k+1;

            $this->assets_model_tb_model->doUpdate($r['id'], $data_params);
        }

        $json_data['is_success'] = TRUE;
        echo json_encode($json_data);
        return;
    }


    public function ajax_check_tag() {

        $json_data = array(
            'is_success' => FALSE,
            'msg'       => ''
        );
        $req = $this->input->post();

        if( ! isset($req['service_tag']) || strlen($req['service_tag']) < 1 ) {
            $json_data['msg'] = getAlertMsg('INVALID_SUBMIT');    
             echo json_encode($json_data);
            return;
        }


		$this->load->model(array(
            'order_item_tb_model',
        ));
        $params = array();
        $params['=']['oi_service_tag'] = $req['service_tag'];
        $cnt = $this->order_item_tb_model->getCount($params)->getData();

        if($cnt > 0) {
            $json_data['msg'] = '발주내역 내에 포함된 서비스태그 정보입니다.<br />';
            $json_data['msg'] .= '해당 발주상품을 선택해 등록해주세요!';
        }else {
            $json_data['is_success'] = TRUE;
        }
        echo json_encode($json_data);
        return;
    }



    // 출고/폐기 처리, 자산 점유 회수 처리
    // rack_space : empty 처리-> out_back_tb
    // IP : empty 처리 -> out_back_tb
    private function _assets_out_process($data=array()) {

		$this->load->model(array(
            'out_backup_tb_model',
            'assets_model_tb_model',
        ));

		$this->load->business(array(
            'assets_model_tb_business',
        ));

        $re_data = $this->assets_model_tb_business->getRelationTblData($data);
        //echo print_r($re_data);  //exit;

        /* 히스토리성 중복 저장 가능
        $params = array();
        $params['=']['ob_assets_model_id'] = $data['am_id'];
        $ob_data = $this->out_backup_tb_model->getList($params)->getData();
        if( sizeof($ob_data) > 0 ) {
            return FALSE;
        }
        */

        $in_params = array(
            'ob_assets_model_id' => $data['am_id'],
            'ob_vmservice'       => isset($re_data['vms_data']) ? serialize($re_data['vms_data']) : '',
            'ob_rack'            => isset($re_data['rack_data']) ? serialize($re_data['rack_data']) : '',
            'ob_ip'              => isset($re_data['ip_data']) ? serialize($re_data['ip_data']) : '',
            'ob_map_info'        => isset($re_data['map_data']) ? serialize($re_data['map_data']) : '',
        );
        if( ! $this->out_backup_tb_model->doInsert($in_params)->isSuccess() ) {
            return FALSE;
        }else {
            foreach($re_data['vmservice'] as $vms) {
                $this->vmservice_tb_model->doDelete($vms['vms_id']);
            }
            foreach($re_data['ip'] as $ip) {
                $this->ip_tb_model->doDelete($ip['ip_id']);
            }
            foreach($re_data['map']['aim'] as $aim) {
                $this->assets_ip_map_tb_model->doDelete($aim['aim_id']);
            }
            foreach($re_data['map']['dim'] as $dim) {
                $this->direct_ip_map_tb_model->doDelete($dim['dim_id']);
            }
            foreach($re_data['map']['vim'] as $vim) {
                $this->vmservice_ip_map_tb_model->doDelete($vim['vim_id']);
            }
            return TRUE;
        }
    }


    public function direct() {


        $data = array();

		$this->load->model(array(
            'assets_model_tb_model',
            'direct_ip_map_tb_model',
            'assets_ip_map_tb_model',
            'service_manage_tb_model',
            'ip_tb_model',
        ));
        $this->load->business(array(
            'location_tb_business',
            'company_tb_business',
            'status_tb_business',
        ));

        $req = $this->input->post();
        $data = array();

        $location_data = $this->location_tb_business->getNameMap();
        $company_data = $this->company_tb_business->getNameMap();
        $status_map= $this->status_tb_business->getRowMap();


        if(isset($req['mode']) && $req['mode'] == 'list') {

            $params = array();
            $extras = array();

            $fields = array(
                'am_models_name', 'am_name', 'am_vmware_name', 'am_serial_no', 'am_rack_code', 'am_tags', 'ip_memo'
            );
            $params = $this->common->transDataTableFiltersToParams($req, $fields);
            $extras = $this->common->transDataTableFiltersToExtras($req);
            //echo print_r($params); exit;

           
            if( ! isset($extras['limit']) || $extras['limit'] < 0) {
                $extras['limit'] = 100;
            }

            $params['join']['assets_model_tb'] = 'dim_assets_model_id = am_id';
            $params['join']['ip_tb'] = 'dim_ip_id = ip_id';
            $params['left_join']['assets_ip_map_tb'] = 'dim_assets_model_id = aim_assets_model_id';
            $params['left_join']['service_manage_tb'] = 'dim_assets_model_id = sm_assets_model_id';
            $extras['fields'] = array('assets_model_tb.*', 'ip_tb.*', 'aim_ip_id', 'service_manage_tb.*');
            $extras['extras'] = array('am_serial_no ASC');

            $count = $this->direct_ip_map_tb_model->getCount($params)->getData();
            $rows = $this->direct_ip_map_tb_model->getList($params, $extras)->getData();
            //echo $this->direct_ip_map_tb_model->getLastQuery(); exit;

            $aim_ip_ids = array_keys($this->common->getDataByPK($rows, 'aim_ip_id'));
            $aim_ips = array();
            if(sizeof($aim_ip_ids) > 0) {
                $params = array();
                $params['in']['ip_id'] = $aim_ip_ids;
                $params['=']['ip_class_category'] = 'IDRAC';
                $aim_ips = $this->ip_tb_model->getList($params)->getData();
                $aim_ips = $this->common->getDataByPK($aim_ips, 'ip_id');
                //echo print_r($aim_ips);
                //exit;
            }

            $data = array();
            foreach($rows as $k=>$r){

                $link = '/admin/assets/detail/servers/'.$r['am_id'];
                $r['am_name'] = nameToLinkHtml($link, $r['am_name'], '_blank');

                $status_color = $status_map[$r['am_status_id']]['opt_color'];
                $status_name = $status_map[$r['am_status_id']]['opt_name'];
                $r['am_status_id'] = $this->status_tb_business->iconStatusName($status_color, $status_name);

                $r['am_company_id'] = $company_data[$r['am_company_id']];
                $r['am_location_id'] = $location_data[$r['am_location_id']];

                $r['remote_ip'] = isset($aim_ips[$r['aim_ip_id']]) ? $aim_ips[$r['aim_ip_id']]['ip_address'] : '';

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
        $status_data = $this->status_tb_business->getNameMap();
        $data['status_type'] = $this->common->genJqgridOption($status_data, false);

		$this->_view('assets/direct', $data);
    }



    public function service_process() {

		$this->load->model(array(
            'service_manage_tb_model',
        ));
        $req = $this->input->post();

        $sess = array();
        $log_array = array();
        $row_data = array();

        $json_data = array(
            'is_success' => FALSE,
            'msg'       => ''
        );

        $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/assets/service_detail';
        
        $field_list = $this->service_manage_tb_model->getFields();
        $data_params = array();
        foreach($field_list as $key) {
			if(array_key_exists($key, $req)) {
				$data_params[$key] = $req[$key];
				continue;
			}

            if($key == 'sm_vmservice_id') {
				$data_params[$key] = isset($req[$key]) ? $req[$key] : 0;
            }

			if(array_key_exists($key.'_date', $req) && array_key_exists($key.'_time', $req)) {
				$data_params[$key] = $req[$key.'_date'].' '.$req[$key.'_time'];
			}
		}

        // 삭제 & 업데이트 시 기존데이터 검증
        $row_data = array();
        if($req['mode'] == 'update' || $req['mode'] == 'delete') {
            
            if( ! $this->service_manage_tb_model->get($req['sm_id'])->isSuccess()) {
                $json_data['msg'] = getAlertMsg('INVALID_SUBMIT');

                switch($req['request']) {
                    
                    case 'ajax':
                        echo json_encode($json_data);
                        return;
                        break;

                    case 'function':
                        return $json_data;
                        break;

                    default:
                        $this->common->alert($log_array['msg']);
                        $this->common->locationhref($rtn_url);
                        $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['aim_id'], $log_array, 'service_manage_tb');
                        return;
                        break;
                
                }
            }
            $row_data = $this->service_manage_tb_model->getData();
            $log_array['prev_data'] = $row_data;
        }
        //echo print_r($data_params).PHP_EOL; exit;

        switch($req['mode']) {

            case 'delete':
                if( ! $this->service_manage_tb_model->doDelete($row_data['sm_id'])->isSuccess()) {
                    $log_array['msg'] = $this->service_manage_tb_model->getErrorMsg();
                    $json_data['msg'] = getAlertMsg('FAILED_DELETE'); 
                }else {
                    $json_data['is_success'] = TRUE;    
                    $this->common->write_history_log($sess, 'DELETE', $req['sm_id'], $log_array, 'service_manage_tb');
                }
                break;

            case 'update':

                $log_array['params'] = $data_params; 
                if( ! $this->service_manage_tb_model->doUpdate($req['sm_id'], $data_params)->isSuccess()) {
                    $log_array['msg'] = $this->service_manage_tb_model->getErrorMsg();
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['sm_id'], $log_array, 'service_manage_tb');
                    $json_data['msg'] = getAlertMsg('FAILED_UPDATE'); 
                }else {
                    $json_data['is_success'] = TRUE;    
                    $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/assets/service_detail/'.$req['sm_id'];
                    $this->common->write_history_log($sess, 'UPDATE', $req['sm_id'], $log_array, 'service_manage_tb');
                }
                break;
            
            case 'insert':

                $data_params['sm_created_at'] = date('Y-m-d H:i:s');
                $data_params['sm_updated_at'] = date('Y-md H:i:s');
                unset($data_params['sm_id']);

                $log_array['params'] = $data_params; 
                //echo print_r($data_params); 
                if( ! $this->service_manage_tb_model->doInsert($data_params)->isSuccess()) {
                    $log_array['msg'] = $this->service_manage_tb_model->getErrorMsg();
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['sm_id'], $log_array, 'service_manage_tb');
                    $json_data['msg'] = getAlertMsg('FAILED_INSERT'); 
                }else {
                    $json_data['is_success'] = TRUE;    
                    $act_key = $this->service_manage_tb_model->getData();
                    $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/assets/service_detail/'.$act_key;
                    $this->common->write_history_log($sess, 'INSERT', $act_key, $log_array, 'service_tb');
                }
                break;
        }


        // request 형태별 결과 정의 
        switch($req['request']) {
            
            case 'ajax':
                echo json_encode($json_data);
                return;
                break;

            case 'function':
                return $json_data;
                break;

            default:
                if( $json_data['is_success'] == TRUE ) {
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['sm_id'], $log_array, 'service_manage_tb');
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                }else {
                    $this->common->locationhref($rtn_url);
                }
                return;
                break;
        }
    }


    public function test() {

        $pw = 'cocen123!@#';

        $passwd_encrypted = md5(crypt($pw,'kc'));
        $password = hash('sha512',md5($pw).$passwd_encrypted);
        echo $password; 

        exit;

        $domain = 'syslog-api.makeshop.co.kr';
        $exec = "/usr/local/bin/curl -L -k -s ".escapeshellarg("http://{$domain}/.well-known/ok.htm");
        $recv = trim(shell_exec($exec));

        echo $recv; exit;



        $this->load->library(array(
            'DaouData'
        ));
        exit;

        /*
        $account_data = $this->daoudata->getData('ACCOUNT');
        echo print_r($account_data);
        exit;
        */

        /*
        $dept_data = $this->daoudata->getData('DEPT');
        echo print_r($dept_data);
        exit;
        */

        $member_data = $this->daoudata->getData('MEMBER');
        echo print_r($member_data);
        exit;


        /*
        $duty_data = $this->daoudata->getData('DUTY');
        echo print_r($duty_data);
        exit;
        */

        $data = array();

        $this->_view('assets/test', $data);
    }


    public function auth() {

        $this->load->library(array(
            'Authenticator'
        ));

        $secret = $this->authenticator->createSecret(32);
        echo $secret.PHP_EOL.'<BR>';

        //$qrcode = $this->authenticator->getCode($secret);
        //echo $qrcode.PHP_EOL;


        $provider = ADMIN_DOMAIN;
        $name = $this->_ADMIN_DATA['login_id'];

        $qrcodeURL = $this->authenticator->getQRCodeGoogleUrl($provider, $name, $secret);
        echo $qrcodeURL.PHP_EOL.'<BR>';
        exit;

        // [TODO] 이미지화 시켜야 함.
        // URL에 secret key 노출됨


        $data['qrcode'] = $qrcodeURL;
        $this->load->view('/admin/default_template/assets/qrcode', $data);
    }


    public function check_auth() {

        $this->load->library(array(
            'Authenticator'
        ));

        $secret = '6OYFQ4ACRG6NQHNBGC32FEA7RC67SVD2';
        $code = '123123'; 
        
        $res = $this->authenticator->verifyCode($secret, $code);
        if( $res == TRUE ) {
            echo 'SUCCESS';
        }else {
            echo 'FAIL';
        }

    }

}
