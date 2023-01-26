<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH.'libraries/REST_Controller.php';


/**
 * @OA\Schema()
 */

class Itam extends REST_Controller {

 
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
        if(strlen($ip) < 1 || !in_array($ip, $allow_ips)) {
            $this->response([
                'status'    => false, 
                'message'   => 'UNAUTHORIZED'
            ], REST_Controller::HTTP_UNAUTHORIZED);
            return;
        }
    }


    public function ping_get() {

        $req = $this->input->get();
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


    public function history_post() {
        $req = $this->input->post();

        //$this->common->write_history_log($sess, 'DOWNLOAD', $req['am_id'], $log_array, 'assets_model_tb');

        $this->response([
            'status'    => false, 
            'message'   => 'BAD REQUEST'
        ], REST_Controller::HTTP_BAD_REQUEST);
    }

}
