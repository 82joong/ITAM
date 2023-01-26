<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Smpos_hub extends CI_Controller {

    protected $req;

    private $response_type = 'echo';

    private $pos_url;   // POS의 URL

    // ERROR_CODE
    private $DENY_PERMISSION = array(
            'result' => false,
            'error_code' => '001',
            'msg' => '서비스가 거부 되었습니다.'
            );
    private $INVALID_GID = array(
            'result' => false,
            'error_code' => '011',
            'msg' => '유효하지않은 상품ID 입니다.'
            );
    private $INVALID_PARAMS = array(
            'result' => false,
            'error_code' => '012',
            'msg' => '유효하지않은 params가 있습니다.'
            );
    private $INVALID_TRANSCODE = array(
            'result' => false,
            'error_code' => '013',
            'msg' => '유효하지않은 번역코드 입니다.'
            );
    private $FAIL_UPDATE = array(
            'result' => false,
            'error_code' => '104',
            'msg' => '업데이트에 실패했습니다.'
            );
    private $FAIL_INSERT = array(
            'result' => false,
            'error_code' => '105',
            'msg' => '등록에 실패했습니다.'
            );


    function __construct($resp_type='echo') {
        parent::__construct();

        $this->response_type = $resp_type;
        $this->req = array_merge($_POST, $_GET);

        $allow_ips = array(
                        '110.92.254.77', // 개발서버
                        '14.129.31.152', // Hamt VPN
                        '14.129.31.214', // Bwwjh VPN
                        '14.129.31.215', // joong VPN
                        '14.129.31.216', // lsyoung VPN
                        '14.129.31.217', // bigtuna VPN
                        '14.129.31.137', // maginc3 VPN
                        '14.129.31.234', // yun2kc VPN
                        '14.129.47.26',  // Hamt VPN (Out Network)
                        '14.129.47.28',  // bigtuna VPIN (Out Network)
                        '14.129.120.218',  // 개발서버 218번
                        '14.129.120.185',  // POS 실서버
                        '127.0.0.1',        // CLI
        );
        if(IS_REAL_SERVER === true) {
            if(!isset($_SERVER['REMOTE_ADDR'])) {
                $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
            }
            if(!in_array($_SERVER['REMOTE_ADDR'], $allow_ips)) {
                $response = array(
                    'result' => false,
                    'error_code' => $this->DENY_PERMISSION['error_code'],
                    'msg' => $this->DENY_PERMISSION['msg'].' '.$_SERVER['REMOTE_ADDR'],
                );
                $this->echo_json_encode($response);
                exit;
            }
        }

        $this->pos_url = POS_URL;
    }


    public function get_goods() {

        ini_set('memory_limit', '2G');
        set_time_limit(0);
        

        $this->common->logWrite('debug', print_r($this->req, true));
        //exit;

        /*
        // DEBUG DATA 
        $this->req = Array(
            'g_ids' => Array(
                '12877234',
                '12877237',
                '12877242',
                '12877251',
                '12877252',
                '12877483',
                '12877485',
                '12877490',
                '12877491'	
            ),
            'offset' => 0,
            'limit' => 500
        );
        */

        $response = array(
            'result' => false,
            'error_code' => '',
            'msg' => ''
        );

        $market_index = strtolower($this->uri->segment(4));
        if(strlen($market_index) < 1) {
            return $this->echo_json_encode($this->INVALID_TRANSCODE);
        }


        if(isset($this->req['g_ids'])) {
            $g_ids = $this->req['g_ids'];
            if(!is_array($g_ids)) {
                $g_ids = array($g_ids);
            }else {
                $g_ids = array_filter($g_ids);
            }
        }
        if(sizeof($g_ids) < 1) {
            return $this->echo_json_encode($this->INVALID_TRANSCODE);
        }


        $this->load->library('elastic');
        $el_header = $this->elastic->get_auth_header();


        $fields = array(
            'g_id' => '',
            'g_vendor' => '',
            'g_sku' => '',
            'g_name' => '',
            'g_import_name' => '',
            'g_short_description' => '',
            'g_weight' => 1.0,
            'g_weight_kg' => 0.5000,
            'g_cost' => 0.00,
            'g_display_price' => 0.00,
            'g_price' => 0.00,
            'g_quantity' => 0,
            'g_real_quantity' => 0, 
            'g_is_adult' => 'NO',
            'g_is_vitamin' => 'NO',
            'g_vitamin_count' => 0,
            'g_entry' => '019',
            'g_thumb_img' => 'EXTERNAL',        // 외부 이미지 생성시 설정
            'g_manage_expire' => 'NO',
            'g_expire_date' => 0,
            'g_block_date' => 0,
            'g_alarm_date' => 0,
            'g_next_quantity' => 0,
            'g_next_price' => 0.00,
            'g_next_cost' => 0.00,
            'g_next_expire_date' => 0,
            'g_upc' => '',
            'g_repository' => '',
            'g_item_no' => '',
            'g_keyword' => '',
            'g_taxable' => 'YES',
            'g_tax' => 0,
            'g_exclusive_shipping' => 'NO',
            'g_sea_shipping' => 'NO',
            'g_delivery_day' => 3,
            'g_is_tax_advance' => 'YES',
            'g_purchase_type' => 'before',
            'g_description' => '',
            'g_directions' => '',
            'g_ingredients' => '',
            'g_is_collection' => 'NO',
            'g_collection_info' => '',
            'g_url_key' => '',
        ); 
    

        $el_params = array();

        if(isset($this->req['offset']) && is_numeric($this->req['offset']) && $this->req['offset'] > 0) { 
            $el_params['from'] = $this->req['offset'];
        }    
        if(isset($this->req['limit']) && is_numeric($this->req['limit']) && $this->req['limit'] > 0) { 
            $el_params['size'] = $this->req['limit'];
        }   


        $opt_gid_field_map = $this->config->item('opt_gid_field_map');
        $opt_gid_field = $opt_gid_field_map[strtoupper($market_index)];

        
        $el_params['query']['bool']['must'] = array(
            'terms' => array('mg_options.'.$opt_gid_field => $g_ids)
        );
        $el_params['sort'] = array('_id' => 'asc');
        $el_params = json_encode($el_params);

        $el_url = ELASTIC_HOST.'/'.ELASTIC_MARKET_GOODS_INDEX.'-'.$market_index.'/_search';
        $el_result = $this->elastic->restful_curl($el_url, $el_params, 'GET', $timeout=10, $el_header);
        $el_result = json_decode($el_result, true);
        //$el_result = $this->elastic->get_hits_total($el_result);
        //echo print_r($el_result); //exit;

        if( ! isset($el_result['hits']) ||  ! isset($el_result['hits']['hits']) ) {
            $res = array(
                'result' => true,
                'msg'    => '',
                'goods'  => array()
            );
            return $this->echo_json_encode($res);
        }
    

        $tmp = array(); 
        foreach($el_result['hits']['hits'] as $k=>$row) {

            // @el_result 예외처리
            if( ! isset($row['_source']) || sizeof($row['_source']) < 1) {
                echo 'NO exists [_doc] data';
				continue;
			}


            $mg_data = $row['_source'];


            if( ! isset($mg_data['mg_options']) ) {
                echo 'NO exists [mg_options] data';
				continue;
            }


			// 공통 데이터
            $tmp = $fields; 


            // [ 중요!!] 부모상품의 ID를 g_url_key넣음 -> 추후 서비스에서 상품 생성시 참조
            $ids = explode('-', $mg_data['mg_id']);
            $tmp['g_url_key'] = $ids[1];


			if(strlen($mg_data['mg_import_name']) > 0) {
				$tmp['g_import_name'] = $mg_data['mg_import_name'];
			}else {
				$tmp['g_import_name'] = $mg_data['mg_name'];
			}
			
			$tmp['g_vendor'] = 'Lafayette';
            $tmp['g_weight'] = $mg_data['mg_weight_lbs'];
            $tmp['g_weight_kg'] = $mg_data['mg_weight_kg'];

            // TODO. 가격정책
            $tmp['g_cost'] = $mg_data['mg_price'];  // 가격을 cost로?
            $tmp['g_display_price'] = $mg_data['mg_price']; 
            $tmp['g_price'] = $mg_data['mg_price']; 

            // TODO. 재고 정책
            $tmp['g_quantity'] = (isset($mg_data['mg_quantity']) && $mg_data['mg_quantity'] > 0) ? $mg_data['mg_quantity'] : 0; 
            $tmp['g_real_quantity'] = $tmp['g_quantity']; 


            $keyword = array();
			$keyword[] = $mg_data['mg_trans_brand']['ko'];
			for($k=0; $k<=3; $k++) {
              	if(isset($mg_data['mg_trans_cate'.$k.'_name'])) {
					$keyword[] = $mg_data['mg_trans_cate'.$k.'_name']['ko'];
				}
			}
			$tmp['g_keyword'] = implode(',', $keyword); 


            $tmp['g_description'] = (strlen($mg_data['mg_description']) > 0) ? $mg_data['mg_description'] : '<br />'; 


            foreach($mg_data['mg_options'] as $opt) {

				// 옵션별 데이터 
                $tmp['g_id'] = $opt['product_code'];
                $tmp['g_upc'] = $opt['product_upc'];

                $opt_name = strtoupper($opt['color']).'-'.strtoupper($opt['size']);
                $tmp['g_name'] = $mg_data['mg_name'].' - '.$opt_name;
                
				// TODO. SKU 정책
				$tmp['g_sku'] = 'LFYT_'.$tmp['g_id'];


                if(IS_REAL_SERVER == true) {
				    $tmp['imgs'] = $opt['imgs'];
                }else {
                    $tmp['imgs'] = array(
                        'http://taillist.joong.hamt.co.kr/webdata/goods_images/441/28441/origin0.jpg',
                    );
                }
				
            	$goods[$tmp['g_id']] = $tmp;
            }
            //echo print_r($goods); exit;

        } // END_FOREACH @g_ids
        //echo print_r($goods); exit;

        $response['result'] = true;
        $response['goods'] = $goods;
        $this->common->logWrite('debug', print_r($response, true));

        return $this->echo_json_encode($response);
    }




    /*
    public function get_goods() {

        ini_set('memory_limit', '2G');
        set_time_limit(0);
        

        $this->common->logWrite('debug', print_r($this->req, true));
        //exit;

        // DEBUG DATA 
		$this->req['g_ids'] = array(
            "lafayette-24417339", 
            "lafayette-12795896", 
            "lafayette-14509180", 
            "lafayette-95537544"
		);
        $this->req = Array(
            'g_ids' => Array(
                'lafayette-24418394',
                'lafayette-12565943',
                'lafayette-14508896',
                'lafayette-12876806'
            ),
            'offset' => 0,
            'limit' => 500
        );

        $response = array(
            'result' => false,
            'error_code' => '',
            'msg' => ''
        );

        $market_index = strtolower($this->uri->segment(4));
        if(strlen($market_index) < 1) {
            return $this->echo_json_encode($this->INVALID_TRANSCODE);
        }

        if(isset($this->req['g_ids'])) {
            $g_ids = $this->req['g_ids'];
            if(!is_array($g_ids)) {
                $g_ids = array($g_ids);
            }else {
                $g_ids = array_filter($g_ids);
            }
        }
        if(sizeof($g_ids) < 1) {
            return $this->echo_json_encode($this->INVALID_TRANSCODE);
        }


        $this->load->library('elastic');
        $el_header = $this->elastic->get_auth_header();


        $fields = array(
            'g_id' => '',
            'g_vendor' => '',
            'g_sku' => '',
            'g_name' => '',
            'g_import_name' => '',
            'g_short_description' => '',
            'g_weight' => 1.0,
            'g_weight_kg' => 0.5000,
            'g_cost' => 0.00,
            'g_display_price' => 0.00,
            'g_price' => 0.00,
            'g_quantity' => 0,
            'g_real_quantity' => 0, 
            'g_is_adult' => 'NO',
            'g_is_vitamin' => 'NO',
            'g_vitamin_count' => 0,
            'g_entry' => '019',
            'g_thumb_img' => 'EXTERNAL',
            'g_manage_expire' => 'NO',
            'g_expire_date' => 0,
            'g_block_date' => 0,
            'g_alarm_date' => 0,
            'g_next_quantity' => 0,
            'g_next_price' => 0.00,
            'g_next_cost' => 0.00,
            'g_next_expire_date' => 0,
            'g_upc' => '',
            'g_repository' => '',
            'g_item_no' => '',
            'g_keyword' => '',
            'g_taxable' => 'YES',
            'g_tax' => 0,
            'g_exclusive_shipping' => 'NO',
            'g_sea_shipping' => 'NO',
            'g_delivery_day' => 3,
            'g_is_tax_advance' => 'YES',
            'g_purchase_type' => 'before',
            'g_description' => '',
            'g_directions' => '',
            'g_ingredients' => '',
            'g_is_collection' => 'NO',
            'g_collection_info' => '',
        ); 
    

        $el_params = array();

        if(isset($this->req['offset']) && is_numeric($this->req['offset']) && $this->req['offset'] > 0) { 
            $el_params['from'] = $this->req['offset'];
        }    
        if(isset($this->req['limit']) && is_numeric($this->req['limit']) && $this->req['limit'] > 0) { 
            $el_params['size'] = $this->req['limit'];
        }   
        
        $el_params['query']['bool']['must'] = array(
            'terms' => array('mg_id' => $g_ids)
        );
        $el_params['sort'] = array('_id' => 'asc');
        $el_params = json_encode($el_params);

        $el_url = ELASTIC_HOST.'/'.ELASTIC_MARKET_GOODS_INDEX.'-'.$market_index.'/_search';
        $el_result = $this->elastic->restful_curl($el_url, $el_params, 'GET', $timeout=10, $el_header);
        $el_result = json_decode($el_result, true);
        //$el_result = $this->elastic->get_hits_total($el_result);
        //echo print_r($el_result); //exit;

        if( ! isset($el_result['hits']) ||  ! isset($el_result['hits']['hits']) ) {
            $res = array(
                'result' => true,
                'msg'    => '',
                'goods'  => array()
            );
            return $this->echo_json_encode($res);
        }
    

        $tmp = array(); 
        foreach($el_result['hits']['hits'] as $k=>$row) {

            // @el_result 예외처리
            if( ! isset($row['_source']) || sizeof($row['_source']) < 1) {
                echo 'NO exists [_doc] data';
				continue;
			}

            $mg_data = $row['_source'];


            if( ! isset($mg_data['mg_options']) ) {
                echo 'NO exists [mg_options] data';
				continue;
            }


			// 공통 데이터
            $tmp = $fields; 

			if(strlen($mg_data['mg_import_name']) > 0) {
				$tmp['g_import_name'] = $mg_data['mg_import_name'];
			}else {
				$tmp['g_import_name'] = $mg_data['mg_name'];
			}
			
			$tmp['g_vendor'] = 'Lafayette';
            $tmp['g_weight'] = $mg_data['mg_weight_lbs'];
            $tmp['g_weight_kg'] = $mg_data['mg_weight_kg'];

            // TODO. 가격정책
            $tmp['g_cost'] = $mg_data['mg_price'];  // 가격을 cost로?
            $tmp['g_display_price'] = $mg_data['mg_price']; 
            $tmp['g_price'] = $mg_data['mg_price']; 

            // TODO. 재고 정책
            $tmp['g_quantity'] = (isset($mg_data['mg_quantity']) && $mg_data['mg_quantity'] > 0) ? $mg_data['mg_quantity'] : 0; 
            $tmp['g_real_quantity'] = $tmp['g_quantity']; 


            $keyword = array();
			$keyword[] = $mg_data['mg_trans_brand']['ko'];
			for($k=0; $k<=3; $k++) {
              	if(isset($mg_data['mg_trans_cate'.$k.'_name'])) {
					$keyword[] = $mg_data['mg_trans_cate'.$k.'_name']['ko'];
				}
			}
			$tmp['g_keyword'] = implode(',', $keyword); 


            $tmp['g_description'] = (strlen($mg_data['mg_description']) > 0) ? $mg_data['mg_description'] : '<br />'; 


            foreach($mg_data['mg_options'] as $opt) {

				// 옵션별 데이터 
                $tmp['g_id'] = $opt['product_code'];
                $tmp['g_upc'] = $opt['product_upc'];

                $opt_name = strtoupper($opt['color']).'-'.strtoupper($opt['size']);
                $tmp['g_name'] = $mg_data['mg_name'].' - '.$opt_name;
                
				// TODO. SKU 정책
				$tmp['g_sku'] = 'LFYT_'.$tmp['g_id'];


                if(IS_REAL_SERVER == true) {
				    $tmp['imgs'] = $opt['imgs'];
                }else {
                    $tmp['imgs'] = array(
                        'http://taillist.joong.hamt.co.kr/webdata/goods_images/441/28441/origin0.jpg',
                    );
                }
				
            	$goods[$tmp['g_id']] = $tmp;
            }
            //echo print_r($goods); exit;

        } // END_FOREACH @g_ids
        //echo print_r($goods); exit;

        $response['result'] = true;
        $response['goods'] = $goods;
        $this->common->logWrite('debug', print_r($response, true));

        return $this->echo_json_encode($response);
    }
    */



    private function echo_json_encode($response) {
        if($this->input->get_post('print_r')) {
            echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
            echo '<xmp>'."\n";
            print_r($response);
            echo '</xmp>';
            exit;
        } else {
            switch($this->response_type) {
                case 'echo' :
                    echo json_encode($response);
                    return;
                case 'return' :
                    return $response;
                case 'empty' :
                    return;
            }
        }
    }


}
