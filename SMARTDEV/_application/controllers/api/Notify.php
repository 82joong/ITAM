<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include_once dirname(__FILE__).'/Base_api.php';

class Notify extends Base_api {

    private $ip;


    function __construct() {
        parent::__construct();
    }


    public function vvic() {

	    $req = file_get_contents('php://input');

        /*
        데이터 형태
        =========== 2020-06-15 14:50:41 ==========
        {"appId":"2125163511342184","data":{"changedFields":["list_grid_image","item_view_image"],"itemVid":"5ee648ba69f6e80001b14286","time":1592200241400,"type":"vvic_item_update"},"msgId":"0AA00742000118B4AAC24B118CFB604C","sign":"521cfab1dfa293d177ad046527a2f9c7db691c5fa6aac32ac50377049dcd04c7","timestamp":1592200241503,"type":"VVIC_MESSAGE_PRODUCT_MODIFY"}
        =========================================
        */
        
        $this->common->logWrite('notify', $req, 'vvic');

        if( strlen($req) < 1) return;
        $req = json_decode($req, true);

        if( (isset($req['data']) && isset($req['data']['itemVid'])) == FALSE) return;

        $vid = trim($req['data']['itemVid']);

        // VVIC Cache
        $filepath = APPPATH.'../appdata/vvic_cache';
        $filepath_name = $filepath.'/'.$vid;
        
        // 3시간 이내인 @vid는 skip
        if(file_exists($filepath_name)) {
            $std_time = time() - (1440*3);
            if(filemtime($filepath_name) > $std_time) {
                //echo 'In 3Hours file'.PHP_EOL;
                return;
            }
        }
        touch($filepath_name);

        $this->load->library('ShopCrawler');
        $this->shopcrawler->getCategoryGoodsList('vvic', 'gz', array('vid' => $vid));
    }



    public function vvic_debug() {

        $req = '{"appId":"2125163511342184","data":{"changedFields":["list_grid_image","item_view_image"],"itemVid":"5ee648ba69f6e80001b14286","time":1592200241400,"type":"vvic_item_update"},"msgId":"0AA00742000118B4AAC24B118CFB604C","sign":"521cfab1dfa293d177ad046527a2f9c7db691c5fa6aac32ac50377049dcd04c7","timestamp":1592200241503,"type":"VVIC_MESSAGE_PRODUCT_MODIFY"}';

        if( strlen($req) < 1) return;
        $req = json_decode($req, true);

        if( (isset($req['data']) && isset($req['data']['itemVid'])) == FALSE) return;

        $vid = trim($req['data']['itemVid']);

        // 테스트 서버용 @vid
        if( IS_REAL_SERVER == FALSE) {
            $vid = '5ed7c98a81278c00010d8fd6';
        }
        //echo $vid; exit;


        // VVIC Cache
        $filepath = APPPATH.'../appdata/vvic_cache';
        $filepath_name = $filepath.'/'.$vid;
        
        // 3시간 이내인 @vid는 skip
        if(file_exists($filepath_name)) {
            $std_time = time() - (1440*3);
            if(filemtime($filepath_name) > $std_time) {
                echo 'In 3Hours file'.PHP_EOL;
                return;
            }
        }
        touch($filepath_name);
            
        echo 'END'; exit;

        $this->load->library('ShopCrawler');
        $this->shopcrawler->getCategoryGoodsList('vvic', 'gz', array('vid' => $vid));
    }

}
