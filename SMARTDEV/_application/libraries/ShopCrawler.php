<?php
require dirname(__FILE__).'/ShopCrawler/BaseShopCrawler.php';

class ShopCrawler {
    public static function getCategory($shop_name, $extra=array()) { // 카테고리 구조 리턴
        $obj = ShopCrawlerFactory::getCrawler($shop_name);
        if( ! $obj) {
            return array();
        }
        return $obj->getCategory($extra);
    }

    public static function getCategoryGoodsList($shop_name, $category_goods_list_url, $extra=array()) { // 카테고리 상품 리스트
        $obj = ShopCrawlerFactory::getCrawler($shop_name);
        if( ! $obj) {
            return array();
        }
        return $obj->getCategoryGoodsList($category_goods_list_url, $extra);
    }

    public static function getGoodsDetail($shop_name, $goods_detail_url, $extra=array()) { // 카테고리 상품 리스트
        $obj = ShopCrawlerFactory::getCrawler($shop_name);
        if( ! $obj) {
            return array();
        }
        return $obj->getGoodsDetail($category_goods_list_url, $extra);
    }


    public static function getGoodsStatus($shop_name, $goods_detail_url, $extra=array()) { // 카테고리 상품 리스트
        $obj = ShopCrawlerFactory::getCrawler($shop_name);
        if( ! $obj) {
            return array();
        }
        return $obj->getGoodsStatus($category_goods_list_url, $extra);
    }
}

class ShopCrawlerFactory {
    // Singleton
    private function __construct() {
    }

    public static function getCrawler($shop_name) {
        $shop_name = strtoupper(trim($shop_name));
        $class_name = $shop_name.'_Crawler';

        switch($shop_name) {
            case 'VVIC' :
            case 'TAOBAO' :
            case 'QEEBOO' :
                if( ! class_exists($class_name)) {
                    require dirname(__FILE__).'/ShopCrawler/'.$class_name.'.php';
                }
                break;
            default :
                // 관리되지 않는 샵 네임
                return false;
        }

        return new $class_name;
    }
}
