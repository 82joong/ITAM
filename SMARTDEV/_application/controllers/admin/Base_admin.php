<?php
defined('BASEPATH') or exit('No direct script access allowed');

abstract class Base_admin extends CI_Controller
{

    // serialize_lib
	public  $sess = array();
	public  $auth_logininfo;
	private $auth_info_idno;
	private $auth_deptno;
	private $auth_urlname;


	protected $IS_SUPER = false;
	private $super_loginids = array(
		'82joong',
	);




    public  $_ASSETS_TYPE = array();
    public  $_ADMIN_DATA = array();

    private $admindir = SHOP_INFO_ADMIN_DIR;
 
    private $header_data = array();
    private $footer_data = array();
    private $css_data = array();


    
    public function __construct()
    {
        parent::__construct();

        date_default_timezone_set('Asia/Seoul');

        /*
        if( 
            IS_REAL_SERVER
            && ! (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
        ) { 
            $str_get = http_build_query($_GET);
            if(strlen(trim($str_get)) > 0) $str_get = '?'.$str_get; 
            die(header("Location: https://".ADMIN_DOMAIN."/".$this->uri->uri_string().$str_get));
        } 
         */ 



        $this->load->library('user_agent');
        $this->load->helper('message');
		$this->load->helper('components');


        $this->load->business(array('admin_setting_tb_business', 'assets_type_tb_business'));
        $this->load->model('admin_setting_tb_model');


		if(USE_AUTH_INFO == true) {
			$this->auth_login();
		} else {
		
            //  ADMIN Session 검증
            $exist_data = $this->session->all_userdata();
            $admin_data = array();
            
            // Exist ADMIN Login Info
            if(!(isset($exist_data['admin']) && sizeof($exist_data['admin']) > 0)) {
                $allow_uris = array(
                    '/'.$this->admindir.'/main/login',
                    '/'.$this->admindir.'/main/login_action'
                );
                $req_uri = explode('?', $_SERVER['REQUEST_URI'],2);
                $url = array_shift($req_uri);
                if(in_array($url, $allow_uris) == false) {
                    $this->common->locationhref('/'.$this->admindir.'/main/login?url='.base64_encode($url));
                    exit;
                }

            }else {

                // Required Change PW 
                $chk_skip_url = array('change_password', 'change_password_action');
                if( 
                    ! in_array($this->uri->segment(3), $chk_skip_url) &&
                    ( ! isset($exist_data['admin']['is_changed_pw']) || $exist_data['admin']['is_changed_pw'] != 'YES') 
                ) {
                    $this->common->locationhref('/'.$this->admindir.'/main/change_password');
                    return;
                }


                if(isset($exist_data['admin']['ip']) && $exist_data['admin']['ip'] != $_SERVER['REMOTE_ADDR']) {
                    $this->session->unset_userdata('admin');
                    return $this->common->locationhref('/'.$this->admindir.'/main/login');
                }

                $admin_data = $exist_data['admin'];
                $this->_ADMIN_DATA = $admin_data;
            }

        }

        if( isset($this->_ADMIN_DATA['login_id']) ) {
		    $this->IS_SUPER = in_array($this->_ADMIN_DATA['login_id'], $this->super_loginids);
        }


        // todo. header_data (타이틀, 구글애널리틱스 코드 .. 등? ) $_SHOP_INFO에 담아와 set 하기
        $this->header_data = array(
            'admin_data'    => $admin_data,
            'page_title'    => ADMIN_SITE_NAME,
            'assets_dir'    => ADMIN_ASSETS_DIR,
            'custom_css'    => ''
        );
        $this->footer_data = array(
            'admin_data'    => $admin_data 
        );


        // @view 페이지별로 추가될 css 파일 및 class 정의
        $this->css_data = array(
            'login' => array(
                'page_css' => array('page-login-alt.css'),
                'page_inner_class'  => 'bg-brand-gradient' 
            ),
            'join' => array(
                'page_css' => array('page-login-alt.css'),
                'page_inner_class'  => 'bg-brand-gradient' 
            ),
            'change_password' => array(
                'page_css' => array('fa-brands.css'),
                'page_inner_class'  => 'bg-brand-gradient' 
            )
        );
        // todo. footer_data (담을게 있다면.. ) $_SHOP_INFO에 담아와 set 하기

        $this->_ASSETS_TYPE = $this->assets_type_tb_business->get_assets_type($is_active='YES');

    }




	protected function auth_login() {

		// 통합 로그인 인증 프로그램
		if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] =="on") {
            $auth_url = "https://"; 
        }else {
            $auth_url= "http://";
        }
		$auth_url .= $_SERVER["HTTP_HOST"];

		if ($_SERVER["SERVER_PORT"]!=80 && $_SERVER["SERVER_PORT"]!="443") {
            $auth_url .= ":".$_SERVER["SERVER_PORT"];
        }
		$auth_url .= str_replace("auth_type=logout","",$_SERVER["REQUEST_URI"]);    // 현재URL을 가져온다.

		// 로그인하는 페이지
		$auth_login_url = "https://info.makeshop.co.kr/auth/?movepage=".urlencode($auth_url);

		if (!isset($_COOKIE["auth_info_id"]) || !isset($_COOKIE["auth_info_key"])) { 
			Header("Location: $auth_login_url");
            exit; 
		}

        // START_AUTH_MAP ADMIN
        $this->load->model('admin_tb_model');

        $auth_recv['id'] = $_COOKIE['auth_info_id'];
        $params = array();
        $params['=']['a_loginid'] = strtolower($auth_recv['id']);
        $admin_data = $this->admin_tb_model->get(array('a_loginid' => strtolower($auth_recv['id'])))->getData();
        //echo print_r($admin_data); //exit;

        // 사용자 존재 여부 
        if($this->admin_tb_model->isSuccess() === FALSE){
            $this->session->unset_userdata('admin');
            $msg = '[CODE:NOUSER] 사이트 접근계정이 없는 사용자입니다.\n';
            $msg .= '정보보안인프라팀에 문의 부탁드립니다.';
            $this->common->alert($msg);
            /*
            $this->common->locationhref('/'.SHOP_INFO_ADMIN_DIR.'/main/login');
            //echo 'No Exists Admin';
            */
            $this->auth_logout();
            return;
        }


        // 아래 세션 변경 시 main/admin_list_process도 같이 수정 
        $admin_userdata = array(
            'id'                => $admin_data['a_id'],
            'login_id'          => $admin_data['a_loginid'],
            'name'              => $admin_data['a_firstname'].' '.$admin_data['a_lastname'],
            'level'             => $admin_data['a_level'],
            'permission'        => unserialize($admin_data['a_permission']),
            'is_changed_pw'     => $admin_data['a_is_changed_pw'],
            'ip'                => $_SERVER['REMOTE_ADDR'],
        );

        $exist_data['admin'] = $admin_userdata;
        $this->session->set_userdata($exist_data);
        
        $params = array();
        $where_params = array();
        $params['a_lastlogin_at'] = date("Y-m-d H:i:s");
        $params['a_permission'] = serialize($admin_userdata['permission']);
        $where_params['=']['a_loginid'] = $id;
        
        $this->admin_tb_model->doUpdateWithWhere($admin_data['a_id'], $params, $where_params);

        $log_array = array();
        $log_array['params'] = $admin_userdata;
        $this->common->write_history_log($admin_userdata, 'LOGIN', $admin_data['a_id'], $log_array, 'admin_tb');
        // END_AUTH_MAP ADMIN



		include_once realpath(dirname(__FILE__).'/../../')."/libraries/serialize_lib.html"; // 시리얼라이즈 


		// 로그아웃처리
		if (isset($_POST["auth_type"]) && ($_POST["auth_type"]=="logout" || $_GET["auth_type"]=="logout")) {
            $this->auth_logout();
        }


		$auth_info_id = $_COOKIE["auth_info_id"];
		$auth_info_key = $_COOKIE["auth_info_key"];


		$authc = new mycall();   

		$authc->ssl = true;
		$authc->host = "info.makeshop.co.kr";
		$authc->script_name = "/auth/getauth_joong.html";
		$authc->method="POST";

		$authc->add("id", $auth_info_id);
		$authc->add("authip", getenv("REMOTE_ADDR"));
		$authc->add("key", $auth_info_key);
		$authc->add("authurl", $_SERVER["HTTP_HOST"].$_SERVER["SCRIPT_NAME"]);       // 페이지인증

		$authc->exec();
		$auth_recv = $authc->get_array_data();
        header('Content-Type: text/html; charset=euc-kr');
		//echo "<pre>";print_r ($auth_recv);
        //exit;

		// 변수초기화 cgi같은 값들로부터 넘어올 수 있음 (global On설정시..)
		$this->auth_info_idno = "";
		$this->auth_deptno = "";

		// 인증실패
		if (isset($auth_recv["result"]) && $auth_recv["result"] == "fail")  {
			$this->sess = array();
			SetCookie("auth_info_id","",0,"/",".makeshop.co.kr");
			SetCookie("auth_info_key","deleted",0,"/",".makeshop.co.kr");

			$msg = mb_convert_encoding($auth_recv["mesg"], "UTF-8", "EUC-KR");
			echo "<script>alert('".$msg."'); location.href='".$auth_login_url."'; </script>";
			exit;
			// 인증성공
		} else {
            /*
			$this->sess = array(
                'loginid'	=> $auth_recv['id'],
                'name'		=> iconv('euc-kr', 'utf-8', $auth_recv['name'])
            );
            */


            // ITAM 내 [admin_tb] 와 로그인 연결



			$auth_name      = mb_convert_encoding($auth_recv["name"], "UTF-8", "EUC-KR");           // 이름
			$auth_lev_name  = mb_convert_encoding($auth_recv["lev_name"], "UTF-8", "EUC-KR");       // 직책
			$auth_deptname  = mb_convert_encoding($auth_recv["deptname"], "UTF-8", "EUC-KR");       // 부서명
			$this->auth_deptno    = $auth_recv["deptno"];   // 부서번호
			$this->auth_info_idno = $auth_recv["idno"];     // id번호
			$auth_logindate  = $auth_recv["logindate"];     // 로긴시간
			$auth_accessdate = $auth_recv["accessdate"];    // access시간
			$auth_accesstime = $auth_recv["accesstime"];    // 작업경과시간

			// 권한설정체크값
			$auth_check_id =  $auth_recv["authid"];         // 권한설정ID값
			$auth_check_dept = $auth_recv["authdept"];      // 권한설정부서
			$auth_check_viewid = $auth_recv["viewid"];      // 조회설정ID값
			$auth_check_viewdept = $auth_recv["viewdept"];  // 조회설정부서
			$auth_check_ssl = $auth_recv["authssl"];        // ssl여부
			$this->auth_urlname = $auth_recv["authname"];   // 페이지이름

			// 일반권한 체크
			if ($this->auth_check($auth_check_id,$auth_check_dept)==true) {
				$auth_onlyview = false;                     // 이값을 가지고, 일반권한을 설정할 수 있다.

			// 일반권한이 없으면 조회권한있는지 체크한다.
            }else if ($this->auth_check($auth_check_viewid,$auth_check_viewdept)==true) {
				$auth_onlyview = true;                      // 이값을 가지고, 보기권한을 설정할 수 있다.
            }else {
                $this->auth_die();
            }


			$auth_dateformat = substr($auth_logindate,0,4)."/";
            $auth_dateformat .= substr($auth_logindate,4,2)."/";
            $auth_dateformat .= substr($auth_logindate,6,2)." ";
            $auth_dateformat .= substr($auth_logindate,8,2).":";
            $auth_dateformat .= substr($auth_logindate,10,2);

		}
	}

	// 인증권한체크
	private function auth_check($idno="", $deptno="") {
		// 인증에 필요한 값이 없으면 die
		if (strlen($this->auth_info_idno)==0 || strlen($this->auth_deptno)==0) return false;
		if (strlen($idno)==0 && strlen($deptno)==0) return false;

		if ($idno=="ALL") return true;

		if (strlen($idno)>0) {
			$id_temp = explode(",",$idno);
			for($i=0;$i<sizeOf($id_temp);$i++) if ($id_temp[$i]==$this->auth_info_idno) return true;  
		}
		if (strlen($deptno)>0) {
			$dept_temp = explode(",",$deptno);
			for($i=0;$i<sizeOf($dept_temp);$i++) if ($dept_temp[$i]==$this->auth_deptno) return true; 
		}
		return false;
	}


	private function auth_die() {
		echo "[$this->auth_urlname] {$_SERVER["HTTP_HOST"]}{$_SERVER["SCRIPT_NAME"]} 사용권한이 없는 페이지 입니다. ";
        echo "<a href='JavaScript:history.go(-1);'>[뒤로]</a> <a href=?auth_type=logout>[로그아웃]</a>";
		exit;
	}


	// 로그아웃하는 프로그램
	private function auth_logout() {

		include_once realpath(dirname(__FILE__).'/../../')."/libraries/serialize_lib.html"; // 시리얼라이즈 
		$auth_info_id = $_COOKIE["auth_info_id"];

		$authc = new mycall();
		$authc->ssl = true;
		$authc->host= "info.makeshop.co.kr";
		$authc->script_name = "/auth/logout.html";
		$authc->method= "POST";
		$authc->add("id", $auth_info_id);
		$authc->exec();

		$auth_recv = $authc->get_array_data();

		if ($auth_recv["result"]!="fail") {
			SetCookie("auth_info_id","",0,"/",".makeshop.co.kr");
			SetCookie("auth_info_key","",0,"/",".makeshop.co.kr");
			echo "로그아웃 되었습니다.";
			exit;
		} else {
			echo "로그아웃에 실패했습니다.";
		}
	}



    public function _view($view, $data = array(), $is_popup = false)
    {

        if(isset($this->css_data[$view])) {
            $this->header_data['custom_css'] = $this->css_data[$view];
        }

        if ($is_popup == FALSE) {
            $this->__view('header', $this->header_data);
        }

        $data['_IS_SUPER'] = $this->IS_SUPER;
        $this->__view($view, $data);

        $except_footer = array(
            'join',
            'login',
            'change_password',
        );

        if (! $is_popup && ! in_array($view, $except_footer)) {
            $this->__view('footer', $this->footer_data);
        }
    }


    private function __view($view, $data = array())
    {
        $default_prefix = $this->admindir.'/default_template/';
        $userview_prefix = $this->admindir.'/'.$this->admindir.'/';

        $view_path = $userview_prefix.$view;

        if (is_file(APPPATH.'/views/'.$userview_prefix.$view.'.php') == false) {
            // 개별디자인에는 존재하지 않음.

            if (is_file(APPPATH.'/views/'.$default_prefix.$view.'.php') !== false) {
                // default template 에 존재. 이 파일을 이용.
                $view_path = $default_prefix.$view;
            } else {
                // 뷰 없음. Error!
                die($view.' 파일이 존재하지 않습니다.');
            }
        }

        //echo $view_path.PHP_EOL; exit;
        $this->load->view($view_path, $data);
    }
}
