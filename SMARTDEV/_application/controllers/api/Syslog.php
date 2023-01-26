<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH.'libraries/REST_Controller.php';


/**
 * @OA\Schema()
 */

class Syslog extends REST_Controller {


    private $_indexname;
 
    function __construct() {
        parent::__construct();

        $url_data = parse_url($_SERVER['HTTP_REFERER']);

        $allow_hosts = array(
            'itam.82joong.joong.co.kr'  
        );
        
        if( ! in_array($url_data['host'], $allow_hosts) ) {
            $this->response([
                'status'    => false, 
                'message'   => 'UNAUTHORIZED'
            ], REST_Controller::HTTP_UNAUTHORIZED);
            return;
        }


        $this->load->library('elastic');
        $this->_indexname = 'syslog-'.date('Y');

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


    public function listHost_post() {
    //public function listHost_get() {

        //echo 'GET'.PHP_EOL.'<br />';

        $from_date = date('Y-m-d H:i:s', time()-86400);


        // 하루전 데이터 부터 조회
        $params = array(
            'query' => array(
                'range' => array(
                    'regdate' => array(
                        'gte' => $from_date 
                    )
                )
            ),

            'aggs' => array(
                'group_by_host' => array(
                    'terms' => array(
                        'field' => 'host',
                        'order' => array('_key' => 'ASC'),
                        'size' => 2000
                    )
                )
            )
        );
        //echo print_r($params); exit;
        $json_params = json_encode($params);


        $this->load->library('elastic');
        $el_header = $this->elastic->get_auth_header();
        $el_url = ELASTIC_SYSLOG_HOST.'/'.$this->_index_name.'/_search?size=0';
        $el_result = $this->elastic->restful_curl($el_url, $json_params, 'GET', $timeout=10, $el_header);
		$el_result = json_decode($el_result, true);
        //echo print_r($el_result);


        $json_data = new stdClass;
        $json_data->draw = $req['draw'];
        if(isset($el_result['error'])) {
            //echo 'QUERY ERROR';
            $this->response([
                'status'    => false, 
                'message'   => 'BAD REQUEST'
            ], REST_Controller::HTTP_BAD_REQUEST);

        }else if(isset($el_result['hits']) && ($this->elastic->get_hits_total($el_result) > 0) === FALSE) {
            //echo 'ROWS 0';
            $this->response([
                'status'    => true, 
                'message'   => 'NO CONTENT'
            ], REST_Controller::HTTP_NO_CONTENT);
        }

        $host_data = $this->common->getDataByPK($el_result['aggregations']['group_by_host']['buckets'], 'key');
        $data = array_keys($host_data);
        //echo print_r($data);
        $this->response(
            $data, 
            REST_Controller::HTTP_OK
        );

    }

    /*
    public function listHost_post() {
        echo 'POST';
    }

    public function listHost_put() {
        echo 'PUT';
    }

    public function listHost_delete() {
        echo 'DELETE';
    }
    */
}
