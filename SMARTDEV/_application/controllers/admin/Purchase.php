<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include_once dirname(__FILE__).'/Base_admin.php';

class Purchase extends Base_admin {


    public function orders() {

        $data = array();


		$this->load->model(array(
            'order_tb_model',
            'order_item_tb_model'
        ));
        $this->load->business(array(
            'company_tb_business',
            'supplier_tb_business',
        ));

        $company_data = $this->company_tb_business->getNameMap();
        $supplier_data = $this->supplier_tb_business->getNameMap();
        $status_color_data = $this->order_tb_model->getStatusColorMap();

        $req = $this->input->post();
        $data = array();


        if(isset($req['mode']) && $req['mode'] == 'list') {

            $out_data = array();
            $params = array();
            $extras = array();

            //echo print_r($req);
            $fields = array('o_estimatenum', 'o_reportnum', 'o_ordernum');
            $params = $this->common->transDataTableFiltersToParams($req, $fields);
            $extras = $this->common->transDataTableFiltersToExtras($req);
            //echo print_r($params); exit;

            $extras = array();           
            $extras['order_by'] = array('o_id DESC');
            

            $count = $this->order_tb_model->getCount($params)->getData();
            $rows = $this->order_tb_model->getList($params, $extras)->getData();

            $data = array();
            foreach($rows as $k=>$r){

                $r['o_company_id'] = $company_data[$r['o_company_id']];
                $r['o_supplier_id'] = $supplier_data[$r['o_supplier_id']];

                $status = $r['o_order_status'];
                $color_code = $status_color_data[$status];
                $r['o_order_status'] = '<span class="badge badge-'.$color_code.'">'.$status.'</span>';
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

        $status_data = $this->order_tb_model->getStatusMap();
        $data['status_data'] = $this->common->genJqgridOption($status_data, false);
        $data['company_data'] = $this->common->genJqgridOption($company_data, false);
        $data['supplier_data'] = $this->common->genJqgridOption($supplier_data, false);

		$this->_view('purchase/orders', $data);
    }


    public function order_items() {

        $data = array();

		$this->load->model(array(
            'order_tb_model',
            'order_item_tb_model'
        ));
        $this->load->business(array(
            'company_tb_business',
            'supplier_tb_business',
        ));


        $company_data = $this->company_tb_business->getNameMap();
        $supplier_data = $this->supplier_tb_business->getNameMap();
        $status_color_data = $this->order_tb_model->getStatusColorMap();

        $req = $this->input->post();
        $data = array();


        if(isset($req['mode']) && $req['mode'] == 'list') {

            $out_data = array();
            $params = array();
            $extras = array();

            //echo print_r($req);
            $fields = array(
                'o_estimatenum', 'o_reportnum', 'o_ordernum',
                'oi_service_tag', 'oi_model_name',
            );
            $params = $this->common->transDataTableFiltersToParams($req, $fields);
            $extras = $this->common->transDataTableFiltersToExtras($req);
            //echo print_r($params); exit;

            $params['join']['order_item_tb'] = 'o_id = oi_order_id';

            $extras = array();           
            $extras['fields'] = array(
                'order_tb.*', 'oi_unit_price', 'oi_tax', 'oi_total_price', 'oi_model_name', 'oi_service_tag' 
            );
            $extras['order_by'] = array('o_id DESC');
            
            //echo print_r($params);
            //echo print_r($extras); exit;

            $count = $this->order_tb_model->getCount($params)->getData();
            $rows = $this->order_tb_model->getList($params, $extras)->getData();

            $data = array();
            foreach($rows as $k=>$r){

                $r['o_company_id'] = $company_data[$r['o_company_id']];
                $r['o_supplier_id'] = $supplier_data[$r['o_supplier_id']];

                $status = $r['o_order_status'];
                $color_code = $status_color_data[$status];
                $r['o_order_status'] = '<span class="badge badge-'.$color_code.'">'.$status.'</span>';
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

        $status_data = $this->order_tb_model->getStatusMap();
        $data['status_data'] = $this->common->genJqgridOption($status_data, false);
        $data['company_data'] = $this->common->genJqgridOption($company_data, false);
        $data['supplier_data'] = $this->common->genJqgridOption($supplier_data, false);

		$this->_view('purchase/order_items', $data);
    }


    public function order_detail($id=0) {

		$this->load->model(array('order_tb_model'));
		$this->load->business(array(
            'company_tb_business',
            'supplier_tb_business',
            'models_tb_business',
        ));

        $row = array();
		$id = intval($id);

        if($id > 0) {
            $mode = 'update'; 
            $row = $this->order_tb_model->get($id)->getData();
        
        }else {

            // SET 초기화 및 기본값 
            $mode = 'insert'; 
            $fields = $this->order_tb_model->getFields();
            foreach($fields as $f) {
                $row[$f] = ''; 
            }
        }

        $data = array();
        $data['mode'] = $mode;
        $data['row'] = $row;

        $status_data = $this->order_tb_model->getStatusMap();
        if($mode == 'insert') {
            $data['select_status'] = getSelect($status_data, 'o_order_status', 'ORDERED', 'disabled');
        }else {
            $data['select_status'] = getSelect($status_data, 'o_order_status', $row['o_order_status']);
        }
        $data['status_data'] = $status_data;
        $data['status_color_data']= $this->order_tb_model->getStatusColorMap();

        $company_data = $this->company_tb_business->getNameMap();
        $data['select_company'] = getSearchSelect($company_data, 'o_company_id', $row['o_company_id'], 'required');

        $supplier_data = $this->supplier_tb_business->getOptionMap();
        $data['select_supplier'] = getGroupSearchSelect($supplier_data, 'o_supplier_id', $row['o_supplier_id']); 

        $model_data = $this->models_tb_business->getNameMap();
        $data['select_model'] = getSearchSelect($model_data, 'sel_model', '');



        $img_path = $this->common->getImgPath('order', $row['o_id']);
        $data['img_name'] = $img_path.'/'.$row['o_filename'];

        if(file_exists($data['img_name'])) {
            $data['img_size'] = filesize($data['img_name']);


            //echo $data['img_name'].PHP_EOL; 
            $file_type = $this->common->get_mime_type($data['img_name']);
            //echo $file_type.PHP_EOL; //exit;


            // 업로드 이미지 썸네일
            if($this->common->is_img_type($file_type)) {
                $base_url = $this->common->getImgUrl('order', $row['o_id']);
                $data['thumbnail'] = $base_url.'/'.$row['o_filename'];
                $data['file_type'] = 'image';
            }else {
                $thumbnail_filename = THUMBNAIL_PATH.'/'.strtoupper($file_type).'.png';
                if(file_exists($thumbnail_filename)) {
                    $data['thumbnail'] = THUMBNAIL_URL.'/'.strtoupper($file_type).'.png';
                }else {
                    $data['thumbnail'] = THUMBNAIL_URL.'/TXT.png';
                }
                $data['file_type'] = 'file';
            }
            $data['file_uri'] = 'order';


        }else {
            
        }

		$this->_view('purchase/order_detail', $data);
    }


    public function order_process() {

		$this->load->model('order_tb_model');
        $this->load->business(array(
            'order_item_tb_business',
        ));
        $req = $this->input->post();

        $sess = array();
        $log_array = array();
        $row_data = array();

        //echo print_r($req).PHP_EOL;

        $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/purchase/orders';
        
        $field_list = $this->order_tb_model->getFields();
        $data_params = $this->common->filterFields($field_list, $req); 

        // 삭제 & 업데이트 시 기존데이터 검증
        $row_data = array();
        if($req['mode'] == 'update' || $req['mode'] == 'delete') {
            if( ! $this->order_tb_model->get($req['o_id'])->isSuccess()) {
                $log_array['msg'] = getAlertMsg('INVALID_SUBMIT');
                $this->common->alert($log_array['msg']);
                $this->common->locationhref($rtn_url);
                $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['o_id'], $log_array, 'order_tb');
                return;
            }
            $row_data = $this->order_tb_model->getData();


            // IMG DELETE
            $delete_path = $this->common->getImgPath('order', $row_data['o_id']);
            $delete_filename = $delete_path.'/'.$row_data['o_filename'];

            $log_array['prev_data'] = $row_data;
        }
        //echo print_r($data_params).PHP_EOL; exit;

        switch($req['mode']) {

            case 'delete':
                
                $data_params = array();
                $data_params['o_is_active'] = 'NO';
                $log_array['params'] = $data_params;

                if( ! $this->order_tb_model->doUpdate($row_data['o_id'], $data_params)->isSuccess()) {
                    $log_array['msg'] = $this->order_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['o_id'], $log_array, 'order_tb');
                    return;
                }

                // 이미지 처리
                @unlink($delete_filename);
                $this->common->write_history_log($sess, 'DELETE', $req['o_id'], $log_array, 'order_tb');
                $this->common->locationhref($rtn_url);
                return;

                break;

            case 'update':

                // 이미지 처리
                $updated_img = FALSE;
                if(isset($req['img_origin']) && sizeof($req['img_origin']) > 0) {
                    $updated_img = TRUE;
                    $data_params['o_filename'] = $req['img_filename'][0];       // 업로드시 Encrypt 된 파일명 사용
                    $data_params['o_origin_filename'] = $req['img_origin'][0];  // Origin 파일명
                }

                $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/purchase/order_detail/'.$req['o_id'];
                $key = 'o_'.strtolower($req['o_order_status']).'_at';
                $date_at = date('Y-m-d H:i:s');
                $data_params[$key] = $date_at;  

                $log_array['params'] = $data_params;
                if( ! $this->order_tb_model->doUpdate($req['o_id'], $data_params)->isSuccess()) {
                    $log_array['msg'] = $this->order_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['o_id'], $log_array, 'order_tb');
                    return;
                }else {
                    $this->order_item_tb_business->SyncOrderItem($req['o_id']);

                    $history_data = $data_params;
                    $history_data['o_id'] = $req['o_id'];
                    $history_data['action'] = 'update';
                    $this->order_item_tb_business->SyncOrder($req['o_id'], $from='order_tb', $history_data);
                }


                // 이미지 처리
                if($updated_img == TRUE) {
                    $tempfile = IMG_TEMP_PATH.'/'.$req['img_filename'][0];
                    $path = $this->common->getImgPath('order', $req['o_id']);
                    $orifile = $path.'/'.$data_params['o_filename'];

                    @unlink($delete_filename);
                    @exec('mv '.escapeshellarg($tempfile).' '.escapeshellarg($orifile));
                }
                $this->common->write_history_log($sess, 'UPDATE', $req['o_id'], $log_array, 'order_tb');

                break;
            
            case 'insert':

                $data_params['o_order_status'] = 'ORDERED';
                $data_params['o_writer_id'] = $this->_ADMIN_DATA['id'];
                //echo print_r($data_params); exit;

                if( ! isset($data_params['o_ordernum'])) {
                    $log_array['msg'] = getAlertMsg('REQUIRED_VALUES'); 
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    return;
                }


                // 이미지 처리
                $inserted_img = FALSE;
                if(isset($req['img_origin']) && sizeof($req['img_origin']) > 0) {
                    $inserted_img = TRUE;
                    $data_params['o_filename'] = $req['img_filename'][0];       // 업로드시 Encrypt 된 파일명 사용
                    $data_params['o_origin_filename'] = $req['img_origin'][0];  // Origin 파일명
                }
			
                $data_params['o_created_at'] = date('Y-m-d H:i:s');
                $data_params['o_ordered_at'] = date('Y-m-d H:i:s');
                unset($data_params['o_id']);

                $history = array(
                    'order' => array(
                        $data_params['o_created_at'] => $data_params,
                        $data_params['o_ordered_at'] => $data_params,
                    ),
                    'order_item' => array()
                );
                $data_params['o_process_history'] = serialize($history);

                //echo print_r($data_params).PHP_EOL; //exit; 
                $log_array['params'] = $data_params;
                if( ! $this->order_tb_model->doInsert($data_params)->isSuccess()) {
                    $log_array['msg'] = $this->order_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    return;
                }
				$act_key = $this->order_tb_model->getData();

                // 이미지 처리
                if($inserted_img == TRUE) {
                    $tempfile = IMG_TEMP_PATH.'/'.$req['img_filename'][0];
                    $path = $this->common->getImgPath('order', $act_key);
                    $orifile = $path.'/'.$data_params['o_filename'];
                    @exec('mv '.escapeshellarg($tempfile).' '.escapeshellarg($orifile));
                }

                $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/purchase/order_detail/'.$act_key;
                $this->common->write_history_log($sess, 'INSERT', $act_key, $log_array, 'order_tb');
                break;
        }
        $this->common->locationhref($rtn_url);
    }


    public function order_item_process() {

		$this->load->model(array(
            'order_tb_model',
            'order_item_tb_model',
            'models_tb_model'
        ));
		$this->load->business(array(
            'order_item_tb_business',
        ));

        $json_data = array(
            'is_success' => FALSE,
            'msg'       => ''
        );
        $req = $this->input->post();

	$sess = array();

        switch($req['mode']) {

            case 'insert':

                if( 
                    (! isset($req['o_id']) || $req['o_id'] < 1) 
                    // STANBY LICENSE 에 대한 금액 처리 0원 
                    //(! isset($req['oi_unit_price']) || $req['oi_unit_price'] < 1)
                ) {
                    $json_data['msg'] = getAlertMsg('INVALID_SUBMIT');    
                     echo json_encode($json_data);
                    return;
                }

                $order_data = $this->order_tb_model->get($req['o_id'])->getData();
                if(sizeof($order_data) < 1) {
                    $json_data['msg'] = getAlertMsg('INVALID_SUBMIT');    
                     echo json_encode($json_data);
                    return;
                }

                $model_data = $this->models_tb_model->get($req['sel_model'])->getData();
                if(sizeof($model_data) < 1) {
                    $json_data['msg'] = getAlertMsg('INVALID_SUBMIT');    
                     echo json_encode($json_data);
                    return;
                }



                // UNIQUE KEY  
                if( isset($req['oi_service_tag']) && strlen($req['oi_service_tag']) > 0 ) {
                    $params = array();
                    $params['=']['oi_service_tag'] = $req['oi_service_tag'];
                    $cnt = $this->order_item_tb_model->getCount($params)->getData();
                    
                    if($cnt > 0) {
                        $json_data['msg'] = getAlertMsg('DUPLICATE_VALUES').' [Service Tag]'; 
                         echo json_encode($json_data);
                        return;
                    }
                }

                $log_array['params'] = $params;

                $params = array(
                    'oi_order_id'       => $req['o_id'],
                    'oi_writer_id'      => $this->_ADMIN_DATA['id'],
                    'oi_model_id'       => $model_data['m_id'],
                    'oi_model_name'     => $model_data['m_model_name'],
                    'oi_service_tag'    => $req['oi_service_tag'],
                    'oi_memo'           => $req['oi_memo'],
                    'oi_unit_price'     => $req['oi_unit_price'] * 1,
                    'oi_quantity'       => $req['oi_quantity'] * 1,
                    'oi_tax'            => $req['oi_tax'] * 1,
                    'oi_total_price'    => ($req['oi_unit_price'] * $req['oi_quantity']) + $req['oi_tax'],
                );
                if($this->order_item_tb_model->doInsert($params)->isSuccess() === FALSE) {
                    $json_data['msg'] = getAlertMsg('INVALID_SUBMIT');    
                }else {
                    $json_data['is_success'] = true;

                    $history_data = $params;
                    $history_data['oi_id'] = $this->order_item_tb_model->getData();
                    $history_data['action'] = 'insert';
                    $sync_res = $this->order_item_tb_business->SyncOrder($req['o_id'], $from='order_item_tb', $history_data);
                    $json_data['o_total_price'] = $sync_res['o_total_price'];
                    $json_data['o_vat_price'] = $sync_res['o_vat_price'];
                }

                $this->common->write_history_log($sess, 'INSERT', $history_data['oi_id'], $log_array, 'order_item_tb');
                break;


            case 'update':
                if(! isset($req['oi_id']) || $req['oi_id'] < 1) {
                    $json_data['msg'] = getAlertMsg('INVALID_SUBMIT');    
                     echo json_encode($json_data);
                    return;
                }


                // UNIQUE KEY 
                if( isset($req['oi_service_tag']) && strlen($req['oi_service_tag']) > 0 ) {
                    $params = array();
                    $params['!=']['oi_id'] = $req['oi_id'];
                    $params['=']['oi_service_tag'] = $req['oi_service_tag'];
                    $cnt = $this->order_item_tb_model->getCount($params)->getData();
                    if($cnt > 0) {
                        $json_data['msg'] = getAlertMsg('DUPLICATE_VALUES').' [Service Tag]';
                        echo json_encode($json_data);
                        return;
                    }
                }

                $params = array(
                    'oi_writer_id'  => $this->_ADMIN_DATA['id'],
                    'oi_unit_price' => $req['oi_unit_price'],
                    'oi_service_tag'=> $req['oi_service_tag'],
                    'oi_memo'       => $req['oi_memo'],
                    'oi_quantity'   => $req['oi_quantity'],
                    'oi_tax'        => $req['oi_tax'],
                    'oi_total_price'=> ($req['oi_unit_price'] * $req['oi_quantity']) + $req['oi_tax'],
                );
                $log_array['params'] = $params;

                if($this->order_item_tb_model->doUpdate($req['oi_id'], $params)->isSuccess() === FALSE) {
                    $json_data['msg'] = getAlertMsg('INVALID_SUBMIT');    
                    echo json_encode($json_data);
                    return;
                }else {
                    $json_data['is_success'] = true;

                    $history_data = $params;
                    $history_data['oi_id'] = $req['oi_id'];
                    $history_data['action'] = 'update';
                    $sync_res = $this->order_item_tb_business->SyncOrder($req['o_id'], $from='order_item_tb', $history_data);
                    $json_data['o_total_price'] = $sync_res['o_total_price'];
                    $json_data['o_vat_price'] = $sync_res['o_vat_price'];
                }

                $this->common->write_history_log($sess, 'UPDATE', $req['oi_id'], $log_array, 'order_item_tb');
                break;


            case 'delete':
                if(! isset($req['oi_id']) || $req['oi_id'] < 1) {
                    $json_data['msg'] = getAlertMsg('INVALID_SUBMIT');    
                    echo json_encode($json_data);
                    return;
                }
                $this->order_item_tb_model->doDelete($req['oi_id']);

                $history_data = array();
                $history_data['oi_id'] = $req['oi_id'];
                $history_data['action'] = 'delete';
                $sync_res = $this->order_item_tb_business->SyncOrder($req['o_id'], $from='order_item_tb', $history_data);
                $json_data['o_total_price'] = $sync_res['o_total_price'];
                $json_data['o_vat_price'] = $sync_res['o_vat_price'];
                $json_data['is_success'] = true;

                $this->common->write_history_log($sess, 'DELETE', $req['oi_id'], $log_array, 'order_item_tb');
                break;
        } // END SWITCH

        echo json_encode($json_data);
        return;
    }


    public function ajax_order_items() {

        $data = array();
		$this->load->model(array(
            'order_item_tb_model',
        ));

        $req = $this->input->post();
        //echo print_r($req); exit;

        $data = array();

        $out_data = array();
        $params = array();
        $extras = array();

        $count = 0;
        $rows = array();
        if(isset($req['oi_order_id']) && $req['oi_order_id'] > 0) {

            $params['=']['oi_order_id'] = $req['oi_order_id'];
            $extras['order_by'] = array('oi_id DESC');

            $count = $this->order_item_tb_model->getCount($params)->getData();
            $rows = $this->order_item_tb_model->getList($params, $extras)->getData();
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

}
