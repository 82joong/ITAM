<?php
require_once dirname(__FILE__).'/IShopCrawler.php';
require_once dirname(__FILE__).'/simple_html_dom_helper.php';

if( ! defined('COOKIE_DIR')) {
    define('COOKIE_DIR', realpath(dirname(__FILE__).'/cookies'));
    ini_set('include_path', realpath(dirname(__FILE__).'/../PEAR'));
    require_once 'HTTP/Request.php';
}

abstract class BaseShopCrawler implements IShopCrawler {
    protected $dom;
    protected $req;
    protected $body;
    protected $cookies = array();


	public function __construct() {
		$this->req =  new HTTP_Request();
		$this->req->addHeader(
			'User-Agent',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_3) AppleWebKit/536.29.13 (KHTML, like Gecko) Version/6.0.4 Safari/536.29.13'
		);

		$this->req->addHeader(
			'Accept-Language',
			'ko_Kr'
		);

		$this->req->addHeader('Keep-Alive', 115);
		$this->req->addHeader('Connection', 'keep-alive');
	}

    // @ IShopCrawler Interface 자식 클래스에서 구현!
    abstract public function getCategory($extra=array());                   // 카테고리 구조 리턴
    abstract public function getCategoryGoodsList($url, $extra=array());    // 카테고리 상품 리스트
    abstract public function getGoodsDetail($url, $extra=array());          // 카테고리 상품 리스트
    abstract public function getGoodsStatus($url, $extra=array());          // 카테고리 상품 리스트


	/////////////////////////
	// @ public method 
	/////////////////////////
	public function getHeaders($headername = null) { 
			// 통신 헤더를 반환. 헤더로 리다이렉션 시킬때 대상 URL 가져오는데 사용.
			return $this->req->getResponseHeader($headername);
	}
	

	public function getBody($url, $referer = '')
	{
			// URL 결과 받아오기 .GET.
			if (empty($url)) {
					return null;
			}
			$this->req->setURL($url);
			$this->req->addHeader('Referer', $referer);
			$this->req->clearCookies();
			if (!empty($this->cookies)) {
					foreach ($this->cookies as $cookie) {
							$this->req->addCookie($cookie['name'], $cookie['value']);
					}
			}
			$this->req->sendRequest();
			$this->_updateCookies();
			$this->body = $this->req->getResponseBody();
			return $this->body;
	}

	public function getBodyWithPost($url, $post_data, $referer='') 
	{
			// URL에 POST 데이터 전송 후 결과 받아오기 .POST.
			$this->req->setMethod(HTTP_REQUEST_METHOD_POST);

            if(is_array($post_data)) {
                foreach($post_data as $k => $v) {
                    $this->req->addPostData($k, $v);
                }
            } else if(strlen($post_data) > 2) {
                $this->req->addRawPostData($post_data);
            }
			return $this->getBody($url, $referer);
	}

   





	/////////////////////////
	// @ protected method
	/////////////////////////

	protected function _updateCookies($response_cookies = array())
	{
			if (empty($response_cookies)) {
					$response_cookies = $this->req->getResponseCookies();
			}
			if (empty($response_cookies)) {
					return false;
			}
			for ($i=0; $i < count($response_cookies); $i++) {
					$create = true;
					for ($j=0; $j < count($this->cookies); $j++) {
							if ($this->cookies[$j]['name'] === $response_cookies[$i]['name']) {
									$this->cookies[$j]['value'] = $response_cookies[$i]['value'];
									$create = false;
							}
					}
					if ($create) {
							$new_cookies[] = array(
											'id' => '',
											'service_id' => $this->id,
											'name' => $response_cookies[$i]['name'],
											'value' => $response_cookies[$i]['value']
											);
					}
			}
			if (!empty($new_cookies)) {
					foreach ($new_cookies as $new_cookie) {
							$this->cookies[] = $new_cookie;
					}
			}
	}
	protected function saveCookie($filename) {
			file_put_contents($filename, serialize($this->cookies));
			exec('chmod 777 '.$filename);
	}
 
    protected function between($str, $left, $right='') {
        $result = array();
        $temp = explode($left, $str);
        array_shift($temp);
        
        if( ! strlen($right)) {
            return $temp;
        }
        
        foreach($temp as $str_with_tail) {
            $result[] = array_shift(explode($right, $str_with_tail, 2));
        }
        return $result;
    }


}
