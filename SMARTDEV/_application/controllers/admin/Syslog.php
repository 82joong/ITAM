<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include_once dirname(__FILE__).'/Base_admin.php';

class Syslog extends Base_admin {


    public function __construct() {
        parent::__construct();
    }


    public function top() {

		$this->load->model(array(
            'vmservice_host_map_tb_model',
        ));

        $this->load->helper('syslog');
        $this->load->library(array('elastic', 'monitor'));

        $req = $this->input->post();
 
		if(isset($req['mode']) && $req['mode'] == 'list') {

            // mapping service 가져오기
            $params = array();
            $params['>=']['vhm_vmservice_id'] = 1;
            $vhm_data = $this->vmservice_host_map_tb_model->getList($params, array())->getData();
            $vhm_data = $this->common->getDataByPK($vhm_data, 'vhm_elk_host_name');
            //echo print_r($vhm_data); exit;


            //echo print_r($req); exit;

            // START =========================== alert 검색 filter
            $bool = array();
            if($req['columns'][1]['name'] == 'alert' &&  strlen($req['columns'][1]['search']['value']) > 0) {

                $values = explode('_%OP%_', $req['columns'][1]['search']['value']);
                $op = $values[1];

                switch($op) {

                    case 'ALL':
                        $port_limit = '['.implode(',', PORT_MAX).']';
                        $script = $this->monitor->disk_alert_script(DISK_MAX);
                        $script .= $this->monitor->port_alert_script($port_limit);
                        $script .= "isPortAlert(doc['parsed_port']) == true || isDiskAlert(doc['parsed_disk']) == true;";
                        $bool['filter'] = $this->monitor->set_script($script); 

                        $fields = array('CPU', 'SWAP', 'MEM');
                        foreach($fields as $field) {
                            $max_value = constant($field.'_MAX');
                            foreach($req['columns'] as $col) {
                                if($col['name'] == strtolower($field)) {
                                    $col['search']['value'] = '';
                                    $bool['should'][] = $this->monitor->set_should_limit($col['name'], $max_value);
                                }
                            }
                        }
                        break;

                    case 'CPU':
                    case 'SWAP':
                    case 'MEM':
                        $max_value = constant($op.'_MAX');
                        foreach($req['columns'] as &$col) {
                            if($col['name'] == strtolower($op)) {
                                $col['search']['value'] = '';
                                $bool['should'][] = $this->monitor->set_should_limit($col['name'], $max_value);
                            }
                        }
                        break;

                    case 'DISK':
                        $max_value = constant($op.'_MAX');
                        $script = $this->monitor->disk_alert_script($max_value);
                        $script .= "isDiskAlert(doc['parsed_disk']) == true;";
                        $bool['filter'] = $this->monitor->set_script($script); 
                        break;

                    case 'PORT':
                        $max_value = constant($op.'_MAX');
                        $max_value = '['.implode(',', $max_value).']';
                        $script = $this->monitor->port_alert_script($max_value);
                        $script .= "isPortAlert(doc['parsed_port']) == true;";
                        $bool['filter'] = $this->monitor->set_script($script); 
                        break;

                }
                $req['columns'][1]['search']['value'] = '';
            }
            // END =========================== alert 검색 filter


            $multiple_filters = array();
            $index_name = 'syslog-'.date('Y');
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


            if(isset($request['sidx']) && strlen($request['sidx']) > 0) {
                $params['sort'] = array(
                    $request['sidx'] => array('order' => $request['sord']),
                );
            }

            if( sizeof($bool) > 0 ) {
                $params['query']['bool'] = $bool;
            }
            //echo print_r($params); //exit;
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

                $alert_data = array();
                $cpu_class = '';
                if($r['_source']['cpu'] > CPU_MAX) {
                    $cpu_class = 'text-danger fw-700';
                    array_push($alert_data, 'cpu'); 
                }
                $mem_class = '';
                if($r['_source']['mem'] > MEM_MAX) {
                    $mem_class = 'text-danger fw-700';
                    array_push($alert_data, 'mem'); 
                }
                $swap_class = '';
                if($r['_source']['swap'] > SWAP_MAX) {
                    $swap_class = 'text-danger fw-700';
                    array_push($alert_data, 'swap'); 
                }
                $disk = parseDiskToHtml($r['_source']['disk']);
                if($disk['is_alert'] == TRUE) array_push($alert_data, 'disk');
                $top = parseTopToHtml($r['_source']);
                if($top['is_alert'] == TRUE) array_push($alert_data, 'top');
                $port = parsePortToHtml($r['_source']['listen_port']);
                if($port['is_alert'] == TRUE) array_push($alert_data, 'port');


                $alert = parseAlertToHtml($alert_data);


                if(isset($vhm_data[$r['_source']['host']])) {
                    $service_id = $vhm_data[$r['_source']['host']]['vhm_vmservice_id'];
                    $class = 'badge badge-warning fs-sm';
                    $host = '<a href="/admin/vmservice/detail/'.$service_id.'" target="_blank" class="'.$class.'">';
                    $host .= $r['_source']['host'];
                    $host .= '<i class="fal fa-external-link-square ml-1"></i></a>';
                }else {
                    $host = '<span class="badge badge-primary fs-sm">';
                    $host .= $r['_source']['host'];
                    $host .= '</span>';
                }

                $row = array(
                    '_id'           => $r['_id'],
                    'host'          => $host, 
                    'alert'         => $alert,
                    'cpu_cnt'       => $r['_source']['cpu_cnt'],
                    'cpu'           => '<span class="'.$cpu_class.'">'.number_format($r['_source']['cpu'], 2).'</span>',
                    'mem'           => '<span class="'.$mem_class.'">'.number_format($r['_source']['mem'], 2).'</span>',
                    'swap'          => '<span class="'.$swap_class.'">'.number_format($r['_source']['swap'], 2).'</span>',
                    'load1'         => number_format($r['_source']['load1'], 2),
                    'load2'         => number_format($r['_source']['load2'], 2),
                    'load3'         => number_format($r['_source']['load3'], 2),
                    'disk'          => $disk['html'],
                    'top'           => $top['html'],
                    'cpu_model'     => $r['_source']['cpu_model'],
                    'mysql_version' => $r['_source']['mysql_version'],
                    'mysql_size'    => $r['_source']['mysql_size'],
                    'listen_port'   => $port['html'],
                    'live'          => number_format($r['_source']['live']),
                    'regdate'       => $r['_source']['regdate'],
                    '@timestamp'    => date('Y-m-d H:i:s', strtotime($r['_source']['@timestamp'])),
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

        $data = array();


        $alert_fields = $this->monitor->get_alert_fields();
        $data['select_alert'] = $this->common->genJqgridOption($alert_fields, false);


		$this->_view('syslog/top', $data);
    }


 
    public function server() {

        $this->load->helper('syslog');

        $facility_map = getFacilityMap();
        $severity_map = getSeverityMap();
        $category_map = getSysCateMap();

        $req = $this->input->post();
        //echo print_r($req);
 
		if(isset($req['mode']) && $req['mode'] == 'list') {


            /*
            // @category Search 처리
            if(strlen($req['columns'][1]['search']['value']) > 0) {
                $req['columns'][1]['data'] = 'syslogtag';
                $req['columns'][1]['name'] = 'syslogtag';
                 
                if($req['columns'][1]['search']['value'] == 'VIRUS') {

                    if( strlen($req['columns'][7]['search']['value']) > 0 ) {
                        $req['columns'][7]['search']['value'] .= '_%AND%_[VIRUS]';
                    } else {
                        $req['columns'][7]['search']['value'] = 'cn_%OP%_[VIRUS]';
                    }
                }
            }
            */
            //echo print_r($req); exit;


            $this->load->library('elastic');

            $multiple_filters = array();
            $index_name = 'systemevents-'.date('Y');
            $params = $this->elastic->datatable_filter_to_params($req, $index_name, $multiple_filters);
            //echo print_r($params); //exit;

            //$params['track_total_hits'] = true; // total default 10000 => 해제
            if($params['from'] >= 10000) {
                $error_msg = '해당 리스트는 [10,000]개 이상 항목을 지원하지 않습니다.'.PHP_EOL;
                $error_msg .= '검색 조건 추가를 통해 리스트의 범위를 줄여주세요!';

                $json_data = new stdClass;
                $json_data->rows = array();
                $json_data->error_msg = $error_msg;
                echo json_encode($json_data);
                return;
            }


            if(isset($request['sidx']) && strlen($request['sidx']) > 0) {
                $params['sort'] = array(
                    $request['sidx'] => array('order' => $request['sord']),
                );
            }
            //echo print_r($params); exit;


            // CATEGORY 대문자 처리
            /*
            if(isset($params['query']['bool']['must'])) {
                foreach($params['query']['bool']['must'] as $mtrows) {
                }
            }
            */

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
		

            $category = '';
			$data = array();
            foreach($el_result['hits']['hits'] as $k=>$r){

                // @facility
                $facility = $r['_source']['facilitytag'];
                $facility_html = '<span class="badge badge-'.strtolower($facility).'">';
                $facility_html .= $facility;
                $facility_html .= '<span>';

                // @severity
                $severity = $r['_source']['prioritytag'];
                $severity_html = '<span class="badge badge-'.strtolower($severity).'">';
                $severity_html .= $severity;
                $severity_html .= '<span>';

                // @category
                /*
                $tag = explode(':', $r['_source']['syslogtag']);
                $tag = explode('[', $tag[0]);
                $category = isset($category_map[strtoupper($tag[0])]) ? $category_map[strtoupper($tag[0])] : 'ETC';
                if($category == 'GREENSHIED' && strpos($r['_source']['syslogtag'], '[VIRUS]') == 0) {
                    $category = 'VIRUS';
                }
                */
                $category = $r['_source']['category'];
                $category_html = '<span class="badge border border-'.strtolower($category).' text-'.strtolower($category).'">';
                $category_html .= $category;
                $category_html .= '<span>';

                
                $row = array(
                    'id'                    => $r['_source']['id'],
                    'category'              => $category_html,
                    'facility'              => $facility_html,
                    'priority'              => $severity_html,
                    'fromhost'              => $r['_source']['fromhost'], 
                    'hostname'              => $r['_source']['hostname'], 
                    'hosturl'               => $r['_source']['hosturl'], 
                    'eventmsg'              => $r['_source']['eventmsg'],
                    'userip'                => $r['_source']['userip'],
                    'message'               => $r['_source']['message'],
                    'devicereportedtime'    => date('y-m-d h:i:s', strtotime($r['_source']['devicereportedtime'])),
                    '@timestamp'            => date('y-m-d h:i:s', strtotime($r['_source']['@timestamp'])),
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

        $data = array();

        $data['facility_map'] = $this->common->genJqgridOption($facility_map, false);
        $data['severity_map'] = $this->common->genJqgridOption($severity_map, false);
        $data['category_map'] = $this->common->genJqgridOption($category_map, false);

		$this->_view('syslog/server', $data);
    }




    public function ssh() {

        $this->load->helper('syslog');

		$this->load->model(array(
            'people_tb_model',
        ));

        $type_map = getSSHTypeMap();

        $req = $this->input->post();
        //echo print_r($req);
 
		if(isset($req['mode']) && $req['mode'] == 'list') {

            $this->load->library('elastic');

            // Type [etc] 처리.
            if(strlen($req['columns'][1]['search']['value']) > 0 && strpos($req['columns'][1]['search']['value'], 'etc') > 0) {
                $ni = array_keys(array_diff($type_map, array('etc')));
                $ni_str = 'ni_%OP%_'.implode('_%AND%_', $ni);
                $req['columns'][1]['search']['value'] = $ni_str;
            }

		
	    
	    // doc_id 검색시, 키워드 처리 
	    if(strlen($req['columns'][0]['search']['value']) > 0) {
		$req['columns'][0]['data'] = 'doc_id.keyword';
		$req['columns'][0]['name'] = 'doc_id.keyword';
	    }


            //echo print_r($req);
            $multiple_filters = array();
            //$index_name = 'syslogssh-'.date('Y');
            $index_name = 'syslogssh-*';
            $params = $this->elastic->datatable_filter_to_params($req, $index_name, $multiple_filters);
            //echo print_r($params); exit;

            //$params['track_total_hits'] = true; // total default 10000 => 해제
            if($params['from'] >= 10000) {
                $error_msg = '해당 리스트는 [10,000]개 이상 항목을 지원하지 않습니다.'.PHP_EOL;
                $error_msg .= '검색 조건 추가를 통해 리스트의 범위를 줄여주세요!';

                $json_data = new stdClass;
                $json_data->rows = array();
                $json_data->error_msg = $error_msg;
                echo json_encode($json_data);
                return;
            }


            if(isset($request['sidx']) && strlen($request['sidx']) > 0) {
                $params['sort'] = array(
                    $request['sidx'] => array('order' => $request['sord']),
                );
            }
            //echo print_r($params); //exit;
            $json_params = json_encode($params);
            //echo $json_params; exit;

            $el_header = $this->elastic->get_auth_header();
            $el_url = ELASTIC_SYSLOG_HOST.'/'.$index_name.'/_search';
			$el_result = $this->elastic->restful_curl($el_url, $json_params, 'GET', $timeout=10, $el_header);
			$el_result = json_decode($el_result, true);
            //echo print_r($el_result); exit;

            $json_data = new stdClass;
            $json_data->draw = $req['draw'];
            if( !is_array($el_result) || isset($el_result['error']) ) {
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
		

            $category = '';
			$data = array();
	    //echo print_r($el_result); exit;
            foreach($el_result['hits']['hits'] as $k=>$r){

                $type_html = '<span class="badge badge-'.strtolower($r['_source']['type_keyword']).'">';
                $type_html .= $r['_source']['type_keyword'];
                $type_html .= '<span>';
                
                $row = array(
                    'id'            => $r['_source']['id'],
                    'doc_id'        => $r['_source']['doc_id'],
                    'user'          => $r['_source']['user'],
                    'name'          => $r['_source']['user'],
                    'userip'        => $r['_source']['userip'],
                    'fromhost'      => $r['_source']['fromhost'], 
                    'syslogtag'     => $r['_source']['syslogtag'],
                    'command'       => '<i class="fal fa-terminal mr-1 text-white"></i><code class="dark">'.$r['_source']['command'].'</code>',
                    'type_keyword'  => $type_html,
                    'message'       => htmlspecialchars($r['_source']['message']),
                    'regdate'       => $r['_source']['regdate'],
                    '@timestamp'    => date('Y-m-d H:i:s', strtotime($r['_source']['@timestamp'])),

		    'stm_id'	    => '',
                    'track_code'    => '',
                    'memo'          => '',
                );
                //echo print_r($row); exit;
				$data[] = $row;
            }
            //echo print_r($data); exit;

	    
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
                $stm_data = $this->ssh_track_map_tb_model->getList($params,$extras)->getData();
                $stm_data = $this->common->getDataByPK($stm_data, 'stm_ssh_id');
            }


            // @Get UserName
            $user_ids = array_keys($this->common->getDataBypk($data, 'user'));
            if( sizeof($user_ids) > 0 ) {
                $params = array();
                $params['in']['pp_login_id'] = $user_ids;
                $extras = array();
                $extras['fields'] = array('pp_id', 'pp_login_id', 'pp_name', 'pp_dept');
                $pp_data = $this->people_tb_model->getList($params, $extras)->getData();
                $pp_data = $this->common->getDataByPk($pp_data, 'pp_login_id');

                foreach($data as &$v) {
                    $name = $v['name'];
                    if( isset($pp_data[$v['user']]) ) {
                        $name = $pp_data[$v['user']]['pp_dept'].'<br />';
                        $name .= '<div class="badge border border-info text-info mt-1">'.$pp_data[$v['user']]['pp_name'].'</div>';
                    }
                    $v['name'] = $name;

		    if( isset($stm_data[$v['doc_id']]) ) {
			 $v['stm_id'] = $stm_data[$v['doc_id']]['stm_id'];
			 $v['track_code'] = $stm_data[$v['doc_id']]['stm_track_code'];
			 $v['memo'] = $stm_data[$v['doc_id']]['stm_memo'];
             	    }
                }
            }
            //echo print_r($data); exit;
			
            $count = $this->elastic->get_hits_total($el_result);

            $json_data->recordsTotal = $count;
            $json_data->recordsFiltered = $count;
            $json_data->data = $data;

            echo json_encode($json_data);
            return;
        }

        $data = array();

        $data['type_map'] = $this->common->genJqgridOption($type_map, false);
		$this->_view('syslog/ssh', $data);
    }



    public function top_dashboard() {
        $data = array();
		$this->_view('syslog/top_dashboard', $data);
    
    }
    public function total_dashboard() {
        $data = array();
		$this->_view('syslog/total_dashboard', $data);
    }


    public function ssh_dashboard() {
        $data = array();
		$this->_view('syslog/ssh_dashboard', $data);
    }

    public function server_dashboard() {
        $data = array();
		$this->_view('syslog/server_dashboard', $data);
    }



    public function virus() {

        $this->load->helper('syslog');

        $facility_map = getFacilityMap();
        $severity_map = getSeverityMap();

        $req = $this->input->post();
        //echo print_r($req);
 
		if(isset($req['mode']) && $req['mode'] == 'list') {

            $this->load->library('elastic');

            $multiple_filters = array();
            $index_name = 'systemevents-'.date('Y');
            $params = $this->elastic->datatable_filter_to_params($req, $index_name, $multiple_filters);
            //echo print_r($params); //exit;

            //$params['track_total_hits'] = true; // total default 10000 => 해제
            if($params['from'] >= 10000) {
                $error_msg = '해당 리스트는 [10,000]개 이상 항목을 지원하지 않습니다.'.PHP_EOL;
                $error_msg .= '검색 조건 추가를 통해 리스트의 범위를 줄여주세요!';

                $json_data = new stdClass;
                $json_data->rows = array();
                $json_data->error_msg = $error_msg;
                echo json_encode($json_data);
                return;
            }
            if(isset($request['sidx']) && strlen($request['sidx']) > 0) {
                $params['sort'] = array(
                    $request['sidx'] => array('order' => $request['sord']),
                );
            }


            // CATEGORY VIRUS 
            $params['query']['bool']['must'][] = array('term' => array('category' => 'VIRUS'));

            //echo print_r($params); //exit;
            $json_params = json_encode($params);
            //echo $json_params; exit;

            $el_header = $this->elastic->get_auth_header();
            $el_url = ELASTIC_SYSLOG_HOST.'/'.$index_name.'/_search';
			$el_result = $this->elastic->restful_curl($el_url, $json_params, 'get', $timeout=10, $el_header);
			$el_result = json_decode($el_result, true);
            //echo print_r($el_result); exit;

            $json_data = new stdclass;
            $json_data->draw = $req['draw'];
            if(isset($el_result['error'])) {
                //echo 'query error';
                //$json_data->error_msg = $el_result['error']['type'];
                //$json_data->status  = $el_result['status'];

                $json_data->recordsTotal = 0;
                $json_data->recordsFiltered = 0;
                $json_data->data = array();
                echo json_encode($json_data);
                return;

            }else if(isset($el_result['hits']) && ($this->elastic->get_hits_total($el_result) > 0) === FALSE) {
                //echo 'rows 0';
                $json_data->recordsTotal = 0;
                $json_data->recordsFiltered = 0;
                $json_data->data = array();
                echo json_encode($json_data);
                return;
            }
		

            $category = '';
			$data = array();
            foreach($el_result['hits']['hits'] as $k=>$r){

                // @facility
                $facility = $r['_source']['facilitytag'];
                $facility_html = '<span class="badge badge-'.strtolower($facility).'">'.$facility.'<span>';

                // @severity
                $severity = $r['_source']['prioritytag'];
                $severity_html = '<span class="badge badge-'.strtolower($severity).'">'.$severity.'<span>';
                
                $row = array(
                    'id'                    => $r['_source']['id'],
                    'facility'              => $facility_html,
                    'priority'              => $severity_html,
                    'fromhost'              => $r['_source']['fromhost'], 
                    'hostip'                => $r['_source']['hostip'], 
                    'eventmsg'              => $r['_source']['eventmsg'], 
                    'userip'                => $r['_source']['userip'], 
                    'country'               => $r['_source']['geoip']['country_name'], 
                    'region'                => $r['_source']['geoip']['region_name'], 
                    'filename'              => $r['_source']['filename'], 
                    'message'               => $r['_source']['message'],
                    'devicereportedtime'    => date('y-m-d h:i:s', strtotime($r['_source']['devicereportedtime'])),
                    '@timestamp'            => date('y-m-d h:i:s', strtotime($r['_source']['@timestamp'])),
                );
                //echo print_r($row); exit;
				$data[] = $row;
            }

            $count = $this->elastic->get_hits_total($el_result);

            $json_data->recordsTotal = $count;
            $json_data->recordsFiltered = $count;
            $json_data->data = $data;

            echo json_encode($json_data);
            return;

        }


        $data = array();
        $data['facility_map'] = $this->common->genJqgridOption($facility_map, false);
        $data['severity_map'] = $this->common->genJqgridOption($severity_map, false);

		$this->_view('syslog/virus', $data);
    }



    public function attack() {

        $this->load->helper('syslog');

        $facility_map = getFacilityMap();
        $severity_map = getSeverityMap();

        $req = $this->input->post();
        //echo print_r($req);
 
		if(isset($req['mode']) && $req['mode'] == 'list') {

            $this->load->library('elastic');

            $multiple_filters = array();
            $index_name = 'systemevents-'.date('Y');
            $params = $this->elastic->datatable_filter_to_params($req, $index_name, $multiple_filters);
            //echo print_r($params); //exit;

            //$params['track_total_hits'] = true; // total default 10000 => 해제
            if($params['from'] >= 10000) {
                $error_msg = '해당 리스트는 [10,000]개 이상 항목을 지원하지 않습니다.'.PHP_EOL;
                $error_msg .= '검색 조건 추가를 통해 리스트의 범위를 줄여주세요!';

                $json_data = new stdClass;
                $json_data->rows = array();
                $json_data->error_msg = $error_msg;
                echo json_encode($json_data);
                return;
            }
            if(isset($request['sidx']) && strlen($request['sidx']) > 0) {
                $params['sort'] = array(
                    $request['sidx'] => array('order' => $request['sord']),
                );
            }


            // CATEGORY ATTCK~ 
            $params['query']['bool']['must'][] = array('term' => array('category' => 'ATTACK'));

            //echo print_r($params); //exit;
            $json_params = json_encode($params);
            //echo $json_params; exit;

            $el_header = $this->elastic->get_auth_header();
            $el_url = ELASTIC_SYSLOG_HOST.'/'.$index_name.'/_search';
			$el_result = $this->elastic->restful_curl($el_url, $json_params, 'get', $timeout=10, $el_header);
			$el_result = json_decode($el_result, true);
            //echo print_r($el_result); exit;

            $json_data = new stdclass;
            $json_data->draw = $req['draw'];
            if(isset($el_result['error'])) {
                //echo 'query error';
                //$json_data->error_msg = $el_result['error']['type'];
                //$json_data->status  = $el_result['status'];

                $json_data->recordsTotal = 0;
                $json_data->recordsFiltered = 0;
                $json_data->data = array();
                echo json_encode($json_data);
                return;

            }else if(isset($el_result['hits']) && ($this->elastic->get_hits_total($el_result) > 0) === FALSE) {
                //echo 'rows 0';
                $json_data->recordsTotal = 0;
                $json_data->recordsFiltered = 0;
                $json_data->data = array();
                echo json_encode($json_data);
                return;
            }
		

            $category = '';
			$data = array();
            foreach($el_result['hits']['hits'] as $k=>$r){

                // @facility
                $facility = $r['_source']['facilitytag'];
                $facility_html = '<span class="badge badge-'.strtolower($facility).'">'.$facility.'<span>';

                // @severity
                $severity = $r['_source']['prioritytag'];
                $severity_html = '<span class="badge badge-'.strtolower($severity).'">'.$severity.'<span>';
                
                $row = array(
                    'id'                    => $r['_source']['id'],
                    'facility'              => $facility_html,
                    'priority'              => $severity_html,
                    'fromhost'              => $r['_source']['fromhost'], 
                    'hostname'              => $r['_source']['hostname'], 
                    'hosturl'               => $r['_source']['hosturl'], 
                    'eventmsg'              => $r['_source']['eventmsg'], 
                    'userip'                => $r['_source']['userip'], 
                    'country'               => $r['_source']['geoip']['country_name'], 
                    'message'               => $r['_source']['message'],
                    'devicereportedtime'    => date('y-m-d h:i:s', strtotime($r['_source']['devicereportedtime'])),
                    '@timestamp'            => date('y-m-d h:i:s', strtotime($r['_source']['@timestamp'])),
                );
                //echo print_r($row); exit;
				$data[] = $row;
            }

            $count = $this->elastic->get_hits_total($el_result);

            $json_data->recordsTotal = $count;
            $json_data->recordsFiltered = $count;
            $json_data->data = $data;

            echo json_encode($json_data);
            return;

        }


        $data = array();
        $data['facility_map'] = $this->common->genJqgridOption($facility_map, false);
        $data['severity_map'] = $this->common->genJqgridOption($severity_map, false);

		$this->_view('syslog/attack', $data);
    }


    public function radius() {

        $this->load->helper('syslog');

        $facility_map = getFacilityMap();
        $severity_map = getSeverityMap();

        $req = $this->input->post();
        //echo print_r($req);
 
		if(isset($req['mode']) && $req['mode'] == 'list') {

            $this->load->library('elastic');

            $multiple_filters = array();
            $index_name = 'systemevents-'.date('Y');
            $params = $this->elastic->datatable_filter_to_params($req, $index_name, $multiple_filters);
            //echo print_r($params); //exit;

            //$params['track_total_hits'] = true; // total default 10000 => 해제
            if($params['from'] >= 10000) {
                $error_msg = '해당 리스트는 [10,000]개 이상 항목을 지원하지 않습니다.'.PHP_EOL;
                $error_msg .= '검색 조건 추가를 통해 리스트의 범위를 줄여주세요!';

                $json_data = new stdClass;
                $json_data->rows = array();
                $json_data->error_msg = $error_msg;
                echo json_encode($json_data);
                return;
            }
            if(isset($request['sidx']) && strlen($request['sidx']) > 0) {
                $params['sort'] = array(
                    $request['sidx'] => array('order' => $request['sord']),
                );
            }


            // CATEGORY ATTCK~ 
            $params['query']['bool']['must'][] = array('term' => array('category' => 'RADIUS'));

            //echo print_r($params); //exit;
            $json_params = json_encode($params);
            //echo $json_params; exit;

            $el_header = $this->elastic->get_auth_header();
            $el_url = ELASTIC_SYSLOG_HOST.'/'.$index_name.'/_search';
			$el_result = $this->elastic->restful_curl($el_url, $json_params, 'get', $timeout=10, $el_header);
			$el_result = json_decode($el_result, true);
            //echo print_r($el_result); exit;

            $json_data = new stdclass;
            $json_data->draw = $req['draw'];
            if(isset($el_result['error'])) {
                //echo 'query error';
                //$json_data->error_msg = $el_result['error']['type'];
                //$json_data->status  = $el_result['status'];

                $json_data->recordsTotal = 0;
                $json_data->recordsFiltered = 0;
                $json_data->data = array();
                echo json_encode($json_data);
                return;

            }else if(isset($el_result['hits']) && ($this->elastic->get_hits_total($el_result) > 0) === FALSE) {
                //echo 'rows 0';
                $json_data->recordsTotal = 0;
                $json_data->recordsFiltered = 0;
                $json_data->data = array();
                echo json_encode($json_data);
                return;
            }
		

            $category = '';
			$data = array();
            foreach($el_result['hits']['hits'] as $k=>$r){

                // @facility
                $facility = $r['_source']['facilitytag'];
                $facility_html = '<span class="badge badge-'.strtolower($facility).'">'.$facility.'<span>';

                // @severity
                $severity = $r['_source']['prioritytag'];
                $severity_html = '<span class="badge badge-'.strtolower($severity).'">'.$severity.'<span>';
                
                $row = array(
                    'id'                    => $r['_source']['id'],
                    'facility'              => $facility_html,
                    'priority'              => $severity_html,
                    'fromhost'              => $r['_source']['fromhost'], 
                    'hostname'              => $r['_source']['hostname'], 
                    'from'                  => $r['_source']['from'], 
                    'eventmsg'              => $r['_source']['eventmsg'], 
                    'userip'                => $r['_source']['userip'], 
                    'username'              => $r['_source']['username'], 
                    'groupname'             => $r['_source']['groupname'], 
                    'country'               => $r['_source']['geoip']['country_name'], 
                    'message'               => $r['_source']['message'],
                    'devicereportedtime'    => date('y-m-d h:i:s', strtotime($r['_source']['devicereportedtime'])),
                    '@timestamp'            => date('y-m-d h:i:s', strtotime($r['_source']['@timestamp'])),
                );
                //echo print_r($row); exit;
				$data[] = $row;
            }

            $count = $this->elastic->get_hits_total($el_result);

            $json_data->recordsTotal = $count;
            $json_data->recordsFiltered = $count;
            $json_data->data = $data;

            echo json_encode($json_data);
            return;

        }


        $data = array();
        $data['facility_map'] = $this->common->genJqgridOption($facility_map, false);
        $data['severity_map'] = $this->common->genJqgridOption($severity_map, false);

		$this->_view('syslog/radius', $data);
    }


    public function secure() {

        $this->load->helper('syslog');

        $facility_map = getFacilityMap();
        $severity_map = getSeverityMap();

        $req = $this->input->post();
        //echo print_r($req);
 
		if(isset($req['mode']) && $req['mode'] == 'list') {

            $this->load->library('elastic');

            $multiple_filters = array();
            $index_name = 'systemevents-'.date('Y');
            $params = $this->elastic->datatable_filter_to_params($req, $index_name, $multiple_filters);
            //echo print_r($params); //exit;

            //$params['track_total_hits'] = true; // total default 10000 => 해제
            if($params['from'] >= 10000) {
                $error_msg = '해당 리스트는 [10,000]개 이상 항목을 지원하지 않습니다.'.PHP_EOL;
                $error_msg .= '검색 조건 추가를 통해 리스트의 범위를 줄여주세요!';

                $json_data = new stdClass;
                $json_data->rows = array();
                $json_data->error_msg = $error_msg;
                echo json_encode($json_data);
                return;
            }
            if(isset($request['sidx']) && strlen($request['sidx']) > 0) {
                $params['sort'] = array(
                    $request['sidx'] => array('order' => $request['sord']),
                );
            }


            // CATEGORY SECURE 
            $params['query']['bool']['must'][] = array('term' => array('category' => 'SECURE'));

            //echo print_r($params); //exit;
            $json_params = json_encode($params);
            //echo $json_params; exit;

            $el_header = $this->elastic->get_auth_header();
            $el_url = ELASTIC_SYSLOG_HOST.'/'.$index_name.'/_search';
			$el_result = $this->elastic->restful_curl($el_url, $json_params, 'get', $timeout=10, $el_header);
			$el_result = json_decode($el_result, true);
            //echo print_r($el_result); exit;

            $json_data = new stdclass;
            $json_data->draw = $req['draw'];
            if(isset($el_result['error'])) {
                //echo 'query error';
                //$json_data->error_msg = $el_result['error']['type'];
                //$json_data->status  = $el_result['status'];

                $json_data->recordsTotal = 0;
                $json_data->recordsFiltered = 0;
                $json_data->data = array();
                echo json_encode($json_data);
                return;

            }else if(isset($el_result['hits']) && ($this->elastic->get_hits_total($el_result) > 0) === FALSE) {
                //echo 'rows 0';
                $json_data->recordsTotal = 0;
                $json_data->recordsFiltered = 0;
                $json_data->data = array();
                echo json_encode($json_data);
                return;
            }
		

            $category = '';
			$data = array();
            foreach($el_result['hits']['hits'] as $k=>$r){

                // @facility
                $facility = $r['_source']['facilitytag'];
                $facility_html = '<span class="badge badge-'.strtolower($facility).'">'.$facility.'<span>';

                // @severity
                $severity = $r['_source']['prioritytag'];
                $severity_html = '<span class="badge badge-'.strtolower($severity).'">'.$severity.'<span>';
                
                $row = array(
                    'id'                    => $r['_source']['id'],
                    'facility'              => $facility_html,
                    'priority'              => $severity_html,
                    'fromhost'              => $r['_source']['fromhost'], 
                    'hostname'              => $r['_source']['hostname'], 
                    'eventmsg'              => $r['_source']['eventmsg'], 
                    'message'               => $r['_source']['message'],
                    'devicereportedtime'    => date('y-m-d h:i:s', strtotime($r['_source']['devicereportedtime'])),
                    '@timestamp'            => date('y-m-d h:i:s', strtotime($r['_source']['@timestamp'])),
                );
                //echo print_r($row); exit;
				$data[] = $row;
            }

            $count = $this->elastic->get_hits_total($el_result);

            $json_data->recordsTotal = $count;
            $json_data->recordsFiltered = $count;
            $json_data->data = $data;

            echo json_encode($json_data);
            return;

        }


        $data = array();
        $data['facility_map'] = $this->common->genJqgridOption($facility_map, false);
        $data['severity_map'] = $this->common->genJqgridOption($severity_map, false);

		$this->_view('syslog/secure', $data);
    }



    public function phperror() {

        $this->load->helper('syslog');

        $facility_map = getFacilityMap();
        $severity_map = getSeverityMap();

        $req = $this->input->post();
        //echo print_r($req);
 
		if(isset($req['mode']) && $req['mode'] == 'list') {

            $this->load->library('elastic');

            $multiple_filters = array();
            $index_name = 'systemevents-'.date('Y');
            $params = $this->elastic->datatable_filter_to_params($req, $index_name, $multiple_filters);
            //echo print_r($params); //exit;

            //$params['track_total_hits'] = true; // total default 10000 => 해제
            if($params['from'] >= 10000) {
                $error_msg = '해당 리스트는 [10,000]개 이상 항목을 지원하지 않습니다.'.PHP_EOL;
                $error_msg .= '검색 조건 추가를 통해 리스트의 범위를 줄여주세요!';

                $json_data = new stdClass;
                $json_data->rows = array();
                $json_data->error_msg = $error_msg;
                echo json_encode($json_data);
                return;
            }
            if(isset($request['sidx']) && strlen($request['sidx']) > 0) {
                $params['sort'] = array(
                    $request['sidx'] => array('order' => $request['sord']),
                );
            }


            // CATEGORY PHPERROR 
            $params['query']['bool']['must'][] = array('term' => array('category' => 'PHPERROR'));

            //echo print_r($params); exit;
            $json_params = json_encode($params);
            //echo $json_params; exit;

            $el_header = $this->elastic->get_auth_header();
            $el_url = ELASTIC_SYSLOG_HOST.'/'.$index_name.'/_search';
			$el_result = $this->elastic->restful_curl($el_url, $json_params, 'get', $timeout=10, $el_header);
			$el_result = json_decode($el_result, true);
            //echo print_r($el_result); exit;

            $json_data = new stdclass;
            $json_data->draw = $req['draw'];
            if(isset($el_result['error'])) {
                //echo 'query error';
                //$json_data->error_msg = $el_result['error']['type'];
                //$json_data->status  = $el_result['status'];

                $json_data->recordsTotal = 0;
                $json_data->recordsFiltered = 0;
                $json_data->data = array();
                echo json_encode($json_data);
                return;

            }else if(isset($el_result['hits']) && ($this->elastic->get_hits_total($el_result) > 0) === FALSE) {
                //echo 'rows 0';
                $json_data->recordsTotal = 0;
                $json_data->recordsFiltered = 0;
                $json_data->data = array();
                echo json_encode($json_data);
                return;
            }
		

            $category = '';
			$data = array();
            foreach($el_result['hits']['hits'] as $k=>$r){

                // @facility
                $facility = $r['_source']['facilitytag'];
                $facility_html = '<span class="badge badge-'.strtolower($facility).'">'.$facility.'<span>';

                // @severity
                $severity = $r['_source']['prioritytag'];
                $severity_html = '<span class="badge badge-'.strtolower($severity).'">'.$severity.'<span>';
                
                $row = array(
                    'id'                    => $r['_source']['id'],
                    'facility'              => $facility_html,
                    'priority'              => $severity_html,
                    'fromhost'              => $r['_source']['fromhost'], 
                    'hostname'              => $r['_source']['hostname'], 
                    'hostnurl'              => $r['_source']['hosturl'], 
                    'eventmsg'              => $r['_source']['eventmsg'], 
                    'message'               => $r['_source']['message'],
                    'devicereportedtime'    => date('y-m-d h:i:s', strtotime($r['_source']['devicereportedtime'])),
                    '@timestamp'            => date('y-m-d h:i:s', strtotime($r['_source']['@timestamp'])),
                );
                //echo print_r($row); exit;
				$data[] = $row;
            }

            $count = $this->elastic->get_hits_total($el_result);

            $json_data->recordsTotal = $count;
            $json_data->recordsFiltered = $count;
            $json_data->data = $data;

            echo json_encode($json_data);
            return;

        }


        $data = array();
        $data['facility_map'] = $this->common->genJqgridOption($facility_map, false);
        $data['severity_map'] = $this->common->genJqgridOption($severity_map, false);

		$this->_view('syslog/phperror', $data);
    }




    public function syslogtag() {

        $this->load->helper('syslog');

        $facility_map = getFacilityMap();
        $severity_map = getSeverityMap();

        $req = $this->input->post();
        //echo print_r($req);
 
		if(isset($req['mode']) && $req['mode'] == 'list') {

            $this->load->library('elastic');

            $multiple_filters = array();
            $index_name = 'systemevents-'.date('Y');
            $params = $this->elastic->datatable_filter_to_params($req, $index_name, $multiple_filters);
            //echo print_r($params); //exit;

            //$params['track_total_hits'] = true; // total default 10000 => 해제
            if($params['from'] >= 10000) {
                $error_msg = '해당 리스트는 [10,000]개 이상 항목을 지원하지 않습니다.'.PHP_EOL;
                $error_msg .= '검색 조건 추가를 통해 리스트의 범위를 줄여주세요!';

                $json_data = new stdClass;
                $json_data->rows = array();
                $json_data->error_msg = $error_msg;
                echo json_encode($json_data);
                return;
            }
            if(isset($request['sidx']) && strlen($request['sidx']) > 0) {
                $params['sort'] = array(
                    $request['sidx'] => array('order' => $request['sord']),
                );
            }


            // CATEGORY SYSLOG 
            $params['query']['bool']['must'][] = array('term' => array('category' => 'SYSLOG'));

            //echo print_r($params); exit;
            $json_params = json_encode($params);
            //echo $json_params; exit;

            $el_header = $this->elastic->get_auth_header();
            $el_url = ELASTIC_SYSLOG_HOST.'/'.$index_name.'/_search';
			$el_result = $this->elastic->restful_curl($el_url, $json_params, 'get', $timeout=10, $el_header);
			$el_result = json_decode($el_result, true);
            //echo print_r($el_result); exit;

            $json_data = new stdclass;
            $json_data->draw = $req['draw'];
            if(isset($el_result['error'])) {
                //echo 'query error';
                //$json_data->error_msg = $el_result['error']['type'];
                //$json_data->status  = $el_result['status'];

                $json_data->recordsTotal = 0;
                $json_data->recordsFiltered = 0;
                $json_data->data = array();
                echo json_encode($json_data);
                return;

            }else if(isset($el_result['hits']) && ($this->elastic->get_hits_total($el_result) > 0) === FALSE) {
                //echo 'rows 0';
                $json_data->recordsTotal = 0;
                $json_data->recordsFiltered = 0;
                $json_data->data = array();
                echo json_encode($json_data);
                return;
            }
		

            $category = '';
			$data = array();
            foreach($el_result['hits']['hits'] as $k=>$r){

                // @facility
                $facility = $r['_source']['facilitytag'];
                $facility_html = '<span class="badge badge-'.strtolower($facility).'">'.$facility.'<span>';

                // @severity
                $severity = $r['_source']['prioritytag'];
                $severity_html = '<span class="badge badge-'.strtolower($severity).'">'.$severity.'<span>';
                
                $row = array(
                    'id'                    => $r['_source']['id'],
                    'facility'              => $facility_html,
                    'priority'              => $severity_html,
                    'fromhost'              => $r['_source']['fromhost'], 
                    'message'               => $r['_source']['message'],
                    'devicereportedtime'    => date('y-m-d h:i:s', strtotime($r['_source']['devicereportedtime'])),
                    '@timestamp'            => date('y-m-d h:i:s', strtotime($r['_source']['@timestamp'])),
                );
                //echo print_r($row); exit;
				$data[] = $row;
            }

            $count = $this->elastic->get_hits_total($el_result);

            $json_data->recordsTotal = $count;
            $json_data->recordsFiltered = $count;
            $json_data->data = $data;

            echo json_encode($json_data);
            return;

        }


        $data = array();
        $data['facility_map'] = $this->common->genJqgridOption($facility_map, false);
        $data['severity_map'] = $this->common->genJqgridOption($severity_map, false);

		$this->_view('syslog/syslogtag', $data);
    }



    public function kernel() {
        $this->_common_template('KERNEL');
    }


    public function vmware() {
        $this->_common_template('SFCB-VMWARE_RAW');
    }

    private function _common_template($ct_param='SYSLOG') {

        $this->load->helper('syslog');

        $facility_map = getFacilityMap();
        $severity_map = getSeverityMap();

        $req = $this->input->post();
        //echo print_r($req);
 
		if(isset($req['mode']) && $req['mode'] == 'list') {

            $this->load->library('elastic');

            $multiple_filters = array();
            $index_name = 'systemevents-'.date('Y');
            $params = $this->elastic->datatable_filter_to_params($req, $index_name, $multiple_filters);
            //echo print_r($params); //exit;

            //$params['track_total_hits'] = true; // total default 10000 => 해제
            if($params['from'] >= 10000) {
                $error_msg = '해당 리스트는 [10,000]개 이상 항목을 지원하지 않습니다.'.PHP_EOL;
                $error_msg .= '검색 조건 추가를 통해 리스트의 범위를 줄여주세요!';

                $json_data = new stdClass;
                $json_data->rows = array();
                $json_data->error_msg = $error_msg;
                echo json_encode($json_data);
                return;
            }
            if(isset($request['sidx']) && strlen($request['sidx']) > 0) {
                $params['sort'] = array(
                    $request['sidx'] => array('order' => $request['sord']),
                );
            }


            // CATEGORY 
            $params['query']['bool']['must'][] = array('term' => array('category' => $ct_param));

            //echo print_r($params); exit;
            $json_params = json_encode($params);
            //echo $json_params; exit;

            $el_header = $this->elastic->get_auth_header();
            $el_url = ELASTIC_SYSLOG_HOST.'/'.$index_name.'/_search';
			$el_result = $this->elastic->restful_curl($el_url, $json_params, 'get', $timeout=10, $el_header);
			$el_result = json_decode($el_result, true);
            //echo print_r($el_result); exit;

            $json_data = new stdclass;
            $json_data->draw = $req['draw'];
            if(isset($el_result['error'])) {
                //echo 'query error';
                //$json_data->error_msg = $el_result['error']['type'];
                //$json_data->status  = $el_result['status'];

                $json_data->recordsTotal = 0;
                $json_data->recordsFiltered = 0;
                $json_data->data = array();
                echo json_encode($json_data);
                return;

            }else if(isset($el_result['hits']) && ($this->elastic->get_hits_total($el_result) > 0) === FALSE) {
                //echo 'rows 0';
                $json_data->recordsTotal = 0;
                $json_data->recordsFiltered = 0;
                $json_data->data = array();
                echo json_encode($json_data);
                return;
            }
		

            $category = '';
			$data = array();
            foreach($el_result['hits']['hits'] as $k=>$r){

                // @facility
                $facility = $r['_source']['facilitytag'];
                $facility_html = '<span class="badge badge-'.strtolower($facility).'">'.$facility.'<span>';

                // @severity
                $severity = $r['_source']['prioritytag'];
                $severity_html = '<span class="badge badge-'.strtolower($severity).'">'.$severity.'<span>';
                
                $row = array(
                    'id'                    => $r['_source']['id'],
                    'facility'              => $facility_html,
                    'priority'              => $severity_html,
                    'fromhost'              => $r['_source']['fromhost'], 
                    'message'               => $r['_source']['message'],
                    'devicereportedtime'    => date('y-m-d h:i:s', strtotime($r['_source']['devicereportedtime'])),
                    '@timestamp'            => date('y-m-d h:i:s', strtotime($r['_source']['@timestamp'])),
                );
                //echo print_r($row); exit;
				$data[] = $row;
            }

            $count = $this->elastic->get_hits_total($el_result);

            $json_data->recordsTotal = $count;
            $json_data->recordsFiltered = $count;
            $json_data->data = $data;

            echo json_encode($json_data);
            return;

        }


        $data = array();
        $data['ct_param'] = strtolower($ct_param);
        $data['facility_map'] = $this->common->genJqgridOption($facility_map, false);
        $data['severity_map'] = $this->common->genJqgridOption($severity_map, false);

		$this->_view('syslog/common_template', $data);
    }


    public function stm_process() {
    
        $req = $this->input->post();
        if($this->input->is_ajax_request()) {
            $req['request'] = 'ajax';
        }
        //echo print_r($req); //exit;

        $this->load->model(array(
            'ssh_track_map_tb_model',
        ));


        $sess = array();
        $ajax_res = array(
            'is_success'    => FALSE,
            'msg'           => ''
        );
        $log_array = array();
        $row_data = array();

        //echo print_r($req).PHP_EOL; //exit;
        $rtn_url = '/'.SHOP_INFO_ADMIN_DIR.'/syslog/ssh_detail/'.$req['stm_id'];
        //echo $rtn_url; exit;
        
        $field_list = $this->ssh_track_map_tb_model->getFields();
        $data_params = array();
        foreach($field_list as $key) {
                       if(array_key_exists($key, $req)) {
                               $data_params[$key] = $req[$key];
                               continue;
                       }
                       if(array_key_exists($key.'_date', $req) && array_key_exists($key.'_time', $req)) {
                               $data_params[$key] = $req[$key.'_date'].' '.$req[$key.'_time'];
                       }
               }



        // 삭제 & 업데이트 시 기존데이터 검증
        $row_data = array();
        if($req['mode'] == 'update' || $req['mode'] == 'delete') {
            if( ! $this->ssh_track_map_tb_model->get($req['stm_id'])->isSuccess()) {
                $log_array['msg'] = getAlertMsg('INVALID_SUBMIT');
                if($req['msg'] == 'ajax') {
                    $ajax_res['msg'] = $log_array['msg'];
                    echo json_encode($ajax_res);
                }else {
                    $this->common->alert($log_array['msg']);
                    $this->common->locationhref($rtn_url);
                    $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['stm_id'], $log_array, 'ssh_track_map_tb');
                }
                return;
            }
            $row_data = $this->ssh_track_map_tb_model->getData();

            $log_array['prev_data'] = $row_data;
        }
        //echo print_r($data_params).PHP_EOL; exit;


        switch($req['mode']) {

            case 'delete':
                
                $data_params = array();
                $log_array['params'] = $data_params;
                if( ! $this->ssh_track_map_tb_model->doDelete($req['stm_id'])->isSuccess()) {
                    $log_array['msg'] = $this->ssh_track_map_tb_model->getErrorMsg();
                    if($req['request'] == 'ajax') {
                        $ajax_res['msg'] = $log_array['msg'];
                        echo json_encode($ajax_res);
                    }else {
                        $this->common->alert($log_array['msg']);
                        $this->common->locationhref($rtn_url);
                        $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['stm_id'], $log_array, 'ssh_track_map_tb');
                    }
                    return;
                }

                if($req['request'] == 'ajax') {
                    $ajax_res['is_success'] = true; 
                    echo json_encode($ajax_res);
                }else {
                    $this->common->locationhref($rtn_url);
                }
                return;
                break;


            case 'update':

                $log_array['params'] = $data_params;
                if( ! $this->ssh_track_map_tb_model->doUpdate($req['stm_id'], $data_params)->isSuccess()) {
                    $log_array['msg'] = $this->ssh_track_map_tb_model->getErrorMsg();

                    if($req['request'] == 'ajax') {
                        $ajax_res['msg'] = $log_array['msg'];
                        echo json_encode($ajax_res);
                    }else {
                        $this->common->alert($log_array['msg']);
                        $this->common->locationhref($rtn_url);
                        $this->common->write_history_log($sess, $req['mode'].' - FAIL', $req['stm_id'], $log_array, 'ssh_track_map_tb');
                    }
                    return;
                }

                $this->common->write_history_log($sess, 'UPDATE', $req['stm_id'], $log_array, 'ssh_track_map_tb');
                break;


            case 'insert':

                if( ! isset($data_params['stm_track_code'])) {
                    $log_array['msg'] = getAlertMsg('REQUIRED_VALUES'); 

                    if($req['request'] == 'ajax') {
                        $ajax_res['msg'] = $log_array['msg'];
                        echo json_encode($ajax_res);
                    }else {
                        $this->common->alert($log_array['msg']);
                        $this->common->locationhref($rtn_url);
                    }
                    return;
                }
                       
                $data_params['stm_created_at'] = date('Y-m-d H:i:s');
                unset($data_params['stm_id']);

                $log_array['params'] = $data_params;
                //echo print_r($data_params); exit;
                if( ! $this->ssh_track_map_tb_model->doInsert($data_params)->isSuccess()) {
                    $log_array['msg'] = $this->ssh_track_map_tb_model->getErrorMsg();

                    if($req['request'] == 'ajax') {
                        $ajax_res['msg'] = $log_array['msg'];
                        echo json_encode($ajax_res);
                    }else {
                        $this->common->alert($log_array['msg']);
                        $this->common->locationhref($rtn_url);
                    }
                    return;
                }

                               $act_key = $this->ssh_track_map_tb_model->getData();
                $this->common->write_history_log($sess, 'INSERT', $act_key, $log_array, 'ssh_track_map_tb');
                break;
        }


        if($req['request'] == 'ajax') {
            $ajax_res['is_success'] = true;
            echo json_encode($ajax_res);
        }else {
            $this->common->locationhref($rtn_url);
        }

    }




    
    public function ajax_get_trackinfo() {
    
               $this->load->model(array(
            'ssh_track_map_tb_model',
        ));

               $this->load->business(array(
        ));

        $this->load->library(array('elastic'));

        $ajax_res = array(
            'is_success'    => FALSE,
            'msg'           => ''
        );

        $req = $this->input->post();

        if( ! isset($req['doc_id']) ) {
            $ajax_res['msg'] = getAlertMsg('INVALID_SUBMIT');    
            echo json_encode($ajax_res);
            return;
        }
        

        $year = substr(trim($req['doc_id']), 0, 4);
        /*
        $doc_code = explode('-', trim($req['doc_id']));
        $doc_id = $doc_code[1];
         */

        $el_header = $this->elastic->get_auth_header();
        $index_name = 'syslogssh-'.$year;

        $el_url = ELASTIC_SYSLOG_HOST.'/'.$index_name.'/_doc/'.trim($req['doc_id']);
        $el_result = $this->elastic->restful_curl($el_url, '', 'GET', $timeout=10, $el_header);
        $el_result = json_decode($el_result, true);
        //echo print_r($el_result);


        if( isset($el_result['_source']) ) {
            $ajax_res['is_success'] = TRUE;
            $ajax_res['msg'] = $el_result['_source'];

            $stm_data = $this->ssh_track_map_tb_model->get(array('stm_ssh_id' => $el_result['_source']['doc_id']))->getData();

            if( is_array($stm_data) && sizeof($stm_data) > 0 ) {
                $ajax_res['msg']['stm_id'] = $stm_data['stm_id'];
                $ajax_res['msg']['stm_track_code'] = $stm_data['stm_track_code'];
                $ajax_res['msg']['stm_memo'] = $stm_data['stm_memo'];
                $ajax_res['msg']['mode'] = 'update';
            }else {
                $ajax_res['msg']['stm_id'] = '';
                $ajax_res['msg']['stm_track_code'] = '';
                $ajax_res['msg']['stm_memo'] = '';
                $ajax_res['msg']['mode'] = 'insert';
            }

        } else {
            $ajax_res['msg'] = getAlertMsg('DATA_NOT_EXITS');    
        }

        echo json_encode($ajax_res);
        return;

    }






}
