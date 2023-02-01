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



    
    public function getTrackData_get() {

        $req = $this->input->get();
        //$req = $this->input->post();


        if( ! isset($req['host']) || strlen($req['host']) < 1 ) {
            $this->response([
                'status'    => false, 
                'message'   => 'BAD REQUEST'
            ], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }


        /*
        {"query":{
          "bool":{
            "must":[
              {"term":{"type_keyword":"MYSQL"}},
              {"wildcard":{"user":"*mis*"}},
              {"range":{"@timestamp":{"gte":"2022-12-01 00:00:00","lte":"2022-12-31 23:59:59"}}}
            ]
          }},
          "sort":[{"@timestamp":"desc"}],
          "size":"25",
          "from":"0"
        }
        */

        // pay : 203.246.167.9
        // mis : 14.129.111.117


        // 하루전 데이터 부터 조회
        $params = array(
            'query' => array(
                'bool' => array(
                    'must' => array(
                       // 조건에 따른 배열 추가 
                    )
                )
            ),
            'sort' => array(
                array('regdate' => 'asc')
            ),
            'size' => 25,
            'from' => 0
        );


        // @command ex) select,update -> select update 변경 쿼리
        if( isset($req['command']) && strlen($req['command']) > 0 ) {
            $str_cmd = explode(',', $req['command']);
            $str_cmd = array_map('trim', $str_cmd);
            $str_cmd = array_filter($str_cmd);
            $str_cmd = implode(' ', $str_cmd);
            $params['query']['bool']['must'][] = array('match' => array('command' => $str_cmd));
        }



        // @type_keyword 조건 기본필수
        //$params['query']['bool']['must'][] = array('term' => array('type_keyword.keyword' => 'MYSQL'));
        //$params['query']['bool']['must'][] = array('term' => array('type_keyword' => 'MYSQL'));
        $params['query']['bool']['must'][] = array(
            'multi_match' => array(
                'query'     => 'MYSQL',
                'fields'    => array('type_keyword', 'type_keyword.keyword') 
            )
        );


        // @fromhost 조건
        switch(strtolower($req['host'])) {
            case 'mis':
                $fromhost = '14.129.111.117';
                break;

            case 'pay':
                $fromhost = '203.246.167.9';
                break;
        }   
        $params['query']['bool']['must'][] = array('term' => array('fromhost' => $fromhost));



        // @regdate 조건
        if( (isset($req['start']) && strlen($req['start']) > 0) &&  (isset($req['end']) && strlen($req['end']) > 0) ) {
            
        }else {

            $req['start'] = date('Y-m-d', strtotime("-1 months"));
            $req['end'] = date('Y-m-d');
        }
        // @doc_id 조건
        if( isset($req['doc_id']) && strlen($req['doc_id']) > 0 ) {
            $params['query']['bool']['must'][] = array('term' => array('doc_id.keyword' => $req['doc_id']));
        }else {
            $params['query']['bool']['must'][] = array('range' => array(
                    'regdate' => array(
                        'gte' => $req['start'].' 00:00:00', 
                        'lte' => $req['end'].' 23:59:59' 
                    )
                )
            );
        }

       
        // @size 조건 // 10000개 한계치
        if( isset($req['size']) && strlen($req['size']) > 0 ) {
            $params['size'] = $req['size'];
        }

        // @from 조건
        if( isset($req['from']) && strlen($req['from']) > 0 ) {
            $params['from'] = $req['from'];
        }
        //echo print_r($params); exit;


        $json_params = json_encode($params);
        //echo $json_params.PHP_EOL; exit;


        $this->load->library('elastic');
        $el_header = $this->elastic->get_auth_header();


        $index_name = 'syslogssh-*';
        $el_url = ELASTIC_SYSLOG_HOST.'/'.$index_name.'/_search';
        $el_result = $this->elastic->restful_curl($el_url, $json_params, 'GET', $timeout=10, $el_header);
               $el_result = json_decode($el_result, true);
        //echo print_r($el_result); exit;


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




        $data = array();
        foreach($el_result['hits']['hits'] as $el) {
            $data[$el['_source']['doc_id']] = $el['_source'];
        }
        


        // @Get doc_id 
        $doc_ids = array_keys($this->common->getDataBypk($data, 'doc_id'));
        //echo print_r($doc_ids); exit;
        if( sizeof($doc_ids) > 0 ) {

            $this->load->model(array(
                'ssh_track_map_tb_model',
            ));

            $params = array();
            $params['in']['stm_ssh_id'] = $doc_ids;
            $extras = array();
            $stm_data = $this->ssh_track_map_tb_model->getList($params, $extras)->getData();
            $stm_data = $this->common->getDataByPK($stm_data, 'stm_ssh_id');
        }

        // @Get UserName
        $user_ids = array_keys($this->common->getDataBypk($data, 'user'));
        if( sizeof($user_ids) > 0 ) {


            $this->load->model(array(
                'people_tb_model',
            ));


            $params = array();
            $params['in']['pp_login_id'] = $user_ids;
            $extras = array();
            $extras['fields'] = array('pp_id', 'pp_login_id', 'pp_name', 'pp_dept');
            $pp_data = $this->people_tb_model->getList($params, $extras)->getData();
            $pp_data = $this->common->getDataByPk($pp_data, 'pp_login_id');

            foreach($data as &$v) {
                $name = $v['name'];
                if( isset($pp_data[$v['user']]) ) {
                    $name = $pp_data[$v['user']]['pp_dept'].'>';
                    $name .= $pp_data[$v['user']]['pp_name'];
                }
                $v['name'] = $name;
                if( isset($pp_data[$v['user']]) ) {
                    $name = $pp_data[$v['user']]['pp_dept'].'>';
                    $name .= $pp_data[$v['user']]['pp_name'];
                }
                $v['name'] = $name;

                if( isset($stm_data[$v['doc_id']]) ) {
                    $v['stm_id'] = $stm_data[$v['doc_id']]['stm_id'];
                    $v['track_code'] = $stm_data[$v['doc_id']]['stm_track_code'];
                    $v['memo'] = $stm_data[$v['doc_id']]['stm_memo'];
                }else {
                    $v['stm_id'] = '';
                    $v['track_code'] = '';
                    $v['memo'] = '';
                }
            }
        }
        
        $this->response(
            $data, 
            REST_Controller::HTTP_OK
        );
        return;
    }




    //public function trackDBPut_get() {
    public function putTrackData_post() {

        /*
        $allow_ips = array(
            '210.217.16.28',   // localhost

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


        //$req = $this->input->post();
        $req = $_REQUEST;
        //echo print_r($req); exit;

        $log_array = array();

        if( ! isset($req['doc_id']) || strlen($req['doc_id']) < 1 ) {
            $this->response([
                'status'    => false, 
                'message'   => 'BAD REQUEST'
            ], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

        if( ! isset($req['track_code']) || strlen($req['track_code']) < 1 ) {
            $this->response([
                'status'    => false, 
                'message'   => 'BAD REQUEST'
            ], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

               $this->load->model(array(
            'ssh_track_map_tb_model',
            'history_tb_model',
        ));
       
        $up_params = array();
        $up_params['stm_ssh_id'] = $req['doc_id'];
        if( isset($req['track_code']) && strlen($req['track_code']) > 0 ) {
            $up_params['stm_track_code'] = $req['track_code'];
        }
        if( isset($req['memo']) && strlen($req['memo']) > 0 ) {
            $up_params['stm_memo'] = strip_tags($req['memo']);
        }



        $params = array();
        $params['=']['stm_ssh_id'] = $req['doc_id'];
        $stm_data = $this->ssh_track_map_tb_model->getList($params)->getData();


        $act_key = 0;
        if( sizeof($stm_data) < 1 ) {
            // INSERT

            $up_params['stm_created_at'] = date('Y-m-d H:i:s');
            $up_params['stm_updated_at'] = date('Y-m-d H:i:s');
        
            $log_array['params']['request'] = $up_params; 
            if( ! $this->ssh_track_map_tb_model->doInsert($up_params)->isSuccess()) {

                $log_array['params']['response'] = $this->ssh_track_map_tb_model->getErrorMsg();

                $this->_writeHistory('인하우스', 'PUT', $act_key, $log_array, 'ssh_track_map_tb');
                $this->response([
                    'status'    => false, 
                    'message'   => 'BAD REQUEST'
                ], REST_Controller::HTTP_BAD_REQUEST);  
            }else {
                               $act_key = $this->ssh_track_map_tb_model->getData();
            }
        
        }else {
            // UPDATE


            $stm_data = array_shift($stm_data);


            $log_array['prev_data'] = $this->ssh_track_map_tb_model->get($stm_data['stm_id'])->getData();
            $log_array['params']['request'] = $up_params; 
            if( ! $this->ssh_track_map_tb_model->doUpdate($stm_data['stm_id'], $up_params)->isSuccess()) {

                $log_array['params']['response'] = $this->ssh_track_map_tb_model->getErrorMsg();
                $this->_writeHistory('인하우스', 'PUT', $act_key, $log_array, 'ssh_track_map_tb');
                $this->response([
                    'status'    => false, 
                    'message'   => 'BAD REQUEST'
                ], REST_Controller::HTTP_BAD_REQUEST);  
                return;
            }else {
                               $act_key = $stm_data['stm_id'];
            }

        }
        $this->_writeHistory('인하우스', 'PUT', $act_key, $log_array, 'ssh_track_map_tb');
        $this->response(
            $act_key, 
            REST_Controller::HTTP_OK
        );
        return;
    }


    //public function trackDBDel_get() {
    public function delTrackData_post() {

        //$req = $this->input->get();
        //$req = $this->input->post();
        $req = $_REQUEST;

        $log_array = array();

        if( ! isset($req['stm_id']) || strlen($req['stm_id']) < 1 ) {
            $this->response([
                'status'    => false, 
                'message'   => 'BAD REQUEST'
            ], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }


               $this->load->model(array(
            'ssh_track_map_tb_model',
            'history_tb_model',
        ));
        $log_array = array();
        $log_array['params']['stm_id'] = $req['stm_id'];


        $prev_data = $this->ssh_track_map_tb_model->get($req['stm_id'])->getData();
        $log_array['prev_data'] = $prev_data;
        if (sizeof($prev_data) < 1) {
        
            $this->_writeHistory('인하우스', 'DELETE', $act_key, $log_array, 'ssh_track_map_tb');
            $this->response(
                'NO CONTENT',
                REST_Controller::HTTP_NO_CONTENT
            );
            return;

        }else {
        
            if( ! $this->ssh_track_map_tb_model->doDelete($req['stm_id'])->isSuccess()) {
 
                $log_array['params']['response'] = $this->ssh_track_map_tb_model->getErrorMsg();
                $this->_writeHistory('인하우스', 'DELETE', $act_key, $log_array, 'ssh_track_map_tb');
                $this->response([
                    'status'    => false, 
                    'message'   => 'BAD REQUEST'
                ], REST_Controller::HTTP_BAD_REQUEST);                 
                return;
            }else{

                $act_key = $req['stm_id'];
                $this->_writeHistory('인하우스', 'DELETE', $act_key, $log_array, 'ssh_track_map_tb');

                $this->response(
                    $act_key, 
                    REST_Controller::HTTP_OK
                );
                return;
            }
        }

    }


    private function _writeHistory($name, $mode, $act_key=0, $log_array=array(), $table) {

               $this->load->model(array(
            'history_tb_model',
        ));
        $params = array();
        $params['h_loginid'] = 'API';
        $params['h_name'] = $name;
        $params['h_ip'] = $_SERVER['REMOTE_ADDR'];
        $params['h_act_mode'] = $mode; 
        $params['h_act_key'] = $act_key;
        $params['h_serialize'] = serialize($log_array);
        $params['h_act_table'] = $table;
        $this->history_tb_model->doInsert($params);

    }


}
