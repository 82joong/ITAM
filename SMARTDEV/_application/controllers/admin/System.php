<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include_once dirname(__FILE__).'/Base_admin.php';

class System extends Base_admin {


	public function manager() {

		$this->load->model('admin_tb_model');
        $req = $this->input->post();
        $data = array();

        if(isset($req['mode']) && $req['mode'] == 'list') {

            $out_data = array();
            $params = array();
            $extras = array();

            //echo print_r($req);
            $fields = array('a_firstname', 'a_lastname', 'a_loginid', 'a_email');
            $params = $this->common->transDataTableFiltersToParams($req, $fields);
            $extras = $this->common->transDataTableFiltersToExtras($req);
            //echo print_r($params); exit;

            $count = $this->admin_tb_model->getCount($params)->getData();
            $rows = $this->admin_tb_model->getList($params, $extras)->getData();

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

        $level_data = $this->admin_tb_model->getAdminLevelMap();
        $data['level_data'] = $this->common->genJqgridOption($level_data, false);
       
        $changed_pw_data = $this->admin_tb_model->getChangedPWMap();
        $data['changed_pw_data'] = $this->common->genJqgridOption($changed_pw_data, false);
 
		$this->_view('system/manager', $data);
    }



	public function manager_detail($id=0) {

		$this->load->model('admin_tb_model');

        $row = array();
		$id = intval($id);

        if($id > 0) {
            $mode = 'update'; 
            $row = $this->admin_tb_model->get($id)->getData();
        
        }else {

            // SET 초기화 및 기본값 
            $mode = 'insert'; 
            $fields = $this->admin_tb_model->getFields();
            foreach($fields as $f) {
                if($f == 'a_ip_filter') {
                    $row[$f] = 'NO'; 
                } else if($f == 'a_ip_filter') {
                    $row[$f] = '1'; 
                } else {
                    $row[$f] = ''; 
                }
            }
        }

        $data = array();
        $data['mode'] = $mode;
        $data['row'] = $row;
        $data['level_data'] = $this->admin_tb_model->getAdminLevelMap();


		$this->_view('system/manager_detail', $data);
    }



    public function manager_process() {
 
		$this->load->model('admin_tb_model');
		$this->load->library('password');

        $request = $this->input->post();

        $sess = array();
        $log_array = array();

        // 삭제 & 업데이트 시 기존데이터 검증
        $admin = array();
        if($request['mode'] == 'update' || $request['mode'] == 'delete') {
            if( ! $this->admin_tb_model->get($request['a_id'])->isSuccess()) {
                $log_array['msg'] = 'INVALID KEY.';
                $this->common->alert($log_array['msg']);
                $this->common->locationhref($rtn_url);
                $this->common->write_history_log($sess, $request['mode'].' - FAIL', $request['a_id'], $log_array, 'admin_tb');
                return;
            }
            $admin = $this->admin_tb_model->getData();
            $log_array['prev_data'] = $admin;
            $log_array['prev_data']['a_passwd'] = '[SECURE_DELETED]';
        }
        

        //echo print_r($request).PHP_EOL;

        $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/system/manager';
        
        $field_list = $this->admin_tb_model->getFields();
        $data_params = array();
        foreach($field_list as $key) {
			if(array_key_exists($key, $request)) {

                if($key == 'a_passwd') {
                    if(strlen($request[$key]) > 0) {
                        $passwd = $this->password->genPassword($request[$key]);
                        
                        if( ! isset($admin['a_passwd']) || $admin['a_passwd'] != $passwd) {
                            $data_params[$key] = $passwd; 
                        }
                    }
                    continue;
                }
				$data_params[$key] = $request[$key];
				continue;
			}

			if(array_key_exists($key.'_date', $request) && array_key_exists($key.'_time', $request)) {
				$data_params[$key] = $request[$key.'_date'].' '.$request[$key.'_time'];
			}
		}



        switch($request['mode']) {

            case 'delete':
                $outmember_pw = 'outmember123!';
                $outmember_passwd = $this->password->genPassword($outmember_pw);
                
                $data_params = array();
                if(substr($admin['a_firstname'], 0, 8) !== '[Expire]') {
                    $data_params['a_firstname'] = '[Expire]'.$admin['a_firstname'];
                }
                $data_params['a_level'] = 1;
                $data_params['a_permission'] = serialize(array());
                $data_params['a_updated_at'] = date('Y-m-d H:i:s');
                $data_params['a_passwd'] = $outmember_passwd;
                
                $log_array['update_data'] = $data_params;
                if( ! $this->admin_tb_model->doUpdate($admin['a_id'], $data_params)->isSuccess()) {
                    $log_array['msg'] = $this->admin_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    $this->common->write_history_log($sess, $request['mode'].' - FAIL', $request['a_id'], $log_array, 'admin_tb');
                    return;
                }
                $this->common->write_history_log($sess, 'EXPIRE', $request['a_id'], $log_array, 'admin_tb');
                $this->common->locationhref($rtn_url);
                return;

                break;

            case 'update':

                $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/system/manager_detail/'.$request['a_id'];
                $log_array['params'] = $data_params;
                if( ! $this->admin_tb_model->doUpdate($request['a_id'], $data_params)->isSuccess()) {
                    $log_array['msg'] = $this->admin_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    $this->common->write_history_log($sess, $request['mode'].' - FAIL', $request['a_id'], $log_array, 'admin_tb');
                    return;
                }
                $this->common->write_history_log($sess, 'UPDATE', $request['a_id'], $log_array, 'admin_tb');

                // 본인 정보를 수정했을 때, 세션을 갱신한다.
                if($sess['id'] == $request['a_id']) {
                    
                    $exist_data = $this->session->all_userdata();
                    $admin = $this->admin_tb_model->get($request['a_id'])->getData();

                    // 아래 세션 변경 시 main/login_action 도 같이 수정 
                    $admin_userdata = array(
                        'id'                => $admin['a_id'],
                        'admin_access_id'   => $admin['a_admin_access_id'],
                        'login_id'          => $admin['a_loginid'],
                        'name'              => $admin['a_firstname'].' '.$admin['a_lastname'],
                        'level'             => $admin['a_level'],
                        'permission'        => unserialize($admin['a_permission']),
                        'ip'                => $_SERVER['REMOTE_ADDR'],
                    );
                    $exist_data['admin'] = $admin_userdata;
                    $this->session->set_userdata($exist_data);
                    $this->common->alert($admin['a_loginid'].' 계정의 세션정보(권한 등)가 갱신되었습니다.');
                }

                break;
            
            case 'insert':
                if( ! isset($data_params['a_passwd'])) {
                    $log_array['msg'] = '필수 항목이 누락되었습니다.'; 
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    $this->common->write_history_log($sess, $request['mode'].' - FAIL', 0, $log_array, 'admin_tb');
                    return;
                }
			
                $data_params['a_created_at'] = date('Y-m-d H:i:s');
                unset($data_params['a_id']);
                //echo print_r($data_params).PHP_EOL; exit; 

                $log_array['params'] = $data_params;
                if( ! $this->admin_tb_model->doInsert($data_params)->isSuccess()) {
                    $log_array['msg'] = $this->admin_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    $this->common->write_history_log($sess, $request['mode'].' - FAIL', 0, $log_array, 'admin_tb');
                    return;
                }
				$act_key = $this->admin_tb_model->getData();
                $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/system/manager_detail/'.$act_key;

                $this->common->write_history_log($sess, 'INSERT', $act_key, $log_array, 'admin_tb');
                break;
        }

        $this->common->locationhref($rtn_url);
    } 




    public function setting() {

        $data = array();

        $data['tabs'] = array(
            /*
            'type' => array(
                'title' => 'Assets Type',
                'ico'   => 'fa-box-alt',
            ),
            */
            'ips' => array(
                'title' => 'Allow IPs',
                'ico'   => 'fa-users-cog',
            ),
            /*
            'currency' => array(
                'title' => 'Currency',
                'ico'   => 'fa-badge-dollar',
            ),
            */
            'threshold' => array(
                'title' => 'Threshold',
                'ico'   => 'fa-monitor-heart-rate',
            ),
        );

    
        $data['fields'] = $this->admin_setting_tb_model->getFields();
        $data['as_data'] = $this->admin_setting_tb_model->get_setting_data();
        $data['currency_info'] = $this->common->get_currency_info_file();

        //echo print_r($this->_ASSETS_TYPE); exit;
        //$data['assets_type'] = (sizeof($this->_ASSETS_TYPE) > 0) ? implode(',', $this->_ASSETS_TYPE) : '';
		$this->_view('system/setting', $data);
    }



    public function manage_setting() {

        $req = $this->input->post();

        if ( ! @$req['key']) {
            $this->common->alert(getAlertMsg('INVALID_SUBMIT'));
            $this->common->historyback();
            return; 
        }       

        $as_data = $this->admin_setting_tb_business->get_setting_data();


        if ( ! is_array($as_data) ) {
            $fields = $this->admin_setting_tb_model->getFields();
            $params = array(
                    'as_order_report_point1' => 50,
                    'as_order_report_point2' => 100,
                    'as_created_at'          => date('Y-m-d H:i:s')
                    );      
            $params[$req['key']] = $req[$req['key']]; 
            $id = $this->admin_setting_tb_model->doInsert($params)->getData();
            //echo $this->admin_setting_tb_model->getErrorMsg();
        } else {

            $params = array();

            switch ($req['key']) {

                case 'as_assets_type':
                    $assets_type = trim($req['as_assets_type']);
                    $assets_type = explode(',', $assets_type);
                    $params[$req['key']] = serialize($assets_type);
                    break;

                case 'as_currency' :    
                    $currency_data = array(
                        CURRENCY_KEY   => array(
                            'rate'  => str_replace('_', 0, $req[$req['key']]) * 1,
                            'time'  => date('Y-m-d H:i:s')
                        )
                    );
                    $params['as_currency_info'] = serialize($currency_data);
                    break;

                default :
                    $params[$req['key']] = $req[$req['key']];
            }                   
            $this->admin_setting_tb_model->doUpdate(1, $params)->getData();         
        }

        $hash = '';                     
        if (@$req['tab'] && ! empty($req['tab'])) {
            $hash = '#'.$req['tab'];
        }                           

        $this->common->locationhref("/admin/system/setting${hash}");
        return;                         
    }                               




    public function history() {
		$this->load->model('history_tb_model');
		$sess = $this->session->userdata('admin');
		$req = $this->input->post();

        $data = array();

		if(isset($req['mode']) && $req['mode'] == 'list') {
			// ajax reqeust. ==> grid list data 

            $out_data = array();
            $params = array();
            $extras = array();


            $fields = array('h_loginid', 'h_name', 'h_ip'. 'h_act_mode');
            $params = $this->common->transDataTableFiltersToParams($req, $fields);
            $extras = $this->common->transDataTableFiltersToExtras($req);

            $extras['fields'] = array('h_id','h_loginid','h_name','h_ip','h_act_table','h_act_mode','h_act_key','h_created_at');
            $count = $this->history_tb_model->getCount($params)->getData();
            $rows = $this->history_tb_model->getList($params, $extras)->getData();

            $data = array();
            foreach($rows as $k=>$r){
                //$r['h_serialize'] = json_encode(unserialize($r['h_serialize']));
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
		
		$this->_view('system/history', $data);
	}


    public function ajax_get_history() {
    
        $req = $this->input->post();
        $json_data = array(
            'is_success' => FALSE,
            'msg'       => ''
        );

        if( ! isset($req['id']) || ! $this->input->is_ajax_request() ) {
            $json_data['msg'] = getAlertMsg('INVALID_SUBMIT');    
            echo json_encode($json_data);
            return;
        }

		$this->load->model(array(
            'history_tb_model'
        ));
        
        $data = $this->history_tb_model->get($req['id'])->getData();
        if(sizeof($data) < 1) {
            $json_data['msg'] = getAlertMsg('INVALID_SUBMIT');    
             echo json_encode($json_data);
            return;
        }

        $json_data['is_success'] = TRUE;
        $json_data['msg'] = $this->load->view('/admin/default_template/system/history_detail_template.php', $data, true);
        echo json_encode($json_data);
        return;

    }


	public function history_detail($id=0) {
		$this->load->model('history_tb_model');

		$id = intval($id);

		$assign = array();
		$assign['mode'] = 'insert';
		$assign['pk'] = 'h_id';
		
		if($id > 0) {
			$this->history_tb_model->get($id);
			if($this->history_tb_model->isSuccess() == false) {
				$this->common->alert('pk '.$id.' is empty.');
				$this->common->locationhref('/'.SHOP_INFO_ADMIN_DIR.'/system/history?keep=yes');
				return;
			}
			$assign['mode'] = 'update';
			$values = $this->history_tb_model->getData();
			foreach($values as $field => &$value) {
				switch($field) {
					// todo. 디폴트값 설정은 여기서
					/*
					case 'g_status' : 
						$value = form_dropdown('dr_groups[]', $user_group_sel, $selected_groups, "multiple style='height:200px;width:215px;' required");
						break;
					*/
					default : 
						$value = htmlspecialchars($value);
				}
			}
			$assign['values'] = $values;
		} else {
			// insert
			$fields = $this->history_tb_model->getFields();
			$values = array();
			foreach($fields as $field) {
				switch($field) {
					// todo. 디폴트값 설정은 여기서
					/*
					case 'g_created_at' : 
						$values[$field] = date('Y-m-d H:i:s');
						break;
					*/
					default : 
						$values[$field] = '';
				}
			}
			$assign['values'] = $values;
		}

		$this->_view('system/history_detail', $assign);
	}



    public function type() {

        $data = array();

		$this->load->model('assets_type_tb_model');
        $req = $this->input->post();
        $data = array();

        if(isset($req['mode']) && $req['mode'] == 'list') {

            $out_data = array();
            $params = array();
            $extras = array();

            //echo print_r($req);
            $fields = array('at_name', 'at_description'); 
            $params = $this->common->transDataTableFiltersToParams($req, $fields);
            $extras = $this->common->transDataTableFiltersToExtras($req);
            //echo print_r($params); exit;

            $count = $this->assets_type_tb_model->getCount($params)->getData();
            $rows = $this->assets_type_tb_model->getList($params, $extras)->getData();

            $data = array();
            foreach($rows as $k=>$r){

                $r['at_icon'] = '<i class="fal '.$r['at_icon'].'">&nbsp;'.$r['at_icon'];
                $r['at_color'] = '<span style="font-weight:bold; color:'.$r['at_color'].'">'.$r['at_color'].'</span>';
                $r['at_description'] = nl2br(trim($r['at_description'])); 

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

        $active_data = $this->assets_type_tb_model->getIsActiveMap();
        $data['active_data'] = $this->common->genJqgridOption($active_data, false);

		$this->_view('system/type', $data);
    }



    public function type_detail($id=0) {
		$this->load->model('assets_type_tb_model');

        $row = array();
		$id = intval($id);

        if($id > 0) {
            $mode = 'update'; 
            $row = $this->assets_type_tb_model->get($id)->getData();
        
        }else {

            // SET 초기화 및 기본값 
            $mode = 'insert'; 
            $fields = $this->assets_type_tb_model->getFields();
            foreach($fields as $f) {
                $row[$f] = ''; 
            }
        }

        $data = array();
        $data['mode'] = $mode;
        $data['row'] = $row;
        $data['active_data'] = $this->assets_type_tb_model->getIsActiveMap();

		$this->_view('system/type_detail', $data);
    }




    public function type_process() {

		$this->load->model('assets_type_tb_model');
        $req = $this->input->post();
        if($this->input->is_ajax_request()) {
            $req['request'] = 'ajax';
        }

        $sess = array();
        $log_array = array();
        $row_data = array();

        //echo print_r($req).PHP_EOL;

        $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/system/type';
        
        $field_list = $this->assets_type_tb_model->getFields();
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
            if( ! $this->assets_type_tb_model->get($req['at_id'])->isSuccess()) {
                $log_array['msg'] = getAlertMsg('INVALID_SUBMIT');
                $this->common->alert($log_array['msg']);
                $this->common->locationhref($rtn_url);
                $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['at_id'], $log_array, 'assets_type_tb');
                return;
            }
            $row_data = $this->assets_type_tb_model->getData();
            $log_array['prev_data'] = $row_data;
        }
        

        //echo print_r($data_params).PHP_EOL; exit;

        switch($req['mode']) {

            case 'delete':
                
                $data_params = array();
                $data_params['at_is_active'] = 'NO';
                $log_array['params'] = $data_params;
                if( ! $this->assets_type_tb_model->doUpdate($row_data['at_id'], $data_params)->isSuccess()) {
                    $log_array['msg'] = $this->assets_type_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['at_id'], $log_array, 'assets_type_tb');
                    return;
                }
                $this->common->write_history_log($sess, 'EXPIRE', $req['at_id'], $log_array, 'assets_type_tb');
                $this->common->locationhref($rtn_url);
                return;

                break;

            case 'update':

                $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/system/type_detail/'.$req['at_id'];
                $log_array['params'] = $data_params;
                if( ! $this->assets_type_tb_model->doUpdate($req['at_id'], $data_params)->isSuccess()) {
                    $log_array['msg'] = $this->assets_type_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['at_id'], $log_array, 'assets_type_tb');
                    return;
                }
                $this->common->write_history_log($sess, 'UPDATE', $req['at_id'], $log_array, 'assets_type_tb');
                break;
            
            case 'insert':
                if( ! isset($data_params['at_name'])) {
                    $log_array['msg'] = getAlertMsg('REQUIRED_VALUES'); 
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    return;
                }
			
                $data_params['at_created_at'] = date('Y-m-d H:i:s');
                unset($data_params['at_id']);
                //echo print_r($data_params).PHP_EOL; exit; 

                $log_array['params'] = $data_params;
                if( ! $this->assets_type_tb_model->doInsert($data_params)->isSuccess()) {
                    $log_array['msg'] = $this->assets_type_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    return;
                }
				$act_key = $this->assets_type_tb_model->getData();
                $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/system/type_detail/'.$act_key;
                $this->common->write_history_log($sess, 'INSERT', $act_key, $log_array, 'assets_type_tb');
                break;
        }

        $this->common->locationhref($rtn_url);
    }


    public function status() {

        $data = array();

		$this->load->model('status_tb_model');
        $req = $this->input->post();
        $data = array();

        if(isset($req['mode']) && $req['mode'] == 'list') {

            $out_data = array();
            $params = array();
            $extras = array();

            //echo print_r($req);
            $fields = array('s_name', 's_code', 's_description'); 
            $params = $this->common->transDataTableFiltersToParams($req, $fields);
            $extras = $this->common->transDataTableFiltersToExtras($req);
            //echo print_r($params); exit;

            $count = $this->status_tb_model->getCount($params)->getData();
            $rows = $this->status_tb_model->getList($params, $extras)->getData();

            $data = array();
            foreach($rows as $k=>$r){

                $r['s_color_code'] = '<span style="font-weight:bold; color:'.$r['s_color_code'].'">'.$r['s_color_code'].'</span>';

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

        $active_data = $this->status_tb_model->getIsActiveMap();
        $nav_data = $this->status_tb_model->getShowNavMap();
        
        $data['active_data'] = $this->common->genJqgridOption($active_data, false);
        $data['nav_data'] = $this->common->genJqgridOption($nav_data, false);

        //$data['select_active'] = getSelect($active_data, 's_is_active', $row['s_is_active']);
        //$data['select_nav'] = getSelect($nav_data, 's_show_nav', $row['s_show_nav']);

		$this->_view('system/status', $data);

    }



    public function status_detail($id=0) {

		$this->load->model('status_tb_model');

        $row = array();
		$id = intval($id);

        if($id > 0) {
            $mode = 'update'; 
            $row = $this->status_tb_model->get($id)->getData();
        
        }else {

            // SET 초기화 및 기본값 
            $mode = 'insert'; 
            $fields = $this->status_tb_model->getFields();
            foreach($fields as $f) {
                $row[$f] = ''; 
            }
        }

        $data = array();
        $data['mode'] = $mode;
        $data['row'] = $row;

        $active_data = $this->status_tb_model->getIsActiveMap();
        $nav_data = $this->status_tb_model->getShowNavMap();

        $data['select_active'] = getSelect($active_data, 's_is_active', $row['s_is_active']);
        $data['select_nav'] = getSelect($nav_data, 's_show_nav', $row['s_show_nav']);

		$this->_view('system/status_detail', $data);
    }


    public function status_process() {

		$this->load->model('status_tb_model');
        $req = $this->input->post();

        $sess = array();
        $log_array = array();
        $row_data = array();

        //echo print_r($req).PHP_EOL;

        $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/system/status';
        
        $field_list = $this->status_tb_model->getFields();
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
            if( ! $this->status_tb_model->get($req['s_id'])->isSuccess()) {
                $log_array['msg'] = getAlertMsg('IsALID_SUBMIT');
                $this->common->alert($log_array['msg']);
                $this->common->locationhref($rtn_url);
                $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['s_id'], $log_array, 'status_tb');
                return;
            }
            $row_data = $this->status_tb_model->getData();
            $log_array['prev_data'] = $row_data;
        }
        

        //echo print_r($data_params).PHP_EOL; exit;

        switch($req['mode']) {

            case 'delete':
                
                if( ! $this->status_tb_model->doDelete($row_data['s_id'])->isSuccess()) {
                    $log_array['msg'] = $this->status_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['s_id'], $log_array, 'status_tb');
                    return;
                }
                $log_array['del_msg'] = $req['del_msg'];
                $this->common->write_history_log($sess, 'DELETE', $req['s_id'], $log_array, 'status_tb');
                $this->common->locationhref($rtn_url);
                return;

                break;

            case 'update':

                $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/system/status_detail/'.$req['s_id'];

                $log_array['params'] = $data_params;
                if( ! $this->status_tb_model->doUpdate($req['s_id'], $data_params)->isSuccess()) {
                    $log_array['msg'] = $this->status_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['s_id'], $log_array, 'status_tb');
                    return;
                }
                $this->common->write_history_log($sess, 'UPDATE', $req['s_id'], $log_array, 'status_tb');
                break;
            
            case 'insert':
                if( ! isset($data_params['s_name']) || ! isset($data_params['s_code'])) {
                    $log_array['msg'] = getAlertMsg('REQUIRED_VALUES'); 
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    return;
                }
			
                $data_params['s_created_at'] = date('Y-m-d H:i:s');
                unset($data_params['s_id']);
                //echo print_r($data_params).PHP_EOL; exit; 

                $log_array['params'] = $data_params;
                if( ! $this->status_tb_model->doInsert($data_params)->isSuccess()) {
                    $log_array['msg'] = $this->status_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    return;
                }
				$act_key = $this->status_tb_model->getData();
                $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/system/status_detail/'.$act_key;
                $this->common->write_history_log($sess, 'INSERT', $act_key, $log_array, 'status_tb');
                break;
        }

        $this->common->locationhref($rtn_url);
    }



    public function ajax_get_manager() {

		$this->load->model('admin_tb');

        $json_data = array(
            'is_success' => FALSE,
            'msg'       => ''
        );
        $req = $this->input->post();

        if( ! isset($req['a_id']) || $req['a_id'] < 1) {
            $json_data['msg'] = getAlertMsg('INVALID_SUBMIT');    
             echo json_encode($json_data);
            return;
        }

        $params = array();
        $params['=']['a_id'] = $req['a_id'];
        $extras = array();
        $extras['fields'] = array('a_id', 'a_firstname', 'a_lastname', 'a_email');

        $data = $this->admin_tb_model->getList($params, $extras)->getData();
        if(sizeof($data) < 0) {
            $json_data['msg'] = getAlertMsg('INVALID_SUBMIT');    
        }else {
            $json_data['data'] = array_shift($data);  
            $json_data['is_success'] = true;
        }
        echo json_encode($json_data);
        return;
    }



    public function ipclass() {

        $data = array();

		$this->load->model('ip_class_tb_model');
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

            $fields = array('ipc_category', 'ipc_name', 'ipc_cidr'); 
            $params = $this->common->transDataTableFiltersToParams($req, $fields);
            $extras = $this->common->transDataTableFiltersToExtras($req);

            if( isset($req['ipc_type']) && strlen($req['ipc_type']) > 0 ) {
                $params['=']['ipc_type'] = $req['ipc_type'];
            }

            $count = $this->ip_class_tb_model->getCount($params)->getData();
            $rows = $this->ip_class_tb_model->getList($params, $extras)->getData();

            $data = array();
            foreach($rows as $k=>$r){

                $link = '/'.SHOP_INFO_ADMIN_DIR.'/system/iplist/'.$r['ipc_id'];
                $icon = '<i class="fal fa-link mr-1"></i>';
                //$r['ipc_cidr'] = '<span class="badge border border-danger text-danger">'.$r['ipc_cidr'].'</span>';
                $r['ipc_cidr'] = '<a href="'.$link.'" class="btn btn-xs btn-danger waves-effect waves-themed">'.$icon.$r['ipc_cidr'].'</a>';
                $r['ipc_location_id'] = $location_data[$r['ipc_location_id']];

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

		$this->_view('system/ipclass', $data);
    }




    //public function ipclass_detail($class_type='', $id=0, $mode='insert') {
    public function ipclass_detail($id=0, $mode='insert') {

        $this->load->model('ip_class_tb_model');

        $this->load->business(array(
            'location_tb_business',
        ));

        $row = array();
		$id = intval($id);

        if($id > 0) {
            if($mode != 'clone') {
                $mode = 'update';
            }
            $row = $this->ip_class_tb_model->get($id)->getData();
        
        }else {

            // SET 초기화 및 기본값 
            $mode = 'insert'; 
            $fields = $this->ip_class_tb_model->getFields();
            foreach($fields as $f) {
                $row[$f] = ''; 
            }
        }

        $data = array();
        $data['mode'] = $mode;
        $data['row'] = $row;

        $location_data = $this->location_tb_business->getNameMap();
        $data['select_location'] = getSearchSelect($location_data, 'ipc_location_id', $row['ipc_location_id'], 'required');


        $type_data = $this->ip_class_tb_model->getTypeMap();
        $data['select_type'] = getSearchSelect($type_data, 'ipc_type', $row['ipc_type'], 'required');

        $category_data = $this->ip_class_tb_model->getCategoryMap();
        $data['select_category'] = getSearchSelect($category_data, 'ipc_category', $row['ipc_category'], 'required');

        $location_code = $this->location_tb_business->getCodeMap();
        $data['location_map'] = json_encode($location_code);
        $data['l_code'] = $location_code[$row['ipc_location_id']];

		$this->_view('system/ipclass_detail', $data);
    }


    public function ipclass_process() {

		$this->load->model('ip_class_tb_model');
        $req = $this->input->post();

        $sess = array();
        $log_array = array();
        $row_data = array();

        //echo print_r($req).PHP_EOL;

        $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/system/ipclass';
        
        $field_list = $this->ip_class_tb_model->getFields();
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
            if( ! $this->ip_class_tb_model->get(array('ipc_id' => $req['ipc_id']))->isSuccess()) {
                $log_array['msg'] = getAlertMsg('INVALID_SUBMIT');
                $this->common->alert($log_array['msg']);
                $this->common->locationhref($rtn_url);
                $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['ipc_id'], $log_array, 'ip_class_tb');
                return;
            }
            $row_data = $this->ip_class_tb_model->getData();
            $log_array['prev_data'] = $row_data;
        }
        

        //echo print_r($data_params).PHP_EOL; exit;

        switch($req['mode']) {

            case 'delete':
                
                $data_params = array();
                $log_array['params'] = $data_params;
                if( ! $this->ip_class_tb_model->doUpdate($row_data['ipc_id'], $data_params)->isSuccess()) {
                    $log_array['msg'] = $this->ip_class_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['ipc_id'], $log_array, 'ip_class_tb');
                    return;
                }
                $this->common->write_history_log($sess, 'DELETE', $req['ipc_id'], $log_array, 'ip_class_tb');
                $this->common->locationhref($rtn_url);
                return;

                break;

            case 'update':

                $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/system/ipclass_detail/'.$req['ipc_id'];

                $log_array['params'] = $data_params;
                if( ! $this->ip_class_tb_model->doUpdate($req['ipc_id'], $data_params)->isSuccess()) {
                    $log_array['msg'] = $this->ip_class_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['ipc_id'], $log_array, 'ip_class_tb');
                    return;
                }
                $this->common->write_history_log($sess, 'UPDATE', $req['ipc_id'], $log_array, 'ip_class_tb');
                break;
            
            case 'insert':
            case 'clone':
                if( ! isset($data_params['ipc_name']) || ! isset($data_params['ipc_cidr'])) {
                    $log_array['msg'] = getAlertMsg('REQUIRED_VALUES'); 
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    return;
                }

                $data_params['ipc_created_at'] = date('Y-m-d H:i:s');
                unset($data_params['ipc_id']);
                //echo print_r($data_params).PHP_EOL; exit; 

                $log_array['params'] = $data_params;
                if( ! $this->ip_class_tb_model->doInsert($data_params)->isSuccess()) {
                    $log_array['msg'] = $this->ip_class_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    return;
                }
				$act_key = $this->ip_class_tb_model->getData();
                $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/system/ipclass_detail/'.$act_key;
                $this->common->write_history_log($sess, 'INSERT', $act_key, $log_array, 'ip_class_tb');
                break;
        }

        $this->common->locationhref($rtn_url);

    }



    public function ajax_valid_cidr() {

        $json_data = array(
            'is_success' => FALSE,
            'msg'       => ''
        );
        $req = $this->input->post();
        
        if( ! isset($req['cidr']) || strlen($req['cidr']) < 1 ) {
            $json_data['msg'] = getAlertMsg('INVALID_SUBMIT');    
            echo json_encode($json_data);
            return; 
        }

        $this->load->library('CIDR');   // 대문자

        list ($ip, $mask) = explode('/',$req['cidr']);
        if($this->cidr->validIP($ip) !== $ip || strlen($mask) < 1) {
            $json_data['msg'] = 'Invalid IP Address.';
            echo json_encode($json_data);
            return;
        }

        $res = $this->cidr->cidrToRange($req['cidr']); 
        if(sizeof($res) !== 2) {
            $json_data['msg'] = getAlertMsg('INVALID_SUBMIT');    
            echo json_encode($json_data);
            return; 
        }
        $json_data['is_success'] = TRUE;
        $json_data['msg'] = $res;

        echo json_encode($json_data);
        return; 
    }



    
    public function iplist($id=0) {

		$id = intval($id);
        if( $id < 1 ) {
            $this->common->alert(getAlertMsg('INVALID_SUBMIT'));
            $this->common->historyback();
            return;
        }

        $data = array();
		$this->load->model(array(
            'ip_tb_model',
            'people_tb_model',
            'people_ip_map_tb_model',
            'ip_class_tb_model',
            'assets_model_tb',
            'vmservice_tb',
            'vmservice_ip_map_tb',
        ));
        $this->load->business(array(
            'location_tb_business',
            'ip_class_tb_business',
            'assets_model_tb_business',
        ));
        $this->load->library('CIDR');   // 대문자

        $class_data = $this->ip_class_tb_model->get($id)->getData();
        $req = $this->input->post();
        //echo print_r($req); exit;
        // TODO. @req['search']['value']

        if(isset($req['mode']) && $req['mode'] == 'list') {
            switch($req['type']) {
                // people_ip_map : LOCAL IP
                // Edit : /assets/pim_template
                case 'pim':
                    $data = $this->_ajax_pim_list($class_data, $req);
                    break;

                // assets_ip_map : IDC IP, VMWARE IP
                // Edit : /assets/aim_template
                case 'aim':
                case 'idrac':
                    $data = $this->_ajax_aim_list($class_data, $req);
                    break;

                // vmservice_ip_map : IDC IP, PUB/PRIV IP
                // Edit : /assets/vim_template or /assets/dim_template
                case 'vim':
                    $data = $this->_ajax_vim_list($class_data, $req);
                    break;

            }
            $json_data = new stdClass;
            $json_data->draw = $req['draw'];
            $json_data->recordsTotal = sizeof($data);
            $json_data->recordsFiltered = sizeof($data);
            $json_data->data = $data;
            $json_data->ipc_data = $class_data;

            //echo print_r($json_data); exit;
            echo json_encode($json_data);
            return;

        }

        $data['id'] = $id;
        $location_data = $this->location_tb_business->getNameMap();
        $class_data['ipc_location_id'] = $location_data[$class_data['ipc_location_id']];
        $data['class_data'] = $class_data;


        $ipc_data = $this->ip_class_tb_business->getLocationGroup();
        $data['select_cidr'] = getGroupSearchSelect($ipc_data, 'ipc_id', $class_data['ipc_id'], '');


		$this->_view('system/iplist', $data);
    }


    public function ipsearch() {

        $req = $this->input->post(); 
        //echo print_r($req);

        $data = array();
		$this->load->model(array(
            'ip_tb_model',
            'people_tb_model',
            'people_ip_map_tb_model',
            'ip_class_tb_model',
            'assets_model_tb',
            'assets_ip_map_tb_model',
            'vmservice_tb',
            'vmservice_ip_map_tb',
        ));

        $type_data = array('server', 'service', 'people', 'direct');

        $rows = array();
        foreach($type_data as $type) {
            $tmp = array();
            $tmp = $this->_getTypeData($type, $req['search_value']);

            if( sizeof($tmp) > 0 )  {
                $rows = array_merge($rows, $tmp);
            }
        }
        //echo print_r($rows);

        if(sizeof($rows) < 0) {
            return;
        }
        //echo print_r($rows); exit;

        $ipc_ids = array_keys($this->common->getDataByPK($rows, 'ip_class_id'));
        $params = array();
        $params['in']['ipc_id'] = $ipc_ids;
        $extras = array();
        $ipc_data = $this->ip_class_tb_model->getList($params, $extras)->getData();
        $ipc_data = $this->common->getDataByPK($ipc_data, 'ipc_id');

        $data = array();
        foreach($rows as $k=>$r){

            $ip_class = '<span class="btn btn-xs btn-secondary mr-1 mb-1 waves-effect waves-themed">';
            $ip_class .= $ipc_data[$r['ip_class_id']]['ipc_name'];
            $ip_class .= '</span>';
            $ip_class .= '<span class="btn btn-xs btn-info waves-effect waves-themed mr-1 mb-1">';
            $ip_class .= $ipc_data[$r['ip_class_id']]['ipc_cidr'];
            $ip_class .= '</span>';
            

            $am_name = '';
            if( isset($r['am_id']) ) {
                $assets_link = '/admin/assets/detail/servers/'.$r['am_id'].'#tab_assets';
                $am_name = nameToLinkHtml($assets_link, $r['am_name'], '_blank');
            }

            $vms_name = '';
            if( isset($r['am_id']) ) {
                $assets_link = '/admin/assets/detail/servers/'.$r['am_id'].'#tab_vmware';
                $vms_name = nameToLinkHtml($assets_link, $r['vms_name'], '_blank');
            }
            
            $pp_name = '';
            if( isset($r['pp_id']) ) {
                $pp_name = $r['pp_name'];
            }

            $data[] = array(
                "ip_class_id"           => $ip_class,
                "ip_id"                 => $r['ip_id'],
                "ip_address"            => $r['ip_address'],
                "ip_class_type"         => $r['ip_class_type'],
                "ip_class_category"     => $r['ip_class_category'],
                "ip_memo"               => $r['ip_memo'],
                "am_name"               => $am_name, 
                "am_models_name"        => isset($r['am_mdoels_name']) ? $r['am_models_name'] : '',
                "am_vmware_name"        => isset($r['am_vmware_name']) ? $r['am_vmware_name'] : '',
                "am_serial_no"          => isset($r['am_serial_no']) ? $r['am_serial_no'] : '',
                "am_tags"               => isset($r['am_tags']) ? $r['am_tags'] : '',
                "am_rack_code"          => isset($r['am_rack_code']) ? $r['am_rack_code'] : '',
                "am_memo"               => isset($r['am_memo']) ? $r['am_memo'] : '',
                "vms_name"              => $vms_name,
                "vms_memo"              => isset($r['vms_memo']) ? $r['vms_memo'] : '',
                "pp_name"               => $pp_name,
                "pp_email"              => isset($r['pp_email']) ? $r['pp_email'] : '',
                "pp_memo"               => isset($r['pp_memo']) ? $r['pp_memo'] : '',
            );
        }

        $json_data = new stdClass;
        $json_data->draw = $req['draw'];
        $json_data->recordsTotal = sizeof($data);
        $json_data->recordsFiltered = sizeof($data);
        $json_data->data = $data;

        echo json_encode($json_data);
        return;

    }



    private function _getTypeData($type, $search_text) {

        
		$this->load->library('CIDR');

        $params = array();
        $extras = array();

        switch($type) {
            case 'server':
                $search_fields = array(
                    'am_name', 'am_models_name', 'am_vmware_name', 'am_serial_no', 'am_tags', 'am_rack_code', 'am_memo',
                    'ip_memo',
                );

                $params['join']['assets_ip_map_tb'] = 'aim_ip_id = ip_id'; 
                $params['join']['assets_model_tb'] = 'aim_assets_model_id = am_id';
                $extras['fields'] = array('ip_tb.*', 'am_id');
                break;

            case 'service':
                $search_fields = array(
                    'am_name', 'am_models_name', 'am_vmware_name', 'am_serial_no', 'am_tags', 'am_rack_code', 'am_memo',
                    'vms_name', 'vms_memo', 'ip_memo'
                );

                $params['join']['vmservice_ip_map_tb'] = 'vim_ip_id = ip_id'; 
                $params['join']['vmservice_tb'] = 'vim_vmservice_id = vms_id';
                $params['join']['assets_model_tb'] = 'vms_assets_model_id = am_id';
                $extras['fields'] = array(
                    'ip_tb.*', 'vms_id', 'am_id', 'am_models_name', 'am_name', 'am_vmware_name', 'am_serial_no', 'am_rack_code', 'am_tags',
                );
                break;

            case 'people':                
                $search_fields = array('pp_name', 'pp_email', 'pp_dept', 'pp_memo', 'ip_memo');
                
                $params['join']['people_ip_map_tb'] = 'pim_ip_id = ip_id'; 
                $params['join']['people_tb'] = 'pim_people_id = pp_id';
                $extras['fields'] = array('ip_tb.*', 'pim_id', 'pp_id');
                break;

            case 'direct':
                $search_fields = array(
                    'am_name', 'am_models_name', 'am_vmware_name', 'am_serial_no', 'am_tags', 'am_rack_code', 'am_memo',
                    'ip_memo',
                );

                $params['join']['direct_ip_map_tb'] = 'dim_ip_id = ip_id'; 
                $params['join']['assets_model_tb'] = 'dim_assets_model_id = am_id';
                $extras['fields'] = array('ip_tb.*', 'am_id');
                break;
        }


        if( $this->cidr->validIP($search_text) !== FALSE ) {
            $params['=']['ip_address'] = $search_text;
        }else {
            $query = array();
            foreach($search_fields as $field) {
                $query[] = $field.' LIKE \'%'.$search_text.'%\'';
            }
            $str_query = implode(' OR ', $query);
            $params['raw'] = array($str_query);
        }

        $extras['fields'] = array_merge($extras['fields'], $search_fields);
        $extras['order_by'] = array('ip_address ASC');

        $data = $this->ip_tb_model->getList($params, $extras)->getData(); 
        //echo $this->ip_tb_model->getLastQuery();
        //echo print_r($data).PHP_EOL.'<br />';

        return $data;
    }
    

    public function ajax_valid_ip() {

        $this->load->business(array(
            'ip_class_tb_business',
        ));

        $req = $this->input->post();
        //echo print_r($req); exit;

        $json_data  = $this->ip_class_tb_business->checkIPinClass($req);
        //echo print_r($json_data);
        echo json_encode($json_data);
        return;
    }


    public function ajax_ip_template() {

        $req = $this->input->post();
        //echo print_r($req); exit;

		$this->load->model(array(
            'ip_tb_model',
            'ip_class_tb_model',
            'people_ip_map_tb_model',
            'assets_ip_map_tb_model',
            'vmservice_tb_model',
            'vmservice_ip_map_tb_model',
            'assets_model_tb_model',
            'direct_ip_map_tb_model'
        ));
		$this->load->business(array(
            'people_tb_business',
            'location_tb_business',
            'assets_model_tb_business'
        ));


        $json_data = array(
            'is_success' => FALSE,
            'msg'       => ''
        );

        if( ! isset($req['ip_id']) || ! isset($req['ip']) ) {
            $json_data['msg'] = getAlertMsg('INVALID_SUBMIT');    
             echo json_encode($json_data);
            return;
        }

        $row = array();
        if( strlen($req['ip_id']) > 0 ) {
            $mode = 'update';

            $row = $this->ip_tb_model->get($req['ip_id'])->getData();
            if(sizeof($row) < 0) {
                $mode = 'insert';
            }

        }else {
            $mode = 'insert';
        }

        if($mode == 'insert') {
            $fields = $this->ip_tb_model->getFields();
            foreach($fields as $f) {
                $row[$f] = ''; 
            }
            $ipc_data = $this->ip_class_tb_model->get($req['ipc_id'])->getData();
            $row['ip_class_id'] = $ipc_data['ipc_id'];
            $row['ip_class_type'] = $ipc_data['ipc_type'];
            $row['ip_class_category'] = $ipc_data['ipc_category'];
        }

        if(strlen($req['ip']) > 0) {
            $row['ip_address'] = $req['ip'];
        }

        $assign_data = array(
            'row'           => $row,
            'mode'          => $mode,
            'type'          => 'modal',
        );

        switch($req['view_type']) {

            case 'aim':

                //echo print_r($assign_data); //exit;

                //$vm_data = $row;
                $assign_data['row']['mode'] = $req['mode'];
                $assign_data['idrac']['mode'] = 'insert';

                $aim_data = array(
                    'aim_id'         => '',
                    'am_id'          => '',
                    'aim_id'         => '',
                    'am_name'        => '',
                    'am_vmware_name' => '',
                );
                if( $row['ip_id'] > 0 ) {
                    $params = array();
                    $params['join']['assets_model_tb'] = 'aim_assets_model_id = am_id';
                    $params['=']['aim_ip_id'] = $row['ip_id'];
                    $extras = array();
                    $extras['fields'] = array('aim_id', 'aim_assets_model_id', 'am_id', 'am_vmware_name', 'am_name');
                    $aim_data = $this->assets_ip_map_tb_model->getList($params, $extras)->getData();
                    $aim_data = array_shift($aim_data);

                    $res = $this->assets_model_tb_business->getAssetsIPType($aim_data['am_id']);
                    $assign_data['idrac']['mode'] = 'insert';
                    if( isset($res['IDRAC']) ) {
                        $assign_data['idrac'] = $res['IDRAC'];
                        $assign_data['idrac']['mode'] = 'update';
                    }
                }
                $assign_data['aim_data'] = $aim_data;
                //echo print_r($assign_data); exit;
                $json_data['msg'] = $this->load->view('admin/default_template/assets/aim_template.php', $assign_data, true);
                break;


            // LOCAL
            case 'pim':
                    
                $pim_data = array(
                    'pim_id'        => '',
                    'pim_people_id' => '',
                    'pp_name'  => '',
                );
                if( $row['ip_id'] > 0 ) {
                    $params = array();
                    $params['join']['people_tb'] = 'pim_people_id = pp_id';
                    $params['=']['pim_ip_id'] = $row['ip_id'];
                    $extras = array();
                    $extras['fields'] = array('pim_id', 'pim_people_id', 'pp_name');
                    $pim_data = $this->people_ip_map_tb_model->getList($params, $extras)->getData();
                    $pim_data = array_shift($pim_data);
                }
                $assign_data['pim_data'] = $pim_data;

                $json_data['msg'] = $this->load->view('admin/default_template/assets/pim_template.php', $assign_data, true);
                break;



            // IDC, PUB/PRI
            case 'vim':
            case 'alias':

                if( $row['ip_id'] > 0 ) {
                    $params = array();
                    $params['join']['vmservice_tb'] = 'vim_vmservice_id = vms_id';
                    $params['join']['ip_tb'] = 'vim_ip_id = ip_id';
                    $params['=']['vim_ip_id'] = $row['ip_id'];
                    $extras = array();
                    $extras['fields'] = array(
                        'vim_id', 'vim_ip_id', 
                        'vms_id', 'vms_assets_model_id', 'vms_alias_id', 'vms_name', 'vms_memo', 'ip_memo'
                    );
                    $vim_data = $this->vmservice_ip_map_tb_model->getList($params, $extras)->getData();
                    $vim_data = array_shift($vim_data);

                    // VMWARE IP 구하기
                    $params = array();
                    $params['=']['am_id'] = $vim_data['vms_assets_model_id'];
                    $params['=']['ip_class_category'] = 'VMWARE';
                    $params['join']['assets_ip_map_tb'] = 'aim_assets_model_id = am_id'; 
                    $params['join']['ip_tb'] = 'aim_ip_id = ip_id';
                    
                    $extras = array();
                    $extras['fields'] = array(
                        'am_id', 'am_vmware_name', 'ip_id', 'ip_address'
                    );
                    $vm_data = $this->assets_model_tb_model->getList($params, $extras)->getData();
                    $vm_data = array_shift($vm_data);
                }
                $assign_data['vim_data'] = $vim_data;
                $assign_data['vm_data'] = $vm_data;

                if($req['view_type'] == 'vim') {
                    $json_data['msg'] = $this->load->view('admin/default_template/assets/vim_template.php', $assign_data, true);
                }else {
                    $assign_data['from'] = 'iptotal';
                    $assign_data['alias'] = $this->vmservice_tb_model->get($vim_data['vms_alias_id'])->getData();

                    $json_data['msg'] = $this->load->view('admin/default_template/assets/alias_template.php', $assign_data, true);
                }
                break;


            // [IDC, PUB/PRIV] Direct
            case 'dim': 

                $dim_data = array();
                if( $row['ip_id'] > 0 ) {
                    $params = array();
                    $params['join']['ip_tb'] = 'dim_ip_id = ip_id';
                    $params['join']['assets_model_tb'] = 'dim_assets_model_id = am_id';
                    $params['=']['dim_ip_id'] = $row['ip_id'];
                    $extras = array();
                    $extras['fields'] = array(
                        'dim_id', 'am_id', 'am_name', 'am_serial_no', 'ip_id', 'ip_memo'
                    );
                    $dim_data = $this->direct_ip_map_tb_model->getList($params, $extras)->getData();
                    $dim_data = array_shift($dim_data);
                }
                $assign_data['dim_data'] = $dim_data;
                //echo print_r($assign_data); exit;
                $json_data['msg'] = $this->load->view('admin/default_template/assets/dim_template.php', $assign_data, true);
                break;


            case 'idrac':
                $aim_data = array(
                    'aim_id'        => '',
                    'am_id'         => '',
                    'am_name'       => '',
                    'ip_id'         => '',
                    'ip_address'    => '',
                );
                if( $row['ip_id'] > 0 ) {
                    // iDrac IP 구하기
                    $params = array();
                    $params['=']['ip_id'] = $row['ip_id'];
                    $params['=']['ip_class_category'] = 'IDRAC';
                    $params['join']['assets_ip_map_tb'] = 'aim_assets_model_id = am_id'; 
                    $params['join']['ip_tb'] = 'aim_ip_id = ip_id';
                    
                    $extras = array();
                    $extras['fields'] = array(
                        'am_id', 'am_name', 'ip_id', 'ip_address', 'aim_id'
                    );
                    $aim_data = $this->assets_model_tb_model->getList($params, $extras)->getData();
                    $aim_data = array_shift($aim_data);
                }
                $assign_data['aim_data'] = $aim_data;

                $json_data['msg'] = $this->load->view('admin/default_template/assets/idrac_template.php', $assign_data, true);
                break;


        }
        $json_data['is_success'] = true;
        echo json_encode($json_data);
        return;
    }


    public function local() {
        $this->ipclass('LOCAL');
    }


    public function idc() {
        $this->ipclass('IDC');
    }



    private function _ajax_pim_list($class_data, $req) {

        $data = array();

        $params = array();
        $extras = array();

        $fields = array(
            'ip_address', 'ip_memo',
            'pp_name', 'pp_login_id', 'pp_email','pp_dept'
        ); 
        $params = $this->common->transDataTableFiltersToParams($req, $fields);
        $extras = $this->common->transDataTableFiltersToExtras($req);

        // 검색 유무 구분
        $is_filter = FALSE;
        if(sizeof($params) > 0) {
            $is_filter = TRUE;
        }

        if( isset($class_data['ipc_id']) ) {
            $params['=']['ip_class_id'] = $class_data['ipc_id'];
        }

        /*
        if( isset($params['=']['pp_name']) && strlen(isset($params['=']['pp_name'])) > 0 ) {
            $params['raw'] = array(
                'pp_lastname LIKE \'%'.$params['=']['pp_name'].'%\' OR pp_firstname LIKE \'%'.$params['=']['pp_name'].'%\''
            );
            unset($params['=']['pp_name']);
        }
        */

        $params['join']['people_ip_map_tb'] = 'pim_ip_id = ip_id'; 
        $params['join']['people_tb'] = 'pim_people_id = pp_id';
        $extras['fields'] = array(
            'ip_tb.*', 'pim_id', 'pp_id', 'pp_login_id', 'pp_name', 'pp_email'
        );

        $extras['order_by'] = array('ip_address ASC');
        $ip_data = $this->ip_tb_model->getList($params, $extras)->getData(); 


        $ip_pp_data = array();
        if(sizeof($ip_data) > 0) {
            $ip_pp_data = $this->common->getDataByPK($ip_data, 'ip_address');
        }

        if($is_filter === TRUE) {
            foreach($ip_pp_data as $k=>$r){
                $data[] = $r;
            }
        }else {
            $res = $this->cidr->cidrToRange($class_data['ipc_cidr']); 

            $start = $res[0];
            $end = $res[1];

            // CIDR 범위내에 IP List 출력
            for($loop = ip2long($start); $loop <= ip2long($end); $loop++) {

                $ip = long2ip($loop);
                $tmp = array(
                    'pim_id'                => '',
                    'ip_address'            => $ip,
                    'pp_name'               => '',
                    'pp_login_id'           => '',
                    'pp_email'              => '',
                    'ip_memo'               => '',
                    'ip_id'                 => '',
                );
                if(isset($ip_pp_data[$ip])) {

                    $link = '/admin/people/employee_detail/'.$ip_pp_data[$ip]['pp_id'].'#tab_ips';
                    $pp_name = $ip_pp_data[$ip]['pp_name'];

                    $tmp['pim_id']          = $ip_pp_data[$ip]['pim_id'];
                    $tmp['ip_address']      = $ip;
                    $tmp['pp_name']         = nameToLinkHtml($link, $pp_name, '_blank');
                    $tmp['pp_login_id']     = $ip_pp_data[$ip]['pp_login_id'];
                    $tmp['pp_email']        = $ip_pp_data[$ip]['pp_email'];
                    $tmp['ip_memo']         = $ip_pp_data[$ip]['ip_memo'];
                    $tmp['ip_id']           = $ip_pp_data[$ip]['ip_id'];
                }
                $data[] = $tmp;
            }
        }
        return $data;
    }


    private function _ajax_aim_list($class_data, $req) {

        $data = array();

        $params = array();
        $extras = array();

        $fields = array('ip_address', 'ip_memo', 'am_models_name', 'am_name', 'am_vmware_name', 'am_serial_no', 'am_rack_code', 'am_tags');
        $params = $this->common->transDataTableFiltersToParams($req, $fields);
        $extras = $this->common->transDataTableFiltersToExtras($req);

        // 검색 유무 구분
        $is_filter = FALSE;
        if(sizeof($params) > 0) {
            $is_filter = TRUE;
        }

        $params['=']['ip_class_id'] = $class_data['ipc_id'];
        $params['=']['am_location_id'] = $class_data['ipc_location_id'];
        $params['join']['assets_ip_map_tb'] = 'aim_ip_id = ip_id'; 
        $params['join']['assets_model_tb'] = 'aim_assets_model_id = am_id';
        $extras['fields'] = array(
            'ip_tb.*', 'am_id', 'am_models_name', 'am_name', 'am_vmware_name', 'am_serial_no', 'am_rack_code', 'aim_id', 'am_tags'
        );
        $extras['order_by'] = array('ip_address ASC');
        $ip_data = $this->ip_tb_model->getList($params, $extras)->getData(); 


        $idrac_data = array();
        $aim_data = array();
        if(sizeof($ip_data) > 0) {

            // iDrac IP 
            $am_ids = array_keys($this->common->getDataByPK($ip_data, 'am_id'));
            $params = array();
            $params['in']['am_id'] = $am_ids;
            $params['=']['ip_class_category'] = 'IDRAC';
            $params['join']['assets_ip_map_tb'] = 'aim_assets_model_id = am_id'; 
            $params['join']['ip_tb'] = 'aim_ip_id = ip_id';
            $extras = array();
            $extras['fields'] = array(
                'am_id', 'ip_address', 'aim_id'
            );
            $idrac_data = $this->assets_model_tb_model->getList($params, $extras)->getData();
            $idrac_data = $this->common->getDataByPK($idrac_data, 'am_id');
            //echo print_r($am_data); exit;

            $aim_data = $this->common->getDataByPK($ip_data, 'ip_address');
            //echo print_r($aim_data); //exit;
        }


        if($is_filter === TRUE) {
            foreach($aim_data as $k=>$r){
                $data[] = $r;
            }

        }else {
            $res = $this->cidr->cidrToRange($class_data['ipc_cidr']); 

            $start = $res[0];
            $end = $res[1];

            // CIDR 범위내에 IP List 출력
            for($loop = ip2long($start); $loop <= ip2long($end); $loop++) {

                $ip = long2ip($loop);
                $tmp = array(
                    'aim_id'                => '',
                    'idrac_aim_id'          => '',
                    'ip_address'            => $ip,
                    'idrac_ip'              => '',
                    'am_models_name'        => '',
                    'am_name'               => '',
                    'am_models_name'        => '',
                    'am_vmware_name'        => '',
                    'am_serial_no'          => '',
                    'am_rack_code'          => '',
                    'ip_memo'               => '',
                    'ip_id'                 => '',
                );
                if(isset($aim_data[$ip])) {

                    $am_id = $aim_data[$ip]['am_id'];
                    $assets_link = '/admin/assets/detail/servers/'.$am_id.'#tab_assets';

                    $tmp['aim_id']              = $aim_data[$ip]['aim_id'];
                    $tmp['idrac_aim_id']        = $idrac_data[$am_id]['aim_id'];
                    $tmp['ip_address']          = $ip;
                    $tmp['idrac_ip']            = $idrac_data[$am_id]['ip_address'];
                    $tmp['am_models_name']      = $aim_data[$ip]['am_models_name'];
                    $tmp['am_name']             = nameToLinkHtml($assets_link, $aim_data[$ip]['am_name'], '_blank');
                    $tmp['am_models_name']      = $aim_data[$ip]['am_models_name'];
                    $tmp['am_vmware_name']      = $aim_data[$ip]['am_vmware_name'];
                    $tmp['am_serial_no']        = $aim_idata[$ip]['am_serial_no'];
                    $tmp['am_rack_code']        = $aim_data[$ip]['am_rack_code'];
                    $tmp['ip_memo']             = $aim_data[$ip]['ip_memo'];
                    $tmp['ip_id']               = $aim_data[$ip]['ip_id'];
                }
                $data[] = $tmp;
            }
        }
        return $data;
    }


    private function _ajax_vim_list($class_data, $req) {

        $data = array();
        $params = array();
        $extras = array();

        $fields = array(
            'ip_address', 'ip_memo', 'vms_name', 'vms_alias_id',
            'am_models_name', 'am_name', 'am_vmware_name', 'am_serial_no', 'am_rack_code', 'am_tags'
        ); 
        $query_params = $this->common->transDataTableFiltersToParams($req, $fields);
        $query_extras = $this->common->transDataTableFiltersToExtras($req);


        // 검색 유무 구분
        $is_filter = FALSE;
        if(sizeof($query_params) > 0) {
            $is_filter = TRUE;
        }

        $params = $query_params;
        $params['=']['ip_class_id'] = $class_data['ipc_id'];
        $params['=']['am_location_id'] = $class_data['ipc_location_id'];
        $params['join']['vmservice_ip_map_tb'] = 'vim_ip_id = ip_id'; 
        $params['join']['vmservice_tb'] = 'vim_vmservice_id = vms_id';
        $params['join']['assets_model_tb'] = 'vms_assets_model_id = am_id';

        $extras = $query_extras;
        $extras['fields'] = array(
            'ip_tb.*', 'vmservice_tb.*', 
            'am_id', 'am_models_name', 'am_name', 'am_vmware_name', 'am_serial_no', 'am_rack_code', 'am_tags',
        );
        $extras['order_by'] = array('ip_address ASC');
        $ip_data = $this->ip_tb_model->getList($params, $extras)->getData(); 
        //echo print_r($ip_data); exit;

        $vim_data = array();
        $am_data = array();
        if(sizeof($ip_data) > 0) {
            $vim_data = $this->common->getDataByPK($ip_data, 'ip_address');
            //echo print_r($vim_data); //exit;

            $am_ids = array_keys($this->common->getDataByPK($ip_data, 'vms_assets_model_id'));

            $params = array();
            $params['in']['am_id'] = $am_ids;
            $params['=']['ip_class_category'] = 'VMWARE';
            $params['join']['assets_ip_map_tb'] = 'aim_assets_model_id = am_id'; 
            $params['join']['ip_tb'] = 'aim_ip_id = ip_id';
            $extras = array();
            $extras['fields'] = array('am_id', 'ip_address');
            $am_data = $this->assets_model_tb_model->getList($params, $extras)->getData();
            $am_data = $this->common->getDataByPK($am_data, 'am_id');
            //echo print_r($am_data); //exit;
        }


        // Direct IP
        $params = $query_params;
        $params['=']['ip_class_id'] = $class_data['ipc_id'];
        $params['=']['am_location_id'] = $class_data['ipc_location_id'];
        $params['join']['direct_ip_map_tb'] = 'dim_ip_id = ip_id'; 
        $params['join']['assets_model_tb'] = 'dim_assets_model_id = am_id';

        $extras = $query_extras;
        $extras['fields'] = array(
            'ip_tb.*', 'am_id', 'am_models_name', 'am_name', 'am_vmware_name', 'dim_id'
        );
        $dim_data = $this->ip_tb_model->getList($params, $extras)->getData(); 
        $dim_data = $this->common->getDataByPK($dim_data, 'ip_address');
        //echo print_r($dim_data); //exit;

        $vim_data = array_merge($vim_data, $dim_data);
        //echo print_r($vim_data); exit;


        if($is_filter === TRUE) {
            foreach($vim_data as $k=>$r){

                $am_id = $r['am_id'];
                $r['vmware_ip'] = isset($am_data[$am_id]['ip_address']) ? $am_data[$am_id]['ip_address'] : '';

                $r['vms_id'] = isset($r['vms_id']) ? $r['vms_id'] : '';
                $r['vms_name'] = isset($r['vms_name']) ? $r['vms_name'] : '';
                $r['vms_memo'] = isset($r['vms_memo']) ? $r['vms_memo'] : '';
                $r['dim_id'] = isset($r['dim_id']) ? $r['dim_id'] : '';

                $data[] = $r;
            }

        }else {
            $res = $this->cidr->cidrToRange($class_data['ipc_cidr']); 

            $start = $res[0];
            $end = $res[1];

            // CIDR 범위내에 IP List 출력
            for($loop = ip2long($start); $loop <= ip2long($end); $loop++) {

                $ip = long2ip($loop);

                $tmp = array(
                    'ip_id'                 => '',
                    'vms_id'                => '',
                    'vms_alias_id'          => '',
                    'ip_address'            => $ip,
                    'vmware_ip'             => '',
                    'am_name'               => '',
                    'am_vmware_name'        => '',
                    'vms_name'              => '',
                    'vms_memo'              => '',
                    'ip_memo'               => '',
                    'dim_id'                => '',
                );
                if(isset($vim_data[$ip])) {
                    $am_id = $vim_data[$ip]['am_id'];

                    $type = 'vmservice';
                    if( ! isset($vim_data[$ip]['vms_name'])) {
                        $type = 'direct';
                    }

                    
                    $assets_link = '/admin/assets/detail/servers/'.$am_id.'#tab_assets';

                    $vms_name = '';
                    if($type == 'vmservice') {
                        $service_link = '/admin/assets/detail/servers/'.$am_id;
                        if($vim_data[$ip]['vms_alias_id'] == 0) {
                            $service_link = $service_link.'#tab_vmware';
                        }else {
                            $service_link = $service_link.'#tab_alias';
                        }
                        $vms_name = nameToLinkHtml($service_link, $vim_data[$ip]['vms_name'], '_blank');
                    }

                    $tmp['ip_id']               = $vim_data[$ip]['ip_id'];
                    $tmp['vms_id']              = ($type == 'vmservice') ? $vim_data[$ip]['vms_id'] : '';
                    $tmp['vms_alias_id']        = ($type == 'vmservice') ? $vim_data[$ip]['vms_alias_id'] : '';
                    $tmp['ip_address']          = $ip;
                    $tmp['vmware_ip']           = isset($am_data[$am_id]['ip_address']) ? $am_data[$am_id]['ip_address'] : '';
                    $tmp['am_name']             = nameToLinkHtml($assets_link, $vim_data[$ip]['am_name'], '_blank');
                    $tmp['am_vmware_name']      = $vim_data[$ip]['am_vmware_name'];
                    $tmp['vms_name']            = $vms_name;
                    $tmp['vms_memo']            = ($type == 'vmservice') ? $vim_data[$ip]['vms_memo'] : '';
                    $tmp['ip_memo']             = $vim_data[$ip]['ip_memo'];
                    $tmp['dim_id']              = ($type == 'direct') ? $vim_data[$ip]['dim_id'] : '';
                }
                //echo print_r($tmp); exit;
                $data[] = $tmp;
            }
        }
        return $data;
    }




    public function file_download() {

        $this->load->helper('download');

        $uri = $this->uri->segment_array();
        if( ! isset($uri[4]) || ! isset($uri[5]) ) {
            $this->common->alert(getAlertMsg('INVALID_SUBMIT'));
            $this->common->historyback();
            exit;
        }

        $log_array = array();
        $params = array();

        $id = $uri[5];
        switch($uri[4]) {

            case 'order':

                $this->load->model(array('order_tb_model'));

                $row = $this->order_tb_model->get($id)->getData();
                $img_path = $this->common->getImgPath('order', $row['o_id']);
                $filename = $img_path.'/'.$row['o_filename'];
                $origin_filename = $row['o_origin_filename'];

                $params['fullpath'] = $filename;
                $params['filename'] = $row['o_filename'];
                $params['origin_filename'] = $origin_filename;
                $log_array['params'] = $params;
                $this->common->write_history_log($sess, 'DOWNLOAD', $id, $log_array, 'order_tb');
                break;

        } // END_switch

        $data = file_get_contents($filename);
        force_download($origin_filename, $data);
    }
 

    public function hostmap() {

        $data = array();

		$this->load->model(array(
            'vmservice_host_map_tb_model',
            'vmservice_tb_model'
        ));
        $req = $this->input->post();
        $data = array();

        if(isset($req['mode']) && $req['mode'] == 'list') {

            $out_data = array();
            $params = array();
            $extras = array();

            //echo print_r($req);
            //$fields = array('s_name', 's_code', 's_description'); 
            $fields = array();
            $params = $this->common->transDataTableFiltersToParams($req, $fields);
            $extras = $this->common->transDataTableFiltersToExtras($req);
            //echo print_r($params); exit;

            $count = $this->vmservice_host_map_tb_model->getCount($params)->getData();
            $rows = $this->vmservice_host_map_tb_model->getList($params, $extras)->getData();

            $data = array();
            foreach($rows as $k=>$r){

                if( $r['vhm_vmservice_id'] > 0 ) {
                    $link = '<a href="/admin/vmservice/detail/'.$r['vhm_vmservice_id'].'" target="_blank">';
                    $link .= $r['vhm_vmservice_name'];
                    $link .= '<i class="fal fa-external-link-square ml-1"></i>';
                    $link .= '</a>';

                    $r['vhm_vmservice_name'] = $link;
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

		$this->_view('system/hostmap', $data);

    }  


	public function hostmap_detail($id=0) {

		$this->load->model(array(
            'vmservice_host_map_tb_model',
            'vmservice_tb_model'
        ));

        $row = array();
		$id = intval($id);

        if($id > 0) {
            $mode = 'update'; 
            $row = $this->vmservice_host_map_tb_model->get($id)->getData();
        
        }else {

            // SET 초기화 및 기본값 
            $mode = 'insert'; 
            $fields = $this->vmservice_host_map_tb_model->getFields();
            
        }

        $data = array();
        $data['mode'] = $mode;
        $data['row'] = $row;

		$this->_view('system/hostmap_detail', $data);
    }


    public function hostmap_process() {

		$this->load->model(array(
            'vmservice_host_map_tb_model',
            'vmservice_tb_model'
        ));

        $request = $this->input->post();

        $model_name = 'vmservice_host_map_tb';
        $sess = array();
        $log_array = array();
        $admin = array();

        //echo print_r($request).PHP_EOL;

        $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/system/hostmap';
        
        $field_list = $this->vmservice_host_map_tb_model->getFields();
        $data_params = array();
        foreach($field_list as $key) {
			if(array_key_exists($key, $request)) {
				$data_params[$key] = $request[$key];
				continue;
			}

			if(array_key_exists($key.'_date', $request) && array_key_exists($key.'_time', $request)) {
				$data_params[$key] = $request[$key.'_date'].' '.$request[$key.'_time'];
			}
		}


        // 삭제 & 업데이트 시 기존데이터 검증
        $vhm_data = array();
        if($request['mode'] == 'update' || $request['mode'] == 'delete') {
            if( ! $this->vmservice_host_map_tb_model->get($request['vhm_id'])->isSuccess()) {
                $log_array['msg'] = 'INVALID KEY.';
                $this->common->alert($log_array['msg']);
                $this->common->locationhref($rtn_url);
                $this->common->write_history_log($sess, $request['mode'].' - FAIL', $request['vhm_id'], $log_array, $model_name);
                return;
            }
            $vhm_data = $this->vmservice_host_map_tb_model->getData();
            $log_array['prev_data'] = $vhm_data;
        }

        switch($request['mode']) {

            case 'delete':
                
                if( ! $this->vmservice_host_map_tb_model->doDelete($vhm_data['vhm_id'])->isSuccess()) {
                    $log_array['msg'] = $this->vmservice_host_map_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    $this->common->write_history_log($sess, $request['mode'].' - FAIL', $request['vhm_id'], $log_array, $model_name);
                    return;
                }
                $this->common->write_history_log($sess, 'EXPIRE', $request['vhm_id'], $log_array, $model_name);
                $this->common->locationhref($rtn_url);
                return;

                break;

            case 'update':

                $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/system/hostmap_detail/'.$request['vhm_id'];
                $log_array['params'] = $data_params;
                unset($data_params['vhm_id']);
                //echo print_r($data_params); exit;
                if( ! $this->vmservice_host_map_tb_model->doUpdate($request['vhm_id'], $data_params)->isSuccess()) {
                    $log_array['msg'] = $this->vmservice_host_map_tb_model->getErrorMsg();
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    $this->common->write_history_log($sess, $request['mode'].' - FAIL', $request['vhm_id'], $log_array, $model_name);
                    return;
                }
                $this->common->write_history_log($sess, 'UPDATE', $request['vhm_id'], $log_array, $model_name);
                break;
            
            case 'insert':
                // 미구현
                // insert 는 Cron 에 자동 등록 기준
                break;
        }

        $this->common->locationhref($rtn_url);
    } 




    public function lablist($id=0) {

		$this->load->model(array(
            'lab_tb_model',
            'ip_class_tb_model'
        ));
		$this->load->library('CIDR');


        $id = 18;

		$id = intval($id);
        if( $id < 1 ) {
            $this->common->alert(getAlertMsg('INVALID_SUBMIT'));
            $this->common->historyback();
            return;
        }

        $class_data = $this->ip_class_tb_model->get($id)->getData();
        echo print_r($class_data);



        $req = $this->input->post();
        //echo print_r($req); exit;
        // TODO. @req['search']['value']

		//if(isset($req['mode']) && $req['mode'] == 'list') {
			// ajax reqeust. ==> grid list data 

            $out_data = array();
            $params = array();
            $extras = array();

            $raw_query = 'INET_ATON(lab_ip) BETWEEN INET_ATON("'.$class_data['ipc_start'].'") AND INET_ATON("'.$class_data['ipc_end'].'")';
            $params['raw'] = array($raw_query);
            $extras['order_by'] = array('INET_ATON(lab_ip) ASC');

            $count = $this->lab_tb_model->getCount($params)->getData();
            $rows = $this->lab_tb_model->getList($params, $extras)->getData();
            echo print_r($rows);

            /*
            $data = array();
            foreach($rows as $k=>$r){
                //$r['h_serialize'] = json_encode(unserialize($r['h_serialize']));
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
            */
		//}
    }


    public function ajax_reset_code() {

        $req = $this->input->post();
        $json_data = array(
            'is_success'    => FALSE,
            'msg'           => '',
        );

        if( ! isset($req['a_id']) || strlen($req['a_id']) < 1 ) {
            $json_data['msg'] = getAlertMsg('EMPTY_PARAMS');    
            echo json_encode($json_data);
            return;
        } 

		$this->load->model('admin_tb_model');

        $data = $this->admin_tb_model->get($req['a_id'])->getData();
        if( sizeof($data) < 1 ) {
            $json_data['msg'] = getAlertMsg('EMPTY_PARAMS');    
            echo json_encode($json_data);
            return;
        } 

        $log_array = array(
            'params' => array(
                'request'   => 'Reset OTP (Secret code)',
                'response'  => array()
            )
        );
        $this->load->library(array(
            'Authenticator'
        ));

        $secret = $this->authenticator->createSecret(32);


        $provider = ADMIN_DOMAIN;
        $qrcodeURL = $this->authenticator->getQRCodeGoogleUrl($provider, $data['a_loginid'], $secret);
        $qrcode = md5($qrcodeURL).".qrcode";

        $params = array();
        $params['a_auth_secret'] = $secret;
        $params['a_auth_qrcode'] = $qrcode;
        $params['a_auth_created_at'] = date('Y-m-d H:i:s');
        if( ! $this->admin_tb_model->doUpdate($req['a_id'], $params)->isSuccess() ) {
            $json_data['msg'] = getAlertMsg('FAILED_UPDATE');    
        }else {

            $filepath = DISPLAY_PATH.'/qrcode/'.$qrcode;
            $exec = "/usr/local/bin/curl -k -s ".escapeshellarg($qrcodeURL)." > ".$filepath;
            @shell_exec($exec);

            $json_data['is_success'] = TRUE;
            $json_data['msg'] = $secret;
        }

        $log_array['params']['response'] = $json_data;
        $this->common->write_history_log($sess, 'RESET CODE', $req['a_id'], $log_array, 'admin_tb');
        echo json_encode($json_data);
        return;
    }


    public function ajax_send_qrcode() {

        $req = $this->input->post();
        $json_data = array(
            'is_success'    => FALSE,
            'msg'           => '',
        );

        $log_array = array(
            'params' => array(
                'request'   => '[Send Mail] - OTP QRCode',
                'response'  => array()
            )
        );

        if( ! isset($req['a_id']) || strlen($req['a_id']) < 1 ) {
            $json_data['msg'] = getAlertMsg('EMPTY_PARAMS');    
            echo json_encode($json_data);
            return;
        } 

		$this->load->model(array('admin_tb_model', 'people_tb_model'));

        $data = $this->admin_tb_model->get($req['a_id'])->getData();
        if( sizeof($data) < 1 ) {
            $json_data['msg'] = getAlertMsg('EMPTY_PARAMS');    
            echo json_encode($json_data);
            return;
        } 

        $pp_data = $this->people_tb_model->get(array('pp_admin_id' => $data['a_id']))->getData();
        if( sizeof($pp_data) < 1 || strlen($pp_data['pp_emp_number']) < 1 ) {
            $json_data['msg'] = getAlertMsg('EMPTY_PARAMS');
            echo json_encode($json_data);
            return;
        } 


        $this->load->library(array(
            'Authenticator',
            'DaouData',
            'encrypt',
            'email'
        ));

        $provider = ADMIN_DOMAIN;
        $qrcodeURL = $this->authenticator->getQRCodeGoogleUrl($provider, $data['a_loginid'], $data['a_auth_secret']);

        if( $data['a_auth_qrcode'] !== md5($qrcodeURL).".qrcode" ) {
            $json_data['msg'] = "QRCode가 생성된 비밀키와 일치 하지 않습니다. 재성성 후 발송 필요.";    
            echo json_encode($json_data);
            return;
        } 


        // URL 길이 짧게 필요
        $code = $data['a_auth_qrcode'].'_%%_%%_'.date(time());
        $this->encrypt->set_cipher(MCRYPT_BLOWFISH);
        $enc_code = $this->encrypt->encode($code);
        $enc_code = urlencode($enc_code);
        $qrcode_url = "http://".ADMIN_DOMAIN."/api/images/qrcode/".$enc_code;


        $sender = "2012071";
        $receiver = array($pp_data['pp_emp_number']);

        $mailtitle = "[".ADMIN_DOMAIN."] ".$data['a_loginid']." 계정 OTP 발급 알림";

        $mailmsg = "<p>OTP 인증을 통해 ".ADMIN_DOMAIN." 에 로그인 가능합니다.</p><br />";
        $mailmsg .= "<p>1. 앱 다룬로드. (안드로이드 : 구글 OTP, 아이폰 : Authenticator)<br />";
        $mailmsg .= "2. 3번(아래) 링크 QRCode를 통해 사이트 OTP 추가 (QRCode 이미지 발급시간 기준 24시간 유효)<br />";
        $mailmsg .= "3. <a href='".$qrcode_url."' target='_blank'>[QRCode 보기]</a> ".$qrcode_url."<br />";
        $mailmsg .= "4. [로그인] <a href='http://".ADMIN_DOMAIN."' target='_blank'>".ADMIN_DOMAIN."</a></p><br />";
        $mailmsg .= "<p> - 문의 [정보보안인프라팀] 김현중</p><br />";


        $res = $this->daoudata->sendNotify($sender, $receiver, $mailtitle, $mailmsg, $mailtitle, $linkurl="");
        if( $res['code'] == '200' ) {
            $json_data['is_success'] = TRUE;
        }else {
            $json_data['msg'] = $res['message'];    
        }

        $log_array['params']['request'] = $mailtitle;
        $log_array['params']['response'] = $res;
        $this->common->write_history_log($sess, 'NOTIFY', $data['a_id'], $log_array, 'admin_tb');
        echo json_encode($json_data);
        return;


        /* EMAIL 발송 로직 (AWS 지원 안하는 문제)
        $config['mailtype'] = 'html';
        $this->email->initialize($config);
        $this->email->from('82joong@cocen.com', '[정보보안인프라팀] 김현중');
        $this->email->to($data['a_email'], $data['a_lastname'].$data['a_firstname']);


        $filepath = DISPLAY_PATH.'/qrcode/'.$data['a_auth_qrcode'];
        $this->email->attach($filepath);
        $cid = $this->email->attachment_cid($filepath);

        $title = "[".ADMIN_DOMAIN."] ".$data['a_loginid']." OTP 발급";

        $content = "<p>OTP 인증을 통해 ".ADMIN_DOMAIN." 에 로그인 가능합니다.</p><br />";
        $content .= "<p>1. 앱 다룬로드. (안드로이드 : 구글 OTP, 아이폰 : Authenticator)<br />";
        $content .= "2. QRCode를 통해 사이트 OTP 추가<br />";
        $content .= "3. [로그인] <a href='http://".ADMIN_DOMAIN."' target='_blank'>".ADMIN_DOMAIN."</a></p><br />";
        $content .= "<img src='cid:".$cid."' alt='QRCode'>";
        $content .= "<p> - 문의 [정보보안인프라팀] 김현중</p><br />";


        $this->email->subject($title);
        $this->email->message($content);
        
        if( ! $this->email->send() ) {
            $json_data['msg'] = getAlertMsg('FAILED_SEND_EMAIL');    
        }else {
            $json_data['is_success'] = TRUE;
        }

        $log_array['params']['request'] = $title;
        $log_array['params']['response'] = $json_data;
        $this->common->write_history_log($sess, 'SENDMAIL', $data['a_id'], $log_array, 'admin_tb');
        echo json_encode($json_data);
        return;
        */
    }
}
