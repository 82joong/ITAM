<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Dev extends CI_Controller {

    private $ip;


    function __construct() {
        parent::__construct();

        $allow_ips = array(
            '14.129.31.152',    // phoenixq
            '14.129.31.214',    // bwwjh
            '14.129.31.215',    // joong
            '14.129.31.216',    // lsyoung
            '14.129.31.217',    // bigtuna
            '14.129.31.136',    // yun3019
            '14.129.31.137',    // maginc3
            // add here
        );

        $ip_idxs = array('REMOTE_ADDR', 'VPNIP');

        $ip = '';
        foreach($ip_idxs as $idx) {
            if(array_key_exists($idx, $_SERVER)) {
                $ip = $_SERVER[$idx];
                break;
            }
        }
        if(strlen($ip) < 1 || !in_array($ip, $allow_ips)) {
            $this->common->locationhref('/');
            return;
        }

        $this->ip = $ip;
        $this->common->logWrite('dev_access_log', $ip, $this->router->fetch_method());
    }


    public function test() {
        echo 'TEST';
    }


    public function elastic_test() {

        echo '<h3>Elasticsearch API TEST</h3><br>'.PHP_EOL;

        $this->load->library('elastic');

        //$res = $this->elastic->generate_snapshot_repository(ELASTIC_REPOSITORY_NAME);
        //$res = $this->elastic->delete_snapshot_repository(ELASTIC_REPOSITORY_NAME, ELASTIC_POS_GOODS_INDEX);
        //$res = $this->elastic->store_snapshot(ELASTIC_REPOSITORY_NAME, ELASTIC_DW_INDEX);
        //$res = $this->elastic->restore_snapshot(ELASTIC_REPOSITORY_NAME, 'dw-account-summary-2020_snapshot_20200210_112622');
        //$res = $this->elastic->delete_snapshot(ELASTIC_REPOSITORY_NAME, 'dw-account-summary-2020_snapshot_20200318_095151');
        //$res = $this->elastic->clean_snapshot();

        //$res = $this->elastic->get_alias_map(ELASTIC_DW_INDEX);
        //$res = $this->elastic->actions_alias(array(), array('dw-account-summary-2020'), ELASTIC_DW_INDEX);
        //$res = $this->elastic->get_alias_map(ELASTIC_DW_INDEX);
        //$res = $this->elastic->actions_alias(array('pos-goods-20200214120857'), array(), 'pos-goods');

        $res = $this->elastic->delete_template('market-goods');
        //$res = $this->elastic->generate_template('market-goods');
        //$res = $this->elastic->get_mapping_fields('dw-account-summary');
        //$res = $this->elastic->get_auth_header();


        //$res = $this->elastic->delete_indices('traffic-*', 10);
        //$res = $this->elastic->delete_indices('metricbeat-*', 10);
        echo print_r($res);

        //echo print_r($res);
    }


    public function vvic_status() {

        $vids = array(
            '5ed839a7b033a20001ebc506',
            '5ed728e267df3d0001c1c7d4'
        );
        $vids = implode(',', $vids);

        $this->load->library('ShopCrawler');
        $res = $this->shopcrawler->getGoodsStatus('vvic', 'gz', array('item_vid' => $vids, 'mode' => 'list'));
        echo print_r($res).PHP_EOL;
 

    }


    public function vvic_detail($vid) {

/*
        $vid = '5fa15d239eace500018255d6';

        $vids = array(
            '5ed839a7b033a20001ebc506',
            '5ed728e267df3d0001c1c7d4'
        );
        $vids = implode(',', $vids);
 */
        $this->load->library('ShopCrawler');
	$res = $this->shopcrawler->getCategoryGoodsList('vvic', 'gz', array('vid' => $vid, 'return' => true));
        //$res = $this->shopcrawler->getGoodsDetail('vvic', 'gz', array('item_vid' => $vids, 'mode' => 'list'));
        echo print_r($res).PHP_EOL;
        echo PHP_EOL;
        echo PHP_EOL;
        echo PHP_EOL;


        /*
        $vid = '5eb815453dbe2e000171d2e1';
        $this->load->library('ShopCrawler');
        $res = $this->shopcrawler->getGoodsDetail('vvic', 'gz', array('item_vid' => $vid));
        echo print_r($res).PHP_EOL;
        */
    } 


}
