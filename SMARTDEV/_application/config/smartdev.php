<?php
if(IS_REAL_SERVER) {

    $config['pos_service_map'] = array(
        'VVIC'      => 0,
        'ATELIER'   => 0,
        'LAFAYETTE' => 0,           // TODO. 실서버 반영시
        'QEEBOO'    => 0,
    );


} else {

    $config['pos_service_map'] = array(
        'VVIC'      => 0,
        'ATELIER'   => 0,
        'LAFAYETTE' => 62,
        'QEEBOO'    => 0,
    );

}


/*
|--------------------------------------------------------------------------
| 마켓별mg_options 필드 이하 상품 코드 필드명  
|--------------------------------------------------------------------------
|
| - market 추가시 매칭 
| - POS 연동시 POS내에 [service_goods_id] 필드로 매칭 
|
*/
$config['opt_gid_field_map'] = array(
        'VVIC'      => 'sku_vid',
        'ATELIER'   => 'Barcode',
        'LAFAYETTE' => 'product_code',           
    );

?>
