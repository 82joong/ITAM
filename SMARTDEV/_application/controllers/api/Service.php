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
            '43.200.49.23',     // lab.makeshop.co.kr

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

        //echo $ip; exit;

        $this->load->helper('message');
    }



    public function getAssets_post() {

        $json_data = array(
            'is_success' => FALSE,
            'msg'       => ''
        );
        $req = $this->input->post();

        if( sizeof($req) < 1 || ! isset($req['am_id']) ) {
            $json_data['msg'] = getAlertMsg('REQUIRED_VALUES');    
            echo json_encode($json_data);
            return;
        }

        $this->load->model(array(
            'assets_model_tb_model',
        ));
        $params = array();
        $params['=']['am_id'] = $req['am_id'];
        $extras = array();
        $extras['limit'] = 50;

        $total_count = $this->assets_model_tb_model->getCount($params)->getData();
        $am_data = $this->assets_model_tb_model->getList($params, $extras)->getData();

        $json_data['is_success'] = true;
        $json_data['data'] = array( 'total_count' => $total_count );


        if( sizeof($am_data) < 1 ) {
            $json_data['data']['items'] = array();
        }else {
            $json_data['data']['items'] = $this->common->getDataByPK($am_data, 'am_id');
        }
        echo json_encode($json_data);
        return;
    }

    public function getVmservice_post() {

        $req = $this->input->post();
        //echo print_r($req);

        $json_data = array(
            'is_success' => FALSE,
            'msg'       => ''
        );
        $req = $this->input->post();

        if( sizeof($req) < 1 || ! isset($req['vms_id']) ) {
            $json_data['msg'] = getAlertMsg('REQUIRED_VALUES');    
            echo json_encode($json_data);
            return;
        }

        $this->load->model(array(
            'vmservice_tb_model',
        ));
        $params = array();
        $params['=']['vms_id'] = $req['vms_id'];
        $extras = array();
        $extras['limit'] = 50;

        $total_count = $this->vmservice_tb_model->getCount($params)->getData();
        $vms_data = $this->vmservice_tb_model->getList($params, $extras)->getData();

        $json_data['is_success'] = true;
        $json_data['data'] = array( 'total_count' => $total_count );


        if( sizeof($vms_data) < 1 ) {
            $json_data['data']['items'] = array();
        }else {
            $json_data['data']['items'] = $this->common->getDataByPK($vms_data, 'vms_id');
        }
        echo json_encode($json_data);
        return;
    }


    public function getAssetsByService_post() {

        $json_data = array(
            'is_success' => FALSE,
            'msg'       => ''
        );
        $req = $this->input->post();

        $this->load->model(array(
            'assets_model_tb_model',
            'vmservice_tb_model',
        ));


        if( sizeof($req) < 1 || ! isset($req['vms_id']) ) {
            $json_data['msg'] = getAlertMsg('REQUIRED_VALUES');    
            echo json_encode($json_data);
            return;
        }

        $params = array();
        $params['=']['vms_id'] = $req['vms_id'];
        $params['join']['assets_model_tb'] = 'am_id = vms_assets_model_id';
        $extras = array();
        $extras['fields'] = array('vmservice_tb.*', 'assets_model_tb.*');
        $extras['limit'] = 50;
        $vms_data = $this->vmservice_tb_model->getList($params, $extras)->getData();

        $json_data['is_success'] = true;
        if( sizeof($vms_data) < 1 ) {
            $json_data['data'] = array();
        }else {
            $json_data['data'] = array_shift($vms_data);
        }
        echo json_encode($json_data);
        return;
    } 
    


    // 현재 그룹웨어 인원정보
    public function getEmployee_get() {


        $allow_ips = array(
            '14.129.31.215',    // joong
            '14.129.118.139',   // secu-kms 
            '43.200.49.23',     // lab.makeshop.co.kr
            '203.238.182.236',  // info.makeshop.co.kr
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

        echo $ip; exit;



        $this->load->library(array(
            'DaouData'
        ));

        $member_data = $this->daoudata->getData('MEMBER');
        echo json_encode($member_data);
        return;
    }
}
