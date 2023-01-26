<?php

class QEEBOO_Crawler extends BaseShopCrawler
{
	private $_CI;
	private $base_url;
    private $QEEBOO_FILE;

    private $target_categories = array(
            1001 => 'OUTLET',
            'Lighting',
            'Seating',
            'Tables',
            'Complements',
            'Velvet Finish',
            'Metal Finish',
            'Outdoor'
            );

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
        $this->base_url = 'https://www.qeeboo.com'; 
        $this->QEEBOO_FILE = APPDATA_PATH.'/market_file/qeeboo/qeeboo_raw.json';
    }

    // @ IShopCrawler Interface 구현
	public function getCategory($extra=array()) {                                 // 카테고리 구조 리턴

		$result = array(
			'cate0' => array(),
		);

        foreach($this->target_categories as $cate_one_key => $cate_one_name) {
			$result['cate0'][$cate_one_key] = array(
				'depth0'    => $cate_one_key,
				'name'	    => $cate_one_name,
				'name_en'	=> $cate_one_name,
				'name_ko'	=> '',
            );
        }

        // 번역
        $params = array(
            'from'  => 'en',        // 영어
            'to'    => array('ko'), // 한국어
            'text'  => array_values($this->target_categories) 
        );
        $trans_res = $this->_CI->common->restful_curl(TRANSLATE_API_URL, http_build_query($params), 'POST');
        $trans_data = json_decode($trans_res, true);

        if($trans_data['is_success'] == TRUE) {
            foreach($result['cate0'] as $key => $val) {
                $idx = array_search($val['name'], $trans_data['text']);
                $result['cate0'][$key]['name_ko'] = $trans_data['result'][$idx]['ko'];
            }
        }
        return $result;
    }

    private function _translate($data) {
    
        $res = array(
            'ko'    => '',
            'cn'    => '',
        );

        // 번역
        $params = array(
            'from'  => 'en',    // 영어
            'to'    => array('ko', 'zh'),
            'text'  => $data,
        );
        $trans_res = $this->_CI->common->restful_curl(TRANSLATE_API_URL, http_build_query($params), 'POST');
        $trans_data = json_decode($trans_res, true);
        //echo print_r($trans_data); exit;

        if($trans_data['is_success'] == TRUE) {
            $temp = $trans_data['result'][0];
            if(isset($temp['zh'])) {
                $temp['cn'] = $temp['zh'];
                unset($temp['zh']);
            }
            return $temp;
        }

        return $res;
    }

    public function getCategoryGoodsList($url, $extra=array()) {
        // $extra['mode'] = 'sync' 파라메커는 크론텝 배치돌며 파일 하는놈만 호출한다.
        $result = array();

        if(isset($extra['mode']) && $extra['mode'] = 'sync') {
            $data = $this->sync();

            if(sizeof($data) > 5) {
                file_put_contents($this->QEEBOO_FILE, serialize($data));
                $result = $data;
            }
            return $result;
        }

        if(is_file($this->QEEBOO_FILE)) {
            $load_data = unserialize(file_get_contents($this->QEEBOO_FILE));
            if(sizeof($load_data) > 10) {
                $result = $load_data;
            }
        }

        return $result;
    }
    public function getGoodsDetail($url, $extra=array()) {
        $result = array();

        return $result;
    }
    public function getGoodsStatus($url, $extra=array()) {
        $result = array();

        return $result;
    }

    private function sync() {
	 	// 카테고리 url 수집
		$main_url = $this->base_url;
		$content = $this->getBody($main_url);
		preg_match_all('/(https:\/\/www.qeeboo.com\/collections\/[a-zA-Z0-9]+)[^>]+>([^<]+)</', $content, $match);
		$category_urls = array_combine($match[2], $match[1]);       

        //print_r($category_urls);exit;
        $cate_name_code_map = array_flip($this->target_categories);

		// 카테고리 상품 리스트에서 링크 수집
		$target_categories = $this->target_categories;

		$cate_links = array();
		foreach($category_urls as $cate_name => $list_url) {
			if( ! in_array($cate_name, $target_categories)) {
				continue;
			}
			echo $cate_name."\n";
			$content = array_pop(explode('<div class="collection--products">', $this->getBody($list_url), 2));
			$titles = $this->between($content, '<div class="product--details--title-row">', '</div>');// <a href="/products/giraffe-in-love-xs-by-marcantonio" target="_self">Giraffe in Love XS</a></h3>

			$detail_urls = array();
			// 상세 URL 취합
			foreach($titles as $title) {
				$detail_urls[array_shift($this->between($title, 'href="', '"'))] = trim(strip_tags($title));
			}
			if( ! sizeof($detail_urls)) continue;

			$cate_links[$cate_name] = $detail_urls;
		}



		// 데이터 수집 시작
		$goods_list = array();
		foreach($cate_links as $cate_name => $links) {
            $cate_code = $cate_name_code_map[$cate_name];
			foreach($links as $detail_url => $goods_name) {
				echo 'GET '.$main_url.$detail_url."\n";
				$content = $this->getBody($main_url.$detail_url, $list_url);

				// 상품정보 추출
				if(strpos($content, 'theme.product_json') === false) {
					echo '"theme.product_json = " is not exists.';
					usleep(100000);
					continue;
				}
				$thumb_cont = trim(array_pop($this->between($content, 'theme.product_json = ', "\n")));

				if(substr($thumb_cont, -1, 1) == ';') {
					$thumb_cont = substr($thumb_cont, 0, -1);
				}
				$goods = json_decode($thumb_cont, true);

				if(empty($goods['handle'])) {
					echo "Parse Error!!\nHTML : \n$content\n\nthumb_cont: \n$thumb_cont\n";
					print_r($goods);
					exit;
				}
				if(isset($goods_list[$goods['handle']])) {
					echo 'Already exists key : '.$goods['handle']."\n";
					continue;
				}
				echo $cate_name.' => '.$goods['handle']."\n";
				usleep(100000);
				$goods['url'] = $main_url.$detail_url;
				$goods['cate_name'] = $cate_name;
				$goods['cate_code'] = $cate_code;
				$goods_list[$goods['handle']] = $goods;

			}
		}
        return $goods_list;


	}


}
