<?php

class VVIC_Crawler extends BaseShopCrawler
{

	private $_CI;

	private $base_url;
	private $app_id;
	private $app_secret;



    // MetricBeats 에서 수집해 갈수 있게 CSV 데이터 정렬 기준
    // logstash/config/vvic-api-filter.conf 참조
	private $csv_key_data = array(
		'item_vid',
		'item_id',
		'item_title',
		'item_view_image',
		'create_time',
		'update_time',
		'up_time',
		'mg_trans_names.cn',
		'mg_trans_names.en',
		'mg_trans_names.ko',
		'mg_import_enname',
		'desc',
		'price',
		'status',
		'color',
		'size',
		'shop_vid',
		'shop_name',
		'market_code',
		'category_id_one',
		'category_name_one',
		'category_id_sub',
		'category_name_sub',
		'category_name_two',
		'category_id_two',
		'supply_level',
		'weight_type',
		'list_grid_image',
		'expired_time',
		'art_no',
		'attr_str',
		'sku_list',
		'color_id',
		'color_imgs',
		'size_id',
		'mg_raw_data',
		'mg_id',
		'mg_market',
		'mg_url_key',
		'mg_weight_kg',
		'mg_weight_lbs',
		'mg_catepath_id',
		'mg_is_active',
		'mg_quantity',
		'mg_has_options',
		'mg_options',
		'mg_color',
		'mg_size',
		'mg_view_imgs',
	);

	function __construct() {
		
	    parent::__construct();

	    $this->_CI = &get_instance();


	    if(IS_REAL_SERVER == TRUE) {
		
            //////////////////// real server /////////////////
            $this->base_url = 'http://api.vvic.com/api'; // real servier
            $this->app_id = '2125163511342184';
            $this->app_secret= '6e0d25f4de8d35f8f136b4a8ff709719';

	    }else {

            //////////////////// sandbox /////////////////
            $this->base_url = 'http://preapi.vvic.com/api'; // sandbox
            $this->app_id = '2066922907990413';
            $this->app_secret= 'b76237f72ad9eff2812cc87a27e28618';

	    }
    }


	/*
	
	$extra = array(
		site_code => : gz | pn | hznz | jfn | hz (광저우 | 푸닝 | 항저우 여성 | 항저우 남성 | 남부신발 ) 중 1 필수!
	)
	*/




    // @ IShopCrawler Interface 구현
	public function getCategory($extra=array()) {                                 // 카테고리 구조 리턴

		ini_set('memory_limit', '2G');

		$result = array(
			'cate1' => array(),
			'cate2' => array(),
			'cate3' => array(),
		);

		$html = $this->getBody('https://www.vvic.com/gz/list/index.html');
		$html_obj = str_get_html($html);

		foreach($html_obj->find('div.nav-pid a.h-item') as $pid_obj) {

			$pid_data = (array)($pid_obj->attr);
			$cate_one_key = $pid_data['data-val'];
			$cate_one_name = $pid_data['title'];
			//echo print_r($pid_data);

			if(strlen($pid_data['data-val']) < 1) continue;

			$result['cate1'][$cate_one_key] = array(
				'depth1'    => $cate_one_key,
				'name'	    => $cate_one_name,
			);
			$cate_html = $this->getBody('https://www.vvic.com/gz/list/index.html?pid='.$cate_one_key);
			$cate_html_obj = str_get_html($cate_html);


			foreach($cate_html_obj->find('div.catid div.nav-category') as $cate_obj) {

				//file_put_contents('./test.info', $cate_obj, FILE_APPEND); exit;
				$cate_two_data = array();
				foreach($cate_obj->find('div.nc-key') as $cate_two_obj) {
					$cate_two_name = $cate_two_obj->innertext;
					//echo $cate_two_name.PHP_EOL; //exit;
				}

				foreach($cate_obj->find('a.h-item') as $cate_three_obj) {
					//echo print_r($cate_three_obj->attr);
					$cate_three_data = (array)($cate_three_obj->attr);
					$cate_two_key = $cate_three_data['data-tagid'];
					$cate_three_key = $cate_three_data['data-val'];
					$cate_three_name = $cate_three_data['title'];


					$result['cate3'][$cate_two_key][$cate_three_key] = array(
						'depth1'    => $cate_one_key,
						'depth2'    => $cate_two_key,
						'depth3'    => $cate_three_key,
						'name'      => $cate_three_name,
					);
				}

				$result['cate2'][$cate_one_key][$cate_two_key] = array(
					'depth1'    => $cate_one_key,
					'depth2'    => $cate_two_key,
					'name'	    => $cate_two_name,
				);
				//echo print_r($result); exit;

			}
		}
		echo print_r($result); //exit;

    
        /* DEBUG FILE 
        $this->_CI->load->helper('file');
        $filename = DISPLAY_PATH.'/category/vvic_category.info';    
        $result = read_file($filename);
        $result = unserialize($result);
        */

        // 번역
        $cate_name_data = array();
        foreach($result as $category) {
            foreach($category as $cate) {
                if( isset($cate['name']) ) {
                    $cate_name_data[] = $cate['name'];
                }else {
                    foreach($cate as $c) {
                        $cate_name_data[] = $c['name'];
                    }
                }
            }
        }
        //echo print_r($cate_name_data);

        $trans_names = array();
        $key = 0;
        foreach(array_chunk($cate_name_data, 30) as $names) {

            // 번역
            $params = array(
                'from'  => 'zh',    // 중국어
                'to'    => array('en', 'ko'),
                'text'  => $names 
            );
            $trans_res = $this->_CI->common->restful_curl(TRANSLATE_API_URL, http_build_query($params), 'POST');
            $trans_data = json_decode($trans_res, true);
            //echo print_r($trans_data); exit;

            if($trans_data['is_success'] == TRUE) {

                foreach($trans_data['result'] as $val) {

                    $name_key = $cate_name_data[$key];
                    $trans_names[$name_key] = $val;
                        
                    $key ++;
                }
            }
        }
        //echo print_r($trans_names); //exit;

        foreach($result as &$category) {
            foreach($category as &$cate) {
                if( isset($cate['name']) ) {
                    if(isset($trans_names[$cate['name']])) {
                       $cate['name_ko'] =  $trans_names[$cate['name']]['ko'];
                       $cate['name_en'] =  $trans_names[$cate['name']]['en'];
                    }
                }else {
                    foreach($cate as &$c) {
                        if(isset($trans_names[$c['name']])) {
                           $c['name_ko'] =  $trans_names[$c['name']]['ko'];
                           $c['name_en'] =  $trans_names[$c['name']]['en'];
                        }
                    }
                }
            }
        }

        return $result;

		// serialiize_file
		//echo 'END'.PHP_EOL.'Generated Serialize File'.PHP_EOL;
		//file_put_contents('./vvic_category_'.date('Ymd').'.info', serialize($result));
	}


    private function _translate($data) {
    
        $res = array(
            'ko'    => '',
            'en'    => '',
        );

        // 번역
        $params = array(
            'from'  => 'zh',    // 중국어
            'to'    => array('en', 'ko'),
            'text'  => $data,
        );
        $trans_res = $this->_CI->common->restful_curl(TRANSLATE_API_URL, http_build_query($params), 'POST');
        $trans_data = json_decode($trans_res, true);
        //echo print_r($trans_data); exit;

        if($trans_data['is_success'] == TRUE) {
           return $trans_data['result'][0];
        }

        return $res;
    }



	public function getCategoryGoodsList($city_market_code, $extra=array()) {    // 카테고리 상품 리스트

		ini_set('memory_limit', -1);
		set_time_limit(99999);
		$s = date('Y-m-d H:i:s');
		$allow_city_market_code = array('all', 'gz','pn','hznz', 'jfn', 'xt', 'hz');
		$city_market_code = strtolower(trim($city_market_code));

		if( ! in_array($city_market_code, $allow_city_market_code)) {
			throw new Exception($city_market_code.' : 관리되지 않는 값 ('.implode(' | ', $allow_city_market_code).')');
		}


		// Notify Updated vid 아이템 CSV 생성
		if( isset($extra['vid']) && strlen($extra['vid']) > 0 ) {

			return $this->_getUpdatedGoodsList($extra); 


		// 전일 00:00 ~23:59 동안의 [up_time] 기반 CSV 생성
		} else {
		
			// 일단 하루 주기
			for($int_day = 1; $int_day > 0; $int_day--) {
				$this->_getGoodsList($city_market_code, $int_day); 
			}
		}
	}


	private function _getUpdatedGoodsList($extra = array()) {

		if( (isset($extra['vid']) && strlen($extra['vid']) > 0) == FALSE ) return;

		$vid = $extra['vid'];
		$row = array(
			'mg_trans_names.cn' => '',
			'mg_trans_names.en' => '',
			'mg_trans_names.ko' => '',
			'mg_import_enname'  => '',
		);

		$extras = array(
			'mode' 		=> 'list', 
			'item_vid' 	=> $vid,
			'lang'	 	=> 'cn', 
		);
		$details = $this->getGoodsDetail('', $extras);
		$d = array_shift($details);
		//echo print_r($d); exit;

		$convert_data = $this->convertDetail($d);
		$row = array_merge($row, $convert_data);

		if( isset($extra['return']) && $extra['return'] == true) {
			return $row;
		}
		//echo print_r($row); exit;


		$row = $this->_setCSVHeaderData($row);
		//echo print_r($row);  exit;

		$filepath = CRAWLER_PATH.'/csvs/vvic-callback-'.date('Ymd').'.csv';	

		// DEBUG Mode
		//$filepath = CRAWLER_PATH.'/csvs/ztest-callback-'.date('Ymd').'.csv';	
		if( ! file_exists($filepath) || filesize($filepath) == 0) {

			$title = array_keys($row);	
			$csv_header_size = sizeof($title);
			$fp = fopen($filepath, 'w');
			fputcsv($fp, $title);
			fputcsv($fp, $row);
			fclose($fp);

		}else {	

			$fp = fopen($filepath, 'a');
			fputcsv($fp, $row);
			fclose($fp);
		}

		return;
	}

	private function _getGoodsList($city_market_code, $int_day) {

		$csv_header_size = 0;
		$start = mktime(0,0,0,date('m'),date('d')-$int_day, date('Y'));  // 어제 0시부터

		//echo date('Y-m-d', $start).PHP_EOL; return;

		// 집계 데이터
		$result = array(
			'TotalCount' 	=> 0,
			'InValidData'	=> array(),
		);

		
		for($i = 0 ; $i < 48 ; $i++) { // 3분 그룹

			$is_first = true;
			$total_page = 1;

			$end = $start+1800;
			$extra['up_time_start'] = date('Y-m-d H:i:s', $start);
			$extra['up_time_end'] = date('Y-m-d H:i:s', $end);
			$start = $end;

			for($page=1 ; $page <= $total_page ; $page++) {

				$item_data = array();
				$item_vids = array();

                $extra['lang'] = 'cn';
                $extra['page'] = $page;
                if(strtolower($city_market_code) !== 'all') {
                    $extra['city_market_code'] = $city_market_code;
                }
                $params = $this->genParams($extra);

                $get = http_build_query($params);
                $data = json_decode($this->getBody($this->base_url.'/item/list/v1?'.$get), true);
                //echo print_r($data); exit;

                if($data['status'] != 200) {
                    $result['Exception'][] = $data;
                    throw new Exception($data['message']);
                }

                if($is_first == true) {
                    $is_first = false;
                    $result['TotalCount'] += $data['data']['total'];

                    // DEBUG
                    //echo 'Total : '.$data['data']['total'].PHP_EOL;
                    //echo 'Item List Count : '.sizeof($data['data']['item_list']).PHP_EOL;;
                }

                $total_page = $data['data']['total_page'];
                $result['TotalPage'] = $total_page;

                //echo 'Total Page : '.$total_page.PHP_EOL;
                //echo 'Current Page : '.$page.PHP_EOL;

                if( sizeof($data['data']['item_list']) < 1 ) continue;
                foreach($data['data']['item_list'] as $row) {

                    // 초기화 :: 번역없는 케이스 발견!!!
                    $row['mg_trans_names.cn'] = '';
                    $row['mg_trans_names.en'] = '';
                    $row['mg_trans_names.ko'] = '';
                    $row['mg_import_enname'] = '';
                
                    $item_data[$row['item_vid']] = $row;
                    $item_vids[] = $row['item_vid'];

                } // END_FOREACH @item_list
				//echo 'VIDS : '.sizeof(array_filter($item_vids)).PHP_EOL; //exit;


				if(sizeof($item_vids) < 1) continue;

				foreach(array_chunk($item_vids, 20) as $vids) {
					if(sizeof($vids) <= 0) continue;
					$vid_str = implode(',',$vids);

					$details = $this->getGoodsDetail('', array('mode' => 'list', 'item_vid' => $vid_str));
					//echo 'DETAILS : '.sizeof($details).PHP_EOL;

                    if( sizeof($details) < 1 ) continue;

					//echo print_r($details); exit;
            

					foreach($details as $d) {

						$convert_data = $this->convertDetail($d);
						$csv_row = array_merge($item_data[$d['item_vid']], $convert_data);
						//echo print_r($csv_row); exit;

		                $filepath = CRAWLER_PATH.'/csvs/vvic-'.$city_market_code.'-'.$page.'-'.date('Ymd', $start).'.csv';	

						if( ! file_exists($filepath) || filesize($filepath) == 0) {

							$title = array_keys($csv_row);	
							$csv_header_size = sizeof($title);
							$fp = fopen($filepath, 'w');
							fputcsv($fp, $title);
							fputcsv($fp, $csv_row);
							fclose($fp);
							//echo print_r($title); exit;
							//echo '::: TITLE ::: '.$filepath.PHP_EOL;

						}else {	

							if($csv_header_size != sizeof($csv_row)) {
								//echo print_r($csv_row);
								$result['InValidData'][] = $csv_row;
							}

							$fp = fopen($filepath, 'a');
							fputcsv($fp, $csv_row);
							fclose($fp);
							//echo print_r($csv_row); exit;
						}
					} // END_FOREACH @details
				} // END_FOREACH @item_vids
			} // END_FOR @page
			//exit;
		} // END_FOR @i


        /*
		$e = date('Y-m-d H:i:s');
		echo "\n\nTotal Size : ".sizeof($result)."\n";
		echo "$s ~ $e\n";
        */

		file_put_contents(CRAWLER_PATH.'/logs/vvic-getCategoryGoodsList_'.date('Ymd_His').'_result.log', print_r($result, true));
		return $result;

	}



	private function convertDetail($data) {

		$data['desc'] = str_replace(array("\r\n","\r","\n"), "", $data['desc']);
		$data['desc'] = preg_replace("/\r|\n/", "", $data['desc']);

		$res = $data;
		$res['mg_trans_names.cn'] = $data['item_title'];

		// 상품이름 번역
		$params = array(
			'from'	=> 'zh',    // 중국어
			'to'	=> array('en', 'ko'),
			'text'	=> array($data['item_title'])
		);
		$trans_data = $this->_CI->common->restful_curl(TRANSLATE_API_URL, http_build_query($params), 'POST');
		$trans_data = json_decode($trans_data, true);
		if($trans_data['is_success'] == TRUE) {
			foreach($trans_data['result'][0] as $lan=>$val) {
				
				switch($lan) {
					case 'en':	
						$res['mg_trans_names.en'] = $val;
						$res['mg_import_enname'] = $val; 
						break;
					case 'ko':
						$res['mg_trans_names.ko'] = $val;
						break;
				}

			}
		}


		$res['mg_raw_data'] = serialize($data);
		$res['mg_id'] = 'vvic-'.$data['item_vid'];
		$res['mg_market'] = 'vvic-'.$data['market_code'];
		$res['mg_url_key'] = 'https://www.vvic.com/item/'.$data['item_id'];

		$res['mg_weight_kg'] = $this->transWeight($data['weight_type'], 'kg');
		$res['mg_weight_lbs'] = $this->transWeight($data['weight_type'], 'lbs');

		$res['mg_catepath_id'] = $data['category_id_one'].'/';
		$res['mg_catepath_id'] .= $data['category_id_sub'].'/';
		$res['mg_catepath_id'] .= $data['category_id_two'];

		if($data['status'] = '1') {
			$res['mg_is_active'] = 'YES';
			$res['mg_quantity'] = 99999;
		}else {
			$res['mg_is_active'] = 'NO';
			$res['mg_quantity'] = 0;
		}

		if(is_array($data['sku_list']) && sizeof($data['sku_list']) > 0) {
			$res['mg_has_options'] = 'YES';
		}else {
			$res['mg_has_options'] = 'NO';
		}

		$res['mg_options'] = json_encode($data['sku_list']);
		$res['sku_list'] = json_encode($data['sku_list']);


		$color = array();
		if(strlen(trim($data['color'])) > 0) {
			$color = explode(',', $data['color']);
		}
		$res['mg_color'] = json_encode($color);


		$size = array();
		if(strlen(trim($data['size'])) > 0) {
			$size = explode(',', $data['size']);
		}
		$res['mg_size'] = json_encode($size);


		$view_imgs = array();
		if(strlen(trim($data['list_grid_image'])) > 0) {
			$view_imgs = explode(',', $data['list_grid_image']);
		}
		$res['mg_view_imgs'] = json_encode($view_imgs);
		return $res;

	}

	private function putCSV($result) {
		$is_first = true;
		$header_cnt = 0;
		$fp = fopen('./csvs/vvic.csv', 'w');
		foreach($result as $vid=>$row) {
		    if($is_first == true) {
			$is_first = false;

			$title = array_keys($row);
			$header_cnt = sizeof($title);
			// DEBOG title
			//echo print_r($title); //exit;
			fputcsv($fp, $title);
		    }

		    if($header_cnt != sizeof($row)) {
			    echo print_r($row); 
		    }else {
			    //echo print_r($row); exit;
			    fputcsv($fp, $row);
		    }
		}
	}

 
	private function _setCSVHeaderData($data) {
		$res = array();
		foreach($this->csv_key_data as $key) {
			if(isset($data[$key])) {
				$res[$key] = $data[$key];
			}else {
				//echo $data[$key].PHP_EOL;
				$res[$key] = '';
			}
		}	
		return $res;
	}

	private function transWeight($weight_type, $return_type) {
		$vvic_weight_table = array(
			'1'	=> array(
				'cate' 	=> '1',
				'kg' 	=> '0.2',
				'lbs' 	=> '0.4409245244',
			),
			'2'	=> array(
				'cate' 	=> '2',
				'kg' 	=> '0.3',
				'lbs' 	=> '0.6613867866',
			),
			'3'	=> array(
				'cate' 	=> '3',
				'kg' 	=> '0.4',
				'lbs' 	=> '0.8818490487',
			),	
			'4'	=> array(
				'cate' 	=> '4',
				'kg' 	=> '0.5',
				'lbs' 	=> '1.1023113109',
			),
			'5'	=> array(
				'cate' 	=> '5',
				'kg' 	=> '0.6',
				'lbs' 	=> '1.3227735731',
			),
			'6'	=> array(

				'cate' 	=> '6',
				'kg' 	=> '0.8',
				'lbs' 	=> '1.7636980975',
			),
			'7'	=> array(
				'cate' 	=> '7',
				'kg' 	=> '0.9',
				'lbs' 	=> '1.9841603597',
			),
			'8'	=> array(
				'cate' 	=> '8',
				'kg' 	=> '0.95',
				'lbs' 	=> '2.0943914908',
			),
		);

		return $vvic_weight_table[$weight_type][$return_type];

	}


    private function _convert_row($row) {
        /* $row 필수 key : 
            item_id
            index_img_url
            video_url
            price
            discount_price
            title
            pid
            vcid
        */
        $map = array(
                'market_detail_url' => 'https://www.vvic.com/item/'.$row['item_id'],
                'market_goods_id' => $row['item_id'],
                'market_thumb_url' => $row['index_img_url'],
                'video_url' => $row['video_url'],
                'display_price' => $row['price'],
                'price' => $row['discount_price'],
                'name' => $row['title'],
                'quantity' => 7654321,
                'category_id' => $row['pid'],
                'subcategory_id' => $row['vcid'],
                );

        return $map;
    }


    public function getGoodsDetail($url, $extra=array()) {    // 카테고리 상품 리스트

		$default_extra = array(
			'item_vid' => '',
			'lang' => 'cn'
		);
		$extra = array_merge($default_extra, $extra);
		extract($extra); // $item_vid, $lang 변수 셋팅

		$item_vid = trim($item_vid);
		if(! is_string($item_vid) || strlen($item_vid) <= 0) {
			throw new Exception('item_vid extra param is empty.');
		}

		$extra['lang'] = strtolower(trim($extra['lang']));
		if( ! in_array($extra['lang'], array('cn','en','ko'))) {
			$extra['lang'] = 'cn';
		}


		$params = $this->genParams($extra);
		$get = http_build_query($params);
		$data = json_decode($this->getBody($this->base_url.'/item/detail/v1?'.$get), true);
        if( ! isset($data['data']['item_list']) ) {
			throw new Exception('['.$data['status'].'] '.$data['message']);
        }

		if(isset($mode) && $mode == 'list') {
			return $data['data']['item_list'];
		}
		return $data['data']['item_list'][0];



        /*
		echo 'Original Data Struct : '."\n";

		///print_r($extra);
		print_r($data);

		$result = array();

		foreach($data['data']['item_list'] as $row) {
			$imgs = explode(',',$row['list_grid_image']);
			$struct = array(
				'product_id' => $row['item_vid'],             // 상점 상품번호
				'product_name' => $row['item_title'],           // 상점 상품명
				'product_images' => $imgs,    // 상점 상품이미지
				'product_price' => $row['price'],         // 상점 상품가격
				'has_options' => (sizeof($row['sku_list']) > 0) ? 'YES' : 'NO', // 단일상품 / 옵션이 존재하는 상품
				'all_options' => array(),       // 모든 옵션정보
				'valid_options' => array(),     // 구매가능한 옵션정보
				'description' => $row['desc'],            // 상점 상품상세 정보
				'image_exists' => (sizeof(array_filter($imgs)) > 0),        // 상점 상품이미지 존재 여부
				'shipget_info' => array(),      // 쉽겟 연동을 위한 데이터이며 단일상품일 때만 사용됨
			);

			$result[] = $struct;
		}

		if(sizeof($result) == 1) {
			return $result[0]; // 단일 조회시 까서 리턴
		}
		return $result;
        */

		/*

		print_r(json_decode($result, true));
		Array
		(
		    [status] => 200
		    [message] => OK
		    [data] => Array
			(
			    [total] => 1127
			    [lang] => ko
			    [page] => 1
			    [total_page] => 57
			    [item_list] => Array
				(
				    [0] => Array
					(
					    [item_vid] => 5ebad40fe6e2b30001b7b374
					    [item_id] => 20598571
					    [item_title] => 국립 대학교 b 지구 314 슈퍼 요정 센 부서 허리 슬림 레트로 중간 길이 스커트
					    [item_view_image] => //img1.vvic.com/upload/1589301139275_503028.jpg
					    [create_time] => 2020-05-13 00:51:27
					    [update_time] => 2020-05-26 10:52:05
					    [up_time] => 2020-05-27 00:00:00
					)
				     (반복...)

	 	*/
	}



    public function getGoodsStatus($url, $extra=array()) {    // 카테고리 상품 리스트

		$default_extra = array(
			'item_vid' => '',
			'lang' => 'cn'
		);
		$extra = array_merge($default_extra, $extra);
		extract($extra); // $item_vid, $lang 변수 셋팅

		$item_vid = trim($item_vid);
		if(! is_string($item_vid) || strlen($item_vid) <= 0) {
			throw new Exception('item_vid extra param is empty.');
		}

		$extra['lang'] = strtolower(trim($extra['lang']));
		if( ! in_array($extra['lang'], array('cn','en','ko'))) {
			$extra['lang'] = 'cn';
		}

		$params = $this->genParams($extra);
		$get = http_build_query($params);
		$data = json_decode($this->getBody($this->base_url.'/item/status/v1?'.$get), true);

        if( ! isset($data['data']['item_list']) ) {
			throw new Exception('['.$data['status'].'] '.$data['message']);
        }


		if(isset($mode) && $mode == 'list') {
			return $data['data']['item_list'];
		}
		return $data['data']['item_list'][0];
    }




	// private
	private function genParams($pParams) {
		$timestamp = $this->getMillisecond();
		$app_id = $this->app_id;
		$app_secret = $this->app_secret;

		$params = array();
		$params["app_id"] = $app_id;
		$params["timestamp"] = $timestamp;
		$params = array_merge($pParams, $params);
		$message = $this->getSortedParams($params) . '' . $app_secret . '' . $timestamp;
		$sign = hash_hmac('sha256', $message, $app_secret, true);
		$params['sign'] = bin2hex($sign);//f7d3e9a7ace3a6f7e194a756652145e52416a0aff399a9e8ba62f41d500607b5

		return $params;
	}
	private function getMillisecond() {
		list($s1, $s2) = explode(' ', microtime());
		return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
	}

	private function getSortedParams($params_array) {
		ksort($params_array);
		$result = "";
		foreach ($params_array as $key => $value) {
			$result .= '' . $key . '' . $value;
		}
		//echo $result;
		return $result;//app_id你的app_idbar2foo1foo_bar3foobar4timestamp1551193296887
	}


}

/*
/// 테스트.
$target = 'gz';     //광저우
$target = 'pn';     // 푸닝
$target = 'hznz';   // 항저우 여자



site_code => : gz | pn | hznz | jfn | hz (광저우 | 푸닝 | 항저우 여성 | 항저우 남성 | 남부신발 ) 중 1 필수!

if(sizeof($argv) == 2 && in_array($argv[1], array('gz','pn','hznz'))) {
    $target = $argv[1];
}

$crawl = new VVIC_Crawler();
$cates = $crawl->getCategory(array('site_code' => $target));
foreach($cates['child'] as $lcate_url => $child_urls) {
    foreach($child_urls as $vcid => $url_info) {
        $url = $url_info['url'];
        $res = $crawl->getCategoryGoodsList('https://www.vvic.com'.$url, 'all');
        $fp = fopen('./vvic_'.$target.'_'.$url_info['parent_id'].'_'.$url_info['id'].'.csv', 'w');
        $is_first = true;
        foreach($res as $row) {
            if($is_first == true) {
                $is_first = false;
                fputcsv($fp, array_keys($row));
            }
            fputcsv($fp, $row);
        }
        fclose($fp);
    }
}
*/


/*
// @ 카테고리 상품 리스트 파일 굽기
$res = $crawl->getCategoryGoodsList('https://www.vvic.com/gz/list/index.html?pid=1&vcid=20000106&merge=1&isTheft=0', 'all');
$fp = fopen('./vvic_gz_1_20000106.csv', 'w');
$is_first = true;
foreach($res as $row) {
    if($is_first == true) {
        $is_first = false;
        fputcsv($fp, array_keys($row));
    }
    fputcsv($fp, $row);
}
fclose($fp);
*/

// @ 카테고리 리스트 수집
/*
print_r($crawl->getCategory(array('site_code' => 'gz')));
*/

// @ 상품 상세정보
/*
$res = $crawl->getGoodsDetail('https://www.vvic.com/item/20104927');
print_r($res);
*/
