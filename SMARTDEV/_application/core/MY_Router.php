<?php
if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}
class MY_Router extends CI_Router
{
    public function __construct($routing = null)
    {
        if (isset($_SERVER['HTTP_HOST'])) {
            // Web 브라우저 호출로 실행중 (CLI 아님)
            // 컨트롤러를 로드할 폴더를 결정하자. ( _application/controllers/[ shop | mobile | admin ]  )
            // makeshop.co.kr 이 주소에 있으면 admin.
            // 없다면
            //   IS_MOBILE_SHOP 이 true 면 mobile. 아니면 shop.

            $segments = explode('/', $_SERVER['REQUEST_URI']);
            if (isset($segments[1]) && $segments[1] == SHOP_INFO_ADMIN_DIR) {
                $this->directory = 'admin/';
            }else if (isset($segments[1]) && $segments[1] == 'api') {
                $this->directory = 'api/';
            }else if (isset($segments[1]) && $segments[1] == 'daemon') {
                $this->directory = 'daemon/';
            } else if (defined('IS_MOBILE_SHOP') && IS_MOBILE_SHOP == true) {
                $this->directory = 'mobile/';
            } else {
                $this->directory = 'shop/';
            }

        }
        parent::__construct($routing);
    }

    public function _validate_request($segments)
    {

        array_shift($segments);
        switch ($this->directory) {
            case 'admin/':
                array_unshift($segments, 'admin');
                break;
            case 'api/':
                array_unshift($segments, 'api');
                break;
            case 'mobile/':
                array_unshift($segments, 'mobile');
                break;
            case 'shop/':
                array_unshift($segments, 'shop');
                break;
            case 'daemon/':
                array_unshift($segments, 'daemon');
                break;
        }
        $this->directory = null;
        return parent::_validate_request($segments);
    }
}
