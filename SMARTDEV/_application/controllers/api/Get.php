<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH.'libraries/REST_Controller.php';


/**
 * @OA\Schema()
 */

class Service extends REST_Controller {

 
    function __construct() {
        parent::__construct();

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
        /* 클라이언트에서 조회하는 Action 에 대한 검증이 있어서 일단 예외처리
        if(strlen($ip) < 1 || !in_array($ip, $allow_ips)) {
            $this->response([
                'status'    => false, 
                'message'   => 'UNAUTHORIZED'
            ], REST_Controller::HTTP_UNAUTHORIZED);
            return;
        }
        */

        $this->load->helper('message');
    }


    public function assets_post() {

        $req = $this->input->post();
        //echo print_r($req);

        $json_data = array(
            'is_success' => FALSE,
            'msg'       => ''
        );
        $req = $this->input->post();

        if( ! isset($req['q']) || strlen($req['q']) < 1 ) {
            $json_data['msg'] = getAlertMsg('REQUIRED_VALUES');    
            echo json_encode($json_data);
            return;
        }


        $this->load->model(array(
            'assets_model_tb_model',
        ));
        $params = array();
        $params['raw'] = array(
            "am_name LIKE '%".$req['q']."%' OR am_models_name LIKE '%".$req['q']."%' OR am_serial_no LIKE '%".$req['q']."%'"
        );
        $extras = array();
        $extras['fields'] = array(
            'am_id AS id', 'am_id', 'am_name', 'am_models_name', 'am_serial_no', 'am_vmware_name'
        );
        $extras['order_by'] = array('am_id DESC');

        $extras['offset'] = 0;
        if( isset($req['page']) ) {
            $extras['offset'] = (($req['page']*1)-1) * 20;
        }
        $extras['limit'] = 20;

        $total_count = $this->assets_model_tb_model->getCount($params)->getData();
        $am_data = $this->assets_model_tb_model->getList($params, $extras)->getData();

        $json_data['is_success'] = true;
        $json_data['data'] = array( 'total_count' => $total_count );


        if( sizeof($am_data) < 1 ) {
            $json_data['data']['items'] = array();
        }else {
            $json_data['data']['items'] = $am_data;
        }
        echo json_encode($json_data);
        return;
    }



    // IP 가 할당되지 않은 자산
    public function empty_ip_assets_post() {

        $req = $this->input->post();
        //echo print_r($req);
      
        $json_data = array(
            'is_success' => FALSE,
            'msg'       => ''
        );
        $req = $this->input->post();

        if( ! isset($req['q']) || strlen($req['q']) < 1 ) {
            $json_data['msg'] = getAlertMsg('REQUIRED_VALUES');    
             echo json_encode($json_data);
            return;
        }


		$this->load->model(array(
            'assets_model_tb_model',
            'assets_ip_map_tb_model',
        ));
        $params = array();
        $params['left_join']['assets_ip_map_tb'] = 'aim_assets_model_id = am_id';
        $params['raw'] = array(
            "am_models_name LIKE '%".$req['q']."%' OR am_serial_no LIKE '%".$req['q']."%' OR am_name LIKE '%".$req['q']."%' AND aim_id IS NULL"
        );
        $extras = array();
        $extras['fields'] = array(
            'am_id AS id', 'am_id', 'am_name', 'am_models_name', 'am_serial_no', 'am_rack_code'
        );
        $extras['order_by'] = array('am_id DESC');

        $extras['offset'] = 0;
        if( isset($req['page']) ) {
            $extras['offset'] = (($req['page']*1)-1) * 20;
        }
        $extras['limit'] = 20;

        $total_count = $this->assets_model_tb_model->getCount($params)->getData();
        $am_data = $this->assets_model_tb_model->getList($params, $extras)->getData();

        $json_data['is_success'] = true;
        $json_data['data'] = array( 'total_count' => $total_count );
        
        // DEBUG
        //$json_data['req'] = $req;

        if( sizeof($am_data) < 1 ) {
            $json_data['data']['items'] = array();
        }else {
            $json_data['data']['items'] = $am_data;
        }
        echo json_encode($json_data);
        return;
    }






    public function people_post() {

        $req = $this->input->post();
        //echo print_r($req);

        $json_data = array(
            'is_success' => FALSE,
            'msg'       => ''
        );
        $req = $this->input->post();

        if( ! isset($req['q']) || strlen($req['q']) < 1 ) {
            $json_data['msg'] = getAlertMsg('REQUIRED_VALUES');    
            echo json_encode($json_data);
            return;
        }


        $this->load->model(array(
            'people_tb_model',
            'company_tb_model',
        ));
        $params = array();
        $params['join']['company_tb'] = 'c_id = pp_company_id';
        $params['raw'] = array(
            "pp_name LIKE '%".$req['q']."%' OR pp_email LIKE '%".$req['q']."%'"
        );
        $extras = array();

        // "id" 값 반드시 필요
        $extras['fields'] = array(
            'pp_id AS id', 'pp_id', 'pp_name', 'pp_email', 'pp_dept', 'c_name'
        );
        $extras['order_by'] = array('pp_name ASC');

        $extras['offset'] = 0;
        if( isset($req['page']) ) {
            $extras['offset'] = (($req['page']*1)-1) * 20;
        }
        $extras['limit'] = 20;

        $total_count = $this->people_tb_model->getCount($params)->getData();
        $am_data = $this->people_tb_model->getList($params, $extras)->getData();

        $json_data['is_success'] = true;
        $json_data['data'] = array( 'total_count' => $total_count );


        if( sizeof($am_data) < 1 ) {
            $json_data['data']['items'] = array();
        }else {
            $json_data['data']['items'] = $am_data;
        }
        echo json_encode($json_data);
        return;
    }


    /*

        VMWare로 등록된 assets 정보 검색
    
     */
    public function vmware_assets_post() {

        $req = $this->input->post();
        //echo print_r($req);
      
        $json_data = array(
            'is_success' => FALSE,
            'msg'       => ''
        );
        $req = $this->input->post();

        if( ! isset($req['q']) || strlen($req['q']) < 1 ) {
            $json_data['msg'] = getAlertMsg('REQUIRED_VALUES');    
             echo json_encode($json_data);
            return;
        }


		$this->load->model(array(
            'assets_model_tb_model',
        ));
        $params = array();
        $params['join']['assets_ip_map_tb'] = 'aim_assets_model_id = am_id';
        $params['join']['ip_tb'] = 'aim_ip_id = ip_id';

        $raw_query = "ip_class_category = 'VMWARE' ";
        $raw_query .= "AND (am_models_name LIKE '%".$req['q']."%' OR am_serial_no LIKE '%".$req['q']."%' OR am_name LIKE '%".$req['q']."%')";

        $params['raw'] = array($raw_query);
        $extras = array();
        $extras['fields'] = array(
            'am_id AS id', 'am_id', 'am_name', 'am_models_name', 'am_vmware_name', 'am_serial_no', 'am_rack_code'
        );
        $extras['order_by'] = array('am_id DESC');

        $extras['offset'] = 0;
        if( isset($req['page']) ) {
            $extras['offset'] = (($req['page']*1)-1) * 20;
        }
        $extras['limit'] = 20;

        $total_count = $this->assets_model_tb_model->getCount($params)->getData();
        $am_data = $this->assets_model_tb_model->getList($params, $extras)->getData();

        $json_data['is_success'] = true;
        $json_data['data'] = array( 'total_count' => $total_count );
        
        if( sizeof($am_data) < 1 ) {
            $json_data['data']['items'] = array();
        }else {
            $json_data['data']['items'] = $am_data;
        }
        echo json_encode($json_data);
        return;
    }




    public function not_idrac_post() {

        $req = $this->input->post();
        //echo print_r($req);
      
        $json_data = array(
            'is_success' => FALSE,
            'msg'       => ''
        );
        $req = $this->input->post();

        if( ! isset($req['q']) || strlen($req['q']) < 1 ) {
            $json_data['msg'] = getAlertMsg('REQUIRED_VALUES');    
             echo json_encode($json_data);
            return;
        }

		$this->load->model(array(
            'assets_model_tb_model',
        ));


        
        $raw_query = "(am_models_name LIKE '%".$req['q']."%' OR am_serial_no LIKE '%".$req['q']."%' OR am_name LIKE '%".$req['q']."%')";

        $params = array();
        $params['join']['assets_ip_map_tb'] = 'aim_assets_model_id = am_id';
        $params['join']['ip_tb'] = 'aim_ip_id = ip_id';
        $params['=']['ip_class_category'] = 'IDRAC';
        $params['raw'] = array($raw_query);
        $extras = array();
        $extras['fields'] = array('am_id');
        $am_ids = $this->assets_model_tb_model->getList($params, $extras)->getData();
        $am_ids = array_keys($this->common->getDataByPK($am_ids, 'am_id'));



        $params = array();
        if(sizeof($am_ids) > 0) {
            $params['not in']['am_id'] = $am_ids;
        }
        $params['raw'] = array($raw_query);
        $extras = array();
        $extras['fields'] = array(
            'am_id AS id', 'am_id', 'am_name', 'am_models_name', 'am_vmware_name', 'am_serial_no', 'am_rack_code'
        );
        $extras['order_by'] = array('am_id DESC');
        $extras['offset'] = 0;
        if( isset($req['page']) ) {
            $extras['offset'] = (($req['page']*1)-1) * 20;
        }
        $extras['limit'] = 20;

        $total_count = $this->assets_model_tb_model->getCount($params)->getData();
        $am_data = $this->assets_model_tb_model->getList($params, $extras)->getData();
        //echo $this->assets_model_tb_model->getLastQuery(); exit;

        $json_data['is_success'] = true;
        $json_data['data'] = array( 'total_count' => $total_count );
        
        if( sizeof($am_data) < 1 ) {
            $json_data['data']['items'] = array();
        }else {
            $json_data['data']['items'] = $am_data;
        }
        echo json_encode($json_data);
        return;
    }



    // Direct IP 할당 할 수 있는 Assets
    // VMWARE IP & Direct(PUB/PRI) 할당된 자산을 제외한 자산
    public function direct_post() {

        $req = $this->input->post();
        //echo print_r($req);
      
        $json_data = array(
            'is_success' => FALSE,
            'msg'       => ''
        );
        $req = $this->input->post();

        if( ! isset($req['q']) || strlen($req['q']) < 1 ) {
            $json_data['msg'] = getAlertMsg('REQUIRED_VALUES');    
             echo json_encode($json_data);
            return;
        }


		$this->load->model(array(
            'assets_model_tb_model',
            'direct_ip_map_tb_model',
            'ip_tb_model',
        ));

        $raw_query = "(am_models_name LIKE '%".$req['q']."%' OR am_serial_no LIKE '%".$req['q']."%' OR am_name LIKE '%".$req['q']."%')";

        // 제외할 Assets id
        $params = array();
        $params['join']['direct_ip_map_tb'] = 'dim_assets_model_id = am_id';
        $params['join']['ip_tb'] = 'dim_ip_id = ip_id';

        // VMWARE or Direct, Direct = (PUBLIC, PRIVATE)
        $params['in']['ip_class_category'] = array('VMWARE', 'PUBLIC', 'PRIVATE');
        $params['raw'] = array($raw_query);
        $extras = array();
        $extras['fields'] = array('am_id');

        $not_in_direct = $this->assets_model_tb_model->getList($params, $extras)->getData();
        $not_in_direct = array_keys($this->common->getDataByPK($not_in_direct, 'am_id'));
        


        $params = array();
        if(sizeof($not_in_direct) > 0) {
            $params['not_in']['am_id'] = $not_in_direct;
        }
        $params['raw'] = array($raw_query);
        $extras = array();
        $extras['fields'] = array(
            'am_id AS id', 'am_id', 'am_name', 'am_models_name', 'am_serial_no', 'am_rack_code'
        );
        $extras['order_by'] = array('am_id DESC');

        $extras['offset'] = 0;
        if( isset($req['page']) ) {
            $extras['offset'] = (($req['page']*1)-1) * 20;
        }
        $extras['limit'] = 20;

        $total_count = $this->assets_model_tb_model->getCount($params)->getData();
        $am_data = $this->assets_model_tb_model->getList($params, $extras)->getData();

        $json_data['is_success'] = true;
        $json_data['data'] = array( 'total_count' => $total_count );
        

        if( sizeof($am_data) < 1 ) {
            $json_data['data']['items'] = array();
        }else {
            $json_data['data']['items'] = $am_data;
        }
        echo json_encode($json_data);
        return;
    }



    public function rack_post() {

        $req = $this->input->post();
        //echo print_r($req);

        $json_data = array(
            'is_success' => FALSE,
            'msg'       => ''
        );
        $req = $this->input->post();

        if( ! isset($req['q']) || strlen($req['q']) < 1 ) {
            $json_data['msg'] = getAlertMsg('REQUIRED_VALUES');    
            echo json_encode($json_data);
            return;
        }


        $this->load->model(array(
            'rack_tb_model',
            'location_tb_model',
        ));
        $params = array();
        $params['raw'] = array(
            "r_code LIKE '%".$req['q']."%' OR l_name LIKE '%".$req['q']."%' OR l_address LIKE '%".$req['q']."%' OR l_memo LIKE '%".$req['q']."%'"
        );
        $params['join']['location_tb'] = 'l_id = r_location_id';
        $extras = array();
        $extras['fields'] = array(
            'r_id AS id', 'r_code', 'l_id', 'l_name', 'l_code', 'l_manager_name', 'l_address', 'l_memo' 
        );
        $extras['order_by'] = array('r_code DESC');

        $extras['offset'] = 0;
        if( isset($req['page']) ) {
            $extras['offset'] = (($req['page']*1)-1) * 20;
        }
        $extras['limit'] = 20;

        $total_count = $this->rack_tb_model->getCount($params)->getData();
        $rack_data = $this->rack_tb_model->getList($params, $extras)->getData();

        $json_data['is_success'] = true;
        $json_data['data'] = array( 'total_count' => $total_count );


        if( sizeof($rack_data) < 1 ) {
            $json_data['data']['items'] = array();
        }else {
            $json_data['data']['items'] = $rack_data;
        }
        echo json_encode($json_data);
        return;
    }





    // VMService
    public function service_post() {

        $req = $this->input->post();
        //echo print_r($req);

        $json_data = array(
            'is_success' => FALSE,
            'msg'       => ''
        );
        $req = $this->input->post();

        if( ! isset($req['q']) || strlen($req['q']) < 1 ) {
            $json_data['msg'] = getAlertMsg('REQUIRED_VALUES');    
            echo json_encode($json_data);
            return;
        }


        $this->load->model(array(
            'vmservice_tb_model',
            'ip_tb_model',
            'vmservice_ip_map_tb_model',
        ));
        $params = array();

        $params['<']['vms_alias_id'] = 1;   // alias service 제외
        $params['raw'] = array(
            "vms_name LIKE '%".$req['q']."%' OR vms_memo LIKE '%".$req['q']."%' OR ip_address LIKE '%".$req['q']."%' OR ip_memo LIKE '%".$req['q']."%'"
        );
        $params['join']['vmservice_ip_map_tb'] = 'vim_vmservice_id = vms_id';
        $params['join']['ip_tb'] = 'ip_id = vim_ip_id';
        $extras = array();
        $extras['fields'] = array(
            'vms_id AS id', 'vms_id', 'vms_name', 'vms_memo', 'ip_id', 'ip_address', 'ip_memo', 'vim_id'
        );
        $extras['order_by'] = array('vms_id DESC');

        $extras['offset'] = 0;
        if( isset($req['page']) ) {
            $extras['offset'] = (($req['page']*1)-1) * 20;
        }
        $extras['limit'] = 20;

        $total_count = $this->vmservice_tb_model->getCount($params)->getData();
        $vms_data = $this->vmservice_tb_model->getList($params, $extras)->getData();

        $json_data['is_success'] = true;
        $json_data['data'] = array( 'total_count' => $total_count );


        if( sizeof($vms_data) < 1 ) {
            $json_data['data']['items'] = array();
        }else {
            $json_data['data']['items'] = $vms_data;
        }
        echo json_encode($json_data);
        return;
    }

    
}
