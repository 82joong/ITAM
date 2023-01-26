<?php

class TAOBAO_Crawler extends BaseShopCrawler
{
    // @ IShopCrawler Interface 구현
    public function getCategory($extra=array()) {                                 // 카테고리 구조 리턴
        $result = array(
            'root' => array(),
            'child' => array(),
        );

        $html = $this->getBody('https://world.taobao.com');
        $cate_json = array_pop(explode('<script class="J_ContextData" type="text/template">', $html, 2));
        $cate_json = array_shift(explode('</script>', $cate_json, 2));
        $cate_json = json_decode($cate_json, true);
        $cate_json = $cate_json['category'];
        //$extra['trans'] = false;


        
        foreach($cate_json as $k => $data) {
            $data['title'] = $this->translate($data['title'], $extra);

            $row = array(
                'id' => 'tbcat_'.$k,
                'name' => $data['title'],
                'url' => $data['href'],
            );
            $result['root'][$k] = $row;



            for($i = 0 ; $i < 100 ; $i++) {
                if( ! isset($data['child'.$i])) {
                    break;
                }



                $row2 = array(
                    'id' => $row['id'].'_'.$i,
                    'name' => $this->translate($data['child'.$i], $extra),
                    //'name' => $data['child'.$i],
                    'url' => $data['childHref'.$i],
                );
                $result['child'][$row['id']][] = $row2;

                if(isset($data['childList'.$i])) {
                    for($j = 0 ; $j < sizeof($data['childList'.$i]) ; $j++) {

                        $row3 = array(
                            'id' => $row2['id'].'_'.$j,
                            'name' => $this->translate($data['childList'.$i][$j]['title'], $extra),
                            //'name' => $data['childList'.$i][$j]['title'],
                            'url' => $data['childList'.$i][$j]['href'],
                        );
                        $result['child'][$row2['id']][] = $row3;
                    }
                }
            }
        }
        return $result;
    }

    public function getCategoryGoodsList($url, $pPage=1) {    // 카테고리 상품 리스트
        /*
        https://www.vvic.com/gz/list/index.html?pid=1&vcid=20000106&merge=1&isTheft=0'
        에서 3페이지 이동시

        https://www.vvic.com/apic/search/asy?merge=1&isTheft=0&algo=113&pid=1&vcid=20000106&searchCity=gz&currentPage=3
        로 ajax가 날아갔고, 리스트 할 상품 정보가 여기서 다 온다.

        $a['data']['search_page']['recordList'] 에 상품 리스트가 담겨있다.
        $a['data']['search_page']['recordList'][0]['item_id'] 가 상품 상세 주소 중 https://www.vvic.com/item/[ 요기 들어갈 숫자 ] 
        $a['data']['search_page']['pageSize'] 가 한 페이지당 아이템 수
        $a['data']['search_page']['pageCount'] 가 맨 끝 페이지 수
        $a['data']['search_page']['discount_price'] 가 최종판매가
        $a['data']['search_page']['price'] 가 일반소비자가
         */


        // URL 검증
        $url_info = parse_url($url);
        if( 
                ! isset($url_info['scheme'])
                || ! isset($url_info['host'])
                || ! isset($url_info['path'])
                || ! isset($url_info['query'])
                || strpos(strtolower($url_info['host']), 'www.vvic.com') === false
                || strpos(strtolower($url_info['path']), '/list/') === false
          ) {
            return array();
        }

        // URL 정보 파싱
        parse_str($url_info['query'], $get_params);

        /*
           print_r($get_params);
           Array
           (
           [pid] => 1  대분류
           [vcid] => 20000106 중분류

           [merge] => 1
           [isTheft] => 0
           ....
           )
         */

        $send_url = 'https://www.vvic.com/apic/search/asy?';
        $send_params = array();


        // 대분류 조건 (필수)
        if( ! isset($get_params['pid'])) {
            return array();
        }
        $send_params['pid'] = $get_params['pid'];

        // 중분류 조건. 있으면 넣기
        if(isset($get_params['vcid'])) {
            $send_params['vcid'] = $get_params['vcid'];
        }

        // 검색어. 있으면 넣기
        if(isset($get_params['q'])) {
            $send_params['q'] = $get_params['q'];
        }

        // 주소에서 searchCity 찾기 (필수)
        $temp = explode('/', $url_info['path']);
        if( ! isset($temp[1]) || strlen($temp[1]) < 2) {
            return array();
        }
        $searchCity = $temp[1];
        $send_params['searchCity'] = strtolower($searchCity);


        $page = $pPage;
	if($page == 'all') {
		$page = 1;
	}

        $max_page = 100;

        $result = array();

        for($i = $page ; $i <= $max_page ; $i++) {
            echo 'Page : '.$i." (".date('Y-m-d H:i:s').")\n"; // todo. delete
            $q = http_build_query($send_params);
            $send_params['currentPage'] = $i;
            $go_url = $send_url.http_build_query($send_params);

            $list = json_decode($this->getBody($go_url), true);
            if($i == 1) {
                $max_page = $list['data']['search_page']['pageCount'];
            }
            foreach($list['data']['search_page']['recordList'] as $row) {
                if(strlen($row['index_img_url']) <= 5) {
                    continue;
                }

                $row['pid'] = $get_params['pid'];
                $row['vcid'] = $get_params['vcid'];
                $result[] = $this->_convert_row($row);
            }
            if(strtolower($pPage) != 'all') {
                break;
            }
        }
        return $result;
    }


    // TODO. 
    public function getGoodsStatus($url, $extra) {

        return true;

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

    // 검색결과 목록. 인터페이스엔 아직 없다. 궂이 필요친 않을 듯. 
    public function getSearchGoodsList($url, $pPage=1) {
        /*
           star 검색시 ajax :
https://www.vvic.com/apic/search/asy?merge=1&isTheft=0&algo=113&engine=opensearch&panggeFlag=1&q=star&searchCity=gz&currentPage=2
        */
        // URL 검증
        $url_info = parse_url($url);
        if( 
                ! isset($url_info['scheme'])
                || ! isset($url_info['host'])
                || ! isset($url_info['path'])
                || ! isset($url_info['query'])
                || strpos(strtolower($url_info['host']), 'www.vvic.com') === false
                || strpos(strtolower($url_info['path']), '/search/') === false
          ) {
            return array();
        }

        // URL 정보 파싱
        parse_str($url_info['query'], $get_params);

        /*
           print_r($get_params);
           Array
           (
           [pid] => 1  대분류
           [vcid] => 20000106 중분류

           [merge] => 1
           [isTheft] => 0
           ....
           )
        */

        $send_url = 'https://www.vvic.com/apic/search/asy?';
        $send_params = array();


        // 검색어 조건 (필수)
        if( ! isset($get_params['q'])) {
            return array();
        }
        $send_params['q'] = $get_params['q'];

        // 대분류 조건. 있으면 넣기
        if(isset($get_params['pid'])) {
            $send_params['pid'] = $get_params['pid'];
        }

        // 중분류 조건. 있으면 넣기
        if(isset($get_params['vcid'])) {
            $send_params['vcid'] = $get_params['vcid'];
        }


        // 주소에서 searchCity 찾기 (필수)
        $temp = explode('/', $url_info['path']);
        if( ! isset($temp[1]) || strlen($temp[1]) < 2) {
            return array();
        }
        $searchCity = $temp[1];
        $send_params['searchCity'] = strtolower($searchCity);


        $page = $pPage;
	if($page == 'all') {
		$page = 1;
	}

        $result = array();

        for($i = $page ; $i <= $max_page ; $i++) {
            $q = http_build_query($send_params);
            $send_params['currentPage'] = $i;
            $go_url = $send_url.http_build_query($send_params);

            $list = json_decode($this->getBody($go_url), true);
            if($i == 1) {
                $max_page = $list['data']['search_page']['pageCount'];
            }
            foreach($list['data']['search_page']['recordList'] as $row) {
                $row['pid'] = $get_params['pid'];
                $row['vcid'] = $get_params['vcid'];
                $result[] = $this->_convert_row($row);
            }
            if(strtolower($pPage) != 'all') {
                break;
            }
        }
        return $result;
    }

    public function getGoodsDetail($url, $extra=array()) {          // 카테고리 상품 리스트
        $response = array(
            'product_id' => '',             // 상점 상품번호
            'product_name' => '',           // 상점 상품명
            'product_images' => array(),    // 상점 상품이미지
            'product_price' => '0',         // 상점 상품가격
            'has_options' => 'NO',          // 단일상품 / 옵션이 존재하는 상품
            'all_options' => array(),       // 모든 옵션정보
            'valid_options' => array(),     // 구매가능한 옵션정보
            'description' => '',            // 상점 상품상세 정보
            'image_exists' => FALSE,        // 상점 상품이미지 존재 여부
            'shipget_info' => array(),      // 쉽겟 연동을 위한 데이터이며 단일상품일 때만 사용됨
        );


        // URL 검증
        $url_info = parse_url($url);
        if( 
                ! isset($url_info['scheme'])
                || ! isset($url_info['host'])
                || ! isset($url_info['path'])
                || strpos(strtolower($url_info['host']), 'www.vvic.com') === false
                || strpos(strtolower($url_info['path']), '/item/') === false
          ) {
            return array();
        }

        $request = array(
            'html' => $this->getBody($url),
            'url' => $url,
        );
        $g_data = array(
                '_ITEMID' => '',
                '_DISCOUNTPRICE' => '',
                '_SIZE' => '',
                '_COLOR' => '',
                '_ITEMTYPE' => '',
                '_SKUMAP' => '',
                '_SIZEID' => '',
                '_COLORID' => '',
                '_INDEXIMGURL' => '',
             );

        $html = str_replace("\r", '', $request['html']);
        foreach(array_keys($g_data) as $vname) {
            $g_data[$vname] = array_shift(explode("';\n", array_pop(explode('    '.$vname." = '", $html, 2)), 2));
        }
        $g_data['_SKUMAP'] = json_decode($g_data['_SKUMAP'], true);

        // @ 상품명
        $html_obj = str_get_html($request['html']);
        $title = $html_obj->find('.d-name strong', 0)->innertext;



        // @ 판매자번호 (예상값)  /shop/12345 
		$seller_id = 0;
		if($html_obj->find('.stall-head-name') != 'NULL') {
			$seller_html = $html_obj->find('.stall-head-name a', 0)->href;
			$seller_id = end(explode('/', $seller_html)); 
		}


		$item_info = array(
			'title'		=> $title,
			'item_id'	=> end(explode('/', $request['url'])),
			'seller_id'	=> $seller_id,
		);

        // @ 쉽겟정보 셋팅.
        $default_shipget_info = array(
            'seller_id' => $item_info['seller_id'],
            'product_name' => $item_info['title'],
            'image_url' => $g_data['_INDEXIMGURL'],
        );
        $response['shipget_info'] = $default_shipget_info;


        // @ 상품번호
        $response['product_id'] = $item_info['item_id'];


        // @ 상품명 
        $response['product_name'] = $item_info['title'];


        // @ 판매가 
		$price = $g_data['_DISCOUNTPRICE'];
        $response['product_price'] = $price;


        // @ 상품상세
        //$description = $html_obj->find('.d-content', 0);
        $description = $html_obj->find('#descTemplate', 0);
        /*
		foreach($description->find('img') as $img) {
			// src 데이터가 이미지바이너리형태로 수집되서 같은 이미지 url인 data-original 데이터를 사용
	        $ori_src = $img->{'data-original'};
            if(strlen($ori_src) > 0) {
				if(strpos($ori_src, 'http') === false) {	 // //img.vvic 로 시작하는 이미지들이 존재함 
					$ori_src = 'https:'.$ori_src;
				}

                $img->src = $ori_src;
            }		
		}
        */
        $description = $html_obj->find('#descTemplate', 0);

		$description_html = trim($description->innertext);
        $response['description'] = '<div style="position:relative !important; overflow:hidden; width:750px; padding:10px 0px 0px; word-wrap:break-word;">'.$description_html.'</div>';



        // @ 상품이미지
        $image_exists = FALSE;
        $product_images = array();
		$thumb = $html_obj->find('#thumblist', 0);

        foreach($thumb->find('div.tb-thumb-item') as $li) {
                $img = $li->find('img', 0);
                $val = $img->src;
                // ex)//img1.vvic.com/upload/1587224916464_337327.jpg_60x60.jpg 

                if(strlen($val) > 0) {
                    $image = $protocol.':'.$val;
                    if(strpos($val, '_60x60') !== false) {
                        $image = array_shift(explode('_60x60', $val));

                        if(strpos($image, 'http:') === 0) {
                            $image = str_replace('http:', '', $image);
                        } else if(strpos($image, 'https:') === 0) {
                            $image = str_replace('https:', '', $image);
                        }
                        $image = $protocol.':'.$image;
                    }
                    $product_images[] = $image;
                    $image_exists = TRUE;
                }
        }
        $response['product_images'] = $product_images;


        // @ 단일상품 / 옵션상품 구분 및 옵션정보 셋팅.

        if(isset($g_data['_SKUMAP']) && sizeof($g_data['_SKUMAP']) > 0) {
            $response['has_options'] = 'YES';
            // @ 모든 옵션정보 가져오기.
            $all_options = array();

			
			foreach($g_data['_SKUMAP'] as $sdata) {
				$key = $sdata['skuid'];
				$option_name = array();
				$option_image = '';
				$sg_option_name = array();
				$sg_option_code = array();
				$sg_option_img = '';

				if(strlen(trim($sdata['color_name'])) > 0) {
					$option_name[] = trim($sdata['color_name']);
					$sg_option_name[] = '?色=='.trim($sdata['color_name']); // 색상
					$sg_option_code[] = $sdata['color_id'];
				}
				if(strlen(trim($sdata['size_name'])) > 0) {
					$option_name[] = trim($sdata['size_name']);
					$sg_option_name[] = '尺?=='.trim($sdata['size_name']);	// 사이즈
					$sg_option_code[] = $sdata['size_id'];
				}

                $valid_options[$key]['name'] = implode(' / ', $option_name);
				if(strlen($sdata['color_pic']) > 0) {
					if(strpos($sdata['color_pic'], 'http') === false) {	 // //img.vvic 로 시작하는 이미지들이 존재함 
						 $sdata['color_pic']= 'https:'.$sdata['color_pic'];
					}
					
					$valid_options[$key]['image'] = $sdata['color_pic'];
					$sg_option_img = $sdata['color_pic'];
                    $image_exists = TRUE;
				}
				


				$option_shipget_info = $default_shipget_info;
                if(strlen($sg_option_img) > 0) {
                    $option_shipget_info['image_url'] = $sg_option_img;
                }
                $option_shipget_info['option_name'] = implode('=-=', $sg_option_name);
                $option_shipget_info['option_code'] = implode('|', $sg_option_code);
                $valid_options[$key]['shipget_info'] = $option_shipget_info;

                $price = $sdata['price'];
                if(isset($sdata['discount_price']) && intval($sdata['discount_price']) > 0) {
                    $price = $sdata['discount_price'];
                }
                $valid_options[$key]['price'] = $price;
 
			}
            $response['valid_options'] = $valid_options;
		}
        $response['image_exists'] = $image_exists;


        $html_obj->clear(); // 꼭 해줘야 함!!!
        unset($html_obj); 	// 꼭 해줘야 함!!!

        return $response;
    }


    public function getList($url='') {

    }


    public function translate($text, $extra=array()) {

        if(isset($extra['trans']) == false || $extra['trans'] == false) {
            return $text;
        }

        $ACCESS_TOKEN = 'AIzaSyABDO9D9k0tAKt2UMuCmLuBNcH7tZqajNU';
        if(strlen($text) < 1) {
            return $text;
        }



        $url = 'https://translation.googleapis.com/language/translate/v2?key='.$ACCESS_TOKEN;
        $params = array(
            'q'      => $text,
            'source' => '',  // 출발어 (빈값을 전달하면 자동 언어감지되어 번역된다)
            'target' => 'ko',    // 도착어 
            'format' => 'text', // text or html
        );


        // command line 실행으로 변경.
        $cmd = 'curl -XPOST "' . $url . '" -d "'.http_build_query($params).'"';
        $response = shell_exec($cmd);
        $response = json_decode($response, true);

        /* $response
           Array (
               [data] => Array (
                   [translations] => Array (
                       [0] => Array (
                           [translatedText] => 여보세요
                           [detectedSourceLanguage] => en // optional. target 에 빈값을 전달하면 넘어옴.
                       )
                   )
               )
           )
           ====
           Array (
               [error] => Array (
                   [code] => 400
                   [message] => Invalid Value
                   [errors] => Array (
                       [0] => Array (
                           [message] => Invalid Value
                           [domain] => global
                           [reason] => invalid
                       )
                   )
               )
           )
        */           
        
        $result = $text;
        if(array_key_exists('data', $response)) {
            if(array_key_exists('translations', $response['data'])) {
                $result = $response['data']['translations'][0]['translatedText'].' ('.$text.')' ;
                //$result = $response['data']['translations'][0]['translatedText'];
            }
        }
        return $result;

    }
}

/*
/// 테스트.
$target = 'gz';     //광저우
$target = 'pn';     // 푸닝
$target = 'hznz';   // 항저우 여자

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





