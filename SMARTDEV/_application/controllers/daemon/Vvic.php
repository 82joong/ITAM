<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Vvic extends CI_Controller {

    public function __construct() {
        parent::__construct();

        if( IS_REAL_SERVER ) {
            if($this->input->is_cli_request() == false) {
                return;
            }
        }

    }


    public function index() {
        echo 'INDEX'.PHP_EOL;
    }


    // TODO 데몬 등록
    public function generate_category() {

        $this->load->library('ShopCrawler');
        $res = $this->shopcrawler->getCategory('vvic');
        //echo print_r($res);

        if(is_array($res) && sizeof($res) > 0) {
            $this->load->helper('file');
            $filename = DISPLAY_PATH.'/category/vvic_category.info';    
            //echo $filename; exit;
            
            $res = strip_tags(serialize($res));
            if( write_file($filename, $res) ) {
                echo 'Update Category Data!';
            } else {
                echo 'Fail Make a file!!';
            }
        }
    }

    public function generate_color() {

        $params = array(
            'aggs'  => array(
                'color' => array(
                    'terms' => array(
                        'field' => 'mg_color',
                        'size'  => 10
                    )
                )
            )
        );
        $json_params = json_encode($params);

        
        $this->load->library('elastic');
        $index_name = ELASTIC_MARKET_GOODS_VVIC_INDEX;

        $el_header = $this->elastic->get_auth_header();
        $el_url = ELASTIC_HOST.'/'.$index_name.'/_search?size=0';
        $el_result = $this->elastic->restful_curl($el_url, $json_params, 'GET', $timeout=10, $el_header);
        $el_result = json_decode($el_result, true);
        //echo print_r($el_result); //exit;


        $color_data = array();
        if( isset($el_result['aggregations']) && sizeof($el_result['aggregations']['color']['buckets']) > 0) {

            $bucket_data = $this->common->getDataByPK($el_result['aggregations']['color']['buckets'], 'key');
            //echo print_r($bucket_data);
            
            // 번역
            $params = array(
                'from'  => 'zh',    // 중국어
                'to'    => array('en', 'ko'),
                'text'  => array_keys($bucket_data) 
            );
            $trans_res = $this->common->restful_curl(TRANSLATE_API_URL, http_build_query($params), 'POST');
            $trans_data = json_decode($trans_res, true);
            //echo print_r($trans_data); //exit;

            if($trans_data['is_success'] == TRUE) {

                foreach($trans_data['text'] as $k=>$v) {
                    $color_data[] = array(
                        'name_cn'   => $v,
                        'name_ko'   => $trans_data['result'][$k]['ko'],
                        'name_en'   => $trans_data['result'][$k]['en'],
                    );
                }
                //echo print_r($color_data);
            }

        }

        if(is_array($color_data) && sizeof($color_data) > 0) {
            $this->load->helper('file');
            $filename = DISPLAY_PATH.'/category/vvic_color.info';    
            //echo $filename; exit;
            
            $res = strip_tags(serialize($color_data));
            if( write_file($filename, $res) ) {
                echo 'Update Category Data!';
            } else {
                echo 'Fail Make a file!!';
            }
        }

    }



    // Daily 수집은 [hznz], [xt] 수행
    public function get_daily_uptime($city_market_code = 'xt') {
	
        $this->load->library('ShopCrawler');

        /* 
        @first
        @second 
            all => 전체
		    @allow_city_market_code = array('gz','pn','hznz', 'jfn', 'xt', 'hz');

		jfn => UnAuth
		hz  => UnAuth

        */

        $this->shopcrawler->getCategoryGoodsList('vvic', $city_market_code, array());
    }


    // 이미지 다운로드
    public function image_down() {

        $this->load->library('elastic');


        $el_header = $this->elastic->get_auth_header();
        $params = array(
            '_source'   => array('mg_market_goods_code', "mg_urlk_key", "mg_list_img", "mg_view_imgs", "mg_description"),
            "sort"      => array(array("mg_updated_at" => "desc")),
            "size"      => 10,
            "from"      => 0
        );
        $json_params = json_encode($params, JSON_NUMERIC_CHECK);  // JSON 내에 숫자 데이터 체크

        $el_url = ELASTIC_HOST.'/'.ELASTIC_MARKET_GOODS_VVIC_INDEX.'/_search';
        $el_result = $this->elastic->restful_curl($el_url, $json_params, 'GET', $timeout=10, $el_header);
        $el_result = json_decode($el_result, true);
        echo print_r($el_result).PHP_ROL; //exit;


        foreach($el_result['hits']['hits'] as $key=>$row) {

            if(!isset($row['_source']['mg_list_img']) || strpos($row['_source']['mg_list_img'], '.jpg') < -1) {
                continue;
            }

            $img_url = $row['_source']['mg_list_img'];
            if(strpos($row['_source']['mg_list_img'], 'http') === FALSE) {
                $img_url = 'https:'.$img_url;
            }
            echo $img_url.PHP_EOL;

            $file_path = '/home/82joong/html/crawl/html/webdata/display/img/'.$row['_source']['mg_market_goods_code'].'.jpg';
            file_put_contents($file_path, file_get_contents($img_url));
        }

    }
}
?>
