<?php

interface IShopCrawler {
    function getCategory($extra=array()); // 카테고리 구조 리턴
    function getCategoryGoodsList($url, $extra=array()); // 카테고리 상품 리스트
    function getGoodsDetail($url, $extra=array()); // 카테고리 상품 리스트
    function getGoodsStatus($url, $extra=array()); // 카테고리 상품 리스트
}
