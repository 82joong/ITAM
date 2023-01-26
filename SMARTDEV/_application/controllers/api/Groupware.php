<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH.'libraries/REST_Controller.php';


/**
 * @OA\Schema()
 */

class Groupware extends REST_Controller {

 
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



    //public function resetUserPw_post() {
    public function resetUserPw_get() {

        $json_data = array(
            'is_success' => FALSE,
            'msg'       => ''
        );
        $req = $this->input->post();

        /*
        if( sizeof($req) < 1 || ! isset($req['userid']) ) {
            $json_data['msg'] = getAlertMsg('REQUIRED_VALUES');    
            echo json_encode($json_data);
            return;
        }
        */

        echo $row['userid'] = '82joong';


        $GW_DB = $this->load->database('groupware', TRUE);
        $count_query = 'SELECT count(*) FROM members';

        if ($GW_DB->query($count_query)) {
            $query = $GW_DB->query($count_query);
            $row_cnt = $query->num_rows();
            echo $row_cnt;
        }else {
            echo 'FAIL DB';
            $GW_DB->close();
            exit;
        }

       
        echo json_encode($json_data);
        return;
    }

}
