<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function getMarket() {
    return array('lafayette' => 'lafayette');
}

function getCategory($divide_string="_%AND%_", $is_tree=false, $category_data=array(), $depth=0, $parent_id=0, $name='name') {

    $category_map = array();
    $all_category_data = unserialize(file_get_contents(DISPLAY_PATH.'/category/lafayette_category.info'));
    //echo print_r($all_category_data); exit;

    if($is_tree === 'serial') {
        return $all_category_data;
    }


    if($parent_id === 0) {
        $category_data = $all_category_data['cate0'];
        $depth = 0;
        $parent_id = 0;
    }

    foreach($category_data as $ct_id => $cate) {
        $key = $ct_id;
        if(strlen($parent_id) > 5 && $is_tree) {
            $key = $parent_id.$divide_string.$ct_id;
        }

        //$category_map[$key] = str_repeat('...', ($depth)).$cate[$name];

        $depth_icon = '<span class="badge badge-secondary badge-pill position-relative mr-2">'.$depth.'</span>';
        $category_map[$key] = str_repeat('&nbsp;', (($depth*1)*8) ).$depth_icon.$cate[$name];
		
        if( isset($all_category_data['cate'.($depth+1)]) && isset($all_category_data['cate'.($depth+1)][$ct_id]) ) {
            $childs = getCategory($divide_string, $is_tree, $all_category_data['cate'.($depth+1)][$ct_id], $depth+1, $key, $name);
            $category_map = $category_map + $childs;
        }
    }
	
    return $category_map;
}
function getCategoryByName($name, $divide_string="_%AND%_", $is_tree=false, $category_data=array(), $depth=1, $parent_id=0) {
	return getCategory($divide_string, $is_tree, $category_data, $depth, $parent_id, $name);
}

function getCategoryToHTML($ct_path, $categories) {
    //$categories = getCategory();

    $ct_path_ids = explode('/', $ct_path);

    $ct_names = array();
    foreach($ct_path_ids as $id) {
        if( ! isset($categories[$id]) ) continue;
        $ct_names[] = $categories[$id];
    }

    $ct_name = implode('<br/>', $ct_names);

    return $ct_name; 
}

function getOptionFields() {
    $fields = array(
        'product_code'  => 'PRODUCT_CODE',
        'product_upc'   => 'PRODUCT_UPC',
        'color'         => 'COLOR',
        'color_code'    => 'COLOR_CODE',
        'size'          => 'SIZE',
        'size_code'     => 'SIZE_CODE',
        'uga'           => 'UGA',
        'active'        => 'ACTIVE',
        'imgs'          => 'IMGS'
    );
    return $fields;
}




function AddCategory($doc_id, $mg_data=array()) {

    if(strlen($doc_id) < 1) return;

    $_CI =& get_instance();
    $_SHOP = 'lafayette';
    $_CI->load->library('elastic');


    $category_filepath = DISPLAY_PATH.'/category/lafayette_category.info';
    $all_category_data = unserialize(file_get_contents($category_filepath));
    //echo print_r($all_category_data); exit;

    
	if(is_array($mg_data) && sizeof($mg_data) < 1) {

		$params = array();
		$params['query']['bool']['must']['term']['mg_id']['value'] = $doc_id;
		$params['_source'] = array(
		    'mg_id',
		    'mg_catepath_id',
		    'mg_trans_cate0_name',
		    'mg_trans_cate1_name',
		    'mg_trans_cate2_name',
		    'mg_trans_cate3_name',
		    'mg_trans_cate4_name',
		);
		$json_params = json_encode($params);

		$el_header = $_CI->elastic->get_auth_header();
		$el_url = ELASTIC_HOST.'/market-goods-'.$_SHOP.'/_search';
		$el_result = $_CI->elastic->restful_curl($el_url, $json_params, 'GET', $timeout=10, $el_header);
		$el_result = json_decode($el_result, true);
		//echo print_r($el_result);

			if(sizeof($el_result['hits']['hits']) < 1) {
		    echo 'No Data';
		    return;
		}
		

		$mg_data = $el_result['hits']['hits'][0]['_source'];
		//echo print_r($mg_data);
	}
        
	/*
	카테고리 path가 2가지 다른 구조 케이스 발생
	구조 : root/levelOne/levelTwo/levelThree/levelFour

	CASE1: N1x03/N2x03x03/N3x03x03x13/N4x03x03x13x47/null
	CASE2: null/N1x03/N2x03x03/N3x03x01x04/N4x03x01x04x47
	*/



	$log_data = array();
	$log_data['_id'] = $mg_data['mg_id'];


    $cate_path = explode('/', $mg_data['mg_catepath_id']);
    if(sizeof($cate_path) != 5) {
        $log_data['mg_data'] = $mg_data;
        $_CI->common->logWrite('debug', print_r($log_data, true), 'addCategory');
        return;
    }


	$is_previous_key = FALSE;	
	if(strlen(trim($cate_path[4])) < 1) {
		unset($cate_path[4]);
		array_unshift($cate_path, '');	
		$is_previous_key = TRUE;	
	}
    //echo print_r($cate_path); //exit;


    $data = array();
    foreach($cate_path as $k=>$cate) {

        $data['cate'.$k] = array();
        for($i=0; $i<=$k; $i++) {
            $cate_code = trim($cate_path[$i]);
            if($i==0 && strlen($cate_code) < 1) {
                $cate_code = 'NONE';
            }
            $data['cate'.$k]['depth'.$i] = $cate_code;
        }

        if($k == 0 && strlen(trim($cate)) < 1) {

            $data['cate'.$k]['name'] = 'NONE';
            $data['cate'.$k]['name_ko'] = 'NONE';
            $data['cate'.$k]['name_en'] = 'NONE';

        }else {

            $name_key = $k;
            if($is_previous_key == TRUE) {	
                $name_key = $name_key - 1;
            }

            $name = $mg_data['mg_trans_cate'.$name_key.'_name']['fr'];
            $name_ko = $mg_data['mg_trans_cate'.$name_key.'_name']['ko'];
            $name_en = $mg_data['mg_trans_cate'.$name_key.'_name']['en'];

            $data['cate'.$k]['name'] = $name;
            $data['cate'.$k]['name_ko'] = $name_ko;
            $data['cate'.$k]['name_en'] = $name_en;
        }
    }

    $cnt = 0;
    foreach($data as $key=>$row) {
        $key_data = array();
        for($i=0; $i<=$cnt; $i++) {
            $key_data[] = $row['depth'.$i];
        }

        $key_data = array_slice($key_data, -2, 2);
        $log_data['insert_cate']['cate'.$cnt] = $key_data;


        if(sizeof($key_data) == 1) {
            if(strlen($key_data[0]) < 1) {
                $log_data['ERROR']['cate'.$cnt] = $key_data;
                $_CI->common->logWrite('debug', print_r($log_data, true), 'addCategory');
                continue;
            }
            $all_category_data['cate'.$cnt][$key_data[0]] = $row;


        }else if(sizeof($key_data) == 2) {
            if(strlen($key_data[0]) < 1 || strlen($key_data[1]) < 1) {
                $log_data['ERROR']['cate'.$cnt] = $key_data;
                $_CI->common->logWrite('debug', print_r($log_data, true), 'addCategory');
                continue;
            }
            $all_category_data['cate'.$cnt][$key_data[0]][$key_data[1]] = $row;
        }
        $cnt = $cnt+1;
    }

    $all_category_data = $_CI->common->multiArrayKSort($all_category_data);
    file_put_contents($category_filepath, serialize($all_category_data));
}


function getCategoryToSelectOpt($lang='name_en') {

    $category_map = array();
    $all_category_data = unserialize(file_get_contents(DISPLAY_PATH.'/category/lafayette_category.info'));
    //echo print_r($all_category_data); exit;


    $opt_data = array();

    foreach($all_category_data['cate0'] as $k=>$v) {
        $tmp = array(
            'id'    => $k,
            'label' => $v[$lang]
        );

        $tmp['children'] = _set_cate_child($all_category_data, $k, 1, $lang);
        $opt_data[] = $tmp;
    }
    //echo print_r($opt_data);
    return $opt_data;
}


function _set_cate_child($all_cate_data, $cate_key, $depth, $lang='name_en') {

    $child = array();
    if(isset($all_cate_data['cate'.$depth][$cate_key])) {
        foreach($all_cate_data['cate'.$depth][$cate_key] as $key=>$value) {
            $temp = array(
                'id'        => $key,
                'label'     => $value[$lang],
            ); 

            $sub_child = _set_cate_child($all_cate_data, $key, $depth+1);
            if(sizeof($sub_child) > 0) {
                $temp['children'] = $sub_child;
            }

            $child[] = $temp;
        }
    }
    return $child;
}
?>
