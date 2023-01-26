<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function getMarket() {
    return array('qeeboo' => 'qeeboo');
}

function getCategory($divide_string="_%AND%_", $is_tree=false, $category_data=array(), $depth=1, $parent_id=0, $name='name') {
    $category_map = array();
    $all_category_data = @unserialize(file_get_contents(DISPLAY_PATH.'/category/qeeboo_category.info'));

    if(empty($all_category_data['cate0'])) {
        return $category_map;
    }

    foreach($all_category_data['cate0'] as $ct_id => $cate) {
        $category_map[$ct_id] = $cate[$name];
    }
    return $category_map;
}

function getCategoryByName($name, $divide_string="_%AND%_", $is_tree=false, $category_data=array(), $depth=1, $parent_id=0) {
	return getCategory($divide_string, $is_tree, $category_data, $depth, $parent_id, $name);
}

function getCategoryToSelectOpt($lang='name_en') {
    $category_map = array();
    $all_category_data = unserialize(file_get_contents(DISPLAY_PATH.'/category/qeeboo_category.info'));
	
    $opt_data = array();
    foreach($all_category_data['cate0'] as $k => $v) {
        $tmp = array(
            'id'    => $k,
            'label' => $v[$lang],
        );
        $opt_data[] = $tmp;
    }
    return $opt_data;
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
        'mg_id'         => 'MG_ID',
        'item_id'       => 'ITEM_ID',
        'sku_id'        => 'SKU_ID',
        'mg_name'       => 'MG_NAME',
        'mg_option_display_text' => 'MG_OPTION_DISPLAY_TEXT',
        'status'        => 'STATUS',
        'product_upc'   => 'PRODUCT_UPC',
        'mg_list_img'   => 'MG_LIST_IMG',
        'mg_weight_kg'  => 'MG_WEIGHT_KG',
        'mg_weight_lbs' => 'MG_WEIGHT_LBS',
        'mg_quantity'   => 'MG_QUANTITY',
        'mg_price'      => 'MG_PRICE',
    );

    return $fields;
}


