<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH.'libraries/REST_Controller.php';


/**
 * @OA\Schema()
 */

class Dashboard extends REST_Controller {

 
    function __construct() {
        parent::__construct();

	/*
        $allow_ips = array(

            '14.129.31.215',    // joong

            // TEST SERVER
            '14.129.118.139',   // secu-kms 

            // REAL SERVER

        );

        $ip_idxs = array('REMOTE_ADDR', 'VPNIP');

        $ip = '';
        foreach($ip_idxs as $idx) {
            if(array_key_exists($idx, $_SERVER)) {
                $ip = $_SERVER[$idx];
                break;
            }
        }
        if(strlen($ip) < 1 || !in_array($ip, $allow_ips)) {
            $this->response([
                'status'    => false, 
                'message'   => 'UNAUTHORIZED'
            ], REST_Controller::HTTP_UNAUTHORIZED);
            return;
        }
	 */
    }


    public function index_get() {
   	echo 'index'; 
    }

    public function ping_get() {

        $req = $this->input->get();

        $req['IP'] = $_SERVER['REMOTE_ADDR'];
        $this->response($req, REST_Controller::HTTP_OK);

        /*
        $this->response([
            'status'    => false, 
            'message'   => 'METHOD NOT ALLOWED'
        ], REST_Controller::HTTP_METHOD_NOT_ALLOWED);
        */
    }
    public function ping_post() {
        $req = $this->input->post();

        $this->response([
            'status'    => false, 
            'message'   => 'BAD REQUEST'
        ], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function ping_put() {
        $this->response([
            'status'    => false, 
            'message'   => 'METHOD NOT ALLOWED'
        ], REST_Controller::HTTP_METHOD_NOT_ALLOWED);
    }
    public function ping_delete() {
        $this->response([
            'status'    => false, 
            'message'   => 'METHOD NOT ALLOWED'
        ], REST_Controller::HTTP_METHOD_NOT_ALLOWED);
    }



    // 자산유형별 모델 수량
    //public function models_get() {
    public function models_post() {

	$this->load->model(array('assets_model_tb_model'));

	$params = array();
	//$params['=']['am_assets_type_id'] = 1;
	$extras = array();
	$extras['fields'] = array('am_models_id', 'am_models_name', 'am_assets_type_id', 'COUNT(*) AS cnt');
	$extras['group_by'] = array('am_models_name');
	$extras['order_by'] = array('am_assets_type_id ASC', 'cnt DESC');

	$data = $this->assets_model_tb_model->getList($params, $extras)->getData();
	$data = $this->common->getDataByDuplPK($data, 'am_assets_type_id');
	//echo print_r($data);
        $this->response($data, REST_Controller::HTTP_OK);
    }


    // 독립/ VM 서비스별 수량
    public function service_get() {
    	
	$this->load->model(array(
            'assets_model_tb_model',
            'ip_tb_model',
            'vmservice_tb_model',
            'vmservice_ip_map_tb_model',
            'assets_ip_map_tb_model',
            'service_manage_tb_model',
        ));


    } 


    // 인력정보 현황 (날짜별 추가된 인원/탈퇴회원)
    public function people_get() {

	    /*
	     	SELECT DATE(pp_created_at), COUNT(*) as cnt 
		FROM people_tb
		WHERE between DATE A AND B
		GROUP BY DATE(pp_created_at)  
	     */
    

	    /*
	     	SELECT DATE(pp_outed_at), COUNT(*) as cnt 
		FROM people_tb
		WHERE between DATE A AND B
		GROUP BY DATE(pp_outed_at)  
	     */
    }






    // IP 현황 (IP 총수량 및 점유/비점유 현황)
    public function ip_get() {
    
    
    } 



}
