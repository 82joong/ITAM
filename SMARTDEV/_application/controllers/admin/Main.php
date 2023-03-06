<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include_once dirname(__FILE__).'/Base_admin.php';

class Main extends Base_admin {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */



    public function index() {
        $this->dashboard();
    }


	public function welcome() {
        /*
		$this->load->model('config_tb_model');

		echo 'config row count is <br />';
		echo $this->config_tb_model->getCount()->getData();
		echo '<br />';
		echo 'last query is ';
		echo '<br />';
		echo $this->config_tb_model->getLastQuery();
		echo '<br />';
		echo '<br />';

		echo 'config row Array is <br />';
		print_r($this->config_tb_model->getList()->getData());
		echo '<br />last query is <br />';
		echo $this->config_tb_model->getLastQuery();
		echo '<br />';
		echo '<br />';

		$unit = $this->config->get('service/define/unit');
		echo 'Site Unit is '.$unit.'<br />';

        $urn = 'service/define/';
        $data = array(
                'unit' => '$'
                );
        

		$this->config->setup('service/define', $data);

		$unit = $this->config->get('service/define/unit');
		echo 'Site Unit is '.$unit.'<br />';
        */

		$this->_view('welcome_message');
	}

    public function blank() {
		$this->_view('blank');
    }


    public function dashboard() {

        $this->load->model(array(
            'assets_model_tb_model',
            'status_tb_model',
            'models_tb_model',
            'category_tb_model',
            'location_tb_model',
            'company_tb_model',
            'people_tb_model',
        ));
        $this->load->business(array(
            'status_tb_business',
            'location_tb_business',
            'category_tb_business'
        ));

        $this->load->library('user_agent');




        $data = array();

        $data['category_map'] = $this->category_tb_business->getNameMap();
        $data['location_map'] = $this->location_tb_business->getNameMap();
        $data['assets_map'] = $this->_ASSETS_TYPE;

	// 월별 자산 등록 현황
        $previous_month = 24;
        if( $this->agent->is_mobile() ) {
            $previous_month = 12;
        }
        $data['previous_month'] = $previous_month; 
        $data['bar_data'] = $this->_statCreatedAssets($previous_month); 
	//echo print_r($data['bar_data']); exit;

	// 상태별 현황
        $data['pie_data'] = $this->_statStatusAssets();

	

        $data['tbl_data'] = $this->_statLocalAssets();

	// 모델별 수량 현황
	$data['model_assets'] = $this->_statModelAssets();


	// TODO. 단독/VM 현황
	

	// 회사별 자산 수량
	$data['company_assets'] = $this->_statCompanyAssets();

	// 날짜별 입사/퇴사 인원
	$previous_day = 30;
        if( $this->agent->is_mobile() ) {
            $previous_day = 15;
        }
	$data['people_data'] = $this->_statInOuttPeople($previous_day);


	$this->_view('main/dashboard', $data);
    }


    private function _statInOuttPeople($previous_day) {
    

        $data = array();

        $pre_date = strtotime('-'.$previous_day.' days');

        $params = array();
	if(IS_REAL_SERVER) {
		$params['>=']['pp_created_at'] = date('Y-m-d', $pre_date);
		$params['<=']['pp_created_at'] = date('Y-m-d');
	}else {
		$params['>=']['pp_created_at'] = '2022-11-15';
		$params['<=']['pp_created_at'] = '2022-12-15';
	}


        $extras = array();
        $extras['fields'] = array("DATE_FORMAT(pp_created_at, '%Y-%m-%d') AS date", "COUNT(pp_id) AS cnt");
        $extras['group_by'] = array("DATE_FORMAT(pp_created_at, '%Y-%m-%d')");
        $extras['order_by'] = array("DATE_FORMAT(pp_created_at, '%Y-%m-%d') ASC");

        $in_data = $this->people_tb_model->getList($params, $extras)->getData();
        $in_data = $this->common->getDataByPK($in_data, 'date');



        $params = array();
	if(IS_REAL_SERVER) {
		$params['>=']['pp_outed_at'] = date('Y-m-d', $pre_date);
		$params['<=']['pp_outed_at'] = date('Y-m-d');
	}else {
		$params['>=']['pp_outed_at'] = '2022-11-15';
		$params['<=']['pp_outed_at'] = '2022-12-15';
	}

        $extras = array();
        $extras['fields'] = array("DATE_FORMAT(pp_outed_at, '%Y-%m-%d') AS date", "COUNT(pp_id) AS cnt");
        $extras['group_by'] = array("DATE_FORMAT(pp_outed_at, '%Y-%m-%d')");
        $extras['order_by'] = array("DATE_FORMAT(pp_outed_at, '%Y-%m-%d') ASC");

        $out_data = $this->people_tb_model->getList($params, $extras)->getData();
	//echo $this->people_tb_model->getLastQuery(); exit;
	$out_data = $this->common->getDataByPK($out_data, 'date');

	/*
	echo print_r($in_data);
	echo print_r($out_data); exit;
	*/


	if(IS_REAL_SERVER) {
		$begin_date = date('Y-m-d', $pre_date); 
		$end_date = date('Y-m-d', time());
	}else {
		$begin_date = '2022-11-15'; 
		$end_date = '2022-12-15';
	}

        $begin = new DateTime($begin_date);
        $end   = new DateTime($end_date);
        

        $res = array(
            'labels'    => array(),
            'datasets'  => array(),
            'colors'     => array()
        );
        for($i = $begin; $i <= $end; $i->modify('+1 day')){
            $date = $i->format("Y-m-d");
            //echo $date.'<br />'.PHP_EOL; exit;
	    
	    // 날짜별 인원 수
	    $params = array();
	    $params['raw'] = array("pp_created_at <= '".$date."'  AND (pp_outed_at > '".$date."' OR pp_outed_at = '0000-00-00 00:00:00')");
	    $res['daily_total'][] = $this->people_tb_model->getCount($params)->getData();


            $res['labels'][] = $i->format("m-d");

            $res['datasets']['in'][] = isset($in_data[$date]) ? $in_data[$date]['cnt'] : 0;
            $res['colors']['in'] = array(
                    'background'    => 'rgba(81,173,246,.4)', 
                    'border'        => 'rgba(33,150,243,1)' 
            );

            $res['datasets']['out'][] = isset($out_data[$date]) ? $out_data[$date]['cnt'] : 0;
	    $res['colors']['out'] = array(
                    'background'    => 'rgba(254,107,176,.4)', 
                    'border'        => 'rgba(253,57,149,1)' 
            );
        }
        //echo print_r($res); exit;
        return $res;
    }


    // 회사별 자산 수량
    private function _statCompanyAssets() {

	    $params = array();
	    $params['>=']['am_company_id'] = 1;
	    $extras = array();
	    $extras['fields'] = array("am_company_id", "COUNT(*) as cnt");
	    $extras['group_by'] = array("am_company_id");
	    $extras['order_by'] = array("am_company_id ASC");

	    $data = $this->assets_model_tb_model->getList($params, $extras)->getData();
	    $data = $this->common->getDataByPK($data, 'am_company_id');

	    $params = array();
	    $params['=']['c_is_active'] = 'YES';
	    $extras = array();
	    $extras['fields'] = array('c_id', 'c_name', 'c_filename');
	    $extras['order_by'] = array('c_id ASC');
	    $company_data = $this->company_tb_model->getList($params, $extras)->getData();
	    //echo print_r($company_data); exit;

	    $res = array();
	    foreach($company_data as $r) {

		    $res[$r['c_id']] = array(
		    	'c_id'	=> $r['c_id'],
		    	'c_name'	=> $r['c_name'],
			'c_img_path'	=> '',
			'c_count'	=> 0,
		    );

		    if(strlen($r['c_filename']) > 0) {
                    	$img_path = $this->common->getImgUrl('company', $r['c_id']);
                    	$res[$r['c_id']]['c_img_path'] = $img_path.'/'.$r['c_filename']; 
		    }

		    if(isset($data[$r['c_id']])) {
		    	$res[$r['c_id']]['c_count'] = $data[$r['c_id']]['cnt'];
		    }
	    }
	    return $res;
    }

    private function _statModelAssets() {

	$url = ADMIN_DOMAIN.'/api/dashboard/models'; 
	$stats_models = $this->common->restful_curl($url, $param=array(), $method='POST');
	$stats_models = json_decode($stats_models, true);
	//echo print_r($stats_models); exit;
    

	$dataset = array();
	foreach($stats_models as $m_id => $rows) {
		$data = array();
		foreach($rows as $r) {
			$data['labels'][] = $r['am_models_name'];
			$data['data'][] = $r['cnt'];
		}
		$dataset[$m_id] = $data;
	}
	//echo print_r($dataset); //exit;
	return $dataset;
    }


    // 최근 장비 등록 현황
    private function _statCreatedAssets($previous_month) {

        $data = array();

        $params = array();
        $pre_month = strtotime('-'.$previous_month.' months');

        $params['>=']['am_created_at'] = date('Y-m', $pre_month);
        $params['<=']['am_created_at'] = date('Y-m');

        $extras = array();
        $extras['fields'] = array("DATE_FORMAT(am_created_at, '%Y-%m') AS date", "am_assets_type_id", "COUNT(am_id) AS cnt");
        $extras['group_by'] = array("DATE_FORMAT(am_created_at, '%Y-%m')", "am_assets_type_id");
        $extras['order_by'] = array("DATE_FORMAT(am_created_at, '%Y-%m') ASC", "am_assets_type_id ASC");

        $data = $this->assets_model_tb_model->getList($params, $extras)->getData();
	//echo $this->assets_model_tb_model->getLastQuery(); exit;
        $data = $this->common->getDataByDuplPK($data, 'date');

        $begin_date = date('Y-m', $pre_month); 
        $end_date = date('Y-m', time());
        /*
        echo 'Begin : '.$begin_date.'<br />'.PHP_EOL;
        echo 'End : '.$end_date.'<br />'.PHP_EOL;
        */

        $begin = new DateTime($begin_date);
        $end   = new DateTime($end_date);

        $res = array(
            'labels'    => array(),
            'datasets'  => array(),
            'colors'     => array()
        );
        for($i = $begin; $i <= $end; $i->modify('+1 month')){
            $date = $i->format("Y-m");
            //echo $date.'<br />'.PHP_EOL; exit;

            $res['labels'][] = $date;

            $at_data = array();
            if(isset($data[$date])) {
                $at_data = $this->common->getDataByPK($data[$date], 'am_assets_type_id');
            }

            foreach($this->_ASSETS_TYPE as $at_id=>$at) {
                $res['datasets'][$at_id][] = isset($at_data[$at_id]) ? $at_data[$at_id]['cnt'] : 0;

                $rgb = $this->common->hexToRGB($at['at_color']);
                $res['colors'][$at_id] = array(
                    'background'    => 'rgba('.implode(',', $rgb).', 0.3)', 
                    'border'        => 'rgba('.implode(',', $rgb).', 0.5)' 
                );
            }
        }
        //echo print_r($res); exit;

        return $res;
    }



    // 상태별 자산 현황
    private function _statStatusAssets() {

        $params = array();
        $extras = array();
        $extras['fields'] = array("am_status_id", "COUNT(am_id) AS cnt");
        $extras['group_by'] = array("am_status_id");
        $extras['order_by'] = array("am_status_id ASC");

        $data = $this->assets_model_tb_model->getList($params, $extras)->getData();
        $data = $this->common->getDataByPK($data, 'am_status_id');

        $res = array(
            'labels'    => array(),
            'datasets'  => array(),
            'colors'    => array(),
        );
        $status_data = $this->status_tb_business->getRowMap();
        foreach($status_data as $st) {

            $res['labels'][] = $st['opt_name'];
            $res['datasets'][] = isset($data[$st['opt_id']]) ? $data[$st['opt_id']]['cnt'] : 0;

            $rgb = $this->common->hexToRGB($st['opt_color']);
            $res['colors'][] = 'rgba('.implode(', ', $rgb).', 0.3)';
        }

        return $res;
    }


    // 위치별 자산 현황
    private function _statLocalAssets() {

        $params = array();
        $params['join']['models_tb'] = 'am_models_id = m_id';

        $extras = array();
        $extras['fields'] = array('am_assets_type_id', 'm_category_id', 'am_location_id', 'COUNT(am_id) as cnt');
        $extras['group_by'] = array('am_assets_type_id', 'm_category_id', 'am_location_id');
        $extras['order_by'] = array('m_category_id ASC', 'am_location_id ASC');

        $rows = $this->assets_model_tb_model->getList($params, $extras)->getData();
        $rows = $this->common->getDataByDuplPK($rows, 'm_category_id');
        //echo print_r($rows); exit;
        return $rows;
    } 
   

    public function login() {
    
        $exist_data = $this->session->all_userdata();
        if(isset($exist_data['admin']) && sizeof($exist_data['admin']) > 0) {
            return $this->common->locationhref('/'.SHOP_INFO_ADMIN_DIR.'/main/dashboard');
        }

        $data = array();
		$url = $this->input->get('url');
		if(strlen($url) > 0) {
			$data['url'] = $url;
		}

        $this->_view('login', $data);
    }



    public function logout(){
        $this->session->unset_userdata('admin');
        return $this->common->locationhref('/'.SHOP_INFO_ADMIN_DIR.'/main/login');
    }

    public function login_action(){

        // HARD CODING 
        $admin_match_allow_ip = array(
		/*
                'cocen'=>array(
                    '14.129.44.25'
                    )
		*/
        );

        $this->load->library(array(
            'Authenticator'
        ));

        $exist_data = $this->session->all_userdata();
        if(isset($exist_data['admin']) && sizeof($exist_data['admin']) > 0) {
            return $this->common->locationhref('/'.SHOP_INFO_ADMIN_DIR);
        }
        
        $this->load->model(array(
            'admin_tb_model',
            'admin_setting_tb_model'
        ));
        $this->load->library('password');
        $id = $this->input->post('admin_id');
        $pw = $this->input->post('admin_pw');
        $admin = $this->admin_tb_model->get(array('a_loginid'=>$id))->getData();
        //echo print_r($admin); //exit;

        $cs_msg = "@문의 -[ 정보보안인프라팀] 김현중";;

        if(USE_OTP == TRUE) {
            $otp = $this->input->post('otp');
            $chk_otp = $this->authenticator->verifyCode($admin['a_auth_secret'], $otp);
            if( $chk_otp == TRUE ) {
                //echo 'SUCCESS';
            }else {
                //echo 'FAIL';
                $msg = 'OTP CODE 인증 오류입니다.\r\n재시도 또는 담당자 문의 부탁드립니다.';
                $this->common->alert($msg.'\r\n'.$cs_msg);
                $this->common->locationhref('/'.SHOP_INFO_ADMIN_DIR.'/main/login');
                return;
            }
        }


        $msg = '아이디 또는 비밀번호가 일치 하지 않습니다.\r\n재시도 또는 담당자 문의 부탁드립니다.';

        // 사용자 존재 여부 
        if($this->admin_tb_model->isSuccess() === FALSE){
            $this->common->alert($msg.'\r\n'.$cs_msg);
            $this->common->locationhref('/'.SHOP_INFO_ADMIN_DIR.'/main/login');
            //echo 'No Exists Admin';
            return;
        }

        // 비밀번호 불일치
        if($this->password->authPassword($pw, $admin['a_passwd']) === FALSE){
            $this->common->alert($msg.'\r\n'.$cs_msg);
            $this->common->locationhref('/'.SHOP_INFO_ADMIN_DIR.'/main/login');
            //echo 'InCorrect Password!';
            return;
        }


        

        // 탈퇴된 회원은 로그인 불가
        $outmember_pw = 'outmember123!';
        if($pw == $outmember_pw) {
            $this->common->alert('This account has expired.');
            $this->common->locationhref('/'.SHOP_INFO_ADMIN_DIR.'/main/login');
            return;
        }
            
        // 아래 세션 변경 시 main/admin_list_process도 같이 수정 
        $admin_userdata = array(
            'id'                => $admin['a_id'],
            'login_id'          => $admin['a_loginid'],
            'name'              => $admin['a_firstname'].' '.$admin['a_lastname'],
            'level'             => $admin['a_level'],
            'permission'        => unserialize($admin['a_permission']),
            'is_changed_pw'     => $admin['a_is_changed_pw'],
            'ip'                => $_SERVER['REMOTE_ADDR'],
	    'permission'	=> array()
        );

        // 추가된 권한에 대한 관리자 세션별 디폴트 처리
        $admin_permission = $this->admin_tb_model->getAdminPermissionMap();
        if(sizeof($admin_userdata['permission']) != sizeof($admin_permission)) {
            foreach($admin_permission as $p_key => $p_val) {
                if(isset($admin_userdata['permission'][$p_key])) { continue; }
                $admin_userdata['permission'][$p_key] = false;
            }
        }


        //특정 ID에서 허용되지 않은 IP로 로그인 시도시 처리
        if($admin['a_ip_filter'] == 'YES'){
            $a_allow_ips = explode("\n", $admin['a_allow_ips']);
            $admin_match_allow_ip = array_map('trim', $a_allow_ips);
            if(in_array($_SERVER['REMOTE_ADDR'], $admin_match_allow_ip) == false){
                $log_array = array();
                $log_array['params']['msg'] = 'In the IP access attempt is not allowed';
                $log_array['params']['ip'] = $_SERVER['REMOTE_ADDR'];
                $this->common->write_history_log($admin_userdata, 'OUT_LOGIN', $admin['a_id'], $log_array, 'admin_tb');
                $this->common->locationhref('/'.SHOP_INFO_ADMIN_DIR.'/main/login');
                return;
            }
        }


        $check_admin_password_valid = true;

        /*
        if($admin['a_level'] == '1') {
            $as = $this->admin_setting_tb_model->get_setting_data();
            if(strlen($as['as_allow_ips']) > 0) {
                $as_allow_ips = explode("\n", $as['as_allow_ips']);
                if(in_array($_SERVER['REMOTE_ADDR'], $as_allow_ips) == false) {
                    $this->common->alert('This is not allow IP in '.ADMIN_SITE_NAME.'.\n\n(Your IP : '.$_SERVER['REMOTE_ADDR'].')');
                    $this->common->locationhref('/'.SHOP_INFO_ADMIN_DIR.'/main/login');
                    return;
                }
            }
        } else {
            if($this->common->admin_password_validate($pw) === false) {
                $check_admin_password_valid = false;
                $admin_userdata['level'] = 0; // level 2 이상인 유저만 비밀번호 강화.
            }
        }
        */

        $validate = $this->common->validate_password($pw);
        //echo print_r($validate);
        $check_admin_password_valid = $validate[0]; 


        $exist_data['admin'] = $admin_userdata;
        $this->session->set_userdata($exist_data);
        
        $params = array();
        $where_params = array();
        $params['a_lastlogin_at'] = date("Y-m-d H:i:s");
        $params['a_permission'] = serialize($admin_userdata['permission']);
        $where_params['=']['a_loginid'] = $id;
        
        $this->admin_tb_model->doUpdateWithWhere($admin['a_id'], $params, $where_params);

        $log_array = array();
        $log_array['params'] = $admin_userdata;
        $this->common->write_history_log($admin_userdata, 'LOGIN', $admin['a_id'], $log_array, 'admin_tb');


        $redirect_url = '/'.SHOP_INFO_ADMIN_DIR;
        $param = $this->input->post();
        if(isset($param['url']) && strlen($param['url']) > 0) {
            $redirect_url = base64_decode($param['url']);
        }



        //비밀번호 보안에 맞게 변경 처리
        if($check_admin_password_valid == false || $admin['a_is_changed_pw'] == 'NO') {
            $redirect_url = '/'.SHOP_INFO_ADMIN_DIR.'/main/change_password';
        }

        /*
        if($admin['a_level'] == '1') {
            // TODO. ???
            $redirect_url = '/'.SHOP_INFO_ADMIN_DIR.'/main/form_name/';
        }
        */
        //echo $redirect_url; exit;
        return $this->common->locationhref($redirect_url);

    }


	// 간단 비밀번호 재설정 
	public function change_password() {

		$sess = $this->session->userdata('admin');
		if(isset($sess['id']) == false) {
			$this->common->alert('There is no info');
			$this->common->locationhref('/'.SHOP_INFO_ADMIN_DIR.'/main/login');
			return;
		}
		$this->load->model('admin_tb_model');
		$data = array();
		$data['admin'] = $this->admin_tb_model->get($sess['id'])->getData();
		$this->_view('change_password', $data);
	}



	public function change_password_action() {

        // TODO. 동일 PW로 변경에 대한 처리(미검증)

		$this->load->library('password');

		$request = $this->input->post();
		if(isset($request['a_id']) == false || strlen($request['a_id']) < 1) {
			$this->common->alert('Not enough data');
			$this->common->locationhref('/'.SHOP_INFO_ADMIN_DIR.'/main/change_password');
			return;
		}
		if(isset($request['new_password']) == false || strlen($request['new_password']) < 1) {
			$this->common->alert('Please enter your password');
			$this->common->locationhref('/'.SHOP_INFO_ADMIN_DIR.'/main/change_password');
			return;
		}
        $data = $this->common->validate_password($request['new_password']);
		if($data[0] !== TRUE) {
			$this->common->alert(getAlertMsg($data[1]));
			$this->common->locationhref('/'.SHOP_INFO_ADMIN_DIR.'/main/change_password');
			return;
		} 
		$this->load->model('admin_tb_model');
		if($this->admin_tb_model->get($request['a_id'])->isSuccess() == false) {
			$this->common->alert('There is no admin data');
			$this->common->hitoryback();
			return;
		}

		$admin_user = $this->admin_tb_model->getData();

		$params = array();
		$params['a_passwd'] = $this->password->genPassword($request['new_password']);
        $params['a_is_changed_pw'] = 'YES';
        $params['a_changed_pw_at'] = date("Y-m-d H:i:s");
		if($this->admin_tb_model->doUpdate($request['a_id'], $params)->isSuccess() == false)  {
			$this->common->alert('Fail to update');
			$this->common->hitoryback();
			return;
		}

		$log_array = array();
		$log_array['change_password'] = 'DONE';
		$this->common->write_history_log($this->session->userdata('admin'), 'CHAHGE_PASSWORD', $admin_user['a_id'], $log_array, 'admin_tb');


		// level 다시 설정.
		$exist_data = $this->session->all_userdata();
		$exist_data['admin']['level'] = $admin_user['a_level'];
		$exist_data['admin']['is_changed_pw'] = 'YES';
		$this->session->set_userdata($exist_data);

		$this->common->alert("Your password has been changed successfully.");
		$this->common->locationhref('/'.SHOP_INFO_ADMIN_DIR.'/main/login');
		return;
    }



    public function ajax_validate_password() {

        $res = array(
            'is_success' => FALSE,
            'msg'        => ''
        );

        $req = $this->input->post();

        if( ! isset($req['in_pw']) || strlen($req['in_pw']) < 1 ) {
            $res['msg'] = getAlertMsg('EMPTY_PARAMS');
            echo json_encode($res);
            return;
        }


        $data = $this->common->validate_password($req['in_pw']);
        $res['is_success'] = $data[0];
        $res['msg'] = getAlertMsg($data[1]);

        echo json_encode($res);
        return;  
    }

}
