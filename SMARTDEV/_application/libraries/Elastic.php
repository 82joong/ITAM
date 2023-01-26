<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Elastic {

    protected $result = array(
        'is_success'    => FALSE,
        'status'        => '',
        'error_msg'     => '',
    );

    protected $headers = array();
    protected $elastic_url = ELASTIC_HOST; 

    protected $elastic_index = array(
        ELASTIC_NGINX_INDEX => ELASTIC_NGINX_INDEX,
    );

    protected $elastic_snapshot_index = array(
        //ELASTIC_MARKET_GOODS_INDEX,
        //ELASTIC_MARKET_GOODS_VVIC_INDEX,
        //ELASTIC_MARKET_GOODS_LAFAYETTE_INDEX
    );

    protected $query_spliter = array(
        'op'    => '_%OP%_',
        'and'   => '_%AND%_'
    );


	public function __construct() {

		$this->CI =& get_instance();

        // SET HTTP/HEADER Authorization
        $this->headers[] = 'Content-Type: application/json';
        if(USE_ELASTIC_SECURITY == true) {
            $token = base64_encode(ELASTIC_USER.':'.ELASTIC_USER_PW);
            $this->headers[] = 'Authorization: Basic '.$token;
        }
	}

    public function get_query_spliter() {
        return $this->query_spliter;
    }

    public function get_elastic_indexes() {
        return $this->elastic_index;
    }

    public function get_elastic_snapshot_indexes() {
        return $this->elastic_snapshot_index;
    }


    public function get_auth_header() {
        return $this->headers;
    }

    /*
    | -------------------------------------------------------------------
    | @indices 정보 가져오기 
    | -------------------------------------------------------------------
    | 
    | 1. indices 정보 리턴
    |
    | -------------------------------------------------------------------
    |
    */
    public function cat_indices($params=array()) {

        if(sizeof($params) < 1) {
            $params = array(
                's'     => 'index',
                'format' => 'json'
            );
        }
        $str_params = http_build_query($params);
        
        $el_url = $this->elastic_url.'/_cat/indices?'.$str_params;
        $el_result = $this->restful_curl($el_url, $el_params='', 'GET', $timeout=10, $this->headers);
        $el_result = json_decode($el_result, true);
        //echo print_r($el_result); exit;

        return $el_result;
    }



    /*
    | -------------------------------------------------------------------
    | @MAPPING _mapping 정보 가져오기
    | -------------------------------------------------------------------
    | 
    | 1. _mapping 통해 field 정보 리턴
    |
    | -------------------------------------------------------------------
    |
    */
    public function get_mapping_fields($index_name) {

        $el_url = $this->elastic_url.'/'.$index_name.'/_mapping';
        $el_result = $this->restful_curl($el_url, $el_params='', 'GET', $timeout=10, $this->headers);
        $el_result = json_decode($el_result, true);
        //echo print_r($el_result); exit;

        if(isset($el_result['error'])) {
            $this->result['error_msg'] = $el_result['error']['type'];
            $this->result['status'] = $el_result['status'];
            return $this->result;
        }

        for($i=0; $i<2; $i++) {
            $el_result = array_shift($el_result);
        }
	    switch(ELASTIC_VERSION) {
            case '6.8':
                // TODO. 확인필요
        	    $fields_map = array_shift($el_result);
                break;

            case '7.6':
        	    $fields_map = $el_result['properties'];
                break;

            default:
        	    $fields_map = $el_result['properties'];
                break;
        }


        $fields = array();
        foreach($fields_map as $k=>$v) {
            if(isset($v['properties'])) {
                $fields[$k] = array_keys($v['properties']);
            }else {
                $fields[$k] = $v;
            }
        }

        $this->result['is_success'] = TRUE;
        $this->result['fields'] = $fields;
        return $this->result; 
    }
   



    /*
    | -------------------------------------------------------------------
    | @TEMPLATE _template 생성
    | -------------------------------------------------------------------
    | 
    | 1. _template 생성
    | 2. _application/bin/tmp/elastic_templates/ 내에 해당 @name.json 파일 존재
    | 3. 해당 json 파일 기반 tepmlate 생성
    |
    | -------------------------------------------------------------------
    | EXPLANATION OF VARIABLES
    | -------------------------------------------------------------------
    |
    |
    */
    public function generate_template($template_name) {
    
        $json_filepath = ELASTIC_TEMPLATE_PATH.'/'.$template_name.'.json';
        if( ! file_exists($json_filepath)) {
            $this->result['error_msg'] = 'No Exists "'.$template_name.'.json" File!';
            return $this->result;
        }
        
        // JSON 파일정보 읽어 오기
        $file_read = fopen($json_filepath, 'r');
        $file_data = fread($file_read, filesize($json_filepath));
        fclose($file_read);

        $el_url = $this->elastic_url.'/_template/'.$template_name;
        $el_result = $this->restful_curl($el_url, $file_data, 'PUT', $timeout=10, $this->headers);
        $el_result = json_decode($el_result, true);

        return $this->_checking_result('acknowledged', $el_result);
    }



    /*
    | -------------------------------------------------------------------
    | @TEMPLATE _template 정보 가져오기
    | -------------------------------------------------------------------
    | 
    | 1. 해당 @template_name _template 정보
    |
    | -------------------------------------------------------------------
    | EXPLANATION OF VARIABLES
    | -------------------------------------------------------------------
    |
    |
    */
    public function get_template($template_name) {

        $el_url = $this->elastic_url.'/_template/'.$template_name;
        $el_result = $this->restful_curl($el_url, '', 'GET', $timeout=10, $this->headers);
        $el_result = json_decode($el_result, true);

        return $el_result;
    }




    /*
    | -------------------------------------------------------------------
    | @TEMPLATE _template 삭제
    | -------------------------------------------------------------------
    | 
    | 1. _template 삭제
    |
    | -------------------------------------------------------------------
    | EXPLANATION OF VARIABLES
    | -------------------------------------------------------------------
    |
    |
    */
    public function delete_template($template_name) {
    
        $el_url = $this->elastic_url.'/_template/'.$template_name;
        $el_result = $this->restful_curl($el_url, $el_params='', 'DELETE', $timeout=10, $this->headers);
        $el_result = json_decode($el_result, true);
        //echo print_r($el_result); exit;

        return $this->_checking_result('acknowledged', $el_result);
    }
    
    
    
        

    /*
    | -------------------------------------------------------------------
    | @SNAPSHOT 논리적 저장소 생성
    | -------------------------------------------------------------------
    | 
    | 1. snapshot 하기 전에 반드시 논리적 저장소 생성 필요!!
    | 2. [ELASTIC_SNAPSHOT_PATH]는 config/elasticsearch.yml에 정의
    | 3. 저장폴더 권한 chown elasticsearch:elasticsearch 
    |
    | [주의!!] 스냅샷은 같은 물리적 저장소 기준 생성(분리하고 싶으면, 물리적 저장소도 다르게 해야함.) 
    |
    | -------------------------------------------------------------------
    | EXPLANATION OF VARIABLES
    | -------------------------------------------------------------------
    |
    | - https://www.elastic.co/guide/en/elasticsearch/reference/6.8/modules-snapshots.html 
    |
    */
    public function generate_snapshot_repository($repository_name) {

        // 기존 레파지토리 여부 확인
        $el_url = $this->elastic_url.'/_snapshot/'.$repository_name."/_verify";
        $el_result = $this->restful_curl($el_url, $el_params='', 'POST', $timeout=10, $this->headers);
        $el_result = json_decode($el_result, true);
        //echo print_r($el_result); exit;

        if(isset($el_result['nodes'])) {
            $this->result['is_success'] = TRUE;
            return $this->result;
        }


        // 존재하지 않을때, 신규생성
        $el_params = array(
            'type'      => 'fs',
            'settings'  => array(
                'location'  =>  ELASTIC_SNAPSHOT_PATH ,
                'compress'  => true
            )
        );
        $el_params = json_encode($el_params);

        $el_url = $this->elastic_url.'/_snapshot/'.$repository_name;
        $el_result = $this->restful_curl($el_url, $el_params, 'PUT', $timeout=10, $this->headers);
        $el_result = json_decode($el_result, true);
        //echo print_r($el_result); exit;

        return $this->_checking_result('acknowledged', $el_result);
    }



    /*
    | -------------------------------------------------------------------
    | @SNAPSHOT 논리적 저장소 삭제
    | -------------------------------------------------------------------
    | 
    | 1. DELETE  @repository_name 은 논리적 정의 저장소만 삭제
    | 2. 실제 저장되어 있던 데이터는 삭제 되어 있지 않음
    | 3. 해당 메소드 호출시, 물리적 데이터 삭제 후 저장소 명명 삭제
    |
    | -------------------------------------------------------------------
    | EXPLANATION OF VARIABLES
    | -------------------------------------------------------------------
    |
    | - https://www.elastic.co/guide/en/elasticsearch/reference/6.8/modules-snapshots.html 
    |
    */
    public function delete_snapshot_repository($repository_name, $index_name) {

        $url = $this->elastic_url.'/_snapshot/'.$repository_name.'/'.$index_name.'_*';
        $result = $this->restful_curl($url, $params='', 'GET', $timeout=10, $this->headers);
        $result = json_decode($result, true);

        // 물리적인 데이터까지 삭제
        if(isset($result['snapshots'])) {
            foreach($result['snapshots'] as $row) {
                $this->delete_snapshot($repository_name, $row['snapshot']);
            }
        }

        $el_url = $this->elastic_url.'/_snapshot/'.$repository_name;
        $el_result = $this->restful_curl($el_url, $el_params='', 'DELETE', $timeout=10, $this->headers);
        $el_result = json_decode($el_result, true);
        //echo print_r($el_result); exit;

        return $this->_checking_result('acknowledged', $el_result);
    }


    /*
    | -------------------------------------------------------------------
    | @SNAPSHOT 생성 - 지정 index 
    | -------------------------------------------------------------------
    | 
    | 1. snapshot 하기 전에 반드시 논리적 저장소 생성 필요!! => $this->generate_snapshot_repository()
    | 2. 동일한 @snapshot_name 지정시 Error
    |
    | -------------------------------------------------------------------
    | EXPLANATION OF VARIABLES
    | -------------------------------------------------------------------
    |
    | - https://www.elastic.co/guide/en/elasticsearch/reference/6.8/modules-snapshots.html 
    |
    */
    public function store_snapshot($repository_name, $index_name, $snapshot_name='') {
    
        $el_params = array(
            'indices'               => $index_name,
            'ignore_uavailable'     => true,
            'include_global_state'  => false
        );
        $el_params = json_encode($el_params);

        if(strlen($snapshot_name) < 1) {
            $snapshot_name = $index_name.'_snapshot_'.date('Ymd_His');
        }

        // wait_for_completion 하면, Timeout  걸릴 수도 있으니 그냥 resonpose 없이 날리자
        //$el_url = $this->elastic_url.'/_snapshot/'.$repository_name.'/'.$snapshot_name.'?wait_for_completion=true';


        $el_url = $this->elastic_url.'/_snapshot/'.$repository_name.'/'.$snapshot_name.'?wait_for_completion=true';
        $el_result = $this->restful_curl($el_url, $el_params, 'PUT', $timeout=10, $this->headers);
        $el_result = json_decode($el_result, true);

        if(isset($el_result['snapshot']) && $el_result['snapshot']['state'] == 'SUCCESS') {
            $this->result['is_success'] = TRUE;
        }else if(isset($el_result['error'])) {
            $this->result['error_msg'] = $el_result['error']['type'];
            $this->result['status'] = $el_result['status'];
        }else {
            $this->result['error_msg'] = $el_result;
        }

        return $this->result;
    }



    /*
    | -------------------------------------------------------------------
    | @SNAPSHOT 삭제 - 지정 snapshot_name 
    | -------------------------------------------------------------------
    | 
    | 1. @snapshot_name 지정 삭제
    |
    | -------------------------------------------------------------------
    | EXPLANATION OF VARIABLES
    | -------------------------------------------------------------------
    |
    | - https://www.elastic.co/guide/en/elasticsearch/reference/6.8/modules-snapshots.html 
    |
    */
    public function delete_snapshot($repository_name, $snapshot_name) {
    
        $el_url = $this->elastic_url.'/_snapshot/'.$repository_name."/".$snapshot_name;
        $el_result = $this->restful_curl($el_url, $el_params='', 'DELETE', $timeout=10, $this->headers);
        $el_result = json_decode($el_result, true);
        //echo print_r($el_result); exit;

        return $this->_checking_result('acknowledged', $el_result);
    }




    /*
    | -------------------------------------------------------------------
    | @SNAPSHOT 정리 - 데몬에서 주기적으로 30일 이전의 snapshot 삭제정리 
    | -------------------------------------------------------------------
    | 
    | 1. [30일] 이전의 snapshot 삭제정리 
    |
    | -------------------------------------------------------------------
    | EXPLANATION OF VARIABLES
    | -------------------------------------------------------------------
    |
    | - https://www.elastic.co/guide/en/elasticsearch/reference/6.8/modules-snapshots.html 
    |
    */
    public function clean_snapshot() {

        $st_time = time() - (86400 * 30);    // 30일 전을 기준 

        $el_url = $this->elastic_url.'/_snapshot';
        $sn_result = $this->restful_curl($el_url, $el_params='', 'GET', $timeout=10, $this->headers);
        $sn_result = json_decode($sn_result, true);
        //echo print_r($sn_result); exit;

        foreach($sn_result as $repo_name => $sn_data) {
            //echo '['.$repo_name.']'.PHP_EOL;

            $el_url = $this->elastic_url.'/_snapshot/'.$repo_name.'/_all';
            $el_result = $this->restful_curl($el_url, $el_params='', 'GET', $timeout=10, $this->headers);
            $el_result = json_decode($el_result, true);
            //echo print_r($el_result); exit;

            if(isset($el_result['snapshots']) && sizeof($el_result['snapshots'])) { 

                foreach($el_result['snapshots'] as $row) {
                    if($row['end_time_in_millis'] < ($st_time * 1000)) {
                        $res = $this->delete_snapshot($repo_name, $row['snapshot']);
                        //echo print_r($res); //exit;
                    }
                }
            }
        }
    }



    /*
    | -------------------------------------------------------------------
    | @SNAPSHOT 복구 - 지정 snapshot 
    | -------------------------------------------------------------------
    | 
    | 1. 복구하려는 index가 존재하면, Error => 복구 인덱스 DELETE 후에_restore
    | 2. 저장된 snapshot 이름으로 데이터 복구 
    |
    | -------------------------------------------------------------------
    | EXPLANATION OF VARIABLES
    | -------------------------------------------------------------------
    |
    | - https://www.elastic.co/guide/en/elasticsearch/reference/6.8/modules-snapshots.html 
    |
    */
    public function restore_snapshot($repository_name, $snapshot_name) {
    
        $el_url = $this->elastic_url.'/_snapshot/'.$repository_name.'/'.$snapshot_name.'/_restore';;
        $el_result = $this->restful_curl($el_url, $el_params='', 'POST', $timeout=10, $this->headers);
        $el_result = json_decode($el_result, true);
        //echo print_r($el_result); exit;


        if(isset($el_result['error'])) {
            $this->result['error_msg'] = $el_result['error']['reason'];
            $this->result['status'] = $el_result['status'];
            return $this->result;
        }

        return $this->_checking_result('accepted', $el_result);
    }





    /*
    | -------------------------------------------------------------------
    | @ALIAS 매핑 정보 
    | -------------------------------------------------------------------
    | 
    | 1. @alias_name으로 매핑된 index 명 반환 
    |
    | -------------------------------------------------------------------
    | EXPLANATION OF VARIABLES
    | -------------------------------------------------------------------
    |
    | - https://www.elastic.co/guide/en/elasticsearch/reference/6.8/indices-aliases.html 
    |
    */

    public function get_alias_map($alias_name) {

        $el_url = $this->elastic_url.'/_alias/'.$alias_name;
        $el_result = $this->restful_curl($el_url, $el_params='', 'GET', $timeout=10, $this->headers);
        $el_result = json_decode($el_result, true);
        //echo print_r($el_result); exit;

        if(isset($el_result['error'])) {
            $this->result['error_msg'] = $el_result['error'];
            $this->result['status'] = $el_result['status'];
        }else {
            $this->result['is_success'] = TRUE;
            $this->result['indices'] = array_keys($el_result);
        }

        return $this->result;
    }


    /*
    | -------------------------------------------------------------------
    | @ALIAS actions(add/delete) 처리 
    | -------------------------------------------------------------------
    | 
    | 1. alias 지정으로 통한 최신 데이터 유지 
    | 2. 과거 alias로 매핑된 index 삭제하고 신규 index 추가(전환) 
    |
    | -------------------------------------------------------------------
    | EXPLANATION OF VARIABLES
    | -------------------------------------------------------------------
    |
    | - https://www.elastic.co/guide/en/elasticsearch/reference/6.8/indices-aliases.html 
    |
    */

    public function actions_alias($del_indices=array(), $add_indices=array(), $alias_name) {


        // alias 내에 매핑된 index 확인 
        $el_res = $this->get_alias_map($alias_name);
        $exists_in = array();
        if(isset($el_res['indices']) && sizeof($el_res['indices'])) {
            $exists_in = array_values($el_res['indices']);
        }


        $actions = array();
        if(sizeof($del_indices) > 0) {
            foreach($del_indices as $del_in) {

                // 기 존재하는 alias내에 해당 index가 있을때만, remove
                if(in_array($del_in, $exists_in)) {
                    $actions[]['remove'] = array('index' => $del_in, 'alias' => $alias_name);  
                }
            }
        }
        if(sizeof($add_indices) > 0) {
            foreach($add_indices as $add_in) {

                // 기 존재하는 alias내에 해당 index가 없을때만, add
                if( ! in_array($add_in, $exists_in)) {
                    $actions[]['add'] = array('index' => $add_in, 'alias' => $alias_name);  
                }
            }
        }


        if(sizeof($actions) > 0) {
            $el_params = array('actions' => $actions);
            $el_params = json_encode($el_params);

            $el_url = $this->elastic_url.'/_aliases';
            $el_result = $this->restful_curl($el_url, $el_params, 'POST', $timeout=10, $this->headers);
            $el_result = json_decode($el_result, true);
            //echo print_r($el_result); exit;

            return $this->_checking_result('acknowledged', $el_result);

        }else {

            $this->result['is_success'] = TRUE;
            return $this->result;
        }
    }



    /*
    | -------------------------------------------------------------------
    | @SCRIPTING raw params 기반으로 search 
    | -------------------------------------------------------------------
    | 
    | 1. @params -> json_encode(@params)  바로 _search 호출
    |
    | -------------------------------------------------------------------
    | EXPLANATION OF VARIABLES
    | -------------------------------------------------------------------
    |
    | - https://www.elastic.co/guide/en/elasticsearch/reference/current
    |
    */
    public function get_raw_search($index_name, $params=array()) {
    
        if(sizeof($params) < 1) {
            return;
        }
        
        $el_params = json_encode($params);
        $el_url = $this->elastic_url.'/'.$index_name.'/_search?size=0';
        $el_result = $this->restful_curl($el_url, $el_params, 'POST', $timeout=10, $this->headers);
        $el_result = json_decode($el_result, true);

        if(isset($el_result['aggregations'])) {
            $this->result['is_success'] = TRUE;
            $this->result['aggs'] = $el_result['aggregations'];
            return $this->result;
        }else {
            $this->result['is_success'] = FALSE;
            $this->result['error_msg'] = 'NOT FOUND';
            return $this->result;
        }

    }



    /*
    | -------------------------------------------------------------------
    | @SCRIPTING _update에 대한 script 처리 
    | -------------------------------------------------------------------
    | 
    | 1. _id 기반으로 데이터 수정할때, 
    | 2. painless 의한 script 처리 동적 수정
    |
    | -------------------------------------------------------------------
    | EXPLANATION OF VARIABLES
    | -------------------------------------------------------------------
    |
    | - https://www.elastic.co/guide/en/elasticsearch/reference/current/module-scripting-using.html 
    |
    */

    public function update_scripting($index_name, $id, $up_params) {

        $res = $this->_set_script_mapping($index_name, $up_params);
	    //echo 'TEST : '.print_r($res); exit;
        if($res['is_success'] == FALSE) {
            return $this->result;
        }else {
            $script_text = $res['script'];
        }

        $el_params = array('script' => $script_text);
        $el_params = json_encode($el_params);

        $el_url = $this->elastic_url.'/'.$index_name.'/_doc/'.$id.'/_update';
        $el_result = $this->restful_curl($el_url, $el_params, 'POST', $timeout=10, $this->headers);
        $el_result = json_decode($el_result, true);
        //echo print_r($el_result); exit;

        return $this->_checking_result('result', $el_result, $value='updated');
    }



    public function get_indices($index_name) {
    
        $el_url = $this->elastic_url.'/'.$index_name;
        $el_result = $this->restful_curl($el_url, '', 'GET', $timeout=10, $this->headers);
        $el_result = json_decode($el_result, true);

        return array_keys($el_result);
    }


    public function delete_indices($index_name, $day=10) {

        $st_time = time() - (86400 * $day);    // 10일 전을 기준 
        $indexs = $this->get_indices($index_name);
        if(sizeof($indexs) < 1) {
            return;
        }

        foreach($indexs as $index) {
        
            $name = explode('-', $index);
            $ymd = end($name);
            $ymd = str_replace('.', '', $ymd);
            $ymd = str_replace('-', '', $ymd);
            $ymd = str_replace(' ', '', $ymd);

            if(strtotime($ymd) < $st_time) {
                $el_url = $this->elastic_url.'/'.$index;
                $this->restful_curl($el_url, '', 'DELETE', $timeout=10, $this->headers);
            }
        }
    }


    /*
    | -------------------------------------------------------------------
    | @SCRIPTING update_params에 대한Elastic 필드내에 ctx._source이하 생성 
    | -------------------------------------------------------------------
    | 
    | 1. @up_params : 업데이트 하려는 필드 
    |    ex) $up_params = array(
    |           'as_date'               => '20200211',
    |           'as_goods_cost'         => 153,
    |           'as_stock_cost_total'   => 22270
    |        );   
    |
    | 2. _mapping 정보로부터 필드 key값 매칭
    | 3. 유효성 검증 (매칭키)
    | 4. return값 "ctx._source.as_date=20200211; ctx._source.as_goods.as_goods_cost=153; ....." 결과생성
    |
    | -------------------------------------------------------------------
    |
    */

    private function _set_script_mapping($index_name, $up_params) {

        // 현재 데이터내의 매핑정보 가져오기
        $res = $this->get_mapping_fields($index_name);
	    //echo print_r($res);

        $this->result = array(
            'is_success'    => FALSE,
            'status'        => '',
            'error_msg'     => '',
        );
 
        if($res['is_success'] == FALSE) {
            $this->result['error_msg'] = 'Fail to get mapping fields data!';
            return $this->result;
        }
        $fields = $res['fields'];

        $script_data = array();
        foreach($up_params as $k=>$v) {

            $script_txt = 'ctx._source';
            if(array_search($k, array_keys($fields)) == FALSE) {

                foreach($fields as $field_name=>$field_child) {
                    if(array_search($k, $field_child) === FALSE) {
                    }else {
                        $script_txt .= '.'.$field_name.'.'.$k.'='.$v.';';
                        $script_data[] = $script_txt;
                    }
                }

            }else {
                $script_txt .= '.'.$k.'='.$v.';';
                $script_data[] = $script_txt;
            }
        }

        if(sizeof($up_params) !== sizeof($script_data)) {
            $this->result['error_msg'] = 'Fail to match your params fields!'; 
            return $this->result;
        }
    
        $this->result['is_success'] = 'TRUE';
        $this->result['script'] = implode(' ', $script_data);
        return $this->result;
    }
 


	/* ======================================================= 
	   [jQuery Grid] filters => return $params array

	 ** Example  ::

	 @input   ::   $filters  = {"groupOp":"AND","rules":[{"field":"g_id","op":"bw","data":"27092"},{"field":"g_sku","op":"bw","data":"49231"}]}
	 @return  ::   $params = Array ( [like_] => Array ( [g_id] => 27092 [g_sku] => 49231 ) )

	 ======================================================= */

    public function filter_to_params($filters, $search, $between_fields=array(), $multiple_fields=array()) {
		$this->ci = & get_instance();

		$filters = str_replace('%7','}',$filters);
		$between_field_map = array();

		$params = array();
		if(($search==true) &&($filters != "")) {

			if(sizeof($between_fields) > 0) {
				foreach($between_fields as $bf) {
					$between_field_map[$bf.'_from'] = $bf;
					$between_field_map[$bf.'_to'] = $bf;
				}
			}

            $filters = str_replace("\r", '', $filters);
            $filters = str_replace("\n", '__split__', $filters);
			$filters = json_decode($filters);
			//echo print_r($filters)."<BR><BR>";
			$rules = $filters->rules;

			$groupOperation = $filters->groupOp;
            $_ATTR = '';
            switch($groupOperation) {
                case 'AND':
                    $_ATTR = 'must';
                    break;
                case 'OR':
                    $_ATTR = 'should';
                    break;
                default:
                    $_ATTR = 'must';
                    break;
            }


            $fieldRange = array();

			foreach($rules as $rule) {
				$fieldName = $rule->field;
				//$fieldData = mysql_real_escape_string($rule->data);
				$fieldData = $this->ci->db->escape_str($rule->data);

				if(isset($between_field_map[$fieldName]) == true) {
					// fieldName_from or fieldName_to 

					$op_type = array_pop(explode('_', $fieldName));
					$fieldName = $between_field_map[$fieldName];


					if($op_type == 'from') {

						// Date type
						if(preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/", $fieldData)){ 
							$fieldRange[$fieldName]['gte'] = $fieldData.' 00:00:00';
						}else{ 
							$fieldRange[$fieldName]['gte'] = $fieldData;
						}

					} else if($op_type == 'to') {
						// Date type
						if(preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/", $fieldData)){ 
							$fieldRange[$fieldName]['lte'] = $fieldData.' 00:00:00';
						}else{ 
							$fieldRange[$fieldName]['lte'] = $fieldData;
						}
					}

					continue;
				} // END_IF @between_field_map

				switch ($rule->op) {
					case "eq":
						// todo. confirm
						$fieldOperation = "=";
					break;
					case "ne":
						// todo. confirm
						$fieldOperation = "!=";
					break;

					case "lt":
					case "gt":
					case "le":
					case "ge":
						// todo. confirm
						$fieldOperation = $rule->op;
					break;

					case "nu":
						// todo. confirm
						$fieldOperation = "=";
					break;

					case "nn":
						// todo. confirm
						$fieldOperation = "!=";
					break;

					case "in":
						$fieldOperation = "in";

                        if( ! is_array($fieldData) && strpos($fieldData, "__split__") !== false) {
                            $fieldData = array_filter(array_map('trim', explode("__split__", $fieldData)));
                        } else if( ! is_array($fieldData) && strpos($fieldData, ",") !== false) {
                            $fieldData = array_filter(array_map('trim', explode(",", $fieldData)));
                        } 

                        if( ! is_array($fieldData)) {
                            if(strlen(trim($fieldData)) > 0) {
                                $fieldData = array($fieldData);
                            }
                        }
                        
                        if(sizeof($fieldData) <= 0) {
                            continue 2;
                        }

                        $params['bool']['must'][] = array('terms' => array($fieldName => $fieldData));
					break;

					case "ni":  // not in
						$fieldOperation = "not in";
                        if( ! is_array($fieldData) && strpos($fieldData, "__split__") !== false) {
                            $fieldData = array_filter(array_map('trim', explode("__split__", $fieldData)));
                        } else if( ! is_array($fieldData) && strpos($fieldData, ",") !== false) {
                            $fieldData = array_filter(array_map('trim', explode(",", $fieldData)));
                        }

                        if( ! is_array($fieldData)) {
                            if(strlen(trim($fieldData)) > 0) {
                                $fieldData = array($fieldData);
                            }
                        }
                        if(sizeof($fieldData) <= 0) {
                            continue 2;
                        }

                        $params['bool']['must_not'][] = array('terms' => array($fieldName => $fieldData));
					break;

					case "bw":  // like_
					case "ew":  // _like
					case "cn":  // like
                        
                        if(isset($multiple_fields[$fieldName]) && is_array($multiple_fields[$fieldName])) {
                            $params['bool'][$_ATTR][] = array('multi_match' => array(
                                        'query' => $fieldData,
                                        'fields' => $multiple_fields[$fieldName]
                            ));
                        }else {
                            $params['bool'][$_ATTR][] = array('match' => array($fieldName => $fieldData));
                        }
                        
					break;

					case "en":  // not _like
					case "bn":  // not like_
					case "nc":  // not like

                        if(isset($multiple_fields[$fieldName]) && is_array($multiple_fields[$fieldName])) {
                            $params['bool'][$_ATTR][] = array('multi_match' => array(
                                        'query' => $fieldData,
                                        'fields' => $multiple_fields[$fieldName]
                            ));
                        }else {
                            $params['bool'][$_ATTR][] = array('match' => array($fieldName => $fieldData));
                        }

					break;

					default:
					    $fieldOperation = "";
					break;

				} // END_SWITCH @rule->op
				//$params[$fieldOperation][$fieldName] = $fieldData;

			} // END_FOREACH


            //echo print_r($fieldRange);
            if(sizeof($fieldRange) > 0) {
                foreach($fieldRange as $name=>$r) {
                    $params['bool'][$_ATTR][] = array(
                        'range' => array($name => $r)
                    );
                }
            }
			//echo print_r($params)."<BR><BR>";   exit;

	    } // END_IF @search == true

		return $params;
	}


	/* ======================================================= 
	 [DataTable] Columns filters => return @params Elastic DSL Query

	 ** Example  ::
    @req 형태 예시
    @req = Array (
        [draw] => 9
        [columns] => Array (
            [0] => Array (
                [data] => mg_goods_id
                [name] => mg_goods_id
                [searchable] => true
                [orderable] => true
                [search] => Array (
                    [value] => eq_%OP%_9907119      // 검색
                    [regex] => false
                )
            ),
            ...
            ...
            [7] => Array (
                [data] => mg_price
                [name] => mg_price
                [searchable] => true
                [orderable] => true
                [search] => Array (
                    [value] => bt_%OP%_40_%AND%_    // between
                    [regex] => false
                )
            )
        ),    
        [order] => Array (
            [0] => Array (
                [column] => 6
                [dir] => asc
            )
        )
        [start] => 0
        [length] => 50
        [search] => Array (
            [value] => test  
            [regex] => false
        )
        [mode] => list
    )



	 ======================================================= */
    public function datatable_filter_to_params($filters, $index_name, $multiple_fields=array(), $source_fields=array()) {

        $param= array(
            'size'      => 10,
            'from'      => 0,
            'query'     => array(
                'bool' => array(        // 관계형 처럼 여러개의 Query 조합 [AND/OR/NAND/FILTER] => [must/must_not/should/filter]
                    'must' => array()
                )
            ),
        );


        // =================== 1. Make @params[query] ========================== 
		$between_field_map = array();
        $fieldRange = array();

        $_SPLITER = $this->get_query_spliter();

        $columns = $filters['columns'];
        $groupOperation = isset($filters['group_op']) ? $filters['group_op'] : '';

        $_ATTR = '';
        switch($groupOperation) {
            case 'AND':
                $_ATTR = 'must';
                break;
            case 'OR':
                $_ATTR = 'should';

                break;
            default:
                $_ATTR = 'must';
                break;
        }


        foreach($columns as $rule) {

            if(strlen($rule['search']['value']) < 1) {
                continue;
            }

            $search = explode($_SPLITER['op'], $rule['search']['value']);
            if( ! is_array($search) || sizeof($search) < 1) {
                continue;
            }

            $fieldName = $rule['name'];
            $operator = trim($search[0]);
            $fieldData = trim($search[1]);


            if(strlen($fieldData) < 1) continue;

            switch ($operator) {
                case "eq":
                    //$fieldOperation = "=";
                    if( strpos($fieldData, $_SPLITER['and']) === FALSE ) {
                        $params['query']['bool']['must'][] = array('term' => array($fieldName => $fieldData));
                    }else {
                        $fieldData = explode($_SPLITER['and'], $fieldData);
                        $fieldData = array_map('trim', $fieldData);
                        $fieldData = array_filter($fieldData);
                        foreach($fieldData as $v) {
                            $params['query']['bool']['must'][] = array('term' => array($fieldName => $v));
                        }
                    }

                break;
                case "ne":
                    // todo. confirm
                    $fieldOperation = "!=";
                    $params['query']['bool']['must_not'][] = array('term' => array($fieldName => $fieldData));
                break;

                case "lt":
                case "gt":
                case "le":
                case "ge":
                    // todo. confirm
                    $fieldOperation = $oprator;
                break;

                case "nu":
                    // todo. confirm
                    $fieldOperation = "=";
                break;

                case "nn":
                    // todo. confirm
                    $fieldOperation = "!=";
                break;

                case "in":
                    $fieldOperation = "in";
                    $fieldData = explode($_SPLITER['and'], $fieldData);
                    $fieldData = array_map('trim', $fieldData);
                    $fieldData = array_filter($fieldData);
                    $params['query']['bool']['must'][] = array('terms' => array($fieldName => $fieldData));
                break;

                case "ni":  // not in
                    $fieldOperation = "not in";
                    $fieldData = explode($_SPLITER['and'], $fieldData);
                    $fieldData = array_map('trim', $fieldData);
                    $fieldData = array_filter($fieldData);                   

                    $params['query']['bool']['must_not'][] = array('terms' => array($fieldName => $fieldData));
                break;

                case "bt":
                    $search_data = explode($_SPLITER['and'], $fieldData);
                    foreach($search_data as $k=>$searchValue) {

                        if(strlen($searchValue) < 1) continue;
                    
                        // From
                        if($k == 0) {
                            $fieldOperation = 'gte';
                            $date_postfix = "00:00:00";

                        // To
                        } else {
                            $fieldOperation = 'lte';
                            $date_postfix = "23:59:59";
                        }

                        // Date Type
                        if(preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/", $searchValue)){ 
							$fieldRange[$fieldName][$fieldOperation] = $searchValue." ".$date_postfix;
						}else{ 
							$fieldRange[$fieldName][$fieldOperation] = $searchValue;
						}
                    }
                    break;


                case "btn":
                    $search_data = explode($_SPLITER['and'], $fieldData);
                    foreach($search_data as $k=>$searchValue) {

                        if(strlen($searchValue) < 1) continue;
                    
                        // From
                        if($k == 0) {
                            $fieldOperation = 'gt';
                        // To
                        } else {
                            $fieldOperation = 'lt';
                        }
						$fieldRange[$fieldName][$fieldOperation] = $searchValue;
                    }
                    break;


                case "bw":  // like_
                case "ew":  // _like
                case "cn":  // like
                    
                    if(isset($multiple_fields[$fieldName]) && is_array($multiple_fields[$fieldName])) {
                        $params['query']['bool'][$_ATTR][] = array('multi_match' => array(
                            'query'     => str_replace($_SPLITER['and'], ' ', $fieldData),
                            'fields'    => $multiple_fields[$fieldName],
                            'operator'  => 'and'
                        ));
                    }else {

                        $search_data = explode($_SPLITER['and'], $fieldData);
                        foreach($search_data as $k=>$searchValue) {
                            $params['query']['bool'][$_ATTR][] = array('wildcard' => array($fieldName => '*'.strtolower($searchValue).'*'));
                        }
                        //$params['query']['bool'][$_ATTR][] = array('match' => array($fieldName => $fieldData));
                    }
                    
                    break;

                case "en":  // not _like
                case "bn":  // not like_
                case "nc":  // not like

                    if(isset($multiple_fields[$fieldName]) && is_array($multiple_fields[$fieldName])) {
                        $params['query']['bool'][$_ATTR][] = array('multi_match' => array(
                            'query'     => str_replace($_SPLITER['and'], ' ', $fieldData),
                            'fields'    => $multiple_fields[$fieldName],
                            'operator'  => 'and'
                        ));
                    }else {
                        $params['query']['bool'][$_ATTR][] = array('match' => array($fieldName => $fieldData));
                    }

                break;

                default:
                    $fieldOperation = "";
                break;

            } // END_SWITCH @rule->op
            //$params[$fieldOperation][$fieldName] = $fieldData;

        } // END_FOREACH


        //echo print_r($fieldRange);
        if(sizeof($fieldRange) > 0) {
            foreach($fieldRange as $name=>$r) {
                $params['query']['bool'][$_ATTR][] = array(
                    'range' => array($name => $r)
                );
            }
        }
        //echo print_r($params)."<BR><BR>";   exit;


        // =================== 2. Make @params[multi_match] ========================== 
        if(isset($filters['search']) && strlen($filters['search']['value'])) {

            $fields_data = $this->get_mapping_fields($index_name);
            $fields = array();

            // Mapping 필드내에 type이 text와 keyword 형식만 
            foreach($fields_data['fields'] as $field_name => $f) {

                if( isset($f['index']) && $f['index'] == false ) continue; 

                if($f['type'] == 'text' || $f['type'] == 'keyword') {
                    $fields[] = $field_name;
                }
            }
            $fields = array_filter($fields);                   

            if(sizeof($fields) > 0) {
                unset($params['query']['bool']);
                $params['query']['multi_match'] = array(
                    'query'     => $filters['search']['value'],
                    'fields'    => $fields 
                );
            }
        }


        // =================== 3. Make @params[sort] ========================== 
        // Sorting / Ordering
        if(isset($filters['order'])  && sizeof($filters['order']) > 0) {
            $sort_data = array();
            foreach($filters['order'] as $order) {
                $columnName = $filters['columns'][$order['column']]['name'];
                $params['sort'][] = array($columnName => strtolower($order['dir']));
            }
        }


        // =================== 4. Make @params[size/from] ========================== 
        // Paging Offset/Limit
        if(isset($filters['start'])  && isset($filters['length'])) {
            $params['size'] = $filters['length'];
            $params['from'] = $filters['start'];
        }else {
            $params['size'] = 0;
            $params['from'] = 0;
        }
        if($params['from'] < 0) $params['from'] = 0;


        // =================== 5. Make @params[_source] ========================== 
        // _source 내에 포함할 데이터 정의 
        if(sizeof($source_fields) > 0) {
            $params['_source'] = $source_fields;
        }


		return $params;
	}




    /*
    | -------------------------------------------------------------------
    | 부모상품 mg_id로 부터 mg_options 내에 옵션별 상품 ids  return  
    | -------------------------------------------------------------------
    | 
    | -  
    |
    | -------------------------------------------------------------------
    |
    */

    public function get_opt_gids_map($index_name, $market, $gids, $return_map=FALSE) {
        // 마켓별 옵션내에 상품 gid (POS: service_goods_id)
        $opt_gid_field_map = $this->CI->config->item('opt_gid_field_map');
        $opt_gid_field = $opt_gid_field_map[strtoupper($market)];

        $el_params = array();
        $el_params['_source'] = array('mg_id', 'mg_options.'.$opt_gid_field);
        $el_params['query']['bool']['must'] = array(
            'terms' => array('mg_id' => $gids)
        );
        $el_params['sort'] = array('_id' => 'asc');
        $el_params['size'] = 1000; 
        $el_params = json_encode($el_params);
        $el_url = $this->elastic_url.'/'.$index_name.'-'.$market.'/_search';
        $el_result = $this->restful_curl($el_url, $el_params, 'GET', $timeout=10, $this->headers);
        $el_result = json_decode($el_result, true);
        //echo print_r($el_result); exit;

        if($return_map == TRUE) {
            if( ! isset($el_result['hits']) ||  ! isset($el_result['hits']['hits']) ) {
                return array();
            }

            $ggods_map = array();
            foreach($el_result['hits']['hits'] as $k=>$row) {
                $row = $row['_source'];

                if( ! isset($row['mg_options']) || ! is_array($row['mg_options']) ) continue;

                foreach($row['mg_options'] as $opt) {
                    $goods_map[$row['mg_id']][] = $opt[$opt_gid_field];
                }
            }

			/*
				@return => 
				Array (
					[lafayette-12877231] => Array
						(
							[0] => 12877234
							[1] => 12877237
							...
						)

					[lafayette-12877480] => Array
						(
							[0] => 12877483
							[1] => 12877485
							...
						)

				)
			*/
            return $goods_map;
        }

        return $el_result;
    }


    /*
    | -------------------------------------------------------------------
    | @Total :  
    | -------------------------------------------------------------------
    | 
    | 1. Elastic version에 따른 항목정의
    |
    | -------------------------------------------------------------------
    |
    */
    public function get_hits_total($result) {
        switch(ELASTIC_VERSION) {
            case '6.8':
                if(isset($result['hits']['total'])) {
                    return $result['hits']['total'];
                } 
                break;

            case '7.6':
                if(isset($result['hits']['total']['value'])) {
                    return $result['hits']['total']['value'];
                } 
                break;

            default:
                if(isset($result['hits']['total']['value'])) {
                    return $result['hits']['total']['value'];
                } 
                break;
        }
        return FALSE;
    }



    private function _checking_result($type, $el_result, $value=TRUE) {

        $this->result = array(
            'is_success'    => FALSE,
            'status'        => '',
            'error_msg'     => '',
        );
    
        if(isset($el_result[$type]) && $el_result[$type] == $value) {
            $this->result['is_success'] = TRUE;
        }else if(isset($el_result['error'])) {
            $this->result['error_msg'] = $el_result['error']['type'];
            $this->result['status'] = $el_result['status'];
        }else {
            $this->result['error_msg'] = $el_result;
        }
    
        return $this->result;
    }




    /*
    | -------------------------------------------------------------------
    | @CURL : PHP curl 호출 함수 
    | -------------------------------------------------------------------
    | 
    | 1. HEADER 정보 포함 추가
    | 2. POST/GET/DELETE/PUT Method 호출 추가
    |
    | -------------------------------------------------------------------
    |
    | ex) HEADER 정보 넣을때,
    |   $header = array(
    |       'Content-Type: application/json',
    |       'Authorization: Basic <TOKEN>'
    |   );
    |
    | ex) POSTFILED 에 @file 넣을때,
    |
    |   // 파일정보 읽어 오기
    |   $file_read = fopen(FILE_PATH, 'r');
    |   $file_data = fread($file_read, filesize(FILE_PATH));
    |   fclose($file_read);
    |
    |   $param = $file_data;
    |
    */
	public function restful_curl($url, $params='', $method='POST', $timeout=10, $header=array()) {
        
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);

        // HTTPS
        if( strpos(strtolower($url), 'https:') !== false ) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }

        if(is_array($header) && sizeof($header) > 0) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }

        switch(strtoupper($method)) {
            case 'POST':
		        curl_setopt($ch, CURLOPT_POST, 1);
                break;
            case 'GET':
		        curl_setopt($ch, CURLOPT_POST, 0);
                break;
            case 'PUT':
            case 'DELETE':
		        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
                break;
        }

        if(strlen($params) > 0) {
		    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_ENCODING, "gzip");
		$result = curl_exec($ch);
		curl_close($ch);

		return $result;
	}


}
?>
