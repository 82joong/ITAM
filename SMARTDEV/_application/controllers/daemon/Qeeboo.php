<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Qeeboo extends CI_Controller {

    public function __construct() {
        parent::__construct();

        if( IS_REAL_SERVER ) {
            if($this->input->is_cli_request() == false) {
                return;
            }
        }

    }

    public function sync_raw_qeeboo() {
        set_time_limit(9999);

        $this->load->library('ShopCrawler');
        $data = $this->shopcrawler->getCategoryGoodsList('qeeboo', '', array('mode' => 'sync')); // 오래걸림. 풀 크롤링
        $this->sync_csv_qeeboo($data);
    }

    private function sync_csv_qeeboo($goods_data=array()) {
        set_time_limit(9999);

        $filepath = APPDATA_PATH.'/market_file/qeeboo/csvs/qeeboo-'.date('Ymd_His').'.csv';
        foreach($goods_data as $goods) {
            $result = $this->convertDetail($goods);

            if( ! file_exists($filepath) || filesize($filepath) == 0) {
                $fp = fopen($filepath, 'w');
                fputcsv($fp, array_keys($result));
                fputcsv($fp, array_values($result));
                fclose($fp);
            } else {
                $fp = fopen($filepath, 'a');
                fputcsv($fp, array_values($result));
                fclose($fp);
            }
        }
    }


    /**
     * 크롤링한 qeeboo 상품정보를 market-goods-qeeboo 스키마에 맞게 변환.
     *
     * @param array $data qeeboo 상품정보 
     * @return array 
     */
    private function convertDetail($data=array()) {

        /*********************************************
         * Default Struct 구성.
         *********************************************/
        foreach(array('description', 'content') as $replace_key) {
            if( ! empty($data[$replace_key])) {
                $data[$replace_key] = str_replace("\r", "", $data[$replace_key]);
                $data[$replace_key] = str_replace("\n", " ", $data[$replace_key]);
            }
        }

        $result = array(
            'mg_id'                 => 'qeeboo-'.$data['id'],
            'mg_market_goods_code'  => $data['id'],
            'mg_name'               => $data['title'],
            'mg_import_enname'      => $data['title'],
            'mg_trans_names.en'     => $data['title'],
            'mg_trans_names.ko'     => '',
            'mg_trans_names.cn'     => '',
            'mg_description'        => $data['description'],
            'mg_created_at'         => $data['created_at'],
            'mg_updated_at'         => date('Y-m-d H:i:s', time()),
            'mg_price'              => $data['price'],
            'mg_status'             => ($data['available'] === TRUE) ? 1 : 0,
            'tags'                  => json_encode($data['tags']),
            'mg_options'            => array(),
            'mg_view_imgs'          => json_encode($data['images']),
            'mg_list_img'           => $data['featured_image'],
            'mg_cate0_id'           => $data['cate_code'],
            'mg_cate0_name'         => $data['cate_name'],
            'mg_trans_cate0_name.en' => $data['cate_name'],
            'mg_trans_cate0_name.ko' => '',
            'mg_catepath_id'        => $data['cate_code'],
            'mg_is_active'          => ($data['available'] === TRUE) ? 'YES' : 'NO',
            'mg_quantity'           => ($data['available'] === TRUE) ? 99999 : 0,
            'mg_url_key'            => $data['url'],
            'mg_weight_kg'          => 0,
            'mg_weight_lbs'         => 0,
            'mg_raw_data'           => serialize($data),
            'mg_has_options'        => 'YES',
            'mg_market'             => 'qeeboo',

            // [POSTFIX] :: _integer 붙여서 추가.
            'mg_created_ts_integer' => strtotime($data['created_at']),
            'mg_display_price_integer' => intVal($data['compare_at_price']),

            // [POSTFIX] :: _keyword 붙여서 추가.
            'mg_handle_keyword'     => $data['handle'],
            'mg_vendor_keyword'     => $data['vendor'],

            // [POSTFIX] :: _text 붙여서 추가.
            'mg_option_display_text' => (is_array($data['options'])) ? array_pop($data['options']) : '',
        );



        /*********************************************
         * 데이터 가공.
         *********************************************/

        // 상품이름, 카테고리 번역
        $text = array();
        if(strlen($result['mg_name']) > 0) {
            $text[] = $result['mg_name'];
        }
        if(strlen($result['mg_cate0_name']) > 0) {
            $text[] = $result['mg_cate0_name'];
        }
        $params = array(
            'from'  => 'en',
            'to'    => array('ko', 'zh'),
            'text'  => $text,
        );  
        $trans_data = $this->common->restful_curl(TRANSLATE_API_URL, http_build_query($params));
        $trans_data = json_decode($trans_data, true);

        if($trans_data['is_success'] === TRUE) {
            $mg_name_idx = array_search($result['mg_name'], $text);
            $mg_cate_name_idx = array_search($result['mg_cate0_name'], $text);

            if($mg_name_idx !== FALSE) {
                foreach($trans_data['result'][$mg_name_idx] as $to => $val) {
                    $to = ($to == 'zh') ? 'cn' : $to;
                    $result['mg_trans_names.'.$to] = $val;
                }
            }

            if($mg_cate_name_idx !== FALSE) {
                foreach($trans_data['result'][$mg_cate_name_idx] as $to => $val) {
                    if($to == 'ko') {
                        $result['mg_trans_cate0_name.'.$to] = $val;
                        break;
                    }
                }
            }
        }


        // mg_created_at 날짜 포맷 변경 :: yyyy-MM-dd HH:mm:ss
        // ex) 2020-11-25T11:06:43+01:00
        $created_at = explode('T', $result['mg_created_at'], 2);
        $created_at_date = $created_at[0];
        $created_at_time = array_shift(explode('+', $created_at[1], 2));
        $result['mg_created_at'] = $created_at_date.' '.$created_at_time;


        // 옵션정보 담기
        $options = array();
        $mg_weight_kg = 0;  // 대표 상품의 무게
        $mg_weight_lbs = 0; // 대표 상품의 무게
        foreach($data['variants'] as $idx => $opt) {
            $_option = array(
                'mg_id'         => 'qeeboo-'.$opt['id'],
                'item_id'       => $opt['id'],
                'sku_id'        => $opt['sku'],
                'mg_name'       => $opt['name'],
                'mg_option_display_text' => $opt['title'],
                'status'        => ($opt['available'] === TRUE) ? 1 : 0,
                'product_upc'   => $opt['barcode'],
                'mg_list_img'   => $opt['featured_image']['src'],
                'mg_weight_kg'  => sprintf('%.2f', $opt['weight'] / 10000),
                'mg_weight_lbs' => sprintf('%.2f', ($opt['weight'] / 10000) * 2.204623), // 1kg=2.204623lbs
                'mg_quantity'   => $opt['inventory_quantity'],
                'mg_price'      => sprintf('%.2f', $opt['price']),
            );
            if($idx == 0) {
                $mg_weight_kg = $_option['mg_weight_kg'];
                $mg_weight_lbs = $_option['mg_weight_lbs'];
            }
            $options[] = $_option;
        }
        $result['mg_options'] = json_encode($options);
        $result['mg_weight_kg'] = $mg_weight_kg;
        $result['mg_weight_lbs'] = $mg_weight_lbs;
    
        return $result;
    }


    /**
     * Qeeboo 카테고리 정보 저장.
     */
    public function generate_category() {
        $this->load->library('ShopCrawler');
        $res = $this->shopcrawler->getCategory('qeeboo');

        if(is_array($res) && sizeof($res) > 0) {
            $this->load->helper('file');
            $filename = DISPLAY_PATH.'/category/qeeboo_category.info';
            
            $res = serialize($res);
            if(write_file($filename, $res)) {
                echo 'Update Category Data!';
            } else {
                echo 'Fail Make a file!!';
            }
        }
    }

}
