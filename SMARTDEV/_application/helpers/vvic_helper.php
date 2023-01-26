<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function getMarket() {
    $site_code = array(
       'gz',        // 광저우 
       'pn',        // 푸닝 
       'hznz',      // 황저우 여성
       'jfn',       // 황저우 남성
       'xt',        // 신탕 청바지 전문 
       'hz'         // 남부신발
    );

    if(IS_REAL_SERVER === FALSE) {
        $site_code[] = 'st';
    }

    foreach($site_code as &$code) {
        $code = 'vvic-'.$code;
    }

    $site_code_map = array_combine($site_code, $site_code);

    return $site_code_map;

}

function getCategory($divide_string="_%AND%_", $is_tree=false, $category_data=array(), $depth=1, $parent_id=0, $name='name') {
    $category_map = array();
    $all_category_data = unserialize(file_get_contents(DISPLAY_PATH.'/category/vvic_category.info'));
    //echo print_r($all_category_data);  exit;
	
    if($parent_id === 0) {
        $category_data = $all_category_data['cate1'];
        $depth = 1;
        $parent_id = 0;
    }

    foreach($category_data as $ct_id => $cate) {
        $key = $ct_id;
        if($parent_id > 0 && $is_tree) {
            $key = $parent_id.$divide_string.$ct_id;
        }

        $category_map[$key] = str_repeat('...', ($depth-1)).$cate[$name];
		
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
        'sku_vid'       => 'SKU VID', 
        'arrive_time'   => 'ARRIVE_TIME',
        'color_img'     => 'COLOR_IMG',
        'color'         => 'COLOR',
        'size'          => 'SIZE',
        'price'         => 'PRICE',
        'is_lack'       => 'IS_LACK',
        'status'        => 'STATUS',
    );

    return $fields;
}


function AddCategory($doc_id) {

    return;

} 



function getCategoryToSelectOpt($lang='name_en') {
    $category_map = array();
    $all_category_data = unserialize(file_get_contents(DISPLAY_PATH.'/category/vvic_category.info'));
	
    $opt_data = array();

    foreach($all_category_data['cate1'] as $k=>$v) {
        $tmp = array(
            'id'    => $k,
            'label' => $v[$lang]
        );

        $tmp['children'] = _set_cate_child($all_category_data, $k, 2, $lang);
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
