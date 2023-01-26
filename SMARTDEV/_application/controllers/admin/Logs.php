<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include_once dirname(__FILE__).'/Base_admin.php';

class Logs extends Base_admin {


    public function admin() {

        $data = array();

        $this->load->library(array('elastic'));
        $req = $this->input->post();


        $this->load->helper('adminlog');
        $tag_map = getTagTypeMap();


		if(isset($req['mode']) && $req['mode'] == 'list') {


            $multiple_filters = array();
            $index_name = 'infolog-*';
            $params = $this->elastic->datatable_filter_to_params($req, $index_name, $multiple_filters);
            //echo print_r($params); exit;

            $params['track_total_hits'] = true; // total default 10000 => 해제
            if($params['from'] >= 10000) {

                $error_msg = '해당 리스트는 [10,000]개 이상 항목을 지원하지 않습니다.'.PHP_EOL;
                $error_msg .= '검색 조건 추가를 통해 리스트의 범위를 줄여주세요!';

                $json_data = new stdClass;
                $json_data->rows = array();
                $json_data->error_msg = $error_msg;
                echo json_encode($json_data);
                return;
            }


            /*
            if(isset($request['uid']) && strlen($request['uid']) > 0) {
                $params['sort'] = array(
                    $request['uid'] => array('order' => $request['sord']),
                );
            }
            */
            //echo print_r($params); exit;
            $json_params = json_encode($params);
            //echo $json_params; exit;


            $el_header = $this->elastic->get_auth_header();
            $el_url = ELASTIC_SYSLOG_HOST.'/'.$index_name.'/_search';
			$el_result = $this->elastic->restful_curl($el_url, $json_params, 'GET', $timeout=10, $el_header);
			$el_result = json_decode($el_result, true);
            //echo print_r($el_result); exit;

            $json_data = new stdClass;
            $json_data->draw = $req['draw'];
            if(isset($el_result['error'])) {
                //echo 'QUERY ERROR';
                //$json_data->error_msg = $el_result['error']['type'];
                //$json_data->status  = $el_result['status'];

                $json_data->recordsTotal = 0;
                $json_data->recordsFiltered = 0;
                $json_data->data = array();
                echo json_encode($json_data);
                return;

            }else if(isset($el_result['hits']) && ($this->elastic->get_hits_total($el_result) > 0) === FALSE) {
                //echo 'ROWS 0';
                $json_data->recordsTotal = 0;
                $json_data->recordsFiltered = 0;
                $json_data->data = array();
                echo json_encode($json_data);
                return;
            }
		

			$data = array();
            foreach($el_result['hits']['hits'] as $k=>$r){

                $sc = $r['_source'];

                // @tag
                $tag = $sc['connect_tag'];
                $tag_html = '<span class="badge badge-'.strtolower($tag).'">'.$tag.'<span>';
                

                // @content
                $service_html = $sc['service'];
                $content_html = $sc['content'];
                switch($sc['service']) {

                    case 'ssh2':
                        $content_html = '<code>'.$sc['content'].'</code>';
                        break;

                    case 'fail':
                        $service_html = '<span class="badge badge-danger">'.$sc['service'].'<span>';
                        $content_html = '<span class="color-danger-900">'.$sc['content'].'</span>';
                        break;

                    case 'check':
                        $service_html = '<span class="badge badge-warning">'.$sc['service'].'<span>';
                        break;

                    default:
                        break;
                }


                $row = array(
                    'date_timestamp'    => $sc['date_timestamp'],
                    'connect_tag'       => $tag_html,
                    'ip'                => $sc['ip'],
                    'service'           => $service_html, 
                    'login_id'          => $sc['login_id'],
                    'login_hname'       => $sc['login_hname'],
                    'sub_id'            => $sc['sub_id'],
                    'page'              => $sc['page'],
                    'content'           => $content_html,
                    'country_code'      => $sc['geoip']['country_code2'],
                );
                //echo print_r($row); exit;
				$data[] = $row;

            }
            //echo print_r($json_data);
			
            $count = $this->elastic->get_hits_total($el_result);

            $json_data->recordsTotal = $count;
            $json_data->recordsFiltered = $count;
            $json_data->data = $data;

            echo json_encode($json_data);
            return;
        }


        $data['tag_map'] = $this->common->genJqgridOption($tag_map, false);

		$this->_view('logs/admin', $data);
    }



  

    public function manage() {

        $data = array();


		$this->load->model(array(
            'people_tb_model',
        ));

        $this->load->library(array('elastic'));
        $req = $this->input->post();


        $this->load->helper('adminlog');
        $tag_map = getTagTypeMap();


		if(isset($req['mode']) && $req['mode'] == 'list') {

            //echo print_r($req);


            // service 필드 "manage" 고정
            $req['columns'][] = array(
                'data'          => 'service.keyword',
                'name'          => 'service.keyword',
                'searchable'    => true,
                'orderable'     => true,
                'search'        => array(
                    'value' => 'eq_%OP%_manage',
                    'regex' => false
                )
            );


            $multiple_filters = array();
            $index_name = 'infolog-*';
            $params = $this->elastic->datatable_filter_to_params($req, $index_name, $multiple_filters);
            //echo print_r($params); exit;

            $params['track_total_hits'] = true; // total default 10000 => 해제
            if($params['from'] >= 10000) {

                $error_msg = '해당 리스트는 [10,000]개 이상 항목을 지원하지 않습니다.'.PHP_EOL;
                $error_msg .= '검색 조건 추가를 통해 리스트의 범위를 줄여주세요!';

                $json_data = new stdClass;
                $json_data->rows = array();
                $json_data->error_msg = $error_msg;
                echo json_encode($json_data);
                return;
            }


            /*
            if(isset($request['uid']) && strlen($request['uid']) > 0) {
                $params['sort'] = array(
                    $request['uid'] => array('order' => $request['sord']),
                );
            }
            */
            //echo print_r($params); exit;
            $json_params = json_encode($params);
            //echo $json_params; exit;


            $el_header = $this->elastic->get_auth_header();
            $el_url = ELASTIC_SYSLOG_HOST.'/'.$index_name.'/_search';
			$el_result = $this->elastic->restful_curl($el_url, $json_params, 'GET', $timeout=10, $el_header);
			$el_result = json_decode($el_result, true);
            //echo print_r($el_result); exit;

            $json_data = new stdClass;
            $json_data->draw = $req['draw'];
            if(isset($el_result['error'])) {
                //echo 'QUERY ERROR';
                //$json_data->error_msg = $el_result['error']['type'];
                //$json_data->status  = $el_result['status'];

                $json_data->recordsTotal = 0;
                $json_data->recordsFiltered = 0;
                $json_data->data = array();
                echo json_encode($json_data);
                return;

            }else if(isset($el_result['hits']) && ($this->elastic->get_hits_total($el_result) > 0) === FALSE) {
                //echo 'ROWS 0';
                $json_data->recordsTotal = 0;
                $json_data->recordsFiltered = 0;
                $json_data->data = array();
                echo json_encode($json_data);
                return;
            }
		

            $emp_no_data = array();
            foreach($el_result['hits']['hits'] as $k=>$r){

                // @auth
                $auth_data = $this->_parseContent($r['_source']['url']['path'], $r['_source']['content']);

                $emp_no_data = array_merge($emp_no_data, $auth_data['add']);
                $emp_no_data = array_merge($emp_no_data, $auth_data['del']);

            }
            $emp_no_data = array_filter($emp_no_data);
            $emp_no_data = array_unique($emp_no_data);
            //echo print_r($emp_no_data); exit;

            $pp_data = array();
            if(sizeof($emp_no_data) > 0) {
                $params = array();
                $params['in']['pp_emp_number'] = $emp_no_data;
                $extras = array();
                $extras['fields'] = array('pp_id', 'pp_name', 'pp_emp_number');
                $pp_data = $this->people_tb_model->getList($params, $extras)->getData();
                $pp_data = $this->common->getDataByPK($pp_data, 'pp_emp_number');
            }

			$data = array();
            foreach($el_result['hits']['hits'] as $k=>$r){

                $sc = $r['_source'];

                // @auth
                $auth_data = $this->_parseContent($sc['url']['path'], $sc['content']);

 
                $add_txt = '';
                $auth_data['add'] = array_filter($auth_data['add']);
                if(sizeof($auth_data['add']) > 0) {
                    foreach($auth_data['add'] as $add) {
                        $add_txt .= '<div>';
                        if( isset($pp_data[$add]) ) {
                            $add_txt .= '<span class="badge border border-info text-info">'.$pp_data[$add]['pp_name'].'</span> ';
                            $add_txt .= '<span class="text-muted">('.$add.')</span>';
                        }else {
                            $add_txt = '<span class="text-muted">('.$add.')</span>';
                        }
                        $add_txt .= '</div>';
                    }
                }

                $del_txt = '';
                $auth_data['del'] = array_filter($auth_data['del']);
                if(sizeof($auth_data['del']) > 0) {
                    foreach($auth_data['del'] as $del) {
                        $del_txt .= '<div class="m-1">';
                        if( isset($pp_data[$del]) ) {
                            $del_txt .= '<span class="badge border border-danger text-danger">'.$pp_data[$del]['pp_name'].'</span> ';
                            $del_txt .= '<span class="text-muted">('.$del.')</span>';
                        }else {
                            $del_txt = '<span class="text-muted">('.$del.')</span>';
                        }
                        $del_txt .= '</div>';
                    }
                }

                // @content
                $content_html = $sc['content'];

                $row = array(
                    'date_timestamp'    => $sc['date_timestamp'],
                    'ip'                => $sc['ip'],
                    'login_id'          => $sc['login_id'],
                    'login_hname'       => $sc['login_hname'],
                    'sub_id'            => $sc['sub_id'],
                    'page'              => $sc['page'],
                    'content'           => $content_html,
                    'auth_tag'          => '<span class="badge badge-primary p-1">'.$auth_data['tag'].'</span>',
                    'auth_site'         => $auth_data['url'],
                    'auth_add'          => $add_txt,
                    'auth_del'          => $del_txt,
                );
                //echo print_r($row); exit;
				$data[] = $row;

            }
            //echo print_r($json_data);
			
            $count = $this->elastic->get_hits_total($el_result);

            $json_data->recordsTotal = $count;
            $json_data->recordsFiltered = $count;
            $json_data->data = $data;

            echo json_encode($json_data);
            return;
        }


		$this->_view('logs/manage', $data);
    }


    private function _parseContent($path, $content) {

        $res_auth = array(
            'tag'   => '',
            'url'   => '',
            'add'   => array(),
            'del'   => array()
        );

        if( strpos($path, 'copy') !== FALSE ) {

            $res = explode('에 ', $content);

            if( strpos($res[0], ':') !== FALSE ) {
                $res_tag = explode(':', $res[0]);
                $res_auth['tag'] = $res_tag[0];
                $res_auth['url'] = $res_tag[1];
            }else {
                $res_auth['url'] = $res[0];
            }

            $res_add = explode('권한', $res[1]);
            $res_auth['add'] = explode(',', $res_add[0]);


        }else if( strpos($path, 'menuadmin_pop') !== FALSE ) {

            $res = explode('(', $content);

            $res_tag = explode(':', $res[0]);
            $res_auth['tag'] = $res_tag[0];

            $res_url = explode(')', $res[1]);
            $res_auth['url'] = $res_url[0];

            $res_add = explode(')', $res[2]);
            $res_auth['add'] = explode(',', $res_add[0]);

            $res_del = explode(')', $res[3]);
            $res_auth['del'] = explode(',', $res_del[0]);
        
        }

        return $res_auth;
    }




}
