<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH.'libraries/REST_Controller.php';


/**
 * @OA\Schema()
 */

class Category extends REST_Controller {

    private $ip;


    function __construct() {
        parent::__construct();

        $allow_ips = array(
            '14.129.31.152',    // phoenixq
            '14.129.31.214',    // bwwjh
            '14.129.31.215',    // joong
            '14.129.31.216',    // lsyoung
            '14.129.31.217',    // bigtuna
            '14.129.31.136',    // yun3019
            '14.129.31.137',    // maginc3

            // TEST SERVER
            '14.129.120.215',  // testdev-ham
            '14.129.120.216',  // testdev-ham
            '14.129.120.229',  // testdev15

            // REAL SERVER
            '14.129.120.183',   // allmall
            '14.129.120.184',   // vita-goods
            '14.129.120.155',   // shiptob
            //'14.129.120.155',   // TODO. Lafayette SERVER 
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




    public function list_get() {
        $this->response([
            'status'    => false, 
            'message'   => 'METHOD NOT ALLOWED'
        ], REST_Controller::HTTP_METHOD_NOT_ALLOWED);
    }
    public function list_post() {
        $req = $this->input->post();

        if( ! isset($req['market']) || strlen($req['market']) < 1 ) {
            $this->response([
                'status'    => false, 
                'message'   => 'BAD REQUEST'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $this->load->helper($req['market']);
        $category_tree_map = getCategoryByName('name_ko', '', 'serial'); // tree 형식으로 카테고리 노출
        $this->response($category_tree_map, REST_Controller::HTTP_OK);
    }
    public function list_put() {
        $this->response([
            'status'    => false, 
            'message'   => 'METHOD NOT ALLOWED'
        ], REST_Controller::HTTP_METHOD_NOT_ALLOWED);
    }
    public function list_delete() {
        $this->response([
            'status'    => false, 
            'message'   => 'METHOD NOT ALLOWED'
        ], REST_Controller::HTTP_METHOD_NOT_ALLOWED);
    }

}
