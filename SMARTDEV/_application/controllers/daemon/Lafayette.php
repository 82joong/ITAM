<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Lafayette extends CI_Controller {

    private $_ELASTIC_HOST= 'http://taillist.joong.data.hamt.co.kr:9200';
    private $_ELASTIC_RAW_INDEX = 'raw-lafayette';
    private $_MARKET = 'lafayette';
    private $_ELASTIC_BULK_PATH = APPPATH.'../appdata/elastic_bulk';

    public function __construct() {
        parent::__construct();

        if( IS_REAL_SERVER ) {

		// 실서버는 raw 데이터와 같이 공존
		$this->_ELASTIC_HOST = ELASTIC_HOST;


            if($this->input->is_cli_request() == false) {
                return;
            }
        }

    }

    public function extract_data_from_rawdata() {

	    ini_set('memory_limit', '2G');
	    set_time_limit(0);

        $_is_init = FALSE;
        if($_is_init === TRUE) {
            $from_date = date('Y-m-d', time()-(86400*30));  // 2일 이전
        }else {
            $from_date = date('Y-m-d', time()-86400);
        }
        $from_date = $from_date.'T00:00:00Z';
        //echo $from_date.PHP_EOL; exit;

	    /*
        $from_date = '2021-04-22T00:00:00Z';
        echo $from_date.PHP_EOL; //exit;
        $to_date   = '2021-04-22T00:00:00Z';
        echo $to_date.PHP_EOL; //exit;
	     */


        $this->load->helper('lafayette');
        $_CATEGORY = getCategoryByName('name_ko');
	    //echo print_r($_CATEGORY); exit;


        $this->load->library('Elastic');
        $el_header = $this->elastic->get_auth_header();

        $params = array(
            'query' => array(
                'range' => array(
                    '@timestamp' => array(
                        'gte' => $from_date,
                        //'lte' => $to_date,
                    )
                )
            )
	    );


        /*// !!!! [DEBUG] 디버그용 Query
        $params = array(
            'query' => array(
                'bool' => array(
                    'must' => array(
                        array(
                            'term' => array(
                                'product.principalRmsCode' => '40408912'
                                //'product.principalRmsCode' => '67350479'
                            )
                        )
                    )
                )
            )
        );
        */


        //$params['track_total_hits'] = true; // total default 10000 => 해제
        $json_params = json_encode($params);  // json 내에 숫자 데이터 체크
        //echo $json_params; exit;

        $el_url = $this->_ELASTIC_HOST.'/'.$this->_ELASTIC_RAW_INDEX.'/_count';
        $el_result = $this->elastic->restful_curl($el_url, $json_params, 'GET', $timeout=10, $el_header);
        $el_result = json_decode($el_result, true);
	    //echo print_r($el_result); exit;

        
        if( ! isset($el_result['count']) || ($el_result['count'] * 1)  < 1) {
	        echo print_r($el_result);
            exit;
        }

        $total_count = ($el_result['count']*1);
	    echo 'TOTAL COUNT : '.$total_count.PHP_EOL;


        $size = 3000;
        for($from=0; $from<=$total_count; $from=$from+$size) {


            $json_filepath = $this->_ELASTIC_BULK_PATH.'/'.$this->_MARKET.'-'.date('YmdHi').'-'.$from.'.json';
            //echo $json_filepath.PHP_EOL; exit;
            @unlink($json_filepath);

            echo 'FROM : '.$from.PHP_EOL;
            $params['from'] = $from;
            $params['size'] = $size;
            $params['sort'] = array('@timestamp' => 'asc');
            $params['track_total_hits'] = TRUE;

            //echo print_r($params);
            $json_params = json_encode($params); 
            //echo $json_params; exit;


            $el_url = $this->_ELASTIC_HOST.'/'.$this->_ELASTIC_RAW_INDEX.'/_search';
            $el_result = $this->elastic->restful_curl($el_url, $json_params, 'GET', $timeout=10, $el_header);
            $el_result = json_decode($el_result, true);
            //echo print_r($el_result); exit;


            if(isset($el_result['error'])) {
                echo 'QUERY ERROR'.PHP_EOL;
                continue;

            }else if(isset($el_result['hits']) && ($this->elastic->get_hits_total($el_result) > 0) === FALSE) {
                echo 'ROWS 0'.PHP_EOL;
                continue;
            }

            
            // SIZE 만큼 Loop
            foreach($el_result['hits']['hits'] as $key=>$row) {

                
                //echo '_id : '.$row['_id'].PHP_EOL;

				/*
			    @mg_data [market-goods-lafayette]에 넣을 데이터 정의 

				"mg_id" : {"type" : "keyword"},
				"mg_trans_names" : {"properties" : {"ko" : {}, "en" : {}, "cn" : {}}},
                "mg_trans_color"
                "mg_trans_size"
                "mg_trans_cate 0~4"

				"mg_is_active" : {"type" : "keyword"},
				"mg_url_key" : {"type" : "text"},
				"mg_price" : {"type" : "float"},
				"mg_options" : {"properties" : {}},
				"mg_description" : {"type" : "text"},
				"mg_catepath_id" : {"type" : "text"},
				"mg_cate1_id" : {"type" : "keyword"},
				"mg_cate1_name" : {"type" : "text"},
				"mg_cate2_id" : {"type" : "keyword"},
				"mg_cate2_name" : {"type" : "text"},
				"mg_cate3_id" : {"type" : "keyword"},
				"mg_cate3_name" : {"type" : "text"},
				"mg_view_imgs" : {"type" : "keyword"},
				"mg_name" : {"type" : "text"},
				"mg_quantity" : {"type" : "integer"},
				"mg_import_enname" : {"type" : "text"},
				"mg_list_img" : {"type" : "keyword"},
				"mg_market_goods_code" : {"type" : "keyword"},
				"mg_weight_kg" : {"type" : "float"},
				"mg_weight_lbs" : {"type" : "float"},
				"mg_has_options" : {"type" : "keyword"},
				"mg_market" : {"type" : "keyword"},
				"mg_color" : {"type" : "keyword"},
				"mg_size" : {"type" : "keyword"}
				"mg_raw_data" : {"index" : false,"type" : "text"},
				"mg_created_at" : {"format" : "yyyy-MM-dd HH:mm:ss",},
				"mg_updated_at" : {"format" : "yyyy-MM-dd HH:mm:ss",},

                "mg_brand_data"
				*/


                $raw_id = $row['_id'];
                $row = $row['_source'];
                //echo print_r($row); exit;

                $mg_data = array(
                    'mg_market'     => $this->_MARKET,
                    'mg_url_key'    => '',
                    'mg_has_options'=> 'NO',
                    'mg_is_active'  => 'YES',
                    'mg_weight_kg'  => 0.3,
                    'mg_weight_lbs' => 0.66,
                    'mg_options'    => array(),
                    'mg_raw_data'   => array(),
                    'mg_raw_id'     => $raw_id,
                    'mg_status'     => 1
                );


                $trans_data = array(); // 번역이 필요한 항목 


                # Market Goods Code =========================================

                $goods_id = $row['product']['principalRmsCode'];			// 대표상품번호
                $mg_data['mg_market_goods_code'] = $this->_MARKET.'-'.$goods_id;
                $mg_data['mg_id'] = $this->_MARKET.'-'.$goods_id;



                # Description =========================================

                $description_fr = $row['product']['description']['values']['fr-FR'];
                $mg_data['mg_description'] = $description_fr;


                # Brand ============================================

                $mg_data['mg_brand_code'] = $row['product']['brand']['code'];
                $mg_data['mg_brand_name'] = $row['product']['brand']['code'];
                $mg_data['mg_trans_brand']['fr'] = $row['product']['brand']['name']['values']['fr-FR'];
                $trans_data['mg_brand'] = $mg_data['mg_trans_brand']['fr'];

                $mg_data['mg_brand_data'] = $row['ecomData']['ecomBrandData'];
                
                # Category =========================================

                $mg_cate_data = array();
                $cate_data = $row['ecomData']['ecomNavigationData'];
                // 실서버쪽은 @row['ecomData']['ecomNavigationData'] 2차 배열 형태
                if( ! array_key_exists('root', $cate_data)) {
                    $cate_data = array_shift($cate_data);
                }

                $mg_data['mg_catepath_id'] = '';
                $cate_key_map = array(
                    'root'       => 0,
                    'levelOne'   => 1,
                    'levelTwo'   => 2,
                    'levelThree' => 3,
                    'levelFour'  => 4,
                );

		        $exists_category = TRUE;
                foreach($cate_data as $k=>$cate) {

                    if($cate === NULL) continue;
                
                    //echo $k.PHP_EOL;
                    $cate_key = $cate_key_map[$k];
		            $cate_code = trim($cate['code']);

                    $mg_data['mg_cate'.$cate_key.'_id'] = $cate_code;
                    $mg_data['mg_cate'.$cate_key.'_name'] = $cate['name'];
                    $mg_data['mg_trans_cate'.$cate_key.'_name']['fr'] = $cate['name'];

                    $trans_data['mg_cate'.$cate_key.'_name'] = $cate['name'];


                    // [lafayette_category.info] CATEGORY DATA 내에 없는 신규 상품에 대해 카테고리 추가 
                    if(strlen($cate_code) > 0 && !isset($_CATEGORY[$cate_code])) {
                        //echo $cate_code.PHP_EOL;
                        $exists_category = FALSE;
                    }	
                }
		        //echo print_r($mg_data); exit;


                foreach($cate_key_map as $k) {
                    $mg_data['mg_catepath_id'] .= $mg_data['mg_cate'.$k.'_id'].'/'; 
                }
                $mg_data['mg_catepath_id'] = substr(trim($mg_data['mg_catepath_id']), 0, -1);

                
                /* ecomCategoriesData 일반 카테고리 기중
                $mg_cate_data = array();
                $cate_data = $row['ecomData']['ecomCategoriesData']['names'];
                foreach($cate_data as $k=>$cate) {
                    $mg_data['mg_cate'.$k.'_id'] = $cate['code'];
                    $mg_data['mg_cate'.$k.'_name'] = $cate['label'];
                    $mg_data['mg_trans_cate'.$k.'_name']['fr'] = $cate['label'];

                    $trans_data['mg_cate'.$k.'_name'] = $cate['label'];
                }
                $mg_data['mg_catepath_id'] = $mg_data['mg_cate1_id'].'/'; 
                $mg_data['mg_catepath_id'] .= $mg_data['mg_cate2_id'].'/'; 
                $mg_data['mg_catepath_id'] .= $mg_data['mg_cate3_id']; 
                */

                

                # View imgs/List img =========================================
                
                $mg_data['mg_list_img'] = '';

                $option_imgs = array();
                foreach($row['assets'] as $asset) {
                   
                    $gids = $asset['productData']['articleErpCodes'];	// 옵션상품번호
                    foreach($asset['files'] as $k=>$img) {

                        /*
                        $name : G_97701460_320_ZP_3
                        [code]_[대표상품코드]_[옵션색상코드]_[이미지type]_[번호] 
                        */

                        $img_data = explode('_', $img['name']);

                        // ZP 가 가장 큰 이미지
                        if($img_data[3] == 'ZP') {

                            // TEST Server 일때, 확인필요
                            $img_src = str_replace('https://static-int', 'http://static', $img['internalUrl']);
                            //echo '<img src="'.$img_src.'"><br/>';
                            // 옵션 g_id 별 이미지 
                            foreach($gids as $opt_id) {
                                $option_imgs[$opt_id][$img_data[4]] = $img_src;
                            }
                        }

                        // TODO
                        if(($img_data[3] == 'VPPM' && $img_data[4] == 1) && strlen($mg_data['mg_list_img']) < 1) {
                            $mg_data['mg_list_img'] = str_replace('https://static-int', 'http://static', $img['internalUrl']);
                        }
                    }
                }


                $mg_data['mg_view_imgs'] = array();
                
                ksort($option_imgs);
                foreach($option_imgs as &$opimg) {
                    ksort($opimg);
                    foreach($opimg as $op) {
                        $mg_data['mg_view_imgs'][] = $op;
                    }
                }
                $mg_data['mg_view_imgs'] = array_values(array_unique($mg_data['mg_view_imgs']));


                # Color/Size =========================================


                $mg_data['mg_size'] = array();
                $mg_data['mg_color'] = array();

                $opt_data = $row['ecomData']['ecomArticlesData'];
                $tmp = array();
                foreach($opt_data as $opt) {

                    $opt_size = $opt['ecomSizeData']['label'];
                    $opt_color = $opt['ecomColorData']['label'];

                    $mg_data['mg_size'][] = $opt_size;
                    $mg_data['mg_color'][] = $opt_color;


                    // 이미지 index count로 reindex 처리 => 0 시작시 bulk insert Error
                    $opt_imgs = array_combine(range(1, count($option_imgs[$opt['uga']])), array_values($option_imgs[$opt['uga']]));

                    $mg_data['mg_options'][] = array(
                        'product_code'  => $opt['uga'],
                        'product_upc'   => $opt['ean'],
                        'color'         => $opt_color,
                        'color_code'    => $opt['ecomColorData']['code'],
                        'size'          => $opt_size,
                        'size_code'     => $opt['ecomSizeData']['code'],
                        'uga'           => $opt['uga'],
                        'imgs'          => $opt_imgs,
                        'active'        => $opt['active']
                    );
                }

                if(sizeof($mg_data['mg_size']) > 0 || sizeof($mg_data['mg_color']) > 0 ) {
                    $mg_data['mg_has_options'] = 'YES';

                    $mg_data['mg_size'] = array_values(array_unique($mg_data['mg_size']));
                    $mg_data['mg_trans_size']['fr'] = $mg_data['mg_size'];
                    $mg_data['mg_color'] = array_values(array_unique($mg_data['mg_color']));
                    $mg_data['mg_trans_color']['fr'] = $mg_data['mg_color'];

                    $trans_data['color'] = implode('|', $mg_data['mg_color']);
                    $trans_data['size'] = implode('|', $mg_data['mg_size']);
                }
                  


                # Price/Quantity =========================================

                $mg_data['mg_price'] = 0;
                $mg_data['mg_quantity'] = 99999;

                foreach($row['offers'] as $k=>$offer) {
                    if($mg_data['mg_price'] < $offer['price']['newRetailPrice']) {
                        $mg_data['mg_price'] = $offer['price']['newRetailPrice'];
                    }
                    foreach($offer['stocks'] as $stock) {
                        if($mg_data['mg_quantity'] < $stock['quanity']) {
                            $mg_data['mg_quantity'] = $stock['quantity'];
                        }
                    }
                }

                if($mg_data['mg_price'] > 0) {
                    $mg_data['mg_price'] = $mg_data['mg_price'] / 100; 
                }



                # Name =========================================

                $name_fr = $row['product']['name']['values']['fr-FR'];
                $trans_data['name'] = $name_fr;

                $mg_data['mg_trans_names'] = array(
                    'ko' => $trans_res['result']['name']['ko'],
                    'en' => $trans_res['result']['name']['en'],
                    'cn' => '',
                    'fr' => $name_fr,
                );
                $mg_data['mg_name'] = $name_fr;


                # Import Name =========================================

                $mg_data['mg_import_enname'] = $name_fr;


                # Updated At =========================================
                $mg_data['mg_updated_at'] = date('Y-m-d H:i:s', time());

                # Created At  =========================================
                $mg_data['mg_created_at'] = date('Y-m-d H:i:s', time());




                # TRANSLATE  =========================================
                // 번역 (실서버일때, 번역 확인가능)
				//echo print_r($trans_data);

                $trans_params = array(
                    'from'  => 'fr',
                    'to'    => array('en', 'ko'),
                    'text'  => $trans_data
                );
                $trans_res = $this->common->restful_curl(TRANSLATE_API_URL, http_build_query($trans_params), 'POST');
                $trans_res = json_decode($trans_res, true);
                //echo print_r($trans_res); //exit;

				if($trans_res['is_success'] == TRUE) {

					//echo 'TRANSLATE SUCCESS'.PHP_EOL;
					$res = $trans_res['result'];
										
					// BRAND
                	$mg_data['mg_trans_brand']['en'] = $res['mg_brand']['en'];
                	$mg_data['mg_trans_brand']['ko'] = $res['mg_brand']['ko'];


					// CATEGORY
                    foreach($cate_data as $k=>$cate) {
                        if($cate === NULL) continue;
                        $cate_key = $cate_key_map[$k];

                        $mg_data['mg_cate'.$cate_key.'_name'] = $res['mg_cate'.$cate_key.'_name']['en'];
						$mg_data['mg_trans_cate'.$cate_key.'_name']['en'] = $res['mg_cate'.$cate_key.'_name']['en'];
						$mg_data['mg_trans_cate'.$cate_key.'_name']['ko'] = $res['mg_cate'.$cate_key.'_name']['ko'];
					}
                

					// COLOR/SIZE
					$trans_color = explode(' | ', $res['color']['en']); // [공백]/[공백] 으로 들어옴
					$color_match = array();
					foreach($mg_data['mg_color'] as $k=>$fr_color) {
						$color_match[$fr_color] = $trans_color[$k];
					}				
					$mg_data['mg_color'] = $trans_color;

					$trans_size = explode(' | ', $res['size']['en']);
					$size_match = array();
                    foreach($mg_data['mg_size'] as $k=>$fr_size) {
						$size_match[$fr_size] = $trans_size[$k];
					}
					$mg_data['mg_size'] = $trans_size;

					$mg_data['mg_trans_color']['en'] = explode(' | ', $res['color']['en']);
					$mg_data['mg_trans_color']['ko'] = explode(' | ', $res['color']['ko']);
					$mg_data['mg_trans_size']['en'] = explode(' | ', $res['size']['en']);
					$mg_data['mg_trans_size']['ko'] = explode(' | ', $res['size']['ko']);

					foreach($mg_data['mg_options'] as &$mg_opt) {
						$mg_opt['color'] = $color_match[$mg_opt['color']];
						$mg_opt['size'] = $size_match[$mg_opt['size']];
					}	
					
					// NAME
					$mg_data['mg_trans_names']['en'] = $res['name']['en'];
					$mg_data['mg_trans_names']['ko'] = $res['name']['ko'];
                	$mg_data['mg_name'] = $res['name']['en'];
                	$mg_data['mg_import_enname'] = $res['name']['en'];
				}
                //echo print_r($mg_data); //exit;


                // [lafayette_category.info] CATEGORY DATA 내에 없는 신규 상품에 대해 카테고리 추가 
                if($exists_category == FALSE) {
                    echo $mg_data['mg_id'].PHP_EOL;
                    addCategory($mg_data['mg_id'], $mg_data);
                }


                # Make a BULK File
                $input_data = array(
                'index' => array(
                    '_index' => ELASTIC_MARKET_GOODS_INDEX.'-'.$this->_MARKET,
                    '_type' => '_doc',
                    '_id' => $mg_data['mg_market_goods_code'],
                    ),   
                );   
                $row_data = array();

		        // BULK 시 아래 줄 반드시 추가
                //$row_data[] = json_encode($input_data);

                $row_data[] = json_encode($mg_data);
                $row_data[] = '';

                if(sizeof($row_data) > 0) { 
                    $row_data[] = '';   // implode전에 마지막행 추가 중요!!!
                    @file_put_contents($json_filepath, implode("\n",$row_data), FILE_APPEND);
		        }

            } // END_FOREACH @el_result
            //exit;


            /* ==============
             * logstash로 변경 
             * ==============
             */


            /*
            // JSON 파일정보 읽어 오기
            $file_read = fopen($json_filepath, 'r');
            $file_data = fread($file_read, filesize($json_filepath));
            fclose($file_read);


            $el_url = ELASTIC_HOST.'/_bulk';
            $el_result = $this->elastic->restful_curl($el_url, $file_data, 'POST', $timeout=10, $el_header);
            $el_result = json_decode($el_result, true);
            //echo print_r($el_result); //exit;


            if( (isset($el_result['errors']) && $el_result['errors'] == true) || ! isset($el_result['items']) ) {
                echo 'EL_ERROR : CHECK!!!! LOG File'.PHP_EOL;
                $this->common->logWrite('daemon', print_r($el_result, true), 'extract_lafayette');
            }else {

                echo 'EL_RESULT : '.sizeof($el_result['items']).PHP_EOL;

                //foreach($el_result['items'] as $item) {
                    // 신규 Insert Data 아닌 경우
                    //if($item['index']['result'] != 'created') {
                        //$this->common->logWrite($log, print_r($item, true), 'pump_to_elastic');
                    //}
                //}
            }
            */

            echo 'END Loop @i'.PHP_EOL;

        } // END_FOR @i
    }


    /*
    |--------------------------------------------------------------------------
    | 라파예프 카테고리 변환 
    |--------------------------------------------------------------------------
    |
    | - API 제공전에 Flat 파일(CSV) 기반으로 데이터 업데이트 
    |
    */

    public function tranfer_category_info() {


        // 카테고리 파일 
		$_CT_FILE = APPPATH.'../appdata/market_file/lafayette/nature-navigation-mapping.csv';
        //$_CT_FILE = APPPATH.'../appdata/market_file/lafayette/cate_test.csv';

		$_INFO_FILE = DISPLAY_PATH.'/category/lafayette_category.info';
 

        $fp = fopen($_CT_FILE, 'r');

        /* TITLE 
        [0] => CODE_NAT
        [1] => ATTS_PRD_TYPE_PRODUIT
        [2] => ATTS_PRD_GENRE
        [3] => ATTS_PRD_AGE
        [4] => ATTS_PRD_OCCASION

        [5] => CODE_NIV0            // CATE0_CODE
        [6] => LIB_NIV0             // CATE0_NAME

        [7] => CODE_NIV1            // CATE1_CODE
        [8] => LIB_NIV1             // CATE1_NAME

        [9] => CODE_NIV2            // CATE2_CODE
        [10] => LIB_NIV2            // CATE2_NAME

        [11] => CODE_NIV3           // CATE3_CODE
        [12] => LIB_NIV3            // CATE3_NAME

        [13] => CODE_NIV4           // CATE4_CODE
        [14] => LIB_NIV4            // CATE4_NAME 

        [15] => ATTS_PRD_MARQUE
        */

        $data = array();
        $cnt = 0;
		$trans_data = array();
        while($row = fgetcsv($fp, 0, ',')) {
            $cnt ++;
            if($cnt == 1) continue;

            $cate_data = array_chunk(array_slice($row, 5, 10), 2);
            //echo print_r($cate_data); //exit;

            $depth_code = array();	// cate0 / cate1 ... code 저장
            foreach($cate_data as $k=>$v) {
                
                //if(strlen($v[1]) < 1) continue;

                if(strlen($v[0]) < 1) {
                    $depth_code[$k] = 'NONE';
                    continue;
                }


				$trans_data[$v[0]] = trim($v[1]);

                $key_name = 'cate'.$k;
                $depth_code[$k] = $v[0];

                $tmp = array();
				foreach($depth_code as $depth=>$code) {
                    $tmp['depth'.$depth] = $depth_code[$depth];
				}
				$tmp['name'] = $v[1];

                $depth_keys = array_slice($depth_code, -2, sizeof($depth_code));
				$code_path = implode('.', array_filter($depth_keys));
                //echo $code_path.PHP_EOL;

				$depth_data = array();
				$this->_setArray($depth_data, $code_path, $tmp);
                //echo print_r($depth_data);

                $first_key = array_key_first($depth_data);
                $depth_data = array_shift($depth_data); 
                //echo 'SIZE : '.sizeof($depth_data).PHP_EOL;

                if(sizeof($depth_data) > 1) {
				    $data[$key_name][$first_key] = $depth_data;
                }else {
                    $second_key = array_key_first($depth_data);
                    $depth_data = array_shift($depth_data); 
				    $data[$key_name][$first_key][$second_key] = $depth_data;
                }

            } // END_FOREACH @cate_data

            /* DEBUG
            echo '== ROW : '.$cnt.'=============================='.PHP_EOL.PHP_EOL;
            echo print_r($data); exit;
            echo '==============================================='.PHP_EOL.PHP_EOL;
            */
        }
        ksort($data);
        //echo print_r($data); exit;
        //exit;


		// 번역
		$trans_params = array(
			'from'  => 'fr',
			'to'    => array('en', 'ko'),
			'text'  => $trans_data
		);
		$trans_res = $this->common->restful_curl(TRANSLATE_API_URL, http_build_query($trans_params), 'POST');
		$trans_res = json_decode($trans_res, true);
		//echo print_r($trans_res); //exit;
		
		if($trans_res['is_success'] == TRUE) {
            $data = $this->_setTransRes($data, $trans_res['result']);
		}

        $data['cate0']['NONE'] = array(
            'depth0'  => 'NONE',
            'name'    => 'NONE',
            'name_ko' => 'NONE',
            'name_en' => 'NONE',
        );
        //echo print_r($data); exit;

        file_put_contents($_INFO_FILE, serialize($data));
    }

    public function test() {
        $test_data = array(
            'cate0' => array(
                'N0x01' => Array(
                    'depth0' => 'N0x01',
                    'name' => 'Enfant'
                )
            )
        );

        $trans_data = array(
            'N0x01' => Array (
                'en' => 'Child',
                'ko' => '아이'
            )
        );
        $test_data = $this->_setTransRes($test_data, $trans_data);
        echo 'RES : ';
        echo print_r($test_data).PHP_EOL;
    }


    private function _setTransRes($data, $trans_data) {
        if( ! is_array($data)) {
            return $data;
        }
        foreach($data as $k=>&$v) {
            if( in_array('name', array_keys($v))) {
                if(isset($trans_data[$k])) {
                    $v['name_ko'] = $trans_data[$k]['ko'];
                    $v['name_en'] = $trans_data[$k]['en']; 
                }else {

                    $v['name_ko'] = '';
                    $v['name_en'] = ''; 
                }
            }else {
                $v = $this->_setTransRes($v, $trans_data);
            }
        }
        return $data;
    }

	public function setArray_mock() {
		$array = Array();
		$this->_setArray($array, "key", Array('value' => 2));
		$this->_setArray($array, "key.test.value", 3);
		print_r($array);
        return;
	}

	private function _setArray(&$array, $keys, $value) {
		$keys = explode(".", $keys);
		$current = &$array;
		foreach($keys as $key) {
			$current = &$current[$key];
		}
		$current = $value;
	}

    /*
    |--------------------------------------------------------------------------
    | 라파예프 상품 가격/재고 업데이트  -> market-goods-lafayette
    |--------------------------------------------------------------------------
    |
    | - API 제공전에 Flat 파일(CSV) 기반으로 데이터 업데이트 
    | - CSV 파일 mtime 읽어서 변경에 대한 이력 판단 후 데몬 실행
    | - 업데이트 일어나는 상품에 대한 POS 로 연동 로직 고려
    |
    */

    public function update_stock_and_price() {

        // PRICE/STOCK FILE 재고/가격 정보파일 
        $_PS_FILE = APPPATH.'../appdata/market_file/lafayette/stock_level_price_glkoreacenter.csv';
        // TIME CHECK FILE 파일 mtime 체크
        $_TC_FILE = APPPATH.'../appdata/market_file/lafayette/.check_mtime';


        if( ! file_exists($_PS_FILE) ) {
            echo 'No Exists Stock/Price File!';
            return;
        }
        $ps_filemtime = filemtime($_PS_FILE);


        if( ! file_exists($_TC_FILE) ) {
            $tc_filemtime = 0;
        }else {
            $tc_filemtime = file_get_contents($_TC_FILE);
        }


        echo $ps_filemtime.PHP_EOL;
        echo $tc_filemtime.PHP_EOL;


        if($ps_filemtime == $tc_filemtime) {
            echo 'No Changed File!';
            return;
        }



        //$csv_data = array();
        $fp = fopen($_PS_FILE, 'r');


        /*
        @ITEM : 
            - It is the item number in GL system. It will be used to do the link with product data you will catch through Kafka.
        @EAN: 
            - It is the barcode number of the item
        @LOC : 
            - It is the location number for Korea center in our system (3207 is not the final value - it is an exemple)
        @UNIT_RETAIL : 
            - Selling price of the item on Korean website
        @REGULAR_RETAIL : 
            - Regular retail price of the item
        @DISCOUNT_PERCENTAGE : 
            - discount percentage of the price (if we want to display the discount on the website)
        @DEPT: 
            - department code in GL system
        @DEPT_NAME: 
            - department name in GL system (normally the brand)
        @DIVISION: 
            - division code in GL system
        @DIV_NAME: 
            - division name in GL system (exemple : Men , Woman, Accessories etc…)
        */



        # TODO. 확인필요 item number가 대표상품 id 인지 확인 =======

        $this->load->library('Elastic');
        $el_header = $this->elastic->get_auth_header();
        $_INDEX_NAME = 'market-goods-'.$this->_MARKET; 

        $up_gids = array();

        $cnt = 0;
        while($row = fgetcsv($fp, 0, ';')) {
            //$cav_data[] = $row;
            $cnt ++;
            if($cnt == 1) continue;

            $_ID = $this->_MARKET.'-'.$row[0];        // 대표상품 인지 확인 반드시 필요
    
            $price = str_replace(',', '', $row[4]);
            $price = ($price * 1) / 100;
            $up_params = array(
                'mg_price'      => $price,
                'mg_quantity'   => $row[3]
            );
            //echo print_r($up_params); //exit;
            $el_result = $this->elastic->update_scripting($_INDEX_NAME, $_ID, $up_params);
            if($el_result['is_success'] == FALSE) {
                $log_data = array(
                    'index_name'    => $_INDEX_NAME,
                    'id'            => $_ID,
                    'update_data'   => $up_params,
                    'result_data'   => $el_result,
                );
                $this->common->logWrite('elastic', print_r($log_data, true), 'update_lafayette_fail_log');
            }else {

                // 성공한 상품 변경 내역 POS로 제공?
                $up_gids[] = $_ID;;
            }
        }



        if(sizeof($up_gids) > 0) {
            // DEBUG
            echo 'UPDATED : '.print_r($up_gids);

            // market-goods-lafayette UPDATE
            $lib_params = array('market' => $this->_MARKET);
            $this->load->library('PosSyncer', $lib_params);
            $this->possyncer->updatePOSGoods($up_gids);
        }
    

        @file_put_contents($_TC_FILE, $ps_filemtime);
    }

}
?>
