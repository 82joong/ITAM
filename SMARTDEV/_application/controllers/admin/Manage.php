<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include_once dirname(__FILE__).'/Base_admin.php';

class Manage extends Base_admin {
    

    public function fieldset() {


        $data = array();

		$this->load->model(array(
            'fieldset_tb_model',
            'custom_field_map_tb_model',
            'custom_field_tb_model'
        ));
		$this->load->business(array(
            'fieldset_tb_business',
            'custom_field_tb_business'
        ));
        $req = $this->input->post();
        $data = array();

        if(isset($req['mode']) && $req['mode'] == 'list') {

            $out_data = array();
            $params = array();
            $extras = array();

            //echo print_r($req);
            $fields = array(
                'cf_name', 'cf_format_element'
            );
            $params = $this->common->transDataTableFiltersToParams($req, $fields);
            $extras = $this->common->transDataTableFiltersToExtras($req);
            //echo print_r($params); exit;

            $count = $this->fieldset_tb_model->getCount($params)->getData();
            $rows = $this->fieldset_tb_model->getList($params, $extras)->getData();

            // 
            $fs_ids = array_keys($this->common->getDataByPK($rows, 'fs_id'));
            $params = array();
            $params['in']['cfm_fieldset_id'] = $fs_ids;
            $params['join']['custom_field_tb'] = 'cfm_custom_field_id = cf_id';

            $extras = array();
            $extras['fields'] = array('cf_id', 'cf_name', 'cf_format_element', 'cfm_fieldset_id');
            $extras['order_by'] = array('cfm_fieldset_id DESC', 'cfm_order ASC');
            $cf_data = $this->custom_field_map_tb_model->getList($params, $extras)->getData();
            $cf_data = $this->common->getDataByDuplPK($cf_data, 'cfm_fieldset_id');

            $icon_map = $this->custom_field_tb_business->getIconTypeMap();
            //

            $data = array();
            foreach($rows as $k=>$r){

                $cf_html = '';
                if(isset($cf_data[$r['fs_id']])) {
                    foreach($cf_data[$r['fs_id']] as $v) {

                        $icon = '<i class="fal '.$icon_map[$v['cf_format_element']].'"></i>';

                        $cf_html .= '<a href="/admin/manage/custom_detail/'.$v['cf_id'].'" class="m-1" target="_blank">';
                        $cf_html .= '<span class="badge border border-success text-danger">'.$icon.'&nbsp;'.$v['cf_name'].'</span>';
                        $cf_html .= '</a>';
                    } 
                }

                $r['custom_fields'] = $cf_html;
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


        $active_data = $this->fieldset_tb_business->getIsActiveMap();
        $data['active_data'] = $this->common->genJqgridOption($active_data, false);
        //$data['select_active'] = getSelect($active_data, 'fs_is_active', $row['fs_is_active']);

		$this->_view('manage/fieldset', $data);

    }


    public function fieldset_detail($id=0) {

		$this->load->model(array('fieldset_tb_model', 'custom_field_tb_model'));
		$this->load->business(array(
            'fieldset_tb_business',
            'custom_field_tb_business'
        ));

        $row = array();
		$id = intval($id);

        if($id > 0) {
            $mode = 'update'; 
            $row = $this->fieldset_tb_model->get($id)->getData();
        
        }else {

            // SET 초기화 및 기본값 
            $mode = 'insert'; 
            $fields = $this->fieldset_tb_model->getFields();
            foreach($fields as $f) {
                $row[$f] = ''; 
            }
            $row['fs_is_active'] = 'YES';
        }

        $data = array();
        $data['mode'] = $mode;
        $data['row'] = $row;

        $active_data = $this->fieldset_tb_business->getIsActiveMap();
        $data['select_active'] = getSelect($active_data, 'fs_is_active', $row['fs_is_active']);


        $params = array();
        $extras = array();
        $extras['fields'] = array('cf_id', 'cf_name');
        $cf_data = $this->custom_field_tb_model->getList($params, $extras)->getData(); 
        $cf_data = $this->common->getDataByPK($cf_data, 'cf_id');

        $custom_data = array();
        foreach($cf_data as $k=>$v) {
            $custom_data[$k] = $v['cf_name'];
        }
        $data['select_custom_fields'] = getSearchSelect($custom_data, 'cfm_custom_field_id', '');



        $element_type = $this->custom_field_tb_business->getElementTypeMap();
        $data['element_type'] = strColumnOptsByKey($element_type);
        $format_type = $this->custom_field_tb_business->getElementFormatValueMap();
        $data['format_type'] = strColumnOptsByKey($format_type);
        $encrypt_type = $this->custom_field_tb_business->getEncryptMap();
        $data['encrypt_type'] = strColumnOptsByKey($encrypt_type);
        $required_type = $this->custom_field_tb_business->getRequiredMap();
        $data['required_type'] = strColumnOptsByKey($required_type);



		$this->_view('manage/fieldset_detail', $data);
    }


    public function fieldset_process() {

		$this->load->model('fieldset_tb_model');
        $req = $this->input->post();

        $sess = array();
        $log_array = array();
        $row_data = array();

        //echo print_r($req).PHP_EOL;

        $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/manage/fieldset';
        
        $field_list = $this->fieldset_tb_model->getFields();
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
            if( ! $this->fieldset_tb_model->get($req['fs_id'])->isSuccess()) {
                $log_array['msg'] = getAlertMsg('INVALID_SUBMIT');
                $this->common->alert($log_array['msg']);
                $this->common->locationhref($rtn_url);
                $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['fs_id'], $log_array, 'fieldset_tb');
                return;
            }
            $row_data = $this->fieldset_tb_model->getData();
            $log_array['prev_data'] = $row_data;
        }
        //echo print_r($data_params).PHP_EOL; exit;

        switch($req['mode']) {

            case 'delete':
                
                if( ! $this->fieldset_tb_model->doDelete($row_data['fs_id'])->isSuccess()) {
                    $log_array['msg'] = $this->fieldset_tb_model->getErrorMsg();
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['fs_id'], $log_array, 'fieldset_tb');

                    if($this->input->is_ajax_request()) {
                        $json_data = array(
                            'is_success' => false,
                            'msg' => $log_array['msg']
                        );
                        echo json_encode($json_data);
                    }else {
                        $this->common->alert($log_array['msg']);
                        $this->common->locationhref($rtn_url);
                    }
                }

                $this->common->write_history_log($sess, 'DELETE', $req['fs_id'], $log_array, 'fieldset_tb');

                if($this->input->is_ajax_request()) {
                    $json_data = array(
                        'is_success' => true,
                        'msg' => ''
                    );
                    echo json_encode($json_data);
                }else {
                    $this->common->locationhref($rtn_url);
                }
                return;


                break;

            case 'update':

                $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/manage/fieldset_detail/'.$req['fs_id'];

                // UNIQUE KEY 
                $params = array();
                $params['!=']['fs_id'] = $req['fs_id'];
                $params['=']['fs_name'] = $data_params['fs_name'];
                $cnt = $this->fieldset_tb_model->getCount($params)->getData();
                if($cnt > 0) {
                    $this->common->alert(getAlertMsg('DUPLICATE_VALUES').' [Name]');
                    $this->common->historyback();
                    return;
                }

                $log_array['params'] = $data_params;
                if( ! $this->fieldset_tb_model->doUpdate($req['fs_id'], $data_params)->isSuccess()) {
                    $log_array['msg'] = $this->fieldset_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['fs_id'], $log_array, 'fieldset_tb');
                    $this->common->locationhref($rtn_url);
                    return;
                }
                $this->common->write_history_log($sess, 'UPDATE', $req['fs_id'], $log_array, 'fieldset_tb');
                break;
            
            case 'insert':

                if( ! isset($data_params['fs_name'])) {
                    $log_array['msg'] = getAlertMsg('REQUIRED_VALUES'); 
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    return;
                }

                // UNIQUE KEY  
                $cnt = $this->fieldset_tb_model->getCount(array('=' => array('fs_name' => $data_params['fs_name'])))->getData();
                if($cnt > 0) {
                    $log_array['msg'] = getAlertMsg('DUPLICATE_VALUES').' [Name]'; 
                    $this->common->alert($log_array['msg']);
                    $this->common->historyback();
                    return;
                }

                $data_params['fs_created_at'] = date('Y-m-d H:i:s');
                unset($data_params['fs_id']);
                //echo print_r($data_params).PHP_EOL; exit; 

                $log_array['params'] = $data_params;
                if( ! $this->fieldset_tb_model->doInsert($data_params)->isSuccess()) {
                    $log_array['msg'] = $this->fieldset_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    return;
                }
				$act_key = $this->fieldset_tb_model->getData();
                $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/manage/fieldset_detail/'.$act_key;
                $this->common->write_history_log($sess, 'INSERT', $act_key, $log_array, 'fieldset_tb');
                break;
        }

        $this->common->locationhref($rtn_url);
    }


    public function custom_field_map() {

        $data = array();
		$this->load->model(array(
                'fieldset_tb_model',
                'custom_field_tb_model',
                'custom_field_map_tb_model'
        ));
        $req = $this->input->post();
        $data = array();


        $out_data = array();
        $params = array();
        $extras = array();

        $count = 0;
        $rows = array();
        if(isset($req['fs_id']) && $req['fs_id'] > 0) {

            //echo print_r($req); exit;

            $params['=']['cfm_fieldset_id'] = $req['fs_id'];
            $params['join']['custom_field_tb'] = 'cfm_custom_field_id = cf_id';
            $params['join']['fieldset_tb'] = 'cfm_fieldset_id = fs_id';

            $extras['fields'] = array('cfm_id', 'cfm_order', 'cf_name', 'cf_format', 'cf_format_element', 'cf_encrypt', 'cfm_required');
            $extras['order_by'] = array('cfm_order ASC');

            $count = $this->custom_field_map_tb_model->getCount($params)->getData();
            $rows = $this->custom_field_map_tb_model->getList($params, $extras)->getData();
        }
        $data = array();
        foreach($rows as $k=>$r){
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



    public function custom_field_map_process() {

		$this->load->model('custom_field_map_tb_model');

        $req = $this->input->post();
        //echo print_r($req); exit;

        $sess = array();
        $log_array = array(
            'is_success' => FALSE,
            'msg'       => ''
        );
        $row_data = array();
        //echo print_r($req).PHP_EOL; exit;

        $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/manage/cfm_list';
        
        $field_list = $this->custom_field_map_tb_model->getFields();
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
            if( ! $this->custom_field_map_tb_model->get($req['cfm_id'])->isSuccess()) {
                $log_array['msg'] = getAlertMsg('INVALID_SUBMIT');
                if($req['request'] == 'ajax') {
                     echo json_encode($log_array);
                }else {
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['cfm_id'], $log_array, 'custom_field_map_tb');
                }
                return;
            }
            $row_data = $this->custom_field_map_tb_model->getData();
            $log_array['prev_data'] = $row_data;
        }
        

        switch($req['mode']) {

            case 'delete':
                if( ! $this->custom_field_map_tb_model->doDelete($req['cfm_id'])->isSuccess()) {
                    $log_array['msg'] = $this->custom_field_map_tb_model->getErrorMsg();
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['cfm_id'], $log_array, 'custom_field_map_tb');
                }
                $this->common->write_history_log($sess, 'DELETE', $req['cfm_id'], $log_array, 'custom_field_map_tb');
                break;


            case 'update':
                $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/manage/cfm_detail/'.$req['cfm_id'];
                $log_array['params'] = $data_params;
                if( ! $this->custom_field_map_tb_model->doUpdate($req['cfm_id'], $data_params)->isSuccess()) {
                    $log_array['msg'] = $this->custom_field_map_tb_model->getErrorMsg();
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['cfm_id'], $log_array, 'custom_field_map_tb');
                }
                $this->common->write_history_log($sess, 'UPDATE', $req['cfm_id'], $log_array, 'custom_field_map_tb');
                break;
            

            case 'insert':
                unset($data_params['cfm_id']);
                $log_array['params'] = $data_params;
                if( ! $this->custom_field_map_tb_model->doInsert($data_params)->isSuccess()) {
                    $log_array['msg'] = $this->custom_field_map_tb_model->getErrorMsg();
                    //$this->common->locationhref($rtn_url);
                }
                $act_key = $this->custom_field_map_tb_model->getData();
                $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/manage/cfm_detail/'.$act_key;
                $this->common->write_history_log($sess, 'INSERT', $act_key, $log_array, 'custom_field_map_tb');
                break;

        } // END_SWITCH




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


    public function ajax_update_cfm_order() {

		$this->load->model(array(
            'custom_field_map_tb_model'
        ));
        $req = $this->input->post();

        if( ! isset($req['data']) || sizeof($req['data']) < 1 ) {
            return;
        }

        foreach($req['data'] as $k=>$row) {
            $data_params = array();
            $data_params['cfm_order'] = $row['cfm_order'];
            if($this->custom_field_map_tb_model->doUpdate($row['cfm_id'], $data_params)->isSuccess() == false) {
                //echo $this->custom_field_map_tb_model->getErrorMsg().PHP_EOL;
            }
        }
        return;
    }



    public function models() {

        $data = array();

		$this->load->model(array(
            'models_tb_model',
            'category_tb_model',
            'fieldset_tb_model'
        ));

        $this->load->business(array(
            'assets_type_tb_business',
            'category_tb_business',
        ));

        $req = $this->input->post();
        $data = array();

        $assets_type_map = $this->assets_type_tb_business->getNameMap();
        $category_data = $this->category_tb_business->getNameMap();

        if(isset($req['mode']) && $req['mode'] == 'list') {

            $out_data = array();
            $params = array();
            $extras = array();

            //echo print_r($req);
            $fields = array(
                'm_model_name', 'm_model_no', 'm_description', 'ct_name', 'fs_name'
            ); 
            $params = $this->common->transDataTableFiltersToParams($req, $fields);
            $extras = $this->common->transDataTableFiltersToExtras($req);
            //echo print_r($params); exit;

            $params['join']['category_tb'] = 'm_category_id = ct_id';
            $params['join']['fieldset_tb'] = 'm_fieldset_id = fs_id';

            $extras['fields'] = array(
                'm_id', 'm_model_name', 'm_model_no', 'm_description', 'm_is_active', 'm_filename', 
                'ct_id', 'ct_type_id', 'ct_name', 'fs_name'
            );

            $count = $this->models_tb_model->getCount($params)->getData();
            $rows = $this->models_tb_model->getList($params, $extras)->getData();

            $data = array();
            foreach($rows as $k=>$r){

                $r['m_img'] = '';
                if(strlen($r['m_filename']) > 0) {
                    $img_path = $this->common->getImgUrl('models', $r['m_id']);
                    $r['m_img'] = '<img src="'.$img_path.'/'.$r['m_filename'].'" class="img-fluid img-thumbnail">';
                }

                $r['m_description'] = nl2br(trim($r['m_description'])); 
                $r['ct_type_id'] = $assets_type_map[$r['ct_type_id']];
                $r['ct_id'] = $category_data[$r['ct_id']];
                //echo print_r($r); exit;

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


        $active_data = $this->models_tb_model->getIsActiveMap();
        $data['active_data'] = $this->common->genJqgridOption($active_data, false);
        $data['type_data'] = $this->common->genJqgridOption($assets_type_map, false);
        $data['category_type'] = $this->common->genJqgridOption($category_data, false);

		$this->_view('manage/models', $data);

    }


    public function models_detail($id=0) {

		$this->load->model(array(
            'models_tb_model',
            'models_custom_fields_tb_model'
        ));

		$this->load->business(array(
            'category_tb_business',
            'vendor_tb_business',
            'supplier_tb_business',
            'fieldset_tb_business',
            'custom_field_tb_business',
        ));

        $row = array();
		$id = intval($id);

        if($id > 0) {
            $mode = 'update'; 
            $row = $this->models_tb_model->get($id)->getData();
            if($row['m_eos_expired_at'] > 0) {
                $row['m_eos_expired_at'] = date('Y-m-d', strtotime($row['m_eos_expired_at']));
            }

            // Get models_custom_fields_tb 데이터 : fieldset area 내에 Elements 
            $params = array();
            $params['=']['mcf_models_id'] = $id;
            $extras = array();
            $extras['order_by'] = array('mcf_order ASC');
            $mcf_data = $this->models_custom_fields_tb_model->getList($params, $extras)->getData();
            if(sizeof($mcf_data) > 0) {
                $format_map = $this->custom_field_tb_business->getElementFormatMap();
                $temp_data = array(
                    'prefix'        => 'mcf_',
                    'mode'          => 'update',
                    'fieldset_data' => array('fs_id' => $mcf_data[0]['mcf_fieldset_id']),
                    'custom_data'   => $mcf_data,
                    'format_map'    => $format_map
                );
                $row['view_data'] = $this->load->view('/admin/default_template/assets/custom_fields_template.php', $temp_data, true);
            }


        }else {

            // SET 초기화 및 기본값 
            $mode = 'insert'; 
            $fields = $this->models_tb_model->getFields();
            foreach($fields as $f) {
                $row[$f] = ''; 
            }
            $row['m_is_active'] = 'YES';
        }

        $data = array();
        $data['mode'] = $mode;
        $data['row'] = $row;

        // 이미지 정보 불러오기
        $img_path = $this->common->getImgPath('models', $row['m_id']);
        $data['img_name'] = $img_path.'/'.$row['m_filename'];
        $data['img_size'] = 0;
        if(file_exists($data['img_name'])) {
            $data['img_size'] = filesize($data['img_name']);
        }
        $base_url = $this->common->getImgUrl('models', $row['m_id']);
        $data['img_url'] = $base_url.'/'.$row['m_filename'];


        $active_data = $this->models_tb_model->getIsActiveMap();
        $vendor_data = $this->vendor_tb_business->getNameMap();
        $fieldset_data = $this->fieldset_tb_business->getNameMap();


        //$category_data = $this->category_tb_business->getNameMap();
        //$data['select_category'] = getSearchSelect($category_data, 'm_category_id', $row['m_category_id'], 'required');
        $data['select_vendor'] = getSearchSelect($vendor_data, 'm_vendor_id', $row['m_vendor_id'], 'required');

        $disabled = '';
        $category_data = $this->category_tb_business->getGroupMap();
        $data['select_category'] = getGroupSearchSelect($category_data, 'm_category_id', $row['m_category_id'], $disabled); 

        $opt = 'required';
        if($mode == 'update') {
            $opt .= ' disabled';
        }
        $data['select_fieldset'] = getSearchSelect($fieldset_data, 'm_fieldset_id', $row['m_fieldset_id'], $opt);
        $data['select_active'] = getSelect($active_data, 'm_is_active', $row['m_is_active']);

		$this->_view('manage/models_detail', $data);

    }


    public function models_process() {

		$this->load->model(array(
            'models_tb_model',
            'models_custom_fields_tb_model',
        ));
		$this->load->business(array(
            'models_custom_fields_tb_business',
        ));

        $req = $this->input->post();

        $sess = array();
        $log_array = array();
        $row_data = array();
        //echo print_r($req).PHP_EOL; exit;

        $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/manage/models';
        
        $field_list = $this->models_tb_model->getFields();
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
            if( ! $this->models_tb_model->get($req['m_id'])->isSuccess()) {
                $log_array['msg'] = getAlertMsg('INVALID_SUBMIT');
                $this->common->alert($log_array['msg']);
                $this->common->locationhref($rtn_url);
                $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['m_id'], $log_array, 'models_tb');
                return;
            }
            $row_data = $this->models_tb_model->getData();

            // 이미지 처리(삭제)
            $delete_path = $this->common->getImgPath('models', $row_data['m_id']);
            $delete_filename = $delete_path.'/'.$row_data['m_filename'];

            $log_array['prev_data'] = $row_data;
        }
        //echo print_r($data_params).PHP_EOL; exit;

        switch($req['mode']) {

            case 'delete':
                
                if( ! $this->models_tb_model->doDelete($row_data['m_id'])->isSuccess()) {
                    $log_array['msg'] = $this->models_tb_model->getErrorMsg();
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['m_id'], $log_array, 'models_tb');

                    if($this->input->is_ajax_request()) {
                        $json_data = array(
                            'is_success' => false,
                            'msg' => $log_array['msg']
                        );
                        echo json_encode($json_data);
                    }else {
                        $this->common->alert($log_array['msg']);
                        $this->common->locationhref($rtn_url);
                    }
                    return;
                }
                
                // 이미지 처리
                @unlink($delete_filename);
                $this->common->write_history_log($sess, 'DELETE', $req['m_id'], $log_array, 'models_tb');
                $where_params= array();
                $where_params['=']['mcf_models_id'] = $req['m_id']; 
                $this->models_custom_fields_tb_model->doMultiDelete($where_params);

                if($this->input->is_ajax_request()) {
                    $json_data = array(
                        'is_success' => true,
                        'msg' => ''
                    );
                    echo json_encode($json_data);
                }else {
                    $this->common->locationhref($rtn_url);
                }
                return;
                break;

            case 'update':

                $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/manage/models_detail/'.$req['m_id'];

                // UNIQUE KEY 
                $params = array();
                $params['!=']['m_id'] = $req['m_id'];
                $params['=']['m_model_name'] = $data_params['m_model_name'];
                $cnt = $this->models_tb_model->getCount($params)->getData();
                if($cnt > 0) {
                    $this->common->alert(getAlertMsg('DUPLICATE_VALUES').' [Name]');
                    $this->common->historyback();
                    return;
                }

                // 이미지 처리
                $updated_img = FALSE;
                if(isset($req['img_origin']) && sizeof($req['img_origin']) > 0) {
                    $updated_img = TRUE;
                    //$data_params['m_filename'] = $req['img_origin'][0];   // 업로드한 이미지명 그대로 저장
                    $data_params['m_filename'] = $req['img_filename'][0];     // 업로드시 Encrypt 된 파일명 사용
                }

                $log_array['params'] = $data_params;
                if( ! $this->models_tb_model->doUpdate($req['m_id'], $data_params)->isSuccess()) {
                    $log_array['msg'] = $this->models_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['m_id'], $log_array, 'models_tb');
                    $this->common->locationhref($rtn_url);
                    return;
                }

                // 이미지 처리
                if($updated_img == TRUE) {
                    $tempfile = IMG_TEMP_PATH.'/'.$req['img_filename'][0];
                    $path = $this->common->getImgPath('models', $req['m_id']);
                    $orifile = $path.'/'.$data_params['m_filename'];

                    @unlink($delete_filename);
                    @exec('mv '.escapeshellarg($tempfile).' '.escapeshellarg($orifile));
                }
 

                // Update custom_value_tb
                $res = $this->models_custom_fields_tb_business->updateCustomValue($req);
                $this->common->write_history_log($sess, 'UPDATE', $req['m_id'], $log_array, 'models_tb');
                break;
            
            case 'insert':

                if( ! isset($data_params['m_model_name'])) {
                    $log_array['msg'] = getAlertMsg('REQUIRED_VALUES'); 
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    return;
                }

                // UNIQUE KEY
                $cnt = $this->models_tb_model->getCount(array('=' => array('m_model_name' => $data_params['m_model_name'])))->getData();
                if($cnt > 0) {
                    $log_array['msg'] = getAlertMsg('DUPLICATE_VALUES').' [Name]'; 
                    $this->common->alert($log_array['msg']);
                    $this->common->historyback();
                    return;
                }
			
                // 이미지 처리
                if(isset($req['img_origin']) && sizeof($req['img_origin']) > 0) {
                    //$data_params['m_filename'] = $req['img_origin'][0];   // 업로드한 이미지명 그대로 저장
                    $data_params['m_filename'] = $req['img_filename'][0];     // 업로드시 Encrypt 된 파일명 사용
                }
 
                $data_params['m_created_at'] = date('Y-m-d H:i:s');
                unset($data_params['m_id']);

                $log_array['params'] = $data_params;
                if( ! $this->models_tb_model->doInsert($data_params)->isSuccess()) {
                    $log_array['msg'] = $this->models_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    return;
                }
				$act_key = $this->models_tb_model->getData();

                // 이미지 처리
                if(isset($req['img_origin']) && sizeof($req['img_origin']) > 0) {
                    $tempfile = IMG_TEMP_PATH.'/'.$req['img_filename'][0];
                    $path = $this->common->getImgPath('models', $act_key);
                    $orifile = $path.'/'.$data_params['m_filename'];
                    @exec('mv '.$tempfile.' '.$orifile);
                }


                // Insert custom_value_tb
                $res = $this->models_custom_fields_tb_business->insertCustomValue($req, $act_key);
                $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/manage/models_detail/'.$act_key;
                $this->common->write_history_log($sess, 'INSERT', $act_key, $log_array, 'models_tb');
                break;
        }

        $this->common->locationhref($rtn_url);
    }




    public function category() {

        $data = array();

		$this->load->model('category_tb_model');
        $req = $this->input->post();
        $data = array();

        if(isset($req['mode']) && $req['mode'] == 'list') {

            $out_data = array();
            $params = array();
            $extras = array();

            //echo print_r($req);

            $fields = array('ct_description', 'ct_name'); 
            $params = $this->common->transDataTableFiltersToParams($req, $fields);
            $extras = $this->common->transDataTableFiltersToExtras($req);
            $extras['order_by'] = array('ct_order ASC');
            $count = $this->category_tb_model->getCount($params)->getData();
            $rows = $this->category_tb_model->getList($params, $extras)->getData();

            $data = array();
            foreach($rows as $k=>$r){

                $type = '<i class="fal '.$this->_ASSETS_TYPE[$r['ct_type_id']]['at_icon'].'">&nbsp;';
                $type .= $this->_ASSETS_TYPE[$r['ct_type_id']]['at_name'];
                $type .= '</i>'; 

                $r['ct_type_id'] = $type; 

                $r['ct_icon'] = '<i class="fal '.$r['ct_icon'].'">&nbsp;'.$r['ct_icon'];
                $r['ct_description'] = nl2br(trim($r['ct_description'])); 

                $r['ct_img'] = '';
                if(strlen($r['ct_filename']) > 0) {
                    $img_path = $this->common->getImgUrl('category', $r['ct_id']);
                    $r['ct_img'] = '<img src="'.$img_path.'/'.$r['ct_filename'].'" class="img-fluid img-thumbnail">';
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

        $assets_type_map = $this->assets_type_tb_business->assets_type_map();

        $active_data = $this->category_tb_model->getIsActiveMap();
        $data['active_data'] = $this->common->genJqgridOption($active_data, false);
        $data['type_data'] = $this->common->genJqgridOption($assets_type_map, false);

		$this->_view('manage/category', $data);
    }


    public function category_detail($id=0) {

		$this->load->model('category_tb_model');
        $this->load->business(array(
            'assets_type_tb_business',
        ));

        $row = array();
		$id = intval($id);

        if($id > 0) {
            $mode = 'update'; 
            $row = $this->category_tb_model->get($id)->getData();
        
        }else {

            // SET 초기화 및 기본값 
            $mode = 'insert'; 
            $fields = $this->category_tb_model->getFields();
            foreach($fields as $f) {
                $row[$f] = ''; 
            }
        }

        $data = array();
        $data['mode'] = $mode;
        $data['row'] = $row;
        $data['active_data'] = $this->category_tb_model->getIsActiveMap();
        $data['assets_types'] = $this->_ASSETS_TYPE;


        $img_path = $this->common->getImgPath('category', $row['ct_id']);
        $data['img_name'] = $img_path.'/'.$row['ct_filename'];
        if(file_exists($data['img_name'])) {
            $data['img_size'] = filesize($data['img_name']);
        }
        $base_url = $this->common->getImgUrl('category', $row['ct_id']);
        $data['img_url'] = $base_url.'/'.$row['ct_filename'];


        $active_data = $this->category_tb_model->getIsActiveMap();
        $data['select_active'] = getSelect($active_data, 'ct_is_active', $row['ct_is_active']);

        $assets_type = $this->assets_type_tb_business->getOptionMap();
        $data['select_type'] = getSearchWithIconSelect($assets_type, 'ct_type_id', $row['ct_type_id']);

		$this->_view('manage/category_detail', $data);
    }


    public function category_process() {

		$this->load->model('category_tb_model');
        $req = $this->input->post();

        $sess = array();
        $log_array = array();
        $row_data = array();

        //echo print_r($req).PHP_EOL;

        $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/manage/category';
        
        $field_list = $this->category_tb_model->getFields();
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
            if( ! $this->category_tb_model->get($req['ct_id'])->isSuccess()) {
                $log_array['msg'] = getAlertMsg('INVALID_SUBMIT');
                $this->common->alert($log_array['msg']);
                $this->common->locationhref($rtn_url);
                $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['ct_id'], $log_array, 'category_tb');
                return;
            }
            $row_data = $this->category_tb_model->getData();
            

            // 이미지 처리 
            $delete_path = $this->common->getImgPath('category', $row_data['ct_id']);
            $delete_filename = $delete_path.'/'.$row_data['ct_filename'];

            $log_array['prev_data'] = $row_data;
        }
        //echo print_r($data_params).PHP_EOL; exit;

        switch($req['mode']) {

            case 'delete':
                
                if( ! $this->category_tb_model->doDelete($row_data['ct_id'])->isSuccess()) {
                    $log_array['msg'] = $this->category_tb_model->getErrorMsg();
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['ct_id'], $log_array, 'category_tb');

                    if($this->input->is_ajax_request()) {
                        $json_data = array(
                            'is_success' => false,
                            'msg' => $log_array['msg']
                        );
                        echo json_encode($json_data);
                    }else {
                        $this->common->alert($log_array['msg']);
                        $this->common->locationhref($rtn_url);
                    }
                    return;
                }

                @unlink($delete_filename);
                $this->common->write_history_log($sess, 'DELETE', $req['ct_id'], $log_array, 'category_tb');

                if($this->input->is_ajax_request()) {
                    $json_data = array(
                        'is_success' => true,
                        'msg' => ''
                    );
                    echo json_encode($json_data);
                }else {
                    $this->common->locationhref($rtn_url);
                }
                return;

                break;

            case 'update':

                $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/manage/category_detail/'.$req['ct_id'];


                // UNIQUE KEY 
                $params = array();
                $params['!=']['ct_id'] = $req['ct_id'];
                $params['=']['ct_name'] = $data_params['ct_name'];
                $cnt = $this->category_tb_model->getCount($params)->getData();
                if($cnt > 0) {
                    $this->common->alert(getAlertMsg('DUPLICATE_VALUES').' [Name]');
                    $this->common->historyback();
                    return;
                }

                // 이미지 처리
                $updated_img = FALSE;
                if(isset($req['img_origin']) && sizeof($req['img_origin']) > 0) {
                    $updated_img = TRUE;
                    //$data_params['ct_filename'] = $req['img_origin'][0];   // 업로드한 이미지명 그대로 저장
                    $data_params['ct_filename'] = $req['img_filename'][0];     // 업로드시 Encrypt 된 파일명 사용
                }

                $log_array['params'] = $data_params; 
                if( ! $this->category_tb_model->doUpdate($req['ct_id'], $data_params)->isSuccess()) {
                    $log_array['msg'] = $this->category_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['ct_id'], $log_array, 'category_tb');
                    $this->common->locationhref($rtn_url);
                    return;
                }

                // 이미지 처리
                if($updated_img == TRUE) {
                    $tempfile = IMG_TEMP_PATH.'/'.$req['img_filename'][0];
                    $path = $this->common->getImgPath('category', $req['ct_id']);
                    $orifile = $path.'/'.$data_params['ct_filename'];

                    @unlink($delete_filename);
                    @exec('mv '.escapeshellarg($tempfile).' '.escapeshellarg($orifile));
                }

                $this->common->write_history_log($sess, 'UPDATE', $req['ct_id'], $log_array, 'category_tb');
                break;
            
            case 'insert':
                if( ! isset($data_params['ct_name'])) {
                    $log_array['msg'] = getAlertMsg('REQUIRED_VALUES'); 
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    return;
                }


                // UNIQUE KEY
                $cnt = $this->category_tb_model->getCount(array('=' => array('ct_name' => $data_params['ct_name'])))->getData();
                if($cnt > 0) {
                    $log_array['msg'] = getAlertMsg('DUPLICATE_VALUES').' [Name]'; 
                    $this->common->alert($log_array['msg']);
                    $this->common->historyback();
                    return;
                }
			

                // 이미지 처리
                if(isset($req['img_origin']) && sizeof($req['img_origin']) > 0) {
                    //$data_params['ct_filename'] = $req['img_origin'][0];   // 업로드한 이미지명 그대로 저장
                    $data_params['ct_filename'] = $req['img_filename'][0];     // 업로드시 Encrypt 된 파일명 사용
                }

                $data_params['ct_created_at'] = date('Y-m-d H:i:s');
                unset($data_params['ct_id']);
                //echo print_r($data_params).PHP_EOL; exit; 

                $log_array['params'] = $data_params; 
                if( ! $this->category_tb_model->doInsert($data_params)->isSuccess()) {
                    $log_array['msg'] = $this->category_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    return;
                }


                // 이미지 처리
                if(isset($req['img_origin']) && sizeof($req['img_origin']) > 0) {
                    $tempfile = IMG_TEMP_PATH.'/'.$req['img_filename'][0];
                    $path = $this->common->getImgPath('category', $act_key);
                    $orifile = $path.'/'.$data_params['ct_filename'];
                    @exec('mv '.$tempfile.' '.$orifile);
                }


				$act_key = $this->category_tb_model->getData();
                $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/manage/category_detail/'.$act_key;
                $this->common->write_history_log($sess, 'INSERT', $act_key, $log_array, 'category_tb');
                break;
        }

        $this->common->locationhref($rtn_url);
    }



    public function ajax_update_ct_order() {

		$this->load->model(array(
            'category_tb_model'
        ));
        $req = $this->input->post();

        if( ! isset($req['data']) || sizeof($req['data']) < 1 ) {
            return;
        }

        foreach($req['data'] as $k=>$row) {
            $data_params = array();
            $data_params['ct_order'] = $row['ct_order'];
            if($this->category_tb_model->doUpdate($row['ct_id'], $data_params)->isSuccess() == false) {
                //echo $this->category_tb_model->getErrorMsg().PHP_EOL;
            }
        }
        return;
    }



    public function do_dropzone() {

        //echo print_r($_FILES);

        $config['upload_path'] = IMG_TEMP_PATH;
        //$config['allowed_types'] = 'gif|jpg|png|jpeg';
        $config['allowed_types'] = '*';

        if(!is_dir($config['upload_path'])){
            if(@mkdir($config['upload_path'], 0777)){
                @chmod($config['upload_path'], 0777);
            }    
        } 

        $files = $this->common->restructArray($_FILES);
        //echo print_r($files); exit;

        $data = array();
        foreach($files as $k=>$v) {

            $_FILES['file']['name'] = $v['name'];
            $_FILES['file']['type'] = $v['type'];
            $_FILES['file']['tmp_name'] = $v['tmp_name'];
            $_FILES['file']['error'] = $v['error'];
            $_FILES['file']['size'] = $v['size'];

            $ext = array_pop(explode('.', $_FILES['file']['name']));
            //$image_name = time().".jpg";
            $image_name = time().$ext;
            if($ext == 'gif') $image_name = time().".gif";
            $config['file_name'] = $image_name;
            $config['encrypt_name'] = TRUE;

            $this->load->library('upload', $config); 
            $res = array();
            if( ! $this->upload->do_upload('file')){

                $error = $this->upload->display_errors();
                //echo print_r($erorr);

                $res['is_success'] = FALSE;
                $res['msg'] = $error;

            }else {

                $upload_data = $this->upload->data();
                //echo print_r($upload_data);
                /*
                    [file_name] => 6ad6b2c08cbfd84df5b65bdc54435b93.jpg
                    [file_type] => image/jpeg
                    [file_path] => /home/team/82joong/html/itam/html/webdata/display/temp_upload/
                    [full_path] => /home/team/82joong/html/itam/html/webdata/display/temp_upload/6ad6b2c08cbfd84df5b65bdc54435b93.jpg
                    [raw_name] => 6ad6b2c08cbfd84df5b65bdc54435b93
                    [orig_name] => 1626164132.jpg
                    [client_name] => origin0.jpg
                    [file_ext] => .jpg
                    [file_size] => 31.98
                    [is_image] => 1
                    [image_width] => 445
                    [image_height] => 445
                    [image_type] => jpeg
                    [image_size_str] => width="445" height="445"
                )
                */
                $res = array(
                    'is_success'    => TRUE,
                    'msg'           => '',
                    'origin_name'   => $upload_data['client_name'],
                    'file_name'     => $upload_data['file_name'],
                );
            }
            $data[$k] = $res;
        }

        echo json_encode($data);
    }



    public function company() {

        $data = array();

		$this->load->model('company_tb_model');
        $req = $this->input->post();
        $data = array();

        if(isset($req['mode']) && $req['mode'] == 'list') {

            $out_data = array();
            $params = array();
            $extras = array();

            //echo print_r($req);
            $fields = array('c_name', 'c_code', 'c_biz_number', 'c_biz_owner'); 
            $params = $this->common->transDataTableFiltersToParams($req, $fields);
            $extras = $this->common->transDataTableFiltersToExtras($req);
            //echo print_r($params); exit;

            $count = $this->company_tb_model->getCount($params)->getData();
            $rows = $this->company_tb_model->getList($params, $extras)->getData();

            $data = array();
            foreach($rows as $k=>$r){

                $r['c_img'] = '';
                if(strlen($r['c_filename']) > 0) {
                    $img_path = $this->common->getImgUrl('company', $r['c_id']);
                    $r['c_img'] = '<img src="'.$img_path.'/'.$r['c_filename'].'" class="img-fluid img-thumbnail">';
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

        $assets_type_map = $this->assets_type_tb_business->assets_type_map();

        $active_data = $this->company_tb_model->getIsActiveMap();
        $data['active_data'] = $this->common->genJqgridOption($active_data, false);

		$this->_view('manage/company', $data);
    }



    public function company_detail($id=0) {

		$this->load->model('company_tb_model');

        $data = array();
        $row = array();
		$id = intval($id);

        if($id > 0) {
            $mode = 'update'; 
            $row = $this->company_tb_model->get($id)->getData();

            // 이미지 정보 불러오기
            $img_path = $this->common->getImgPath('company', $row['c_id']);
            $data['img_name'] = $img_path.'/'.$row['c_filename'];
            if(file_exists($data['img_name'])) {
                $data['img_size'] = filesize($data['img_name']);
            } else {
                $row['c_filename'] = '';
            }
            $base_url = $this->common->getImgUrl('company', $row['c_id']);
            $data['img_url'] = $base_url.'/'.$row['c_filename'];
        
        }else {

            // SET 초기화 및 기본값 
            $mode = 'insert'; 
            $fields = $this->company_tb_model->getFields();
            foreach($fields as $f) {

                if($f == 'c_is_active') {
                    $row[$f] = 'YES'; 
                } else {
                    $row[$f] = ''; 
                }
            }
        }

        $data['mode'] = $mode;
        $data['row'] = $row;
        $data['active_data'] = $this->company_tb_model->getIsActiveMap();

        $active_data = $this->company_tb_model->getIsActiveMap();
        $data['select_active'] = getSelect($active_data, 'c_is_active', $row['c_is_active']);

		$this->_view('manage/company_detail', $data);
    }


    public function company_process() {

		$this->load->model('company_tb_model');
        $req = $this->input->post();

        $sess = array();
        $log_array = array();
        $row_data = array();

        //echo print_r($req).PHP_EOL; exit;

        $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/manage/company';
        
        $field_list = $this->company_tb_model->getFields();
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
            if( ! $this->company_tb_model->get($req['c_id'])->isSuccess()) {
                $log_array['msg'] = getAlertMsg('INVALID_SUBMIT');
                $this->common->alert($log_array['msg']);
                $this->common->locationhref($rtn_url);
                $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['c_id'], $log_array, 'company_tb');
                return;
            }
            $row_data = $this->company_tb_model->getData();

            // 이미지 처리(삭제)
            $delete_path = $this->common->getImgPath('company', $row_data['c_id']);
            $delete_filename = $delete_path.'/'.$row_data['c_filename'];

            $log_array['prev_data'] = $row_data;
        }
        //echo print_r($data_params).PHP_EOL; exit;

        switch($req['mode']) {

            case 'delete':
                
                if( ! $this->company_tb_model->doDelete($row_data['c_id'])->isSuccess()) {
                    $log_array['msg'] = $this->company_tb_model->getErrorMsg();
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['c_id'], $log_array, 'company_tb');

                    if($this->input->is_ajax_request()) {
                        $json_data = array(
                            'is_success' => false,
                            'msg' => $log_array['msg']
                        );
                        echo json_encode($json_data);
                    }else {
                        $this->common->alert($log_array['msg']);
                        $this->common->locationhref($rtn_url);
                    }
                    return;
                }
                // 이미지 처리
                @unlink($delete_filename);
                $this->common->write_history_log($sess, 'DELETE', $req['c_id'], $log_array, 'company_tb');

                if($this->input->is_ajax_request()) {
                    $json_data = array(
                        'is_success' => true,
                        'msg' => ''
                    );
                    echo json_encode($json_data);
                }else {
                    $this->common->locationhref($rtn_url);
                }
                return;

                break;

            case 'update':

                $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/manage/company_detail/'.$req['c_id'];

 
                // UNIQUE KEY 
                $params = array();
                $params['!=']['c_id'] = $req['c_id'];
                $params['=']['c_name'] = $data_params['c_name'];
                $cnt = $this->company_tb_model->getCount($params)->getData();
                if($cnt > 0) {
                    $this->common->alert(getAlertMsg('DUPLICATE_VALUES').' [Name]');
                    $this->common->historyback();
                    return;
                }

                // 이미지 처리
                $updated_img = FALSE;
                if(isset($req['img_origin']) && sizeof($req['img_origin']) > 0) {
                    $updated_img = TRUE;
                    //$data_params['c_filename'] = $req['img_origin'][0];   // 업로드한 이미지명 그대로 저장
                    $data_params['c_filename'] = $req['img_filename'][0];     // 업로드시 Encrypt 된 파일명 사용
                }

                $data_params['c_updated_at'] = date('Y-m-d H:i:s');
                $log_array['params'] = $data_params;
                if( ! $this->company_tb_model->doUpdate($req['c_id'], $data_params)->isSuccess()) {
                    $log_array['msg'] = $this->company_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['c_id'], $log_array, 'company_tb');
                    $this->common->locationhref($rtn_url);
                    return;
                }

                // 이미지 처리
                if($updated_img == TRUE) {
                    $tempfile = IMG_TEMP_PATH.'/'.$req['img_filename'][0];
                    $path = $this->common->getImgPath('company', $req['c_id']);
                    $orifile = $path.'/'.$data_params['c_filename'];

                    @unlink($delete_filename);
                    @exec('mv '.escapeshellarg($tempfile).' '.escapeshellarg($orifile));
                }
                $this->common->write_history_log($sess, 'UPDATE', $req['c_id'], $log_array, 'company_tb');
                break;
            
            case 'insert':
                if( ! isset($data_params['c_name'])) {
                    $log_array['msg'] = getAlertMsg('REQUIRED_VALUES'); 
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    return;
                }


                // UNIQUE KEY
                $cnt = $this->company_tb_model->getCount(array('=' => array('c_code' => $data_params['c_code'])))->getData();
                if($cnt > 0) {
                    $log_array['msg'] = getAlertMsg('DUPLICATE_VALUES').' [Code]'; 
                    $this->common->alert($log_array['msg']);
                    $this->common->historyback();
                    return;
                }

						
                // 이미지 처리
                if(isset($req['img_origin']) && sizeof($req['img_origin']) > 0) {
                    //$data_params['c_filename'] = $req['img_origin'][0];   // 업로드한 이미지명 그대로 저장
                    $data_params['c_filename'] = $req['img_filename'][0];     // 업로드시 Encrypt 된 파일명 사용
                }
 
                $data_params['c_created_at'] = date('Y-m-d H:i:s');
                unset($data_params['c_id']);
                //echo print_r($data_params).PHP_EOL; exit; 

                $log_array['params'] = $data_params;
                if( ! $this->company_tb_model->doInsert($data_params)->isSuccess()) {
                    $log_array['msg'] = $this->company_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    return;
                }

				$act_key = $this->company_tb_model->getData();

                // 이미지 처리
                if(isset($req['img_origin']) && sizeof($req['img_origin']) > 0) {
                    $tempfile = IMG_TEMP_PATH.'/'.$req['img_filename'][0];
                    $path = $this->common->getImgPath('company', $act_key);
                    $orifile = $path.'/'.$data_params['c_filename'];
                    @exec('mv '.$tempfile.' '.$orifile);
                }
                $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/manage/company_detail/'.$act_key;
                $this->common->write_history_log($sess, 'INSERT', $act_key, $log_array, 'company_tb');
                break;
        }

        $this->common->locationhref($rtn_url);
    }


    public function location() {

        $data = array();

		$this->load->model('location_tb_model');
        $this->load->business(array(
            'location_tb_business',
        ));
        $req = $this->input->post();
        $data = array();

        if(isset($req['mode']) && $req['mode'] == 'list') {

            $out_data = array();
            $params = array();
            $extras = array();

            //echo print_r($req);
            $fields = array('l_name', 'l_code', 'l_manager_name', 'l_city', 'l_address'); 
            $params = $this->common->transDataTableFiltersToParams($req, $fields);
            $extras = $this->common->transDataTableFiltersToExtras($req);
            //echo print_r($params); exit;

            $count = $this->location_tb_model->getCount($params)->getData();
            $rows = $this->location_tb_model->getList($params, $extras)->getData();

            $data = array();
            foreach($rows as $k=>$r){
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


        $location_code = $this->location_tb_business->getCodeMap('l_code');
        $data['select_code'] = $this->common->genJqgridOption($location_code, false);

		$this->_view('manage/location', $data);
    }


    public function location_detail($id=0, $mode='insert') {

		$this->load->model('location_tb_model');

        $row = array();
		$id = intval($id);

        if($id > 0) {
            if($mode != 'clone') {
                $mode = 'update';
            }
            $row = $this->location_tb_model->get($id)->getData();
            $row['l_address'] = explode('<br />', $row['l_address']);

        }else {

            // SET 초기화 및 기본값 
            $mode = 'insert'; 
            $fields = $this->location_tb_model->getFields();
            foreach($fields as $f) {
                $row[$f] = ''; 
            }
        }

        $data = array();
        $data['mode'] = $mode;
        $data['row'] = $row;

        $data['select_country'] = getCountriesSearchSelect('l_country', $row['l_country']);

		$this->_view('manage/location_detail', $data);
    }



    public function location_process() {

		$this->load->model('location_tb_model');
        $req = $this->input->post();

        $sess = array();
        $log_array = array();
        $row_data = array();

        //echo print_r($req).PHP_EOL; exit;

        $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/manage/location';
        
        $field_list = $this->location_tb_model->getFields();
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
            if( ! $this->location_tb_model->get($req['l_id'])->isSuccess()) {
                $log_array['msg'] = getAlertMsg('INVALID_SUBMIT');
                $this->common->alert($log_array['msg']);
                $this->common->locationhref($rtn_url);
                $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['l_id'], $log_array, 'location_tb');
                return;
            }
            $row_data = $this->location_tb_model->getData();
            $log_array['prev_data'] = $row_data;
        }
        


        //echo print_r($data_params).PHP_EOL; exit;

        switch($req['mode']) {

            case 'delete':

                if( ! $this->location_tb_model->doDelete($row_data['l_id'])->isSuccess()) {
                    $log_array['msg'] = $this->location_tb_model->getErrorMsg();
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['l_id'], $log_array, 'location_tb');

                    if($this->input->is_ajax_request()) {
                        $json_data = array(
                            'is_success' => false,
                            'msg' => $log_array['msg']
                        );
                        echo json_encode($json_data);
                    }else {
                        $this->common->alert($log_array['msg']);
                        $this->common->locationhref($rtn_url);
                    }
                    return;
                }
                $this->common->write_history_log($sess, 'DELETE', $req['l_id'], $log_array, 'location_tb');

                if($this->input->is_ajax_request()) {
                    $json_data = array(
                        'is_success' => true,
                        'msg' => ''
                    );
                    echo json_encode($json_data);
                }else {
                    $this->common->locationhref($rtn_url);
                }
                return;



                break;

            case 'update':

                $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/manage/location_detail/'.$req['l_id'];
                
                // UNIQUE KEY 
                $params = array();
                $params['!=']['l_id'] = $req['l_id'];
                $params['=']['l_code'] = $data_params['l_code'];
                $cnt = $this->location_tb_model->getCount($params)->getData();
                if($cnt > 0) {
                    $this->common->alert(getAlertMsg('DUPLICATE_VALUES').' [Name]');
                    $this->common->historyback();
                    return;
                }

                // 주소 저장
                if (strlen(trim($data_params['l_address'][0])) > 0) {
                    $data_params['l_address'] = implode('<br />', $data_params['l_address']);
                }

                $data_params['l_updated_at'] = date('Y-m-d H:i:s');
                $log_array['params'] = $data_params;
                if( ! $this->location_tb_model->doUpdate($req['l_id'], $data_params)->isSuccess()) {
                    $log_array['msg'] = $this->location_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['l_id'], $log_array, 'location_tb');
                    $this->common->locationhref($rtn_url);
                    return;
                }
                $this->common->write_history_log($sess, 'UPDATE', $req['l_id'], $log_array, 'location_tb');
                break;
            
            case 'insert':
            case 'clone':

                // UNIQUE KEY 
                $cnt = $this->location_tb_model->getCount(array('=' => array('l_code' => $data_params['l_code'])))->getData();
                if($cnt > 0) {
                    $this->common->alert(getAlertMsg('DUPLICATE_VALUES').' [Name]');
                    $this->common->historyback();
                    return;
                }
                

                // 주소 저장
                $data_params['l_address'] = '';
                if (strlen(trim($data_params['l_address'][0])) > 0) {
                    $data_params['l_address'] = implode('<br />', $data_params['l_address']);
                }
			
                $data_params['l_created_at'] = date('Y-m-d H:i:s');
                unset($data_params['l_id']);


                if( ! $this->location_tb_model->doInsert($data_params)->isSuccess()) {
                    $log_array['msg'] = $this->supplier_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    return;
                }
				$act_key = $this->location_tb_model->getData();
                $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/manage/location_detail/'.$act_key;
                $this->common->write_history_log($sess, 'INSERT', $act_key, $log_array, 'location_tb');
                break;
        }

        $this->common->locationhref($rtn_url);
    }



    public function vendor() {

        $data = array();

		$this->load->model('vendor_tb_model');
        $this->load->business(array(
            'vendor_tb_business',
        ));
        $req = $this->input->post();
        $data = array();

        if(isset($req['mode']) && $req['mode'] == 'list') {

            $out_data = array();
            $params = array();
            $extras = array();

            //echo print_r($req);
            $fields = array('vd_name', 'vd_url', 'vd_support_url', 'vd_support_email'); 
            $params = $this->common->transDataTableFiltersToParams($req, $fields);
            $extras = $this->common->transDataTableFiltersToExtras($req);
            //echo print_r($params); exit;

            $count = $this->vendor_tb_model->getCount($params)->getData();
            $rows = $this->vendor_tb_model->getList($params, $extras)->getData();

            $data = array();
            foreach($rows as $k=>$r){

                $r['vd_img'] = $this->vendor_tb_business->getVendorIcon($r['vd_id'], $r['vd_filename']);
                $r['vd_url'] = '<a href="http://'.$r['vd_url'].'"n target="_blank">'.$r['vd_url'].'</a>';
                $r['vd_support_url'] = '<a href="http://'.$r['vd_support_url'].'" target="_blank">'.$r['vd_support_url'].'</a>';

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

		$this->_view('manage/vendor', $data);
    }



    public function vendor_detail($id=0) {

		$this->load->model('vendor_tb_model');

        $row = array();
		$id = intval($id);

        if($id > 0) {
            $mode = 'update'; 
            $row = $this->vendor_tb_model->get($id)->getData();
        
        }else {

            // SET 초기화 및 기본값 
            $mode = 'insert'; 
            $fields = $this->vendor_tb_model->getFields();
            foreach($fields as $f) {
                $row[$f] = ''; 
            }
        }

        $data = array();
        $data['mode'] = $mode;
        $data['row'] = $row;


        $img_path = $this->common->getImgPath('vendor', $row['vd_id']);
        $data['img_name'] = $img_path.'/'.$row['vd_filename'];
        if(file_exists($data['img_name'])) {
            $data['img_size'] = filesize($data['img_name']);
        }
        $base_url = $this->common->getImgUrl('vendor', $row['vd_id']);
        $data['img_url'] = $base_url.'/'.$row['vd_filename'];

		$this->_view('manage/vendor_detail', $data);
    }



    public function vendor_process() {

		$this->load->model('vendor_tb_model');
        $req = $this->input->post();

        $sess = array();
        $log_array = array();
        $row_data = array();

        $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/manage/vendor';
        
        $field_list = $this->vendor_tb_model->getFields();
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
            if( ! $this->vendor_tb_model->get($req['vd_id'])->isSuccess()) {
                $log_array['msg'] = getAlertMsg('INVALID_SUBMIT');
                $this->common->alert($log_array['msg']);
                $this->common->locationhref($rtn_url);
                $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['vd_id'], $log_array, 'vendor_tb');
                return;
            }
            $row_data = $this->vendor_tb_model->getData();

            $delete_path = $this->common->getImgPath('vendor', $row_data['vd_id']);
            $delete_filename = $delete_path.'/'.$row_data['vd_filename'];

            $log_array['prev_data'] = $row_data;
        }
        //echo print_r($data_params).PHP_EOL; exit;

        switch($req['mode']) {

            case 'delete':

                if( ! $this->vendor_tb_model->doDelete($row_data['vd_id'])->isSuccess()) {
                    $log_array['msg'] = $this->vendor_tb_model->getErrorMsg();
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['vd_id'], $log_array, 'vendor_tb');

                    if($this->input->is_ajax_request()) {
                        $json_data = array(
                            'is_success' => false,
                            'msg' => $log_array['msg']
                        );
                        echo json_encode($json_data);
                    }else {
                        $this->common->alert($log_array['msg']);
                        $this->common->locationhref($rtn_url);
                    }
                    return;
                }

                // 이미지 처리
                @unlink($delete_filename);
                $this->common->write_history_log($sess, 'DELETE', $req['vd_id'], $log_array, 'vendor_tb');

                if($this->input->is_ajax_request()) {
                    $json_data = array(
                        'is_success' => true,
                        'msg' => ''
                    );
                    echo json_encode($json_data);
                }else {
                    $this->common->locationhref($rtn_url);
                }
                break;

            case 'update':

                $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/manage/vendor_detail/'.$req['vd_id'];

                // UNIQUE KEY 
                $params = array();
                $params['!=']['vd_id'] = $req['vd_id'];
                $params['=']['vd_name'] = $data_params['vd_name'];
                $cnt = $this->vendor_tb_model->getCount($params)->getData();
                if($cnt > 0) {
                    $this->common->alert(getAlertMsg('DUPLICATE_VALUES').' [Name]');
                    $this->common->historyback();
                    return;
                }

                // 이미지 처리
                $updated_img = FALSE;
                if(isset($req['img_origin']) && sizeof($req['img_origin']) > 0) {
                    $updated_img = TRUE;
                    //$data_params['vd_filename'] = $req['img_origin'][0];   // 업로드한 이미지명 그대로 저장
                    $data_params['vd_filename'] = $req['img_filename'][0];     // 업로드시 Encrypt 된 파일명 사용
                }

                //echo print_r($data_params); exit;
                $log_array['params'] = $data_params;
                if( ! $this->vendor_tb_model->doUpdate($req['vd_id'], $data_params)->isSuccess()) {
                    $log_array['msg'] = $this->vendor_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['vd_id'], $log_array, 'vendor_tb');
                    $this->common->locationhref($rtn_url);
                    return;
                }

                // 이미지 처리
                if($updated_img == TRUE) {
                    $tempfile = IMG_TEMP_PATH.'/'.$req['img_filename'][0];
                    $path = $this->common->getImgPath('vendor', $req['vd_id']);
                    $orifile = $path.'/'.$data_params['vd_filename'];

                    @unlink($delete_filename);
                    @exec('mv '.escapeshellarg($tempfile).' '.escapeshellarg($orifile));
                }
                $this->common->write_history_log($sess, 'UPDATE', $req['vd_id'], $log_array, 'vendor_tb');
                break;
            
            case 'insert':
                if( ! isset($data_params['vd_name'])) {
                    $log_array['msg'] = getAlertMsg('REQUIRED_VALUES'); 
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    return;
                }

                $cnt = $this->vendor_tb_model->getCount(array('=' => array('vd_name' => $data_params['vd_name'])))->getData();
                if($cnt > 0) {
                    $log_array['msg'] = getAlertMsg('DUPLICATE_VALUES').' [Name]'; 
                    $this->common->alert($log_array['msg']);
                    $this->common->historyback();
                    return;
                }

                // 이미지 처리
                if(isset($req['img_origin']) && sizeof($req['img_origin']) > 0) {
                    //$data_params['vd_filename'] = $req['img_origin'][0];   // 업로드한 이미지명 그대로 저장
                    $data_params['vd_filename'] = $req['img_filename'][0];     // 업로드시 Encrypt 된 파일명 사용
                }
            
                $data_params['vd_created_at'] = date('Y-m-d H:i:s');
                unset($data_params['vd_id']);

                
                $log_array['params'] = $data_params;
                if( ! $this->vendor_tb_model->doInsert($data_params)->isSuccess()) {
                    $log_array['msg'] = $this->vendor_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    return;
                }

				$act_key = $this->vendor_tb_model->getData();

                // 이미지 처리
                if(isset($req['img_origin']) && sizeof($req['img_origin']) > 0) {
                    $tempfile = IMG_TEMP_PATH.'/'.$req['img_filename'][0];
                    $path = $this->common->getImgPath('vendor', $act_key);
                    $orifile = $path.'/'.$data_params['vd_filename'];
                    @exec('mv '.$tempfile.' '.$orifile);
                }

                $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/manage/vendor_detail/'.$act_key;
                $this->common->write_history_log($sess, 'INSERT', $act_key, $log_array, 'location_tb');
                break;
        }

        $this->common->locationhref($rtn_url);
    }

    public function supplier() {

        $data = array();

		$this->load->model('supplier_tb_model');
        $req = $this->input->post();
        $data = array();

        if(isset($req['mode']) && $req['mode'] == 'list') {

            $out_data = array();
            $params = array();
            $extras = array();

            //echo print_r($req);
            $fields = array('sp_name', 'sp_address', 'sp_country', 'sp_email', 'sp_memo');
            $params = $this->common->transDataTableFiltersToParams($req, $fields);
            $extras = $this->common->transDataTableFiltersToExtras($req);
            //echo print_r($params); exit;

            $count = $this->supplier_tb_model->getCount($params)->getData();
            $rows = $this->supplier_tb_model->getList($params, $extras)->getData();

            $data = array();
            foreach($rows as $k=>$r){
                $r['sp_url'] = '<a href="'.$r['sp_url'].'"n target="_blank">'.$r['sp_url'].'</a>';
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
		$this->_view('manage/supplier', $data);
    }


    public function supplier_detail($id=0, $mode='clone') {

		$this->load->model('supplier_tb_model');
        $row = array();
		$id = intval($id);

        if($id > 0) {
            if($mode != 'clone') {
                $mode = 'update';
            }
            $row = $this->supplier_tb_model->get($id)->getData();
	    if( strlen($row['sp_address']) > 0) {
            	$row['sp_address'] = explode('<br />', $row['sp_address']);
	    }else { 
		$row['sp_address'] = array(
			0 => '', 
			1 => ''
		);
	    }

        }else {

            // SET 초기화 및 기본값 
            $mode = 'insert'; 
            $fields = $this->supplier_tb_model->getFields();
            foreach($fields as $f) {
                $row[$f] = ''; 
            }
	    $row['sp_address'] = array(
		0 => '', 
		1 => ''
	    );
        }

        $data = array();
        $data['mode'] = $mode;
        $data['row'] = $row;

        $data['select_country'] = getCountriesSearchSelect('sp_country', $row['sp_country']);

		$this->_view('manage/supplier_detail', $data);
    }


    public function supplier_process() {

		$this->load->model('supplier_tb_model');
        $req = $this->input->post();

        $sess = array();
        $log_array = array();
        $row_data = array();

        //echo print_r($req).PHP_EOL; exit;

        $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/manage/supplier';
        
        $field_list = $this->supplier_tb_model->getFields();
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
            if( ! $this->supplier_tb_model->get($req['sp_id'])->isSuccess()) {
                $log_array['msg'] = getAlertMsg('INVALID_SUBMIT');
                $this->common->alert($log_array['msg']);
                $this->common->locationhref($rtn_url);
                $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['sp_id'], $log_array, 'supplier_tb');
                return;
            }
            $row_data = $this->supplier_tb_model->getData();
            $log_array['prev_data'] = $row_data;
        }
        //echo print_r($data_params).PHP_EOL; exit;

        switch($req['mode']) {

            case 'delete':

                if( ! $this->supplier_tb_model->doDelete($row_data['sp_id'])->isSuccess()) {
                    $log_array['msg'] = $this->supplier_tb_model->getErrorMsg();
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['sp_id'], $log_array, 'supplier_tb');

                    if($this->input->is_ajax_request()) {
                        $json_data = array(
                            'is_success' => false,
                            'msg' => $log_array['msg']
                        );
                        echo json_encode($json_data);
                    }else {
                        $this->common->alert($log_array['msg']);
                        $this->common->locationhref($rtn_url);
                    }
                    return;
                }
                $this->common->write_history_log($sess, 'DELETE', $req['sp_id'], $log_array, 'supplier_tb');
                if($this->input->is_ajax_request()) {
                    $json_data = array(
                        'is_success' => true,
                        'msg' => ''
                    );
                    echo json_encode($json_data);
                }else {
                    $this->common->locationhref($rtn_url);
                }
                return;

                break;

            case 'update':

                $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/manage/supplier_detail/'.$req['sp_id'];

                // 주소 저장
                if (strlen(trim($data_params['sp_address'][0])) > 0) {
                    $data_params['sp_address'] = implode('<br />', $data_params['sp_address']);
                }

                $log_array['params'] = $data_params;
                if( ! $this->supplier_tb_model->doUpdate($req['sp_id'], $data_params)->isSuccess()) {
                    $log_array['msg'] = $this->supplier_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['sp_id'], $log_array, 'supplier_tb');
                    $this->common->locationhref($rtn_url);
                    return;
                }
                $this->common->write_history_log($sess, 'UPDATE', $req['sp_id'], $log_array, 'supplier_tb');
                break;
            
            case 'insert':
            case 'clone':

                if( ! isset($data_params['sp_name'])) {
                    $log_array['msg'] = getAlertMsg('REQUIRED_VALUES'); 
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    return;
                }
               

                // 주소 저장
                $data_params['sp_address'] = '';
                if (strlen(trim($data_params['sp_address'][0])) > 0) {
                    $data_params['sp_address'] = implode('<br />', $data_params['sp_address']);
                }
			
                $data_params['sp_created_at'] = date('Y-m-d H:i:s');
                unset($data_params['sp_id']);
                //echo print_r($data_params).PHP_EOL; exit; 

                $log_array['params'] = $data_params;
                if( ! $this->supplier_tb_model->doInsert($data_params)->isSuccess()) {
                    $log_array['msg'] = $this->supplier_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    return;
                }
				$act_key = $this->supplier_tb_model->getData();
                $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/manage/supplier_detail/'.$act_key;
                $this->common->write_history_log($sess, 'INSERT', $act_key, $log_array, 'supplier_tb');
                break;
        }

        $this->common->locationhref($rtn_url);
    }


    public function custom() {

        $data = array();
        $this->load->model(array(
                'fieldset_tb_model',
                'custom_field_tb_model',
                'custom_field_map_tb_model'
        ));
		$this->load->business('custom_field_tb_business');
        $req = $this->input->post();
        $data = array();

        if(isset($req['mode']) && $req['mode'] == 'list') {

            $out_data = array();
            $params = array();
            $extras = array();

            //echo print_r($req);
            $fields = array(
                'cf_name', 'cf_format_element', 'cf_help_text', 'cf_element_value'
            ); 
            $params = $this->common->transDataTableFiltersToParams($req, $fields);
            $extras = $this->common->transDataTableFiltersToExtras($req);
            $extras['order_by'] = array('cf_id DESC');
            //echo print_r($params); exit;
            $count = $this->custom_field_tb_model->getCount($params)->getData();
            $rows = $this->custom_field_tb_model->getList($params, $extras)->getData();

            $cf_ids = array_keys($this->common->getDataByPK($rows, 'cf_id'));
            $params = array();
            $extras = array();
            $params['join']['fieldset_tb'] = 'cfm_fieldset_id = fs_id';
            $params['in']['cfm_custom_field_id'] = $cf_ids;
            $extras['fields'] = array('cfm_custom_field_id', 'fs_id', 'fs_name');
            $fs_data = $this->custom_field_map_tb_model->getList($params, $extras)->getData();
            $fs_data = $this->common->getDataByDuplPK($fs_data, 'cfm_custom_field_id');


            $icon_map = $this->custom_field_tb_business->getIconTypeMap();

            $data = array();
            foreach($rows as $k=>$r){
                $fs_names = array();
                if( isset($fs_data[$r['cf_id']])) {
                    foreach($fs_data[$r['cf_id']] as $k=>$v) {
                        $url = '/admin/manage/fieldset_detail/'.$v['fs_id'];

                        $btn_html = '<a href="'.$url.'" class="m-1" target="_blank">';
                        $btn_html .= '<span class="badge border border-success text-danger">'.$v['fs_name'].'</span>';
                        $btn_html .= '</a>';

                        $fs_names[] = $btn_html; 
                    }
                }

                $icon = '<i class="fal '.$icon_map[$r['cf_format_element']].'"></i>';
                $r['cf_format_element'] = $icon.'&nbsp;'.$r['cf_format_element'];

                $r['fs_name'] = implode(' ', $fs_names);
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


        $element_type = $this->custom_field_tb_business->getElementTypeMap();
        $data['element_type'] = $this->common->genJqgridOption($element_type, false);
        $format_type = $this->custom_field_tb_business->getElementFormatValueMap();
        $data['format_type'] = $this->common->genJqgridOption($format_type, false);
        $encrypt_type = $this->custom_field_tb_business->getEncryptMap();
        $data['encrypt_type'] = $this->common->genJqgridOption($encrypt_type, false);
        $required_type = $this->custom_field_tb_business->getRequiredMap();
        $data['required_type'] = $this->common->genJqgridOption($required_type, false);

		$this->_view('manage/custom', $data);
    }



    public function custom_detail($id=0, $mode='insert') {

		$this->load->model('custom_field_tb_model');
		$this->load->business('custom_field_tb_business');

        $row = array();
		$id = intval($id);

        if($id > 0) {
            if($mode != 'clone') {
                $mode = 'update';
            }
            $row = $this->custom_field_tb_model->get($id)->getData();

        }else {

            // SET 초기화 및 기본값 
            $mode = 'insert'; 
            $fields = $this->custom_field_tb_model->getFields();
            foreach($fields as $f) {
                switch($f) {
                    case 'cf_format':
                        $row['cf_format'] = 'ANY';
                        break;
                    case 'cf_required':
                        $row['cf_required'] = 'NO';
                        break;
                    case 'cf_encrypt':
                        $row['cf_encrypt'] = 'NO';
                        break;
                    default:
                        $row[$f] = '';
                        break;
                }

            }
        }

        $data = array();
        $data['mode'] = $mode;
        $data['row'] = $row;


        $element_type = $this->custom_field_tb_business->getElementTypeMap();
        $format_type = $this->custom_field_tb_business->getElementFormatValueMap();
        $encrypt_type = $this->custom_field_tb_business->getEncryptMap();
        $required_type = $this->custom_field_tb_business->getRequiredMap();

        $data['select_element'] = getSearchSelect($element_type, 'cf_format_element', $row['cf_format_element']);
        $data['format_help_map'] = json_encode($this->custom_field_tb_business->getElementFormatHelpMap());
        $data['select_format'] = getSearchSelect($format_type, 'cf_format', $row['cf_format']);
        $data['select_encrypt'] = getSelect($encrypt_type, 'cf_encrypt', $row['cf_encrypt']);
        $data['select_required'] = getSelect($required_type, 'cf_required', $row['cf_required']);

		$this->_view('manage/custom_detail', $data);
    }



    public function custom_process() {

		$this->load->model('custom_field_tb_model');
        $req = $this->input->post();

        $sess = array();
        $log_array = array();
        $row_data = array();
        //echo print_r($req).PHP_EOL; exit;

        $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/manage/custom';
        
        $field_list = $this->custom_field_tb_model->getFields();
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
            if( ! $this->custom_field_tb_model->get($req['cf_id'])->isSuccess()) {
                $log_array['msg'] = getAlertMsg('INVALID_SUBMIT');
                $this->common->alert($log_array['msg']);
                $this->common->locationhref($rtn_url);
                $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['cf_id'], $log_array, 'custom_field_tb');
                return;
            }
            $row_data = $this->custom_field_tb_model->getData();
            $log_array['prev_data'] = $row_data;
        }
        //echo print_r($data_params).PHP_EOL; exit;

        switch($req['mode']) {

            case 'delete':
                
                if( ! $this->custom_field_tb_model->doDelete($row_data['cf_id'])->isSuccess()) {
                    $log_array['msg'] = $this->custom_field_tb_model->getErrorMsg();
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['cf_id'], $log_array, 'custom_field_tb');
                    
                    if($this->input->is_ajax_request()) {
                        $json_data = array(
                            'is_success' => false,
                            'msg' => $log_array['msg']
                        );
                        echo json_encode($json_data);
                    }else {
                        $this->common->alert($log_array['msg']);
                        $this->common->locationhref($rtn_url);
                    }
                    return;
                }
                $this->common->write_history_log($sess, 'DELETE', $req['cf_id'], $log_array, 'custom_field_tb');

                if($this->input->is_ajax_request()) {
                    $json_data = array(
                        'is_success' => true,
                        'msg' => ''
                    );
                    echo json_encode($json_data);
                }else {
                    $this->common->locationhref($rtn_url);
                }
                return;

                break;

            case 'update':

                $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/manage/custom_detail/'.$req['cf_id'];

                // UNIQUE KEY 
                $params = array();
                $params['!=']['cf_id'] = $req['cf_id'];
                $params['=']['cf_name'] = $data_params['cf_name'];
                $cnt = $this->custom_field_tb_model->getCount($params)->getData();
                if($cnt > 0) {
                    $this->common->alert(getAlertMsg('DUPLICATE_VALUES').' [Name]');
                    $this->common->historyback();
                    return;
                }

                $log_array['params'] = $data_params;
                if( ! $this->custom_field_tb_model->doUpdate($req['cf_id'], $data_params)->isSuccess()) {
                    $log_array['msg'] = $this->custom_field_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['cf_id'], $log_array, 'custom_field_tb');
                    $this->common->locationhref($rtn_url);
                    return;
                }
                $this->common->write_history_log($sess, 'UPDATE', $req['cf_id'], $log_array, 'custom_field_tb');
                break;
            
            case 'insert':
            case 'clone':
                if( ! isset($data_params['cf_name'])) {
                    $log_array['msg'] = getAlertMsg('REQUIRED_VALUES'); 
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    return;
                }
                
                // UNIQUE KEY
                $cnt = $this->custom_field_tb_model->getCount(array('=' => array('cf_name' => $data_params['cf_name'])))->getData();
                if($cnt > 0) {
                    $log_array['msg'] = getAlertMsg('DUPLICATE_VALUES').' [Name]'; 
                    $this->common->alert($log_array['msg']);
                    $this->common->historyback();
                    return;
                }

                $data_params['cf_created_at'] = date('Y-m-d H:i:s');
                unset($data_params['cf_id']);
                //echo print_r($data_params).PHP_EOL; exit; 

                $log_array['params'] = $data_params;
                if( ! $this->custom_field_tb_model->doInsert($data_params)->isSuccess()) {
                    $log_array['msg'] = $this->custom_field_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    return;
                }
				$act_key = $this->custom_field_tb_model->getData();
                $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/manage/custom_detail/'.$act_key;
                $this->common->write_history_log($sess, 'INSERT', $act_key, $log_array, 'custom_field_tb');
                break;
        }

        $this->common->locationhref($rtn_url);
    }



    public function rack() {

        $data = array();

		$this->load->model(array(
            'rack_tb_model',
            'location_tb_model'
        ));
        $this->load->business(array(
            'location_tb_business',
        ));

        $req = $this->input->post();
        $data = array();

        if(isset($req['mode']) && $req['mode'] == 'list') {

            $out_data = array();
            $params = array();
            $extras = array();

            //echo print_r($req);
            $fields = array('r_code', 'l_name', 'l_code'); 
            $params = $this->common->transDataTableFiltersToParams($req, $fields);
            $extras = $this->common->transDataTableFiltersToExtras($req);


            $params['join']['location_tb'] = 'r_location_id = l_id';
            $extras['fields'] = array(
                'rack_tb.*',
                'l_id', 'l_name', 'l_code',
            );
            //echo print_r($params); exit;

            $count = $this->rack_tb_model->getCount($params)->getData();
            $rows = $this->rack_tb_model->getList($params, $extras)->getData();

            $data = array();
            foreach($rows as $k=>$r){
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


        $location_code= $this->location_tb_business->getCodeMap('l_code');
        $data['location_code_map'] = $this->common->genJqgridOption($location_code, false);


		$this->_view('manage/rack', $data);
    }


    public function rack_detail($id=0, $mode='insert') {

		$this->load->model('rack_tb_model');
		$this->load->business(array(
            'location_tb_business',
        ));

        $row = array();
		$id = intval($id);

        if($id > 0) {
            if($mode != 'clone') {
                $mode = 'update';
            }
            $row = $this->rack_tb_model->get($id)->getData();

        }else {

            // SET 초기화 및 기본값 
            $mode = 'insert'; 
            $fields = $this->rack_tb_model->getFields();
            foreach($fields as $f) {
                $row[$f] = ''; 
            }
        }

        $data = array();
        $data['mode'] = $mode;
        $data['row'] = $row;

        $location_data = $this->location_tb_business->getNameMap();
        $data['select_location'] = getSearchSelect($location_data, 'r_location_id', $row['r_location_id'], 'required');


        $location_code = $this->location_tb_business->getCodeMap();
        $data['location_map'] = json_encode($location_code);
        $data['l_code'] = isset($location_code[$row['r_location_id']]) ? $location_code[$row['r_location_id']] : '';

		$this->_view('manage/rack_detail', $data);
    }



    public function rack_process() {

		$this->load->model(array(
            'rack_tb_model',
            'assets_model_tb_model'
        ));
        $req = $this->input->post();

        $sess = array();
        $log_array = array();
        $row_data = array();

        //echo print_r($req).PHP_EOL; exit;

        $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/manage/rack';
        
        $field_list = $this->rack_tb_model->getFields();
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
            if( ! $this->rack_tb_model->get($req['r_id'])->isSuccess()) {
                $log_array['msg'] = getAlertMsg('INVALID_SUBMIT');
                $this->common->alert($log_array['msg']);
                $this->common->locationhref($rtn_url);
                $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['r_id'], $log_array, 'rack_tb');
                return;
            }
            $row_data = $this->rack_tb_model->getData();
            $log_array['prev_data'] = $row_data;
        }
        
 
        // 중복값 처리
        if($req['mode'] != 'delete') {

            $params = array();
            $params['=']['r_code'] = $data_params['r_code'];
            if($req['mode'] == 'update') {
                $params['!=']['r_id'] = $req['r_id']; 
            }
            $cnt = $this->rack_tb_model->getCount($params)->getData();

            if($cnt > 0) {
                $log_array['msg'] = getAlertMsg('DUPLICATE_VALUES').' [Code]'; 
                $this->common->alert($log_array['msg']);
                $this->common->historyback();
                return;
            }
        }


        switch($req['mode']) {

            case 'delete':

                // [Assets_mode.rack_code] 데이터 존재여부 확인
                $params = array();
                $params['=']['am_rack_id'] = $row_data['r_id'];
                $cnt = $this->assets_model_tb_model->getCount($params)->getData();
                if($cnt > 0) {
                    $log_array['msg'] = getAlertMsg('FAILED_DELETE_BINDED').' [assets_model.rack_code]'; 
                    if($this->input->is_ajax_request()) {
                        $json_data = array(
                            'is_success' => false,
                            'msg' => $log_array['msg']
                        );
                        echo json_encode($json_data);
                    }else {
                        $this->common->alert($log_array['msg']);
                        $this->common->locationhref($rtn_url);
                    }
                    return;
                }


                if( ! $this->rack_tb_model->doDelete($row_data['r_id'])->isSuccess()) {
                    $log_array['msg'] = $this->rack_tb_model->getErrorMsg();
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['r_id'], $log_array, 'rack_tb');

                    if($this->input->is_ajax_request()) {
                        $json_data = array(
                            'is_success' => false,
                            'msg' => $log_array['msg']
                        );
                        echo json_encode($json_data);
                    }else {
                        $this->common->alert($log_array['msg']);
                        $this->common->locationhref($rtn_url);
                    }
                    return;
                }
                $this->common->write_history_log($sess, 'DELETE', $req['r_id'], $log_array, 'rack_tb');
                if($this->input->is_ajax_request()) {
                    $json_data = array(
                        'is_success' => true,
                        'msg' => ''
                    );
                    echo json_encode($json_data);
                }else {
                    $this->common->locationhref($rtn_url);
                }
                return;

            case 'update':

                $tn_url = '/'.SHOP_INFO_ADMIN_DIR.'/manage/rack_detail/'.$req['r_id'];
                $log_array['params'] = $data_params;
                if( ! $this->rack_tb_model->doUpdate($req['r_id'], $data_params)->isSuccess()) {
                    $log_array['msg'] = $this->rack_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['r_id'], $log_array, 'rack_tb');
                    return;
                }

                // [Assets_model] am_rack_code 데이터 정합성 확보
                $params = array(
                    'am_location_id' => $data_params['r_location_id'],
                    'am_rack_code'   => $data_params['r_code']
                );
                $where_params = array();
                $where_params['=']['am_rack_id'] = $req['r_id'];
                $this->assets_model_tb_model->doMultiUpdate($params, $where_params);


                $this->common->write_history_log($sess, 'UPDATE', $req['r_id'], $log_array, 'rack_tb');
                break;
            
            case 'insert':
            case 'clone':

                if( ! isset($data_params['r_location_id'])) {
                    $log_array['msg'] = getAlertMsg('REQUIRED_VALUES'); 
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    return;
                }
			
                $data_params['r_created_at'] = date('Y-m-d H:i:s');
                unset($data_params['r_id']);
                //echo print_r($data_params).PHP_EOL; exit; 

                $log_array['params'] = $data_params;
                if( ! $this->rack_tb_model->doInsert($data_params)->isSuccess()) {
                    $log_array['msg'] = $this->rack_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    return;
                }
				$act_key = $this->rack_tb_model->getData();
                $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/manage/rack_detail/'.$act_key;
                $this->common->write_history_log($sess, 'INSERT', $act_key, $log_array, 'rack_tb');
                break;
        }

        $this->common->locationhref($rtn_url);
    }




    public function ajax_get_custom_fields() {

		$this->load->model(array(
            'models_tb_model',
            'custom_field_map_tb_model',
            'custom_field_tb_model',
            'fieldset_tb_model',
        ));

        $this->load->business(array(
            'custom_field_tb_business',
        ));

        $json_data = array(
            'is_success' => FALSE,
            'msg'       => ''
        );
        $req = $this->input->post();

        $fieldset_id = 0;
        if(isset($req['fieldset_id']) && strlen($req['fieldset_id']) > 0) {
            $fieldset_id = $req['fieldset_id'];
        }

        if($fieldset_id < 0) {
            $json_data['msg'] = getAlertMsg('INVALID_SUBMIT');    
            echo json_encode($json_data);
            return; 
        }

        $params = array();
        $params['=']['cfm_fieldset_id'] = $fieldset_id;
        $params['join']['custom_field_tb'] = 'cf_id = cfm_custom_field_id';
        $extras = array();
        $extras['fields'] = array(
            'cfm_id', 'cfm_fieldset_id', 'cfm_order', 'cfm_required',
            'cf_id', 'cf_name', 'cf_format', 'cf_format_element', 'cf_help_text', 'cf_element_value', 'cf_encrypt'
        );
        $extras['order_by'] = array('cfm_order ASC');
        $cf_data = $this->custom_field_map_tb_model->getList($params, $extras)->getData();
        if(sizeof($cf_data) > 0) {
            $fs_data = $this->fieldset_tb_model->get($fieldset_id)->getData();
            $format_map = $this->custom_field_tb_business->getElementFormatMap();

            $temp_data = array(
                'prefix'        => 'cf_',
                'fieldset_data' => $fs_data,
                'custom_data'   => $cf_data,
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
}
