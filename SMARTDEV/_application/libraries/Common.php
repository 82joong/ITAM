<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Common {
	private $transCurrency = -1;
	private $ci;

    function __construct() {
        $this->ci = & get_instance();
    }

    function get_inc_path() {
        $inc_path = "";
        if(strlen($this->ci->uri->uri_string()) > 0) {
            $inc_path = "..";
        }
        return $inc_path;
    }

    // javascript alert
    function alert($msg) {
        $msg = str_replace("'", "\'", $msg);
        $TMP_HTML = "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";
        $TMP_HTML .="<script>alert('".$msg."');</script>";
        echo $TMP_HTML;
    }


    // javascript historyback
    function historyback() {
        $TMP_HTML = "<script>history.back(-1);</script>";
        echo $TMP_HTML;
    }

    // javascript self.close
    function selfclose() {
        $TMP_HTML = "<script>self.close();</script>";
        echo $TMP_HTML;
    }


    function setLocalStorage($key, $val) {
        $TMP_HTML = "<script>localStorage.setItem('".$key."', '".$val."');</script>";
        echo $TMP_HTML; 
    }
    
    public function clear_header_cache() {
        header_remove("Expires");
        header_remove("Cache-Control");
        header_remove("Pragma");
        header_remove("Last-Modified");
    }

    // javascript location
    function locationhref($url) {
        $TMP_HTML = "<script>location.href='".$url."';</script>";
        echo $TMP_HTML;
    }

    function parent_locationhref($url) {
        $TMP_HTML = "<script>parent.location.href='".$url."';</script>";
        echo $TMP_HTML;
    }

 
    function parent_fail_alert($msg) {
        $msg = str_replace("'", "\'", $msg);
        $TMP_HTML = "<script>parent.payment_processing_fail('".$msg."');</script>";
        echo $TMP_HTML;
    }

	function popup($url, $name, $width, $height) {
        $TMP_HTML = "<script>window.open('".$url."', '".$name."', 'width=".$width.",height=".$height.",scrollbars=yes');</script>";
        echo $TMP_HTML;
    }
    function getDataByPK($arr, $key='idx') {
        $result = array();
        foreach($arr as $item) {
            if(is_array($item)) {
                $result[$item[$key]] = $item;
            } else if(is_object($item)) {
                $result[$item->$key] = $item;
            }
        }
        return $result;
    }
	function getDataByDuplPK($arr, $key='idx') {
        $result = array();
        foreach($arr as $item) {
            if(is_array($item)) {
                $result[$item[$key]][] = $item;
            } else if(is_object($item)) {
                $result[$item->$key][] = $item;
            }
        }
        return $result;
    }
    
    function getDataByDuplPkKeepKey($arr, $key='idx') {
        $result = array();
        foreach($arr as $index=>$item) {
            if(is_array($item)) {
                $result[$item[$key]][$index] = $item;
            } else if(is_object($item)) {
                $result[$item->$key][$index] = $item;
            }
        }
        return $result;
    }
	function indexSort($idxs, $datas, $pk='') {
		if(strlen($pk) > 0) {
			$datas = $this->getDataByPK($datas, $pk);
		}
		$result = array();
		foreach($idxs as $idx) {
			if(isset($datas[$idx]) == false) {
				continue;
			}
			$result[$idx] = $datas[$idx];
		}
		return $result;
	}

	function indexSortAll($idxs, $datas, $pk='') {
		if(strlen($pk) > 0) {
			$datas = $this->getDataByPK($datas, $pk);
		}
		$result = array();
		foreach($idxs as $idx) {
			if(isset($datas[$idx])) {
				$result[$idx] = $datas[$idx];
				unset($datas[$idx]);
			}
		}
		foreach($datas as $data) {
			if($pk == '') {
				$result[] = $data;
			} else {
				$result[$data[$pk]] = $data;
			}
		}
		return $result;
	}

    function getArrayDiff($removeArr, $standArr){
        $result = array();

        $keydiff = array_diff_key($removeArr, $standArr);
        foreach($keydiff as $key =>$val){
            unset($removeArr[$key]);
        }

        $result = array_diff_assoc($standArr, $removeArr);

        return $result;
    }

    function getArrayURLDecode($params){
        $result = array();
        foreach($params as $key =>$val){
            $decode_key = urldecode($key);
            $decode_val = urldecode($val);
            $result[$decode_key] = $decode_val;
        }
        return $result;
    }

    function getMultiArrayURLDecode($data) {
        if(is_array($data)) {
            $reval = array();
            foreach($data as $key=>$val)  {
                $reval[$key] = $this->getMultiArrayURLDecode($val);
            }
        }else {
            $reval = urldecode($data); 
        }
        return $reval;
    }

    function getMultiArrayURLEncode($data) {
        if(is_array($data)) {
            $reval = array();
            foreach($data as $key=>$val)  {
                $reval[$key] = $this->getMultiArrayURLEncode($val);
            }
        }else {
            $reval = urlencode($data); 
        }
        return $reval;
    }


    function multiArrayKSort($data) {
        if(is_array($data) && sizeof($data) > 0) {
            foreach($data as &$value) {
                if(is_array($value) && sizeof($data) > 0) {
                    ksort($value);
                    $value = $this->multiArrayKSort($value);
                }
            }
        }
        return $data;
    }


    function restructArray(array $images) {
        $result = array();
        foreach ($images as $key => $value) {
            foreach ($value as $k => $val) {
                for ($i = 0; $i < count($val); $i++) {
                    $result[$i][$k] = $val[$i];
                }
            }
        }
        return $result;
    }


    function memberContentVar($content, $member_array=array()){
        if(count($member_array) == 0){
            $member_array = array(
                    'u_loginid' =>'',
                    'u_firstname' =>'',
                    'u_lastname' =>'',
                );
        }

        $member_var_list = array(
               'u_loginid'=>'{{customer_email}}',
               'u_firstname'=>'{{customer_firstname}}',
               'u_lastname'=>'{{customer_lastname}}'
            );
        foreach($member_var_list as $key=>$val){
            $content = str_replace($val, $member_array[$key], $content);
        }
        return $content;
    }

    function genJqgridOption($array, $nullset=false){
        $string = ":;";
        if($nullset == true) $string = "";

        foreach($array as $key=>$val){
            $string .= $key.':'.$val.';';
        }
        return substr($string, 0, -1);
    }

    /*
       format define
        $sdate, $edate => 2014-01-01
        $period => day, month, year
        $struct => array()
    */
    function getDummyDateValue($start, $end, $period='day', $struct=array()){
        $duumy_date = array();
        switch($period){
            case 'day' :
                for($i = strtotime($start); $i <= strtotime($end) ; $i+=86400) {
                    $key = date('Y-m-d', $i);
                    $dummy_date[$key] = $struct;
                }
                break;
            case 'month' :
                list($y, $m, $d) = explode('-', $start);
                $start = $y.'-'.$m.'-01';
                list($y, $m, $d) = explode('-', $end);
                $end = $y.'-'.$m.'-01';
                while($start <= $end) {
                    $key = date('Y-m', strtotime($start));
                    $dummy_date[$key] = $struct;
                    list($y, $m, $d) = explode('-', $start);
                    $start = date('Y-m-d', mktime(0,0,0,$m+1, $d, $y));
                }
                break;
            case 'year' :
                list($y, $m, $d) = explode('-', $start);
                $start = $y.'-01-01';
                list($y, $m, $d) = explode('-', $end);
                $end = $y.'-01-01';
                while($start <= $end) {
                    $key = date('Y', strtotime($start));
                    $dummy_date[$key] = $struct;
                    list($y, $m, $d) = explode('-', $start);
                    $start = date('Y-m-d', mktime(0,0,0,$m, $d, $y+1));
                }
                break;
        }
        return $dummy_date;
    }

    function getMicroTime(){
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }


    /* ======================================================= 
       [jQuery Grid] filters => return $params array

       ** Example  ::
       
        @input   ::   $filters  = {"groupOp":"AND","rules":[{"field":"g_id","op":"bw","data":"27092"},{"field":"g_sku","op":"bw","data":"49231"}]}

        @return  ::   $params = Array ( [like_] => Array ( [g_id] => 27092 [g_sku] => 49231 ) )

    ======================================================= */
    function filter_to_params($filters, $search, $between_fields=array()) {

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
			$rules = $filters->rules;
			$groupOperation = $filters->groupOp;
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
							$params['>='][$fieldName] = $fieldData.' 00:00:00';
						}else{ 
							$params['>='][$fieldName] = $fieldData;
						}

					} else if($op_type == 'to') {
						// Date type
						if(preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/", $fieldData)){ 
							$params['<='][$fieldName] = $fieldData." 23:59:59";
						}else{ 
							$params['<='][$fieldName] = $fieldData;
						}
					}
					continue;
				}

				switch ($rule->op) {
					case "eq":
						$fieldOperation = "=";
					break;
					case "ne":
						$fieldOperation = "!=";
					break;
					case "lt":
						$fieldOperation = "<";
					break;
					case "gt":
						$fieldOperation = ">";
					break;
					case "le":
						$fieldOperation = "<=";
					break;
					case "ge":
						$fieldOperation = ">=";
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
						// todo. confirm
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
					break;
					case "ni":
						// todo. confirm
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
					break;
					case "bw":
						$fieldOperation = "like_";
					break;
					case "bn":
						// todo. confirm
						$fieldOperation = "not like_";
					break;
					case "ew":
						$fieldOperation = "_like";
					break;
					case "en":
						// todo. confirm
						$fieldOperation = "not _like";
					break;
					case "cn":
						$fieldOperation = "like";
					break;
					case "nc":
						// todo. confirm
						$fieldOperation = "not like";
					break;
					default:
					$fieldOperation = "";
					break;
				}
				$params[$fieldOperation][$fieldName] = $fieldData;
			}
			//echo print_r($params)."<BR><BR>";
		}

		return $params;
	}



    /* ============================================================


       @operator 
            1. eq : =
            2. 


       ============================================================ */

    public function transDataTableFiltersToParams($filters, $fields=array()) {

        $params = array();

        if( ! isset($filters['columns']) ) {
            return $params;
        }

        $_OP_SPLITER = '_%OP%_';
        $_VALUE_SPLITER = '_%AND%_';


        foreach($filters['columns'] as $row) {

            if(strlen($row['search']['value']) < 1) {
                continue;
            }

            $search = explode($_OP_SPLITER, $row['search']['value']);
            if( ! is_array($search) || sizeof($search) < 1) {
                continue;
            }

            $operator = trim($search[0]);
            $search_text = trim($search[1]);

            if(strlen($search_text) < 1) continue;
            $fieldName = $row['name'];

            switch ($operator) {
                case "eq":
                    $fieldOperation = "=";
					$params[$fieldOperation][$fieldName] = $search_text;
                    break;

                case "ne":
                    $fieldOperation = "!=";
					$params[$fieldOperation][$fieldName] = $search_text;
                    break;

                case "lt":
                    $fieldOperation = "<";
					$params[$fieldOperation][$fieldName] = $search_text;
                    break;

                case "gt":
                    $fieldOperation = ">";
					$params[$fieldOperation][$fieldName] = $search_text;
                    break;

                case "le":
                    $fieldOperation = "<=";
					$params[$fieldOperation][$fieldName] = $search_text;
                    break;

                case "ge":
                    $fieldOperation = ">=";
					$params[$fieldOperation][$fieldName] = $search_text;
                    break;

                case "nn":
                    $fieldOperation = "!=";
					$params[$fieldOperation][$fieldName] = $search_text;
                    break;

                case "in":
                    $fieldOperation = "in";
                    $fieldData = explode($_VALUE_SPLITER, $search_text);
                    $fieldData = array_map('trim', $fieldData);
                    $fieldData = array_filter($fieldData);
					$params[$fieldOperation][$fieldName] = $fieldData;
                    break;

                case "ni":
                    $fieldOperation = "not in";
                    $fieldData = explode($_VALUE_SPLITER, $search_text);
                    $fieldData = array_map('trim', $fieldData);
                    $fieldData = array_filter($fieldData);
					$params[$fieldOperation][$fieldName] = $fieldData;
                    break;

                case "bt":
                    $search_data = explode($_VALUE_SPLITER, $search_text);
                    foreach($search_data as $k=>$fieldData) {

                        if(strlen($fieldData) < 1) continue;
                    
                        // From
                        if($k == 0) {
                            $fieldOperation = ">=";
                            $date_postfix = "00:00:00";

                        // To
                        } else {
                            $fieldOperation = "<=";
                            $date_postfix = "23:59:59";
                        }

                        // Date Type
                        if(preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/", $fieldData)){ 
							$params[$fieldOperation][$fieldName] = $fieldData." ".$date_postfix;
						}else{ 
							$params[$fieldOperation][$fieldName] = $fieldData;
						}
                    }
                    break;

                case "bw":
                    $fieldOperation = "like_";
					$params[$fieldOperation][$fieldName] = $search_text;
                    break;

                case "bn":
                    $fieldOperation = "not like_";
					$params[$fieldOperation][$fieldName] = $search_text;
                    break;

                case "ew":
                    $fieldOperation = "_like";
					$params[$fieldOperation][$fieldName] = $search_text;
                    break;

                case "en":
                    $fieldOperation = "not _like";
					$params[$fieldOperation][$fieldName] = $search_text;
                    break;

                case "cn":
                    $fieldOperation = "like";
					$params[$fieldOperation][$fieldName] = $search_text;
                    break;

                case "nc":
                    $fieldOperation = "not like";
					$params[$fieldOperation][$fieldName] = $search_text;
                    break;

                default:
                $fieldOperation = "";
                break;

            } // END_SWITCH @operator

        
        } // END_FOREACH @filters



        // Searching Box  
        if(isset($filters['search']) && strlen($filters['search']['value']) && sizeof($fields) > 0) {
            $or_data = array();
            foreach($fields as $field) {
                $or_data[] = $field.' LIKE "%'.$filters['search']['value'].'%"';
            }
            $params['or_raw'] = array(implode(' OR ', $or_data));
        }

        return $params;
    }



    public function transDataTableFiltersToExtras($filters) {

        $extras = array();

        if( ! isset($filters['columns']) ) {
            return $extras;
        }

        // Sorting / Ordering
        if(isset($filters['order'])  && sizeof($filters['order']) > 0) {
            $sort_data = array();
            foreach($filters['order'] as $order) {
                $columnName = $filters['columns'][$order['column']]['name'];
                $sort_data[] = $columnName.' '.$order['dir'];
            }
            $extras['order_by'] = $sort_data;
        }

        // Paging Offset/Limit
        if(isset($filters['start'])  && isset($filters['length'])) {
            $extras['offset'] = $filters['start'];
            $extras['limit'] = $filters['length'];
        }else {
            $extras['offset'] = 0;
            $extras['limit'] = 0;
        }

        // 최대 Limit 300 
        if($extras['limit'] < 0) $extras['limit'] = 300;

        return $extras;
    }



        
    public function getYNArray(){
        $arr = array(
                'YES'=>'YES',
                'NO'=>'NO'
            );
        return $arr;
    }
    function getPeriodArray(){
        $arr = array(
                'day'=>'Day',
                'month'=>'Month',
                'year'=>'Year'
            );
        return $arr;
        
    }

	
	// 상품 썸네일 생성
	public function genThumbFiles($goods_ids=array(), $filenum='', $ext='jpg') { // filenum : 안넘기면 상품사진 전체. 넘기면 origin번호 1개만 컨버팅.
		if(is_array($goods_ids) == false && intval($goods_ids) > 0) {
			$goods_ids = array($goods_ids);
		}

		$this->ci->load->model('goods_tb_model_model');

		$thumb_size = $this->ci->goods_tb_model->getThumbSizeMap();

		$msg = array(
			'success' => array(),
			'fail' => array()
		);

		$s = 0;
		$e = 100;
		if(strlen($filenum) > 0) {
			$s = $e = intval($filenum);
		}

		foreach($goods_ids as $goods_id) {
			$path = IMG_PATH.'/';
			$path .= $this->getGoodsImagePath($goods_id).'/';

			for($i = $s ; $i <= $e; $i++) {
				if(is_file($path.'origin'.$i.'.'.$ext) == false) {
					continue;
				}
				$from = $path.'origin'.$i.'.'.$ext;

				$loop_size = $thumb_size;
				foreach($loop_size as $type => $size) {

					// 일반 jpg 이미지 처리 
					$to = $path.'origin'.intval($i).'_'.$type.'.jpg';
					$exec = ('/usr/local/bin/convert '.$from.' -resize '.$size.' -size '.$size.' xc:white +swap -gravity center -composite '.$to);

					// 움직이는 이미지 처리 
					if($ext == 'gif') {
						$to = $path.'origin'.intval($i).'_'.$type.'.gif';
						$exec = ('/usr/local/bin/convert '.$from.' -coalesce -resize '.$size.' -size '.$size.' xc:white +swap -gravity center '.$to);
					}

					
					exec($exec);

					if(is_file($to) == false) {
						$msg['fail'][] = $to;
					} else {
						if($ext == 'gif') {
							exec('mv '.$to.' '.$path.'origin'.intval($i).'_'.$type.'.jpg');
						}
						$msg['success'][] = $to;
					}
				}
				exec('chmod 777 '.$path.'*');
			}
		}
		return $msg;
	}


    // 로그 파일 생성 (년/월 로 디렉토리 생성됨)
    function logWrite($filepath, $logdata, $filename='') {
        $path = realpath(dirname(__FILE__)."/../..")."/appdata/logdata";
        $ymd = date("Ymd", time());
		$filename = (strlen($filename) > 0) ? '_'.$filename : '';

        $log_dir_filepath = $path."/".$filepath;

        if(!is_dir($path."/".$filepath)) { 
            @mkdir($path."/".$filepath, 0777, true);
            @chmod($path."/".$filepath, 0777);
        }
        if(!is_dir($log_dir_filepath.'/'.date('Y'))) { 
            @mkdir($log_dir_filepath.'/'.date('Y'), 0777, true);
            @chmod($log_dir_filepath.'/'.date('Y'), 0777);
        }
        if(!is_dir($log_dir_filepath.'/'.date('Y').'/'.date('m'))) { 
            @mkdir($log_dir_filepath.'/'.date('Y').'/'.date('m'), 0777, true);
            @chmod($log_dir_filepath.'/'.date('Y').'/'.date('m'), 0777);
        }

	if(is_array($logdata)) {
		$logdata = print_r($logdata, true);
	}


        $log_dir_filepath = $path."/".$filepath."/".date('Y')."/".date('m');

        $file_name_log = $log_dir_filepath."/".$ymd.$filename.".log";

        $filep = fopen("$file_name_log","a");
	
	
	    $log_content = "=========== " . date("Y-m-d H:i:s") . " ==========\n";
	    $log_content .= $logdata."\n";
		$log_content .= "=========================================\n\n\n";

        fputs($filep, "$log_content");
        fclose($filep);
		@chmod($file_name_log, 0777);
    }


	public function getGoodsImagePath($goods_id = null) {
		if (true === is_null($goods_id)) {
			return $goods_id;
		}

		$tmp = (strlen($goods_id) < 3) ? $goods_id : substr($goods_id, -3);
		$segment = sprintf("%03d", $tmp);
		$goods_image_dir = $goods_id;

		return $segment . "/" . $goods_image_dir;
	}

    public function rmdirAll($dir) {
        $dirs = dir($dir);
        while(false !== ($entry = $dirs->read())) {
            if(($entry != '.') && ($entry != '..')) {
                if(is_dir($dir.'/'.$entry)) {
                    rmdirAll($dir.'/'.$entry);
                } else {
                    @unlink($dir.'/'.$entry);
                }
            }
        }
        $dirs->close();
        @rmdir($dir);
    }

	// ex) $-3.11 => -$3.11  변환.
	public function convertNegativeNumber($number, $decimal=2, $prefix='$') {
		$negative = '';
		if(strstr($number, '-')) {
			$number = str_replace('-', '', $number);
			$negative = '-';
		}

		return $negative.$prefix.number_format($number, $decimal);
	}
    
    //주소 검색 API  => 구주소 검색시 $type="old"
    public function get_new_address($str_addr, $type=""){
        $data = array();

        include_once('serialize_lib.php');
        $c = new mycall();
        $c->host = "post2.makeshop.co.kr";
        $c->script_name = "/post.html";
        $c->method = "POST";
        $c->add("addr", $str_addr);
        $c->add("charset", "UTF-8");
        $c->add("type", $type);
        $c->exec();
        $post = $c->get_array_data();

        if($post['total'] > 1000){
            $data['code'] = '검색 결과가 너무 많습니다. 다른 검색어를 같이 넣어보세요.';
            $data['is_success'] = FALSE;
        } else if($post['row'] > 0){
            $data['res'] = unserialize($post['post']);
            $data['code'] = "SUCCESS";
            $data['is_success'] = TRUE;
        } else{
            $data['is_success'] = FALSE;
            $data['code'] = "결과가 없습니다.";
        }
        return $data;
    }

    //create history cookie
    public function set_history_cookie($goods_id){
        $cookie_values = $this->ci->input->cookie('history_cookie');

        $cookie_value_array = array();
        if($cookie_values !== FALSE){
            $cookie_value_array = explode(",", $cookie_values);
            
            //overlap check
            foreach($cookie_value_array as $key=>$val){
                if($goods_id == $val){
                    unset($cookie_value_array[$key]);
                    $cookie_value_array = array_values($cookie_value_array);
                }
            }

            //cookie count check ( max = 21 )
            if(count($cookie_value_array) > 20){
                array_pop($cookie_value_array);
            }
        }

        $cookie_value_array = array_merge(array($goods_id), $cookie_value_array);
        $cookie_values = implode(",", $cookie_value_array);

        $cookie = array(
                'name'=>'history_cookie',
                'value'=>$cookie_values,
                'expire'=>'86500',
                );

        $this->ci->input->set_cookie($cookie);
    }


    // CURL 함수
    function restful_curl($url, $param='', $method='POST', $timeout=10, $header=0) {
        $method = (strtoupper($method) == 'POST') ? '1' : '0';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, $header);
        curl_setopt($ch, CURLOPT_POST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_ENCODING, "gzip");
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    function restful_curl_get($url, $port=80){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_PORT, $port);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }


  
    // 신용카드 type 
    function get_ccard_type($cnum) {

        if(strlen($cnum) <= 10) return false;

        $type = '';
        if(in_array(substr($cnum, 0, 2), array(34, 37)) == true) {
            $type = 'Amex';
        }else if(substr($cnum, 0, 1) == '4') {
            $type = 'Visa';
        }else if(intVal(substr($cnum, 0, 2)) >= 51 && intVal(substr($cnum, 0, 2)) <= 55) {
            $type = 'MasterCard'; 
        }else if(
            substr($cnum, 0, 2) == '65'
            || (intVal(substr($cnum, 0, 3)) >= 644 && intVal(substr($cnum, 0, 3)) <= 649)
            || substr($cnum, 0, 5) == '60110'
            || (intVal(substr($cnum, 0, 5)) >= 60112 && intVal(substr($cnum, 0, 5)) <= 60114)
            || (intVal(substr($cnum, 0, 6)) >= 601174 && intVal(substr($cnum, 0, 6)) <= 601179)
            || (intVal(substr($cnum, 0, 6)) >= 601186 && intVal(substr($cnum, 0, 6)) <= 601199)
			|| (intVal(substr($cnum, 0, 8)) >= 62212600 && intVal(substr($cnum, 0, 8)) <= 62292599)
			|| (intVal(substr($cnum, 0, 8)) >= 62400000 && intVal(substr($cnum, 0, 8)) <= 62699999)
			|| (intVal(substr($cnum, 0, 8)) >= 62820000 && intVal(substr($cnum, 0, 8)) <= 62889999)
		) {
            $type = 'Discover';
		} else if((intVal(substr($cnum, 0, 4)) >= 3528 && intVal(substr($cnum, 0, 4)) <= 3589)) {
			$type = 'JCB';
		} else if(
			(intVal(substr($cnum, 0, 3)) >= 300 && intVal(substr($cnum, 0, 3)) <= 305)
            || substr($cnum, 0, 4) == '3095'
            || substr($cnum, 0, 2) == '36'
			|| (intVal(substr($cnum, 0, 2)) >= 38 && intVal(substr($cnum, 0, 2)) <= 39)
		) {
			$type = 'DinersClub';
		}


        if(
			intVal(substr($cnum, 0, 2)) == 62
		) {
			$type = 'UnionPay';
        }
 
        return $type;
    }

    function password_validate($pw){
        $pw = strtolower($pw);
         if((preg_match('/\d/',$pw) == true && preg_match('/[a-z]/',$pw) == true) == false) {
             return FALSE;
         }
		 if(strlen($pw) < 8) {
			return FALSE;
		 }
         return TRUE;
    }

    function admin_password_validate($pw='') {
        if(strlen($pw) < 7) {
            return FALSE;
        }

        $alphas = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $nums = '0123456789';

        $alpha_cnt = 0;
        $num_cnt = 0;
        $sp_cnt = 0;
        
        for($i = 0; $i < strlen($pw); $i++) {
            if(strpos($alphas, $pw[$i]) !== false) {
                $alpha_cnt++;
                continue;
            }
            
            if(strpos($nums, $pw[$i]) !== false) {
                $num_cnt++;
                continue;
            }
            $sp_cnt++;
        }

        //echo "$alpha_cnt | $num_cnt | $sp_cnt";

        if($alpha_cnt * $num_cnt * $sp_cnt == 0) {
            return FALSE;
        }

        return TRUE;
    }



    function validate_password($pw) {
        $num = preg_match('/[0-9]/u', $pw);
        $eng = preg_match('/[a-z]/u', $pw);
        $spe = preg_match("/[\!\@\#\$\%\^\&\*]/u",$pw);

        if(strlen($pw) < 10 || strlen($pw) > 20) {
            //return array(false,"비밀번호는 영문, 숫자, 특수문자를 혼합하여 최소 10자리 ~ 최대 30자리 이내로 입력해주세요.");
            return array(false, 'SHORT_LENGTH');
            exit;
        }

        if(preg_match("/\s/u", $pw) == true) {
            //return array(false, "비밀번호는 공백없이 입력해주세요.");
            return array(false, 'EXITS_BLANK');
            exit;
        }

        if( $num == 0 || $eng == 0 || $spe == 0) {
            //return array(false, "영문, 숫자, 특수문자를 혼합하여 입력해주세요.");
            return array(false, 'NOT_MIXED');
            exit;
        }
        return array(true, 'SUCCESS');
    }


    
    /* 
       어드민 히스토리 기록
       log_array example :::
       array(
        'params'=>array() or string,
        'target'=>array() or string,
        ...
        );

       */
    function write_history_log($sess = array(), $act_mode, $act_key, $log_array, $table){

        if( ! is_array($sess) || sizeof($sess) < 1) {
            $loginid = $this->ci->_ADMIN_DATA['login_id'];
            $name    = $this->ci->_ADMIN_DATA['name'];
        }else {
            $loginid = $sess['login_id'];
            $name = $sess['name'];
        }


        $serialize = serialize($log_array);

        $params = array();
        $params['h_loginid'] = $loginid;
        $params['h_name'] = $name;
        $params['h_act_mode'] = $act_mode;
        $params['h_act_key'] = $act_key;
        $params['h_serialize'] = $serialize;
        $params['h_act_table'] = $table;

        $this->ci->load->model('history_tb_model');

        $reval = $this->ci->history_tb_model->doInsert($params)->isSuccess();
        return $reval;
    }

	function get_nation_ship_price($weight, $nation) {
		// index.php daemon create_shipping_price
		// CLI 모드로 실행시켜 얻은 결과값.

        switch($nation) {
            case 'KR':
                $deliprice_by_weight = array();
                $deliprice_by_weight['1lbs'] = 15.00;
                $deliprice_by_weight['2lbs'] = 15.00;
                $deliprice_by_weight['3lbs'] = 15.00;
                $deliprice_by_weight['4lbs'] = 17.00;
                $deliprice_by_weight['5lbs'] = 19.00;
                $deliprice_by_weight['6lbs'] = 21.00;
                $deliprice_by_weight['7lbs'] = 23.00;
                $deliprice_by_weight['8lbs'] = 25.00;
                $deliprice_by_weight['9lbs'] = 27.00;
                $deliprice_by_weight['10lbs'] = 29.00;
                $deliprice_by_weight['11lbs'] = 31.00;
                $deliprice_by_weight['12lbs'] = 33.00;
                $deliprice_by_weight['13lbs'] = 35.00;
                $deliprice_by_weight['14lbs'] = 37.00;
                $deliprice_by_weight['15lbs'] = 39.00;
                $deliprice_by_weight['16lbs'] = 41.00;
                $deliprice_by_weight['17lbs'] = 43.00;
                $deliprice_by_weight['18lbs'] = 45.00;
                $deliprice_by_weight['19lbs'] = 47.00;
                $deliprice_by_weight['20lbs'] = 49.00;
                $deliprice_by_weight['21lbs'] = 51.00;
                $deliprice_by_weight['22lbs'] = 53.00;
                $deliprice_by_weight['23lbs'] = 55.00;
                $deliprice_by_weight['24lbs'] = 57.00;
                $deliprice_by_weight['25lbs'] = 59.00;
                $deliprice_by_weight['26lbs'] = 61.00;
                $deliprice_by_weight['27lbs'] = 63.00;
                $deliprice_by_weight['28lbs'] = 65.00;
                $deliprice_by_weight['29lbs'] = 67.00;
                $deliprice_by_weight['30lbs'] = 69.00;
                $deliprice_by_weight['31lbs'] = 71.00;
                $deliprice_by_weight['32lbs'] = 73.00;
                $deliprice_by_weight['33lbs'] = 75.00;
                $deliprice_by_weight['34lbs'] = 77.00;
                $deliprice_by_weight['35lbs'] = 79.00;
                $deliprice_by_weight['36lbs'] = 81.00;
                $deliprice_by_weight['37lbs'] = 83.00;
                $deliprice_by_weight['38lbs'] = 85.00;
                $deliprice_by_weight['39lbs'] = 87.00;
                $deliprice_by_weight['40lbs'] = 89.00;
                $deliprice_by_weight['41lbs'] = 91.00;
                $deliprice_by_weight['42lbs'] = 93.00;
                $deliprice_by_weight['43lbs'] = 95.00;
                $deliprice_by_weight['44lbs'] = 97.00;
                $deliprice_by_weight['45lbs'] = 99.00;
                $deliprice_by_weight['46lbs'] = 101.00;
                $deliprice_by_weight['47lbs'] = 103.00;
                $deliprice_by_weight['48lbs'] = 105.00;
                $deliprice_by_weight['49lbs'] = 107.00;
                $deliprice_by_weight['50lbs'] = 109.00;
                $deliprice_by_weight['51lbs'] = 110.75;
                $deliprice_by_weight['52lbs'] = 112.50;
                $deliprice_by_weight['53lbs'] = 114.25;
                $deliprice_by_weight['54lbs'] = 116.00;
                $deliprice_by_weight['55lbs'] = 117.75;
                $deliprice_by_weight['56lbs'] = 119.50;
                $deliprice_by_weight['57lbs'] = 121.25;
                $deliprice_by_weight['58lbs'] = 123.00;
                $deliprice_by_weight['59lbs'] = 124.75;
                $deliprice_by_weight['60lbs'] = 126.50;
                break;
            case 'HK':
                $deliprice_by_weight = array();
                $deliprice_by_weight['1lbs'] =  77.06;
                $deliprice_by_weight['2lbs'] =  91.62;
                $deliprice_by_weight['3lbs'] =  106.56;
                $deliprice_by_weight['4lbs'] =  117.44;
                $deliprice_by_weight['5lbs'] =  133.51;
                $deliprice_by_weight['6lbs'] =  142.72;
                $deliprice_by_weight['7lbs'] =  154.75;
                $deliprice_by_weight['8lbs'] =  161.17;
                $deliprice_by_weight['9lbs'] =  170.62;
                $deliprice_by_weight['10lbs'] =  177.26;
                $deliprice_by_weight['11lbs'] =  185.07;
                $deliprice_by_weight['12lbs'] =  192.76;
                $deliprice_by_weight['13lbs'] =  200.51;
                $deliprice_by_weight['14lbs'] =  208.26;
                $deliprice_by_weight['15lbs'] =  216.01;
                $deliprice_by_weight['16lbs'] =  223.76;
                $deliprice_by_weight['17lbs'] =  231.51;
                $deliprice_by_weight['18lbs'] =  239.26;
                $deliprice_by_weight['19lbs'] =  247.01;
                $deliprice_by_weight['20lbs'] =  254.76;
                $deliprice_by_weight['21lbs'] =  262.31;
                $deliprice_by_weight['22lbs'] =  269.86;
                $deliprice_by_weight['23lbs'] =  277.41;
                $deliprice_by_weight['24lbs'] =  284.96;
                $deliprice_by_weight['25lbs'] =  292.51;
                $deliprice_by_weight['26lbs'] =  300.06;
                $deliprice_by_weight['27lbs'] =  307.61;
                $deliprice_by_weight['28lbs'] =  315.16;
                $deliprice_by_weight['29lbs'] =  322.71;
                $deliprice_by_weight['30lbs'] =  330.26;
                $deliprice_by_weight['31lbs'] =  337.81;
                $deliprice_by_weight['32lbs'] =  345.36;
                $deliprice_by_weight['33lbs'] =  352.91;
                $deliprice_by_weight['34lbs'] =  360.46;
                $deliprice_by_weight['35lbs'] =  368.01;
                $deliprice_by_weight['36lbs'] =  375.56;
                $deliprice_by_weight['37lbs'] =  383.11;
                $deliprice_by_weight['38lbs'] =  390.66;
                $deliprice_by_weight['39lbs'] =  398.21;
                $deliprice_by_weight['40lbs'] =  405.76;
                $deliprice_by_weight['41lbs'] =  413.31;
                $deliprice_by_weight['42lbs'] =  420.86;
                $deliprice_by_weight['43lbs'] =  428.41;
                $deliprice_by_weight['44lbs'] =  435.96;
                $deliprice_by_weight['45lbs'] =  443.51;
                $deliprice_by_weight['46lbs'] =  451.06;
                $deliprice_by_weight['47lbs'] =  458.61;
                $deliprice_by_weight['48lbs'] =  466.16;
                $deliprice_by_weight['49lbs'] =  473.71;
                $deliprice_by_weight['50lbs'] =  481.26;
                $deliprice_by_weight['51lbs'] =  489.00;
                $deliprice_by_weight['52lbs'] =  496.74;
                $deliprice_by_weight['53lbs'] =  504.48;
                $deliprice_by_weight['54lbs'] =  512.22;
                $deliprice_by_weight['55lbs'] =  519.96;
                $deliprice_by_weight['56lbs'] =  527.70;
                $deliprice_by_weight['57lbs'] =  535.44;
                $deliprice_by_weight['58lbs'] =  543.18;
                $deliprice_by_weight['59lbs'] =  550.92;
                $deliprice_by_weight['60lbs'] =  558.66;
                $deliprice_by_weight['61lbs'] =  566.40;
                $deliprice_by_weight['62lbs'] =  574.14;
                $deliprice_by_weight['63lbs'] =  581.88;
                $deliprice_by_weight['64lbs'] =  589.62;
                $deliprice_by_weight['65lbs'] =  597.36;
                $deliprice_by_weight['66lbs'] =  605.10;
                $deliprice_by_weight['67lbs'] =  612.84;
                $deliprice_by_weight['68lbs'] =  620.58;
                $deliprice_by_weight['69lbs'] =  628.32;
                $deliprice_by_weight['70lbs'] =  636.06;
                $deliprice_by_weight['71lbs'] =  643.03;
                $deliprice_by_weight['72lbs'] =  650.00;
                $deliprice_by_weight['73lbs'] =  656.97;
                $deliprice_by_weight['74lbs'] =  663.94;
                $deliprice_by_weight['75lbs'] =  670.91;
                $deliprice_by_weight['76lbs'] =  677.88;
                $deliprice_by_weight['77lbs'] =  684.85;
                $deliprice_by_weight['78lbs'] =  691.82;
                $deliprice_by_weight['79lbs'] =  698.79;
                $deliprice_by_weight['80lbs'] =  705.76;
                $deliprice_by_weight['81lbs'] =  712.73;
                $deliprice_by_weight['82lbs'] =  719.70;
                $deliprice_by_weight['83lbs'] =  726.67;
                $deliprice_by_weight['84lbs'] =  733.64;
                $deliprice_by_weight['85lbs'] =  740.61;
                $deliprice_by_weight['86lbs'] =  747.58;
                $deliprice_by_weight['87lbs'] =  754.55;
                $deliprice_by_weight['88lbs'] =  761.52;
                $deliprice_by_weight['89lbs'] =  768.49;
                $deliprice_by_weight['90lbs'] =  775.46;
                $deliprice_by_weight['91lbs'] =  782.43;
                $deliprice_by_weight['92lbs'] =  789.40;
                $deliprice_by_weight['93lbs'] =  796.37;
                $deliprice_by_weight['94lbs'] =  803.34;
                $deliprice_by_weight['95lbs'] =  810.31;
                $deliprice_by_weight['96lbs'] =  817.28;
                $deliprice_by_weight['97lbs'] =  824.25;
                $deliprice_by_weight['98lbs'] =  831.22;
                $deliprice_by_weight['99lbs'] =  838.19;
                $deliprice_by_weight['100lbs'] =  845.16;
                break;
            default:    // 정의되지 않은 국가 
                return false;
                break;
        }

		if($weight <= 0) {
			return $deliprice_by_weight['1lbs'];
		}


		$weight = ceil($weight).'lbs';

		$price = 126.50;
		if(isset($deliprice_by_weight[$weight])) {
			$price = $deliprice_by_weight[$weight];
		}
		return $price;
	}

    public function get_field_names($table){
        $field = $this->ci->db->list_fields($table);
        return $field;
    }

    public function table_exists($table){
        if($this->ci->db->table_exists($table)){
            return TRUE;
        } else{
            return FALSE;
        }
    }

    public function get_field_data($table){
        $data = $this->ci->db->field_data($table);
        return $data;
    }

    //미국지사 IP 체크
    public function us_ip_check(){
        $us_ip = array(
				'69.178.138.162',
                );
    
        $ip = $_SERVER['REMOTE_ADDR'];

        if(in_array($ip, $us_ip)){
            return TRUE;
        }
        return FALSE;
    }

    public function auth_dev(){
        $dev_id = array(
                    '82joong',
                );
        $dev_ip = array(
						'115.41.221.177',   // 김현중
						'115.41.221.255',   // 김현중 맥
						'14.129.31.215',    // joong VPN
                        '14.129.47.64',     // 김현중
                        '210.217.16.28'     // 김현중
                );


        $sess = $this->ci->session->userdata('admin');

        $id = $sess['login_id'];
        $ip = $_SERVER['REMOTE_ADDR'];

        if(in_array($id, $dev_id) && in_array($ip, $dev_ip)){
            return TRUE;
        }
        return FALSE;
    }


    //특정페이지 특정IP 오픈 체크시 사용
    public function auth_emp(){
        $emp_ip = array(
                '115.41.221.147',   //함승목
                '115.41.221.177',   //김현중
                '115.41.221.179',   //김재호
                '115.41.221.176',   //이정희
                '14.129.44.49',     //최승식
                '14.129.43.123',    //차용익
                '14.129.43.124',    //이예경
                '14.129.43.130',    //김성경
                '61.36.82.72',		//김성경
                '64.60.8.194'       //미국
                );

        $ip = $_SERVER['REMOTE_ADDR'];

        if(in_array($ip, $emp_ip)){
            return TRUE;
        }
        return FALSE;
    }

    //부분 환불 정보가 있는지 확인하여 있는 경우에는 환불 정보를 넘겨준다. 없는 경우에는 return FALSE;
    public function partial_refund_check($origin_info, $refund_array){
        $reval = array(
                'is_refund'=>FALSE,
                'msg'=>'default'
                );

        //환불정보가 존재하는지에 대해 검사
        if(is_array($refund_array) && sizeof($refund_array) > 0){
            //넘어온 아이템에 대한 환불정보가 있는지 체크
            if(array_key_exists($origin_info['oi_goods_id'], $refund_array)){
                //환불수량이 없는 경우 return FALSE
                if($refund_array[$origin_info['oi_goods_id']]['qty']  == 0){
                    $reval['msg'] = 'No refund amount.';
                    return $reval;
                }
                //해당 상품 전체환불인 경우
                //TODO. == 로 조건 수정 필요 환불량이 주문량보다 클수는 없음.
                if($refund_array[$origin_info['oi_goods_id']]['qty'] >= $origin_info['oi_qty_ordered']){
                    $reval['is_refund'] = TRUE;
                    $reval['msg'] = 'ALL_REFUND';
                } else{ //부분환불인 경우 변경 정보 넘겨줌
                    $qty = $origin_info['oi_qty_ordered'] - $refund_array[$origin_info['oi_goods_id']]['qty'];

                    $reval['is_refund'] = TRUE;
                    $reval['msg'] = 'PARTIAL_REFUND';
                    $reval['data'] = array(
                            'modify_qty'=>$qty,
                            );
                }
            }
        }
        return $reval;
    }

    public function make_refund_table($refund_serialize){
        $refund_info = unserialize($refund_serialize);
        $table = '<table>';
        foreach($refund_info as $list){
            if(!isset($list['name'])) $list['name'] = '-';
            $table .= '<tr><td style="font-size:11px;">'.$list['name'].'<td>';
            $table .= '<td width="20%" style="text-align:right; font-size:11px;">'.$list['qty'].'<td></tr>';
        }
        $table .= '</table>';
        return $table;
    }




    public function replace_search_text($string){
        $replace_string = '';
		$replace_string = trim(strip_tags($string));
		$replace_string = str_replace("'", " ", $replace_string);
		$replace_string = str_replace('"', " ", $replace_string) ;
		$replace_string = str_replace('@', " ", $replace_string);
		$replace_string = str_replace('*', " ", $replace_string);
		$replace_string = str_replace('(', " ", $replace_string);
		$replace_string = str_replace(')', " ", $replace_string);
		$replace_string = str_replace('.', " ", $replace_string);
		$replace_string = str_replace('-', " ", $replace_string);
		$replace_string = str_replace('’', " ", $replace_string);
		$replace_string = str_replace("\\", " ", $replace_string);

        return $replace_string;
    }


	public function check_redirect_map($uri, $type='PC') {

		// SHOP <-> MOBILE간 redirect 할 URI 등록 
		// key : SHOP URL  / value : MOBILE URL
		$uri_map = array(
			'/product/category/530'	=> '/product/event_main',
		);

		if($type == 'MOBILE') {
			$uri_map = array_flip($uri_map);

			// 모바일 이벤트 상세페이지는 샵에 없는 페이지이기 때문에 category 530으로 보냄.  (이벤트 상세페이지는 여러개일 수 있음)
			if(strpos($uri, '/product/event/') !== false) {
				$uri_map[$uri] = '/product/category/530';
			}
		}

		if(in_array($uri, array_keys($uri_map)) == true) {
			return $uri_map[$uri];
		}

		return $uri;
	}

	private $XDigitsChars = array(
						'V','C','S','U','A',
						'K','D','G','J','L'
						,'M','Z','N','H','X',
						'Q','R','P','T','W',
						'B','Y','I','O'
						);
	public function getUserCode($usernum) {// 사용자 프로모션 코드. user_tb.u_id 를 인자로 넘기면 해당 사용자 코드를 반환.
		if(strlen($usernum) < 6) {
			$usernum = sprintf('%06d', $usernum);
		}
		
		$tail = substr($usernum, -3);
		$num = substr($usernum, 0, strlen($usernum)-3);

		$xdigits_chars_count = sizeof($this->XDigitsChars);
				
		$res = $this->createXDigits($num);
		return $res.$tail;
	}
	public function createXDigits($num, $start_idx=0, $jump=5, $dic=array()) {
		if(sizeof($dic) <= 0) {
				$dic = $this->XDigitsChars;
		}

		$len = sizeof($dic);

		$key  = $start_idx;//시작숫자 다양하도록
		$jump = $jump;//자리수마다 1이 의미하는 알파멧이 바뀌도록. $term 에 더해짐. 
		$term = 0;

		for($i = 1 ; $i < 6 ; $i++) {

				$temp_dic = array();

				for($j = 0 ; $j < $len ; $j++) {
						$temp_dic[] = $dic[($key + $j + $term) % $len];
				}
				${'dic'.$i} = $temp_dic;
				$term += $jump;
		}

		$calc = $num;
		$gen_num_arr = array();

		do { //$num 을 $len진수로 변환.
				$gen_num = floor($calc / $len);
				$gen_num_tail = $calc % $len;

				$gen_num_arr[] = $gen_num_tail;
				$calc = $gen_num;

		}while($calc >= $len);

		$gen_num_arr[] = $calc;
		$res_arr = array();

		$i = (sizeof($gen_num_arr) <= 3) ? 3 : sizeof($gen_num_arr); //이건 4자리로 늘어나면 4자리 출력하는 부분. 함승목.
		//$i = 3; //이건 무조건 세자리. 함승목.

		$var_i = 1;
		for($i-- ; $i >= 0 ; $i--) {
				$dic_idx = isset($gen_num_arr[$i]) ? $gen_num_arr[$i] : 0;
				$res_arr[] = ${'dic'.$var_i++}[intval($dic_idx)];
		}

		// shake
		$temp = $res_arr[sizeof($res_arr)-1];
		$res_arr[sizeof($res_arr)-1] = $res_arr[1];
		$res_arr[1] = $temp;

		return implode('', array_reverse($res_arr));
	}


    public function set_vcode_cookie($write_vcode='') {
        $vcode = $this->ci->input->get('vcode') ? $this->ci->input->get('vcode') : '';
		if(strlen($write_vcode) >= 6) {
			// 장바구니에서 수정시 Cart 컨트롤러 ajax_check_referral 에서만 $write_vcode 에 값 담아 호출
			$vcode = $write_vcode;
		}
		$vcode = trim($vcode);

        if(strlen($vcode) > 0) {
            $cookie_data = array(
                'name' => 'vcode', 
                'value' => strtoupper($vcode),
                'expire'=>'86500'
            );
            $this->ci->input->set_cookie($cookie_data);

			if((strlen($write_vcode) >= 6) == false) {
				// base 에서 링크타고 와서 호출됨. redirect
				redirect("/".$this->ci->uri->uri_string());
			}
        }
    }

    public function delete_vcode_cookie() {
        $cookie_data = array(
            'name' => 'vcode', 
            'expire'=>''
        );
        $this->ci->input->set_cookie($cookie_data);
    }

    function base64url_encode($data) { 
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); 
    } 

    function base64url_decode($data) { 
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT)); 
    } 

    /* 
       모바일 장치 정보 가져오기
       @ platform : ANDROID / IOS / WEB
       todo. taillist에서 불필요함 함수가 복사됨. 삭제필요.
    */
    public function get_device_info() {
        $reval = array();
        $this->ci->load->library('user_agent');
        $device_data = explode('_smartdev_', $this->ci->agent->agent_string());

        $reval['old_key'] = '';
        if(isset($device_data[1]) && strlen($device_data[1]) > 0) {
            $agent_data = explode('/', $device_data[1]);

            $reval['platform'] = $agent_data[0];
            $reval['device_key'] = $agent_data[1];
            if(isset($device_data[2]) && strlen($device_data[2]) > 10){
                $reval['old_key'] = $agent_data[2];
            }
        }else {
            $reval['platform'] = 'WEB';
            $reval['device_key'] = '';
        }
        return $reval;
    }
    
    public function push_alert_time_check($start, $end, $sent_time){
        if($end >= $start){
            if($sent_time >= $start && $sent_time <= $end){
                return TRUE;
            }
        } else {
            if($sent_time >= $start || $send_time <= $end){
                return TRUE;
            }
        }
        return FALSE;
    }


	// DHL은 주소를 최대 3줄까지 연동가능. 1줄당 35자까지만 입력가능. 주소를 나눠서 3줄로 반환 하거나 false 처리
	public function check_dhl_addresses($addr1, $addr2 = '') {
		$str = $addr1;
		if(strlen($addr2) > 0) {
			$str .= ' '.$addr2;
		}

		$address = explode(' ', $str);

		$lines = array();
		$line = '';
		foreach($address as $addr) {
			if(strlen($line.' '.$addr) <= 35) {
				if(strlen($line) > 0) {
					$line .= ' ';
				}
				$line .= $addr;
				continue;
			}

			$lines[] = $line;
			$line = $addr;
		}
		if(strlen($line) > 0) {
			$lines[] = $line;
		}

		if(sizeof($lines) > 3) {
			return false;
		}
		return $lines;
	}


    public function convert_currency($price, $from_currency='USD', $to_currency='KRW', $is_former=true) {
        if($this->transCurrency < 0) {
            $result = $this->get_currency_info_file($from_currency, $to_currency);
            $this->transCurrency = $result[$from_currency.'_'.$to_currency]['rates'];
        }
        $trans_val = (($price * 100) * $this->transCurrency)/100;

		if($is_former == true) {
			return number_format(round($trans_val, 2));
		}
		return round($trans_val,2);
    }

	/*
	※ 주의!!!!!!!!!  Admin에서만 보여져야 하는 데이터 get_hana_kr_currency...... 관련 메소드들.
	   admin_setting_tb에서 as_kr_payment_currency의 원본데이터정보(=하나은행에서 크롤링 된 환율)
	*/
	public function get_hana_kr_currency() {
		$result = $this->get_hana_kr_currency_info_file();
		return isset($result['orign_currency'])?$result['orign_currency']+2:'';
	}

	public function get_hana_kr_currency_info_file() {
		$kr_currency_file_path = DISPLAY_PATH.'/hana_kr_currency_rate.info';
		
		$result = array();
		if(is_file($kr_currency_file_path)) {
			$result = file_get_contents($kr_currency_file_path);	
			$result = unserialize($result);

			/*
			Array
			(
				[orign_currency] => 1121	// 하나은행송금환율(=원본)
				[commission] => 1.01		// 수수료
				[kr_currency] => 1132.21	// 수수료 포함된 최종환율
				[updated_at] => 2019-04-29 13:45:22	// 업데이트날짜
			)
			*/
		}
		return $result;
	}

    public function get_currency_info_file($from_currency='USD', $to_currency='KRW', $return_rate=false) {

        $key_name = $from_currency.'_'.$to_currency;
        $result = array(
            $key_name => array(
                'rates' => '',
                'time'  =>  ''
            )
        );

        $currency_file_path = DISPLAY_PATH.'/currency_rates.info';
        if(is_file($currency_file_path)) {
            $result = file_get_contents($currency_file_path);
            $result = unserialize($result);
        }else {
            if($this->set_currency_info_file($from_currency, $to_currency)) {
                $result = file_get_contents($currency_file_path);
                $result = unserialize($result);
            }
        }


		// 환율 정보만 리턴 (제휴사 주문건 업로드에서 사용)
		if($return_rate == true) {
            return $result[$from_currency.'_'.$to_currency]['rates'];
		}
        return $result;
    }


    public function set_currency_info_file($from_currency='USD', $to_currency='KRW') {
		$from_to_arr = array(
			'USD' => 'KRW',
			'KRW' => 'USD',
			'CNY' => 'USD',
			'JPY' => 'USD',
			'EUR' => 'USD'
		);
		$current_info = array();
		
		$currency_file_path = DISPLAY_PATH.'/currency_rates.info';
		$cur_data = array();
		if(is_file($currency_file_path)) {
			$cur_data = file_get_contents($currency_file_path);
			$cur_data = unserialize($cur_data);
		}
		
		/*
		# 18.06.01부로 서비스 종료됨에 따라 api 불러오는 정보 URL 변경
		# API를 1달에 1000번만 불러올 수 있고, EUR에 대한 환율정보만 불러오기 때문에 
		  API는 한번만 실행하도록 변경.
			{
			  "success": true,
			  "timestamp": 1519296206,
			  "base": "EUR",
			  "date": "2018-06-18",
			  "rates": {
				  "AUD": 1.566015,
				  "CAD": 1.560132,
				  "CHF": 1.154727,
				  "CNY": 7.827874,
				  "GBP": 0.882047,
				  "JPY": 132.360679,
				  "USD": 1.23396,
				  [...]
			  }
		  }
		*/
		//$url = 'http://api.fixer.io/latest?base='.$from_currency;
		$access_key ='34ce1064179e7d6aef45f02a5971d17f'; 
		$url = 'http://data.fixer.io/api/latest?access_key='.$access_key;
		$rate_info = file_get_contents($url);
		foreach($from_to_arr as $from_currency => $to_currency) {
			$is_success = false; 

			if(strlen($rate_info) > 0) {
				$result = json_decode($rate_info, true);
				if(isset($result['rates']) && $result['rates'][$from_currency] > 0) {
					$pow = 1 / $result['rates'][$from_currency];
					$result['rates'][$from_currency] = $pow;
					$result['base'] = $from_currency;	
				}

				if(isset($result['rates'][$to_currency]) && $result['rates'][$to_currency] > 0) {
					$is_success = true;
					$result['rates'][$to_currency] = $pow * $result['rates'][$to_currency];
					$current_info[$from_currency.'_'.$to_currency]['rates'] = sprintf('%.10f', str_replace(',','',$result['rates'][$to_currency]));
					$current_info[$from_currency.'_'.$to_currency]['time'] = date("Y-m-d H:i:s");
				} else { // 기존 데이터 그대로 쓰자
					$current_info[$from_currency.'_'.$to_currency]['rates'] = $cur_data[$from_currency.'_'.$to_currency]['rates'];
					$current_info[$from_currency.'_'.$to_currency]['time'] = $cur_data[$from_currency.'_'.$to_currency]['time'];
				}
			}


			// 위에 사이트에서 데이터 못가져오면 여기서 가져오자.(http://www.webservicex.net/ < 여기 종종 죽네..)
			if($is_success == false) {
				if(!isset($replace_rate_info)) {
					$replace_access_key = '5cbe8d1ea358c3f0138b3a57c97c2342';
					$replace_url = 'http://www.apilayer.net/api/live?access_key='.$replace_access_key;
					$replace_rate_info = file_get_contents($replace_url);
				}

				if(strlen($replace_rate_info) > 0) {
					$result = json_decode($replace_rate_info, true);
					$pow = 1/$result['quotes']['USD'.$from_currency];
					$result['quotes']['USD'.$from_currency] = $pow;
					$result['source'] = $from_currency;
				}
				$cur_key = $from_currency.'_'.$to_currency;

				if(isset($result['quotes']['USD'.$to_currency]) && $result['quotes']['USD'.$to_currency] > 0) {
					$result['quotes']['USD'.$to_currency] = $pow * $result['quotes']['USD'.$to_currency];
					$current_info[$cur_key]['rates'] = sprintf('%.10f', str_replace(',','',$result['quotes']['USD'.$to_currency]));
					$current_info[$cur_key]['time'] = date("Y-m-d H:i:s");
				}
			}
		}


		$display_path = DISPLAY_PATH.'/currency_rates.info';
        $write_info = serialize($current_info);

        
        if( ! file_exists($display_path)) {
            return false;
        }

        if(file_put_contents($display_path, $write_info)) {
            return true;
        }else {
            return false;
        }
    }

    public function get_maintool_banner_filters($maintool_data, $key='') {
        $data = array();

        // maintool 설정 option > tab > is_use = false 일때, (롤링되는 이미지로 간주한다)
        if(in_array('banner', array_keys($maintool_data), true)) {
            foreach($maintool_data['banner'] as $row) {
                $data[]['banner'][] = $row;
            }
        }else {
            $data = $maintool_data;
        }

        $reval = array();
        foreach($data as $k=>$row) {
            $banner_id = 0;

           if(!isset($row['banner'][$banner_id]) || strlen($row['banner'][$banner_id]['src']) <= 0) continue;

            $banner_info = $row['banner'][$banner_id];
            if(isset($banner_info['sdate'])) {
                if(strtotime($banner_info['sdate']) > time()) continue;
            }
            if(isset($banner_info['edate'])) {
                if(strtotime($banner_info['edate']) < time()) continue;
            }
            $mtime = is_file(DISPLAY_PATH.'/../../'.$banner_info['src']) ? filemtime(DISPLAY_PATH.'/../../'.$banner_info['src']) : '0';
            if($mtime == '0') continue;

            $tmp['name'] = isset($row['text']) ? $row['text'][0]['name'] : '';
            $tmp['img'] = GOODS_IMG_BASEURL.$banner_info['src'].'?v='.$mtime;

			/*
            $app_target_link = $this->maintool_apptarget($row);
            $tmp['type'] = $app_target_link['type'];
            $tmp['data'] = $app_target_link['data'];
			*/
            $tmp['url'] = $banner_info['url'];

            // URL 미지정 시, SHOP_URL로 처리
            $tmp['banner_url'] = '';
            if(isset($banner_info['url'])) {
                $tmp['banner_url'] = SHOP_URL.$banner_info['url'];
                if(
                    strpos($banner_info['url'], 'http://') !== false ||
                    strpos($banner_info['url'], 'https://') !== false ||
                    strpos($banner_info['url'], 'www.') !== false
                ) {
                    $tmp['banner_url'] = $banner_info['url'];
                    $tmp['url'] = $tmp['banner_url'];
                }
            }

            $tmp['target'] = isset($banner_info['target']) ? $banner_info['target'] : '';
            $tmp['map'] = isset($banner_info['map']) ? $banner_info['map'] : '';

            // map_name 추가
            $tmp['map_name'] = '';
            if(strlen($tmp['map']) > 0) {
                $dom = new DOMDocument();
                @$dom->loadHTML($tmp['map']);
                $map = $dom->getElementsByTagName('map')->item(0);
                $tmp['map_name'] = $map->getAttribute("name");
            }
            $reval[$k] = $tmp;
        }
        if(isset($key) && strlen($key) > 0) {
            $reval = $this->getDataByPK($reval, 'name');
            if(isset($reval[$key])) $reval = $reval[$key];
            else $reval = array();
        }
        return $reval;
    }


	public function get_vendor_list() {
		$this->ci->load->model('vendor_tb_model');
		$params = array();
		$extra = array();
		$extra['order_by'] = array('v_name asc');
		$extra['fields'] = array('v_id', 'v_name');
		$extra['cache_sec'] = 86400;
		$extra['slavedb'] = true;
		$vendor = $this->ci->vendor_tb_model->getList($params, $extra)->getData();
		$vendor_map = array();
		foreach($vendor as $v) {
			$vendor_map[$v['v_id']] = $v['v_name'];
		}

		return $vendor_map;
	}

	public function gen_product_title($title) {
		// 상품 상세에서 제목 편집 처리는 여기서 한다. 2016.10.20. hamt.

		// oz 단위만 써있는 제목 mm 도 보여주기. 
		// 부피 : oz => 29.57353 ml

		$oz_power = 29.57353;
		if(preg_match('/[.0-9]+[ ]*[fl]*[ ]*[.]*[ ]*oz[.]*/i', $title, $oz_matchs)) {

			if(preg_match('/[.0-9]+[ ]*ml/i', $title, $ml_matchs)) {
				return $title;
			}
			if(preg_match('/[.0-9]+[ ]*g/', $title, $ml_matchs)) {
				return $title;
			}
			if(preg_match('/[.0-9]+[ ]*L/', $title, $ml_matchs)) {
				return $title;
			}

			foreach($oz_matchs as $oz_match) {
				$mm = round(floatval($oz_match) * $oz_power);
				$title = str_replace($oz_match, $oz_match.' / '.$mm.'ml', $title);
			}
		}

		return $title;
	}

    function num2Alpha($n) {
        for($r = ""; $n >= 0; $n = intval($n / 26) - 1) {
            $r = chr($n%26 + 0x41) . $r;
        }
        return $r;
    }
	

    function alpha2Num($col) {
        $col = str_pad($col,2,'0',STR_PAD_LEFT);
        $i = ($col[0] == '0') ? 0 : (ord($col[0]) - 64) * 26;
        $i += ord($col[1]) - 64;
     
        return $i;
    }


	function keyArraytoSequenceArray($data) {
        $cnt = 0;
        $reval = array();
        foreach($data as &$v) {
            $reval[$cnt] = $v;
            $cnt++;
        }
        return $reval;
    }



    /*
    |--------------------------------------------------------------------------
    | 비타트라 기타 수수료 
    |--------------------------------------------------------------------------
    |
    | @ LA(오픈마켓)
    |   1. 해외배송비 : (무게(kg) * (1.51 + 0.05)) + 0.9
    |   2. 국내배송비 : 
    |       - 일반택배 : 2.32
    |       - 띵    똥 : 43 * 주문상품갯수 (total_qty_ordered)
    |   3. 부자재 및 비용 : 1
    |   4. 포장비 : 0
    |   5. 통관비 : 0.3(일통만)
    |   6. 보상비 : 매출액 * 0.1%
    |   7. PG 수수료 : 
    |       - USD : 주문금액 * 4%
    |       - 한화(KCP) : 주문금액 * 2.3%
    |
    | @ LA(자사)
    |   1. 해외배송비 : 위와동일 
    |   2. 국내배송비 : 위와동일 
    |   3. 부자재 및 비용 : 1.68
    |   4. 포장비 : 5 
    |   5. 통관비 : 위와동일
    |   6. 보상비 : 위와동일 
    |   7. PG 수수료 : 위와동일 
    |
    |--------------------------------------------------------------------------
    |
    |
    |   @extra = array(
    |       'is_entry'          => true/false,          // 목록통관, 일반통관
    |       'express_cp_code'   => 'GENERAL/DDONG..',   // 특송업체
    |       'grand_total'       => '',                  // 주문금액
    |       'total_qty_ordered' => '',                  // 고정배송비 상품을 제외한 주문상품 총 수량(o_total_qty_ordered)
    |       'weight_unit'       => '',                  // 무게단위 (LBS/KG) - 비타는 일단 모두 LBS
    |       'company_code'      => '',                  // 배송사 코드 (몰테일연동코드) - 테일에서만
    |   );
    |
    |--------------------------------------------------------------------------
    */
    function get_detail_fees($order, $weight, $extra=array()) {

        $is_entry = (isset($extra['is_entry'])) ? $extra['is_entry'] : true;
        $express_cp_code = (isset($extra['express_cp_code']) && strlen($extra['express_cp_code']) > 0) ? $extra['express_cp_code'] : 'GENERAL';
        $grand_total = (isset($extra['grand_total']) && strlen($extra['grand_total']) > 0) ? $extra['grand_total'] : '0';
        $total_qty_ordered = (isset($extra['total_qty_ordered'])) ? $extra['total_qty_ordered'] : 1;
        $weight_unit = (isset($extra['weight_unit']) && strlen($extra['weight_unit']) > 0) ? $extra['weight_unit'] : 'LBS';
        $company_code = (isset($extra['company_code']) && strlen($extra['company_code']) > 0) ? $extra['company_code'] : '';
        //$sea_shipping = (isset($extra['sea_shipping']) && strlen($extra['sea_shipping']) > 0) ? $extra['sea_shipping'] : 'NO';



        // @ 포장비 : 자사 5 / 오픈마켓 0
        // @ 부자재 및 비용 : 자사 1.68 / 오픈마켓 1
        $packing_fee = 5;
        $unknown_fee = 1.68;
        if(isset($order['o_is_openmarket']) && $order['o_is_openmarket'] == 'YES') {
            $packing_fee = 0;
            $unknown_fee = 1;
        }

		switch($order['o_nation']) {
            case 'KR'	: 

                $delivery_fee = 0;
                if($weight > 0) {
                    $weight_kg = ($weight * 0.453592 * 10) / 10; // lbs -> kg
                    $oversea_fee = ($weight_kg * (1.51 + 0.05)) + 0.9; // 해외배송비

                    switch($express_cp_code) {
                        case 'GENERAL' : // 일반택배
                        default : 
                            $domestic_fee = 2.32;
                            break;
                            
                        case 'DDONG' : // 띵똥

                            // TV 같은 경우는 띵똥 업체를 사용 함.
                            // 일반적인 주문 케이스라면, $total_qty_ordered = 1 임.
                            // "상품설정 오류"로 인해, 특송상품에 다른 상품과 묶어서 주문했을 경우 or 특송상품 주문 수량이 1보다 클 경우.
                            // 상품별로 각각 배송비를 적용함.

                            // ex) TV(2개)                                      ====> 국내 배송비 : $43*2
                            // ex) TV(1개) + TV(1개)                            ====> 국내 배송비 : $43*2
                            // ex) TV(1개) + 귤젤리(2개)                        ====> 국내 배송비 : $43*3
                            // ex) 고정배송비 상품(2개) + TV(1개) + 귤젤리(2개) ====> 국내 배송비 : $43*3 (고정배송비 상품 제외)

                            $domestic_fee = 43 * $total_qty_ordered;
                            break;
                    }
                    $delivery_fee = $oversea_fee + $domestic_fee;   // 배송비 = 해외배송비 + 국내배송비
                }


                // 일반통관 일 때 통관비 추가.
                $entry_fee = 0;
                if($is_entry === false) {
                    $entry_fee = 0.3;
                }


                $fees = array(
                    'delivery_fee' => sprintf('%.6f', $delivery_fee),           // 배송비
                    'packing_fee'  => $packing_fee,                             // 박스당 포장비
                    'etc'          => array(
                        'unknown_fee'   => $unknown_fee,                        // 배송부자재
                        'entry_fee'     => sprintf('%.6f', $entry_fee),         // 통관비 
                        'reward_fee'    => sprintf('%.6f', $grand_total*0.001)  // 보상비 (o_grand_total * 0.1%)
                    ),
                );
                break;

            default : 
                break; 
		}


		// 이미 o_etc_info에 데이터가 존재할 경우 merge 처리 (openmarketfee 같은 경우)
		if(array_key_exists('o_etc_info', $order)  == true && strlen($order['o_etc_info']) > 0) {
			$o_etc_info = unserialize($order['o_etc_info']);
			if(is_array($o_etc_info) && sizeof($o_etc_info) > 0) {
				$fees['etc'] = array_merge($o_etc_info, $fees['etc']);
			}
		}

		return $fees;
	}




	// 비타트라 기타 수수료 산출
    // [첨부파일 참조] http://mantis.phoenixq.hamt.co.kr/view.php?id=3789
    // [주의!!!] @order 데이터로 넘겨줘야  o_delivery_weight (몰테일 실측무게로 재계산됨) ex) daemon 에서 해당 함수 참조!!
	function get_delivery_etc_fees($order, $check_goods=false) {

		$o_etc_info = unserialize($order['o_etc_info']);

        $this->ci->load->model(array(
                'goods_tb_model',
                'order_tb_model',
                'order_item_tb_model',
                'payment_tb_model',
        ));

        $payment = $this->ci->payment_tb_model->get(array('p_order_id' => $order['o_id']))->getData();

        $params = array();
        $params['=']['oi_order_id']	= $order['o_id'];
        $extra = array();
        $extra['fields'] = array('oi_goods_id', 'oi_qty_ordered');
        $oi_data = $this->ci->order_item_tb_model->getList($params, $extra)->getData();
        $oi_data = $this->getDataByPk($oi_data, 'oi_goods_id');

        $params = array();
        $params['in']['g_id'] = array_keys($oi_data);
        $extra = array();
        $extra['fields'] = array('g_id', 'g_weight', 'g_entry', 'g_express_cp_code', 'g_express_info');
        $goods = $this->ci->goods_tb_model->getList($params, $extra)->getData();

        $is_entry = true;               // 목록통관(true) / 일반통관(false) 구분
        $express_cp_code = 'GENERAL';   // 일반택배
        $total_qty_ordered = $order['o_total_qty_ordered']; // 총 상품 구매수량(oi_qty_ordered 의 합산)
        foreach($goods as $g) {
            if($is_entry == true && in_array($g['g_entry'], array('0', '019'))) {
                $is_entry = false;
            }

            if($express_cp_code == 'GENERAL' && $g['g_express_cp_code'] != 'GENERAL') { // 맨 처음 찾은 특송코드로 설정.
                $express_cp_code = $g['g_express_cp_code'];
            }
        }
        $fee_extra = array(
            'is_entry'          => $is_entry,
            'express_cp_code'   => $express_cp_code,
            'grand_total'       => $order['o_grand_total'],
            'total_qty_ordered' => $total_qty_ordered,
        );


		// 상품정보 돌면서 상품배송비 있으면 배송비를 구하고, 배송비가 없으면 무게합을 구함
		if($check_goods == true) {

            $sum_g_weight_except_shipping_fee_goods = 0;
            $delivery_fee = 0 ;
            foreach($goods as $g) {
                // 상품에 배송비 없는 경우는 상품의 무게정보 저장 
                $sum_g_weight_except_shipping_fee_goods += $g['g_weight'] * $oi_data[$g['g_id']]['oi_qty_ordered'];
            }

            $fee = $this->get_detail_fees($order, $sum_g_weight_except_shipping_fee_goods, $fee_extra);
            $fee['delivery_weight'] = $order['o_real_weight'];

            $delivery_fee = $fee['delivery_fee']; 
            if(is_array($o_etc_info) && sizeof($o_etc_info) > 0) {
                $fee['etc'] = array_merge($o_etc_info, $fee['etc']);
            }

            // Etc - PG 수수료
            if(strlen($payment['p_method']) > 0) {
                switch($payment['p_method']) {
                    case 'direct' :         // 주문금액(o_grand_total) * 4%
                    case 'fdgg_direct' :    // 주문금액(o_grand_total) * 4%
                    case 'paypal_direct' :  
                    case 'paypal_express' :  
                        $pg_fee = $order['o_grand_total'] * 0.04;
                        $fee['etc'] = array_merge($fee['etc'], array('pg_fee' => sprintf('%.6f', $pg_fee)));
                        break;

                    case 'kcp' :            // 주문금액(o_grand_total) * 2.3%
                        $pg_fee = $order['o_grand_total'] * 0.023;
                        $fee['etc'] = array_merge($fee['etc'], array('pg_fee' => sprintf('%.6f', $pg_fee)));
                        break;

                    default :
                        break;
                }
            }
            return $fee;
        }


		// [[doComplete, daemon->update_weight_and_tax 처리시 무게정보가 변경될 때 마다 계산식에 의해서 배송비 정보 업데이트]]
		// !! 중요!! o_etc_info['add_shipping_fee'] 키값 존재하면 상품 배송비가 존재한다는 의미 이기 때문에 무게산식에 의한 배송비는 버림. 
		// 상품배송비가 최우선! 
		$fee = $this->get_detail_fees($order, $order['o_delivery_weight'], $fee_extra);
		if(is_array($o_etc_info) && sizeof($o_etc_info) > 0) {
			$fee['etc'] = array_merge($o_etc_info, $fee['etc']);
		}


		// 상품 배송원가산식 외 추가비용이 적용된 주문건이면 무게 배송비산식이 아닌 상품 배송비로 유지함
		if(is_array($o_etc_info) == true && sizeof($o_etc_info) > 0 && array_key_exists('add_shipping_fee', $o_etc_info)) {
			$fee['delivery_fee'] = $order['o_delivery_fee'];
		}

		return $fee;
	}




    function get_distance($prev_point, $points, $type='min') {
        // 마주보는 Repository 묶음
        $bundle = array(
            array(2,3), array(4,5), array(6,7), array(8,9),
            array(3,2), array(5,4), array(7,6), array(9,8)
        );

        //$tmp = array();

        $cnt = 0;
        $x_weight = 1;
        $y_weight = 15;

        foreach($points as $k=>$v) {

            /* 동일점
            if($prev_point == $v) {
                continue;;
            }
            */
            $xd = abs($prev_point['x']-$v['x']) * $x_weight;
            $yd = abs($prev_point['y']-$v['y']) * $y_weight;

            // 마주보는 Repository 높이 0 처리
            if(in_array(array($prev_point['y'], $v['y']), $bundle)) {
                $yd = 0;
            }
            $d = $xd + $yd;
            //$tmp[$k] = $d;

            if($cnt == 0) {
                $std_val = $d;
                $std_key = $k;
            }else {
                
                if($type == 'min') {
                    if($std_val >= $d) {
                        $std_val = $d;
                        $std_key = $k;
                    }
                }else if($type == 'max') {
                    if($std_val <= $d) {
                        $std_val = $d;
                        $std_key = $k;
                    }
                }
            }
            $cnt++;
        }
        //echo print_r($tmp);
        return array($std_key => $std_val);
    }



    /*
    | -------------------------------------------------------------------
    | 레파지토리 최단경로 정렬
    | -------------------------------------------------------------------
    |
    |   - arrange_repository_mst($repository_map, 'single')
    |   - arrange_repository_mst($repository_map, 'multi')
    |
    | -------------------------------------------------------------------

    >>>> @type == 'sigle'
    repository_map = array(
        [47649] => Array
            (
                [g_sku] => 47649
                [g_repository] => F-D-01
                [g_is_collection] => NO
                [g_collection_info] => 
            ),
        [47527] => Array
            (
                [g_sku] => 47527
                [g_repository] => F-A-05
                [g_is_collection] => NO
                [g_collection_info] => 
            )
    );


    >>>> @type == 'multi' 
    >>>> 주문서 기준 2차배열
    $repository_map = array(
        '1111' => array(
            '56308' => array(
                    'g_sku' => 56308,
                    'g_repository' => 'D-B-04'
                ),
            '56307' => array(
                    'g_sku' => 56307,
                    'g_repository' => 'D-B-04'
                ),
            '56211' => array( 
                    'g_sku' => 56211,
                    'g_repository' => 'J-A-02'
                ),
        ),
        '1112' => array(
            '56211' => array( 
                    'g_sku' => 56211,
                    'g_repository' => 'J-A-02'
                ),
            '56134' => array(
                    'g_sku' => 56134,
                    'g_repository' => 'A-E-02'
                )
        )
    );
    */


    function arrange_repository_mst($repository_map, $type='single') {

        // 주문서 기준 2차배열 Merge
        if($type == 'multi') {
            $multi_repogitory_map = $repository_map;
            $merge_map = array();
            foreach($repository_map as $oid=>$repo) {
                $merge_map = array_merge($repo, $merge_map);
            }
            $repository_map = $this->getDataByPK($merge_map, 'g_sku');
        }
       

        $points = array();
        $shoes = array();
        foreach($repository_map as $sku=>$v) {
            if(strlen(trim($v['g_repository'])) < 1) {
		        $shoes[] = $v['g_sku'];
        		continue;
	        }

            $repo = explode('-', $v['g_repository']);
            
            if($repo[0] == 'SHOE') {
		        $shoes[] = $v['g_sku'];
                continue;
            }
            
            $y = $this->alpha2Num(strtoupper(substr($repo[0],0,1)));
            if(sizeof($repo) < 3 && strlen($repo[0]) > 1) {
                $x = $this->alpha2Num(strtoupper(substr($repo[0],1,1)));
            }else {
                $x = $this->alpha2Num(strtoupper(substr($repo[1],0,1)));
            }
            $points[$sku]['x'] = $x;
            $points[$sku]['y'] = $y;
        }

        // 인접 행렬
        $min_path_data = array();
        $prev_point = array('x' => 20, 'y' => 20);
        $point_data = $points;
        for($i=sizeof($points); $i--; $i<1) {
            $min_val = $this->get_distance($prev_point, $point_data);
            $node_key = key($min_val);
            $node_weight = array_shift($min_val);
            
            $prev_point = $points[$node_key];
            unset($point_data[$node_key]);
            $min_path_data[] = $node_key;
        }
        
        $reval = array();
        $min_path_data = array_merge($min_path_data, $shoes);
        
        foreach($min_path_data as $sku) {
            if($type == 'multi') {
                foreach($multi_repogitory_map as $oid=>$repo) {
                    if(isset($repo[$sku])) {
                        $repo[$sku]['o_id'] = $oid;
                        $repo[$sku]['g_name'] = str_replace("'", '', $repo[$sku]['g_name']);
                        //$repo[$sku]['g_name'] = addslashes($repo[$sku]['g_name']);
                        $reval[] = $repo[$sku];
                    }
                }
            }else {
                $reval[$sku] = $repository_map[$sku]['g_repository'];
            }
        }
        return $reval;
    }
	
	/* 작업중 처리자 동시접근
        - 관련파일 : main/ajax_worker_control , worker_contorl.js
        - daemon에서 오래된 페이지별 작업자 파일은 삭제처리 
    */
    public function worker_control($state, $configs, $extras=array()) {
        if(strlen($state) < 1 || !isset($configs['sess_id']) || !isset($configs['page'])) {
            return false;
        }
        if(is_dir(DISPLAY_PATH.'/worker_page') == false) {
            if(@mkdir(DISPLAY_PATH.'/worker_page', 0777)) {
                @chmod(DISPLAY_PATH.'/worker_page', 0777);
            }
        }
        $fullpath = DISPLAY_PATH.'/worker_page/'.$configs['page'].'.info';
        
        
        // 유효시간(초) - 설정 값보다 크면 작업자가 해제됨 
        $wait_sec = 20;
        $exit_wait_sec = 60;

        
        /* 호출순서 
            case1) 본인작업 갱신    : worker_check -> worker_register -> worker_view
            case2) 남의작업 체크    : worker_check -> worker_view
            case3) (이벤트)종료요청 : worker_req_exit -> worker_register -> worker_view
            case4) (이벤트)종료거부 : worker_register -> worker_view

            => 작업자가 자신으로 변경되면 'case1' 에서 'case2'로 변경되어 동작
         */

        switch(strtoupper($state)) {
            case 'WORKER_CHECK' :   // 먼저호출되는 메인부 (작업중인처리자 정보가 유효한지 판단) 
                
                // 내용이 유효하지 않으면 '작업자 없음'
                if(!is_file($fullpath)) {
                    return $this->worker_control('worker_empty', $configs);
                }
                $worker = unserialize(file_get_contents($fullpath));
                if(!is_array($worker) || sizeof($worker) < 1) {
                    return $this->worker_control('worker_empty', $configs);
                }
                
                // 종료요청이 있고, 시간이 만료되었으면 '작업자 등록'
                if(sizeof($worker['exit_queue']) > 0 && $worker['exit_queue']['etime'] < time()) {
                    $a_id = $worker['exit_queue']['a_id'];

                    $worker = array();
                    $worker['a_id'] = $a_id;
                    $worker['stime'] = time();
                    $worker['exit_queue'] = array();

                    return $this->worker_control('worker_register', $configs, $worker);
                }

                // 작업시간이 만료되었으면 '작업자 없음'
                if(time() - $worker['stime'] > $wait_sec) {
                    return $this->worker_control('worker_empty', $configs);
                }

                // 본인작업이면 '작업자 등록' - 시간갱신
                if($configs['sess_id'] == $worker['a_id']) {
                    $worker['stime'] = time();
                    return $this->worker_control('worker_register', $configs, $worker);
                }
                
                return $this->worker_control('worker_view', $configs, $worker);
                break;
            case 'WORKER_REQ_EXIT' :    // 종료요청 이벤트
                $worker = unserialize(file_get_contents($fullpath));
                $worker['exit_queue'] = array(
                    'a_id' => $configs['sess_id'],
                    'etime' => time() + $exit_wait_sec,
                );

                return $this->worker_control('worker_register', $configs, $worker);
                break;
            case 'WORKER_EMPTY' :       // 작업자 없음
                $re_state = 'worker_register';

                // 리스트노출용은 신규등록하지않음
                if(isset($configs['listmode'])) {
                    @unlink($fullpath);
                    $re_state = 'worker_view';
                }
                return $this->worker_control($re_state, $configs);
                break;
            case 'WORKER_REGISTER' :    // 작업자 등록
                
                // 별도 작업자정보가 주어지면 해당 내용으로 등록
                $worker = $extras;
                if(sizeof($extras) != 3) {
                    $worker = array();
                    $worker['a_id'] = $configs['sess_id'];
                    $worker['stime'] = time();
                    $worker['exit_queue'] = array();
                }
                file_put_contents($fullpath, serialize($worker), LOCK_EX);
                
                return $this->worker_control('worker_view', $configs, $worker);
                
                break;
            case 'WORKER_VIEW' :        // 작업자 정보
                $this->ci->load->model('admin_tb_model');
                $worker = $extras;
                if(sizeof($worker) != 3 && is_file($fullpath)) {
                    $worker = unserialize(file_get_contents($fullpath));
                }
                
                $rtn_data = array(
                    'html' => array('main'=> '', 'sub'=> '',),
                    'msg' => '',
                    'is_mywork' => true,
                );
                
                // 작업자 없음 (리스트 모드)
                if(sizeof($worker) == 0) {
                    if(isset($configs['listmode'])) {
                        $rtn_data['worker'] = array();
                    }
                    
                    return $rtn_data;
                }

                $admin_data = $this->ci->admin_tb_model->get($worker['a_id'])->getData();
                // 유효하지 않은 작업자
                if(sizeof($admin_data) < 1) {
                    @unlink($fullpath);
                    
                    return $rtn_data;
                }

                $rtn_data['is_mywork'] = ($configs['sess_id'] == $worker['a_id']);
                $rtn_data['html']['main'] = '작업자 : '.$admin_data['a_loginid'].' ('.$admin_data['a_lastname'].' '.$admin_data['a_firstname'].')'.' | '.date('Y-m-d H:i:s', $worker['stime']);
                
                if(sizeof($worker['exit_queue']) > 0) {
                    $exit_queue = $worker['exit_queue'];
                    
                    $admin_data = $this->ci->admin_tb_model->get($exit_queue['a_id'])->getData();
                    if(sizeof($admin_data) < 1) {
                        return $rtn_data;
                    }
                
                    $rtn_data['html']['sub'] = '대기 : '.$admin_data['a_loginid'].' ('.$admin_data['a_lastname'].' '.$admin_data['a_firstname'].')'.' | '.date('Y-m-d H:i:s', $exit_queue['etime']);

                    // 본인 작업의 종료요청 알림
                    if($rtn_data['is_mywork'] && ($exit_queue['etime'] - time()) > 1) {
                        $rtn_data['msg'] = $exit_queue['etime'] - time().'s 남았습니다.<br />';
                        $rtn_data['msg'] .= '대기자 : '.$admin_data['a_loginid'].' ('.$admin_data['a_lastname'].' '.$admin_data['a_firstname'].')<br />';
                        $rtn_data['msg'] .= '교체예정 :'.date('Y-m-d H:i:s', $exit_queue['etime']);
                    }
                }

                // 픽업페이지 같은 리스트에서 노출용
                if(isset($configs['listmode'])) {
                    $rtn_data['worker'] = $worker;
                }
                
                return $rtn_data;
                
                break;
        }
    }


    // 이벤트 메인툴 데이터 유효성 검증 
    public function getValidEventData($device, $event_maintool_data=array()) {
        
        $key = 0;   // 모바일
        if(strtolower($device) == 'pc') $key = 2;   // PC

        if(sizeof($event_maintool_data) < 1) {
            $this->ci->load->library(array('maintool_v2'));
            $event_maintool_data= $this->ci->maintool_v2->setData('m_event')->openRegisterFile();
        }
        $valid_event_list = array();
        foreach($event_maintool_data as $k=>$event) { 
            if((isset($event['banner'][$key]['src']) && strlen($event['banner'][$key]['src']) > 0) === false) continue;
            
            if(isset($event['banner'][$key]['sdate']) && strtotime($event['banner'][$key]['sdate']) > time()) continue;
            if(isset($event['banner'][$key]['edate']) && strtotime($event['banner'][$key]['edate']) < time()) continue;
            
            $href = 'javascript:void(0);';
            $target = "_self";

            if(isset($event['banner'][$key]['url']) && strlen($event['banner'][$key]['url']) > 0) {
                $href = $event['banner'][$key]['url'];
                $target = $event['banner'][$key]['target'];
            }else {
                $href = '/product/event/'.$event['text'][0]['url'];
            }

            $valid_event_list[$k]['name'] = $event['text'][0]['name'];
            $valid_event_list[$k]['code'] = $event['text'][0]['url'];
            $valid_event_list[$k]['href'] = $href;
            $valid_event_list[$k]['target'] = $target;
            $valid_event_list[$k]['src'] = GOODS_IMG_BASEURL.$event['banner'][$key]['src'];
            $valid_event_list[$k]['filetime'] = filemtime(DISPLAY_PATH.'/../../'.$event['banner'][$key]['src']);
        }
        return $valid_event_list;
    }

    public function remove_protocol($str) {
    	$str = str_replace(HTTP_SHOP_URL, '', $str);
    	$str = str_replace(HTTPS_SHOP_URL, '', $str);
    	$str = str_replace('http://', '//', $str);
    	$str = str_replace('https://', '//', $str);


		// 국민카드 라이프샵은 http 로만 접근 가능하여 다시 치환.
    	$str = str_replace('//life.kbcard.com', 'http://life.kbcard.com', $str);
    	return $str;
    }

		// NHN 거부문자 처리기. (상품명, 수령인이름 등 입력값에 적용)
	public function kcp_deny_text($str) {
			return str_replace(array(
				',',
				'&',
				';',
				"\n",
				"\\",
				'|',
				'‘',
				'“',
				'<'
			), '', $str);
	}
    
    /**
     * POS Company Notify 등록요청 Request
     *
     * 
     */
    public function AddPOSNotify($summary, $desc) {
        if(!is_string($summary) || strlen($summary) < 1) {
            return;
        }
        if(!is_string($desc) || strlen($desc) < 1) {
            return;
        }

        $sid = $this->get_pos_sid();
        $cid = $this->get_pos_cid();

        $summary .= ' - '.SERVICE_NAME;
        
        $api_params = array();
        $api_params['summary'] = $summary;
        $api_params['desc'] = $desc;
        $api_params['service_id'] = $sid;
        $api_params['cid'] = $cid;

        $this->restful_curl(POS_URL.'/smapi/AddCompanyNotifyRequest', http_build_query($api_params));
    }

    /**
     * System Alert Insert    
     *
     * 
     */
    public function AddSystemAlert($subject, $content, $to='DEV', $add_params=array()) {
        $this->ci->load->model('system_alert_queue_tb_model');
        if(!is_string($subject) || strlen($subject) < 1) {
            return; 
        }
        if(!is_string($content) || strlen($content) < 1) {
            return; 
        }
        if(!is_string($to) || strlen($to) < 1) {
            return; 
        }

        $notify_method = isset($add_params['method']) ? strtoupper($add_params['method']) : 'TG';
        $priority = isset($add_params['priority']) ? $add_params['priority'] : 5;

        $to = strtoupper($to);

        if($to == 'ALL') {
            $to = 'OFFICIAL';

            $add_params = array();
            $add_params['method'] = $notify_method;
            $add_params['priority'] = $priority;
            $this->AddSystemAlert($subject, $content, 'DEV', $add_params);
            $this->AddSystemAlert($subject, $content, 'MANAGER', $add_params);
        }


        if($to == 'DEV') {
            $add_dev_info = "\n\n < ETC INFO >";
            $add_dev_info .= "\n - Router->fetch_class(): ".$this->ci->router->fetch_class();
            $add_dev_info .= "\n - Router->fetch_method(): ".$this->ci->router->fetch_method();
            $all_sess = $this->ci->session->all_userdata();
            if(isset($all_sess['admin']) && isset($all_sess['admin']['login_id'])) {
                $add_dev_info .= "\n - Admin: ".$all_sess['admin']['login_id'];
            }
            if(isset($all_sess['customer']) && isset($all_sess['customer']['email'])) {
                $add_dev_info .= "\n - Customer: ".$all_sess['customer']['email'];
            }
            $uri_string = $this->ci->uri->uri_string(); 
            if(strlen($uri_string) > 0) {
                $add_dev_info .= "\n - Uri_string: ".$uri_string;
                $qs = $this->ci->input->server('QUERY_STRING');
                $add_dev_info .= $qs;
            }

            $content .= $add_dev_info;
        }
        $subject .= ' - '.SERVICE_NAME;
        $content .= "\n ServerTime: ".date('Y-m-d H:i:s');

        $insert_params = array();
        $insert_params['saq_notify_method'] = $notify_method; 
        $insert_params['saq_notify_to'] = $to;
        $insert_params['saq_subject'] = $subject;
        $insert_params['saq_content'] = $content;
        $insert_params['saq_priority'] = $priority;

        $this->ci->system_alert_queue_tb_model->doInsert($insert_params);
    }

    public function str_hide($str, $front_letter_cnt=1, $end_letter_cnt=1) {
        if(
            mb_strlen($str) < 1 
            || $front_letter_cnt > mb_strlen($str)
            || $end_letter_cnt > mb_strlen($str)
        ) {
            return $str;
        }

        $front_area = mb_substr($str, 0, $front_letter_cnt);
        $end_area = mb_substr($str, -$end_letter_cnt);

        $hide_cnt = mb_strlen($str) - mb_strlen($front_area) - mb_strlen($end_area);
        $hide_area = str_repeat('*', $hide_cnt);
        

        return $front_area.$hide_area.$end_area;
    }


    // 엑셀업로드 파트너 loginid 리턴
    public function get_upload_partner_ids() {
		$this->ci->load->model('payment_tb_model');

        $excel_partners = $this->ci->payment_tb_model->getOtherPaymentMap();
        $excel_partner_ids = array();
        foreach($excel_partners as $k=>$v) {
            if(defined(strtoupper($k).'_ID')) {
                $excel_partner_ids[] = constant(strtoupper($k).'_ID');
            }
        }
        return $excel_partner_ids;
    }

    // 생년월일로 나이 추출 (dayofbirth 형식 Y-m-d)
    public function getAgeByBirth($dayofbirth) {
        $age = 0;

        if(in_array($dayofbirth, array(null, '', '0000-00-00'))) {
            return $age;
        }

        $age = date('Y') - intval(array_shift(explode('-', $dayofbirth, 2))) + 1;
        return $age;
    }


    public function removeWaterMarkDescription($html_data) {

        // 상세이미지 복사하기
        // 원본이미지가 있으면 원본이미지로, 없으면 워터마크라도 찍힌 이미지로.

        // TODO. 동일 서버에 있는 이미지므로, 치환 처리만 하기.
        return $html_data;
 
    }


    /**
     *  Get POS Service ID
     *  @return integer 
     */
    public function get_pos_sid() {
        $sid = 18;
        if(IS_REAL_SERVER) {
            $sid = 1;
        }
        return $sid;
    }
    
    /**
     *  Get POS Company ID
     *  @return integer 
     */
    public function get_pos_cid() {
        $cid = 1;
        if(IS_REAL_SERVER) {
            $cid = 1;
        }
        return $cid;
    }


    // 숫자전화번호 변환 :: 01071828457 => 010-7182-8457
    public function number_to_tel_number($number) {

        $number = preg_replace("/[^0-9]/", "", $number);
                 
        if(substr($number, 0, 2) =='02') {  
            $tel = preg_replace("/([0-9]{2})([0-9]{3,4})([0-9]{4})$/","\\1-\\2-\\3", $number);
        }else if(substr($number, 0, 2) =='8' && substr($number, 0, 2) =='15' || substr($number, 0, 2) =='16'|| substr($number, 0, 2) =='18') {
            $tel = preg_replace("/([0-9]{4})([0-9]{4})$/","\\1-\\2", $number);  
        }else {
            $tel = preg_replace("/([0-9]{3})([0-9]{3,4})([0-9]{4})$/","\\1-\\2-\\3" ,$number);
        }
        return $tel;
    }


    /**
     * formula 계산을 위한 수식 필터
     * 
     */
    public function filter_infix_notation($infix_notation, $data=array()) {

        $show_debug = false;

        // step1. 중위식 연산자, 피연산자, 괄호 공백으로 구분
     
        // ex) 음수 붙여서 처리 
        // prev: -3 * (A + B) + 4 / - 32 + 3.22 - (- 32 + - 42)
        // after: -3 * ( A + B ) + 4 / -32 + 3.22 - ( -32 + -42 )
        if($show_debug) $this->debug_msg($infix_notation);
        
        $operators = array('+', '-', '/', '*');
        $parenthesis = array('(', ')');
        
        $infix = str_replace(' ', '', $infix_notation);
        if($show_debug) $this->debug_msg($infix);

        $filter_infix = array();
        
        $pass_pointer = array();
        for($i = 0; $i < strlen($infix); $i++) {
            if(in_array($i, $pass_pointer)) {
                continue;
            }
            $value = $infix[$i];
            
            $back_pointer = $i - 1;
            $next_pointer = $i + 1;
            

            // 포인터 위치가 연산자, 소괄호일 경우
            if(in_array($value, $operators) || in_array($value, $parenthesis) ) {

                // 연산의 의미가 아닌, 음수를 나타내는 - 인 경우
                if(
                    $value == '-' 
                    && (
                        strlen(@$infix[$back_pointer]) < 1  // 가장 처음 음수인 케이스 
                        || in_array($infix[$back_pointer], $operators)      // 바로 전에 연산자가 있는 경우 
                        || $infix[$back_pointer] == '('    // 바로 전에 소괄호가 있는 경우 
                    )
                ) {
                    $word = $value;
                    for($j = $next_pointer; $j < strlen($infix); $j++) {
                        $sub_value = $infix[$j];
                        if(in_array($sub_value, $operators) || in_array($sub_value, $parenthesis)) {
                            break;
                        }
                        $pass_pointer[] = $j;
                        $word .= $sub_value;
                    }

                    $filter_infix[] = $word;
                    continue;
                }
                
                $filter_infix[] = $value;
                continue;
            }
            
            $word = '';
            for($j = $i; $j < strlen($infix); $j++) {
                $sub_value = $infix[$j];
                if(in_array($sub_value, $operators) || in_array($sub_value, $parenthesis) ) {
                    break;
                }
                $pass_pointer[] = $j;
                $word .= $sub_value;
            }
            $filter_infix[] = $word;
        }


        if(sizeof($data) > 0) {
            foreach($filter_infix as $idx => $operand) {
                if(is_numeric($operand) || in_array($operand, $operators) || in_array($operand, $parenthesis)) { continue; }

                if(!array_key_exists($operand, $data)) {
                    //$filter_infix[$idx] = 0.0000;
                    continue;
                }
                $filter_infix[$idx] = str_replace(array(' ', ','), '', $data[$operand]);
            }
        }

        $math_str = implode(' ', $filter_infix);
        if($show_debug) $this->debug_msg($math_str);

        return $math_str;
    }

    // 문자열 사칙연산을 파싱하여 계산 수행.
    /*
        - 사칙연산 외에 % 연산, 소수점, 음수연산 추가 
        - 음수를 나타내는 -는 숫자와 붙어있어야 함 (공백으로 떨어져있으면 마이너스연산으로 간주)
        - 괄호로 우선순위를 설정할 때, 공백 한칸으로 구분 ex) ( A + B ) , (A+B)는 오류

        Oversea Solution에서 이식
     */
	public function calculator($math_str, $return_direct_value=true) {
		// 순수하게 연산 가능한 케릭만으로 구성일때만 동작.
		// 여기로 호출할때는 변수 및 함수 치환이 완료된 후 넘길것.
		// 비교문을 위한 연산자 (<, <=, >, <, >=, ==, !=) 은 잘라서 없애고, 수식만 넘기고, 리턴 받아 비교하기.

        $result = array(
            'is_success' => false,
            'msg' => '',
            'data' => 0.0000,
        );

		$orig_math_str = $math_str;
		
		// 수식만 존재하는지 검수.
		if(preg_match_all('/[^ \.\+\-\*\/\(\)0-9]+/', $math_str, $deny_chars)) {
            $result['msg'] = $orig_math_str.' contains a formula that can not be computed. value : '.implode(' ', $deny_chars[0]);

            if($return_direct_value) { return $result['data']; }
			return $result;
		}

        $math_str = trim((string) $math_str);
        if(strlen($math_str) < 1) {
            $result['msg'] = 'There are no characters in '.$orig_math_str.'. strlen : '.strlen($math_str);
            
            if($return_direct_value) { return $result['data']; }
			return $result;
        }

        // 공백기준으로 연산자와 피연산자, 괄호를 구분
        $token_array = explode(' ', $math_str);
        if(is_array($token_array) === false || empty($token_array)) {
            $result['msg'] = 'There are no spaces in '.$orig_math_str.'. array : '.var_export($token_array, true);
            
            if($return_direct_value) { return $result['data']; }
            return $result;
        }

        $op_score_map = array(
			'+' => 1,
			'-' => 1,
			'*' => 10,
			'/' => 10,
		);

        $tokens = array();
        foreach($token_array as $t) {

            // 연산자 
            if(array_key_exists($t, $op_score_map)) {
                $token = array(
                    'value' => $t,
                    'type'  => 'oper',
                    'score' => $op_score_map[$t],
                );

            // 숫자
            } else if(is_numeric($t)) {
                $token = array(
                    'value' => $t,
                    'type'  => 'num',
                ); 
            // 왼쪽괄호
            } else if($t == '(') {
                $token = array(
                    'value' => $t,
                    'type'  => 'parentheis_left',
                );
            // 오른쪽괄호
            } else if($t == ')') {
                $token = array(
                    'value' => $t,
                    'type'  => 'parentheis_right',
                ); 
            } else if($t == '') {
                continue;
            } else {
                $result['msg'] = 'Invalid formula for '.$orig_math_str.'. value : '.$t;
                return $result;
            }
            $tokens[] = $token;
        }
        unset($token_array, $token);

        /* 대괄호, 연산자 스코어별 우선순위 1차정렬
            - 연산자, 괄호를 oper 배열에 저장
                ㄴ op_score_map 에 정의된 스코어 비교하여 스코어 가 낮으면 cal_queue에 넘김
                ㄴ 오른쪽괄호를 발견하면 왼쪽괄호나올때까지 연산자 cal_queue에 넘김
            
            - 숫자는 (음수기호포함) cal_queue 배열에 저장
         */
        $oper_stack = array();
        $cal_queue = array();
        foreach($tokens as $token) {
            
            switch($token['type']) {
                case 'oper':
                    while($oper = array_pop($oper_stack)) {
                        if($oper['type'] != 'oper' || $oper['score'] < $token['score']) {
                            $oper_stack[] = $oper;
                            break;
                        }
                        $cal_queue[] = $oper;
                    }
                    $oper_stack[] = $token;
                    break;
                case 'num':
                    $cal_queue[] = $token;
                    break;
                case 'parentheis_left':
                    $oper_stack[] = $token;
                    break;
                case 'parentheis_right':
                    while($oper = array_pop($oper_stack)) {
                        if(is_array($oper) && $oper['type'] != 'parentheis_left') {
                            $cal_queue[] = $oper; 
                            continue;
                        }
                        // null
                        //if(is_array($oper) === false) {
                            break;
                        //}
                    }
                    break;
                default:
                    break;
            }
        }
        if(sizeof($oper_stack) > 0) {
            while($oper = array_pop($oper_stack)) {
                $cal_queue[] = $oper;
            }
        }
		
        // 정렬된 계산순서대로 연산수행
        $num_stack = array();
        foreach($cal_queue as $cal) {

            if($cal['type'] == 'num') {
                $num_stack[] = $cal['value'];
                continue;
            }

            switch($cal['value']) {
                case '+' :
                    $a = array_pop($num_stack);
                    $b = array_pop($num_stack);
                    
                    array_push($num_stack, $a + $b);
                    break;
                case '-' :
                    // 순서주의
                    $b = array_pop($num_stack);
                    $a = array_pop($num_stack);
            
                    array_push($num_stack, $a - $b);
                    break;
                case '*' :
                    $a = array_pop($num_stack);
                    $b = array_pop($num_stack);

                    array_push($num_stack, $a * $b);
                    break;
                case '/' :
                    // 순서주의
                    $b = array_pop($num_stack);
                    if($b == 0) return $orig_math_str;
                    
                    $a = array_pop($num_stack);
            
                    array_push($num_stack, $a / $b);
                    break;
                case '%' :
                    // 순서주의
                    $b = array_pop($num_stack);
					
                    $a = array_pop($num_stack);
            
                    array_push($num_stack, $a % $b);
                    break;
                default:
                    break;
            }
		}
		$result['data'] = array_pop($num_stack);
        $result['is_success'] = true;

        if($return_direct_value) { return $result['data']; }
        return $result;

	}


    public function getCurrencySymbols($currency_code) {
        $currency_symbols = array(
            'JPY' => '￥',
            'USD' => '$',
            'CNY' => '￥',
            'EUR' => '€',
            'KRW' => '￦', 
        );
        if(!array_key_exists($currency_code, $currency_symbols)) { return $currency_code; }

        return $currency_symbols[$currency_code];
    }


    // margin%, margin, markup 계산
    public function calc_margin($order) {
	    $expense_total = $order['o_cost_total'] + $order['o_paid_tax'] + $order['o_delivery_fee'] + $order['o_packing_fee'] + $order['o_etc'];
        $grand_total = $order['o_grand_total'] - $order['o_refunded_total'] - $order['o_refunded_point'];

        $result = array(
            'margin_per' => '0%',
            'margin' => 0,
            'markup' => '0%',
        );

        if($grand_total > 0) {
            $result['margin_per'] = number_format((1 - ($expense_total/$grand_total))*100, 2).'%';
        }

        $result['margin'] = number_format($grand_total-$expense_total, 2);

        if($expense_total > 0) {
            $result['markup'] = number_format((($grand_total/$expense_total) -1)*100, 2).'%';
        }

        return $result;




    }

	public function check_exponential_string($code) {
		$pattern = "/^[0-9]+(\.[0-9]+)(E|e)(\+|\-)[0-9]*$/";

		preg_match($pattern, $code, $matches);

		if(sizeof($matches) > 0) {
			return false;
		}
	}

    // 개인통관고유부호 
    public function validate_entry_num_v2($entry) {
        if( ! is_string($entry) || strlen($entry) !== 13) {
            return FALSE;
        }

        // 앞 1자리 개인코드 : 무조건 P
        $personal_code = strtoupper(substr($entry, 0, 1));
        if($personal_code !== 'P') {
            return FALSE;
        }
        
        /**
         *  나머지 12자리 모두 숫자이므로, 숫자로 체크
         *
         *  - 발급년도 코드 : 끝 2자리 00 ~ 99
         *  - 부여번호 9자리 : 숫자 
         *  - 마지막 1자리 오류검증부호
         * 
         */
        $created_year_code = substr($entry, 1, 12);
        $pattern = "/^[0-9]{12}$/";
		preg_match($pattern, $created_year_code, $matches);
		if(sizeof($matches) < 1) {
			return FALSE;
		}

        return TRUE;
    }
    
    //사업자번호 체크
    public function validate_company_no($n) {
        $checknums = "137137135";
        $checksum = 0;
        $lastnumber = 0;

        $no = str_replace("-", "", $n);
        
        for($i=0; $i<9; $i++) {
            $tmp = ($checknums[$i] * $no[$i]);

            if($i < 8) {
                $checksum += $tmp;
            } else { 
                //9번째 곱셉의 결과를 각 자리수에 더함
                $tmp2 = (string)$tmp;
                
                if(strlen($tmp2) < 2) {
                    return FALSE;
                }

                $checksum += (int)$tmp2[0] + (int)$tmp2[1];
                $lastnumber = (10 - ($checksum % 10)) % 10;
            }
        }

        if($no[9] == $lastnumber) {
            //마지막 숫자가 같으면 TRUE;
            return TRUE;
        } else {
            return FALSE;
        }
    }

	// Admin 개발자 ID
	public function isDeveloper() {
        $sess = $this->ci->session->userdata('admin');
		
        $is_developer = FALSE;
		if( ! isset($sess['id']) || strlen($sess['id']) < 1) {
            return $is_developer;
        }
		$developer = array(
            '82joong',
            'mj128',
        );

		if(in_array($sess['login_id'], $developer)) {
			$is_developer = TRUE;
		}
		return $is_developer;
	}


    /**
     * 무게별 과세운임 비용 리턴
     */
    public function getTaxShippingAmount($order=array(), $apply_fee_type='express') {
        $fee = 0;
        return $fee;
    }


    /*
    |--------------------------------------------------------------------------
    | Tax Advanced 과세 정보데이터 산출
    |--------------------------------------------------------------------------
    |
    | @Return
    | 1. 고객결제금액	=> @billing_amount
    | 2. 신고대상금액	=> @report_amount
    | 3. 과세운임		=> @tax_shipping_amount
    | 4. 관세(8%)		=> @customs_amount
    | 5. 부가세(10%)	=> @added_ammount
    | 6. 국내설치비용	=> @install_amount
    */

    public function get_report_amount_info($o_data = array(), $billing_amount = 0) {

        if(sizeof($o_data) < 1 || $billing_amount == 0) {
            return false;
        }

        $reval = array(
            'billing_amount'		=> $billing_amount,		// 고객결제금액
            'report_amount'			=> 0,					// 신고대상금액
            'tax_shipping_amount'	=> 0,					// 과세운임
            'customs_amount'		=> 0,					// 관세
            'added_amount'			=> 0,					// 부가세
            'install_amount'		=> 0,					// 국내설치비용	
        );

        // TV인 경우 설치비 발생
        if($o_data['o_has_tv'] == 'YES') {
            $reval['install_amount'] = 54000;		// 고정비 (TODO. 도서산간 구분값 추가)
        }
        $reval['tax_shipping_amount'] = $this->getTaxShippingAmount($o_data);	// 무게별 관세운임 비용 리턴


        // 1) 신고대상금액 = (고객실결제금액 - 과세운임 - 국내설치비용) / 1.188
        $p_amount = $reval['billing_amount'] - $reval['tax_shipping_amount'] - $reval['install_amount'];
        if($o_data['o_is_tax_advance'] == 'YES') {
            $reval['report_amount'] = round((($p_amount * 1000) / 1.188) / 1000);
            

            // 2) 관세 = 신고대상금액 * 0.08
            $reval['customs_amount'] = round((($reval['report_amount'] * 100) * 0.08) / 100);


            // 4) 부가세 = (관세 + 신고대상금액) * 0.1
            $reval['added_amount'] = round(((($reval['customs_amount'] + $reval['report_amount']) * 10) * 0.1) / 10);

        } else {
            $reval['report_amount'] = $p_amount;
        }

        return $reval;
    }


     /*          
         echo formatBytes(24962496);     // 23.81MB
         echo formatBytes(24962496, 0);  // 24MB
         echo formatBytes(24962496, 4);  // 23.8061MB
     */              
     public function formatBytes($size, $precision = 2) {
         $base = log($size, 1024);
         $suffixes = array('B', 'KB', 'MB', 'GB', 'TB');   
                 
         return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)];
     }  


     // $field_list = $this->order_tb_model->getFields();
     public function filterFields($field_list, $req) {
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
        return $data_params;
     }


     /*
        @type vendor
        @key  vd_id : 1

        @return /home/team/82joong/html/itam/html/webdata/display/images/vendor/001/000001
    */
    public function getImgPath($type, $key) {
        $path = IMG_PATH;
        $res = $this->_genPath($path, $type, $key);
        return $res;
    }
    
    public function getImgUrl($type, $key) {
        $path = '/webdata/display/images';
        $res = $this->_genPath($path, $type, $key);
        return $res;
    }

    private function _genPath($path, $type, $key) {

        $first_path = sprintf('%03d', $key);
        $path .= '/'.$type.'/'.$first_path;
        if(!is_dir($path)) { 
            @mkdir($path, 0777, true);
            @chmod($path, 0777);
        }
        $second_path = sprintf('%06d', $key);
        $path .= '/'.$second_path;
        if(!is_dir($path)) { 
            @mkdir($path, 0777, true);
            @chmod($path, 0777);
        }
        return $path; 
    }

    public function is_serialized($str) {
        $data = @unserialize($str);
        if ($str === 'b:0;' || $data !== false) {
            return TRUE; 
        } else {
            return FALSE; 
        }
    } 


    // https://developer.mozilla.org/ko/docs/Web/HTTP/Basics_of_HTTP/MIME_types/Common_types
    public function get_mime_type($filename) {

         $mime_types = array(

             'text/plain'                       => 'txt', 
             //'text/html'                      => 'htm', 
             'text/html'                        => 'html',
             //'text/html'                      => 'php', 
             'text/css'                         => 'css', 
             'application/javascript'           => 'js',
             'application/json'                 => 'json',
             'application/xml'                  => 'xml', 
             'application/x-shockwave-flash'    => 'swf', 
             'video/x-flv'                      => 'flv', 
                        
             // images
             'image/png'                => 'png',   
             //'image/jpeg'             => 'jpe',   
             //'image/jpeg'             => 'jpeg',  
             'image/jpeg'               => 'jpg',   
             'image/gif'                => 'gif',   
             'image/bmp'                => 'bmp',   
             'image/vnd.microsoft.icon' => 'ico',   
             'image/tiff'               => 'tiff',  
             //'image/tiff'             => 'tif',   
             'image/svg+xml'            => 'svg',   
             //'image/svg+xml'          => 'svgz',  

             // archives
             'application/zip'                      => 'zip',   
             'application/x-rar-compressed'         => 'rar',  
             'application/x-msdownload'             => 'exe', 
             'application/x-msdownload'             => 'msi',
             'application/vnd.ms-cab-compressed'    => 'cab',

             // audio/video
             'audio/mpeg'           => 'mp3',   
             'video/quicktime'      => 'qt',
             'video/quicktime'      => 'mov',  

             // adobe
             'application/pdf'              => 'pdf',  
             'image/vnd.adobe.photoshop'    => 'psd',  
             //'application/postscript'     => 'ai',   
             //'application/postscript'     => 'eps',  
             'application/postscript'       => 'ps',   

             // ms office
             'application/msword'               => 'doc',   
             'application/rtf'                  => 'rtf',   
             'application/vnd.ms-excel'         => 'xls',   
             'application/vnd.ms-powerpoint'    => 'ppt',   

             // open office
             'application/vnd.oasis.opendocument.text'          => 'odt',   
             'application/vnd.oasis.opendocument.spreadsheet'   => 'ods',   


             // 알려지지 않은 파일 타입에 대한 기본값 
             'application/octet-stream'     => 'pdf',
        );


        $mime_type = mime_content_type($filename);
        //echo $mime_type.PHP_EOL;
        $type = isset($mime_types[$mime_type]) ? $mime_types[$mime_type] : 'txt';

        return $type;
    }


    public function is_img_type($ext) {
        $images = array(
            'png',   
            'jpe',   
            'jpeg',  
            'jpg',   
            'gif',   
            'bmp',   
            'ico',   
            'tiff',  
            'tif',   
            'svg',   
            'svgz',
        );

        return in_array($ext, $images);
    }


    /* 
        #2200F1 = array(
            'r' => 34, 'g' => 0, 'b' => 241
        );


        $rgb = $this->common->hexToRGB('#200F1');
        rgba('.implode(',', $rgb).', 0.3)
    */
    public function hexToRGB($hex, $alpha = false) {
        $hex      = str_replace('#', '', $hex);
        $length   = strlen($hex);
        $rgb['r'] = hexdec($length == 6 ? substr($hex, 0, 2) : ($length == 3 ? str_repeat(substr($hex, 0, 1), 2) : 0));
        $rgb['g'] = hexdec($length == 6 ? substr($hex, 2, 2) : ($length == 3 ? str_repeat(substr($hex, 1, 1), 2) : 0));
        $rgb['b'] = hexdec($length == 6 ? substr($hex, 4, 2) : ($length == 3 ? str_repeat(substr($hex, 2, 1), 2) : 0));
        if ( $alpha ) {
            $rgb['a'] = $alpha;
        }
        return $rgb;
    }


    public function checkDelete() {
        if( isset($this->ci->_ADMIN_DATA['level']) && $this->ci->_ADMIN_DATA['level'] == 9) {
            return TRUE;
        }else {
            return FALSE;
        }
    }


    public function diffDate($from_date='', $to_date='') {
        $datediff = $from_date - strtotime($to_date);
        return round($datediff / (60 * 60 * 24));
    }


    public function calAssetLevel($params) {

        if( ! isset($params['sm_secure_conf']) || $params['sm_secure_conf'] < 1 ) $params['sm_secure_conf'] = 1;
        if( ! isset($params['sm_secure_inte']) || $params['sm_secure_inte'] < 1 ) $params['sm_secure_inte'] = 1;
        if( ! isset($params['sm_secure_avail']) || $params['sm_secure_avail'] < 1 ) $params['sm_secure_avail'] = 1;

        $score = $params['sm_secure_conf'] +  $params['sm_secure_inte'] + $params['sm_secure_avail'];
        $level = 0;
        if( $score >= 1 && $score <=3 ) $level = 3;
        else if( $score >= 4 && $score <= 6 ) $level = 2;
        else $level = 1;

        $params['sm_important_score'] = $score;
        $params['sm_important_level'] = $level;
 
        return $params;
    }
}
