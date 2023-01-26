<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


function getMarket() {
    return array();
}

function getCategory($divide_string="/", $is_tree=false, $category_data=array(), $depth=1, $parent_id=0) {

    $category_map = array();

    /*
    $all_category_data = unserialize(file_get_contents(DISPLAY_PATH.'/category/vvic_category.info'));

    if($parent_id == 0) {
        $category_data = $all_category_data['cate1'];
        $depth = 1;
        $parent_id = 0;
    }

    foreach($category_data as $ct_id => $cate) {
        $key = $ct_id;
        if($parent_id > 0 && $is_tree) {
            $key = $parent_id.$divide_string.$ct_id;
        }

        $category_map[$key] = str_repeat('...', ($depth-1)).$cate['name'];

        if( isset($all_category_data['cate'.($depth+1)]) && isset($all_category_data['cate'.($depth+1)][$ct_id]) ) {
            $childs = getCategory($divide_string, $is_tree, $all_category_data['cate'.($depth+1)][$ct_id], $depth+1, $key);
            $category_map = $category_map + $childs;
        }
    }
    */

    return $category_map;
}

function getCategoryByName($name, $divide_string="/", $is_tree=false, $category_data=array(), $depth=1, $parent_id=0) {
	
	return getCategory($divide_string, $is_tree, $category_data, $depth, $parent_id, $name);
}

function getCategoryToHTML($ct_path, $categories) {

    $ct_naem = '';

    /*
    //$categories = getCategory();

    $ct_path_ids = explode('/', $ct_path);

    $ct_names = array();
    foreach($ct_path_ids as $id) {
        $ct_names[] = $categories[$id];
    }

    $ct_name = implode('<br/>', $ct_names);
    */

    return $ct_name; 
}

function getOptionFields() {
    $fields = array(
        'Barcode'   => 'BARCODE',
        'Size'      => 'SIZE',
        'Qty'       => 'QTY',
    );

    return $fields;
}

function AddCategory($doc_id) {
    return;
} 



function getCategoryToSelectOpt($lang='name_en') {
    return;
}


?>
