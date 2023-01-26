<?php
defined('BASEPATH') or exit('No direct script access allowed');

abstract class Base_shop extends CI_Controller
{
    private $adminuser = _SHOP_INFO_DATABASE_;
    
    
    private $header_data = array();
    private $footer_data = array();


    
    public function __construct()
    {
        parent::__construct();

        // todo. header_data (타이틀, 구글애널리틱스 코드 .. 등? ) $_SHOP_INFO에 담아와 set 하기
        $this->header_data = array(
            'page_title' => 'PLATINUM makeshop'
        );
        $this->footer_data = array(
        );
        // todo. footer_data (담을게 있다면.. ) $_SHOP_INFO에 담아와 set 하기
    }

    public function _view($view, $data = array(), $is_popup = false)
    {
        if (! $is_popup) {
            //$this->__view('header', $this->header_data);
        }

        $this->__view($view, $data);


        $except_footer = array(
            'join',
            'login',
        );

        if (! $is_popup && ! in_array($view, $except_footer)) {
            $this->__view('footer', $this->footer_data);
        }
    }

    private function __view($view, $data = array())
    {
        $default_prefix = 'shop/default_template/';
        $userview_prefix = 'shop/'.$this->adminuser.'/';

        $view_path = $userview_prefix.$view;

        if (is_file(APPPATH.'/views/'.$userview_prefix.$view.'.php') == false) {
            // 개별디자인에는 존재하지 않음.

            if (is_file(APPPATH.'/views/'.$default_prefix.$view.'.php') !== false) {
                // default template 에 존재. 이 파일을 이용.
                $view_path = $default_prefix.$view;
            } else {
                // 뷰 없음. Error!
                die($view.' 파일이 존재하지 않습니다.');
            }
        }
        $this->load->view($view_path, $data);
    }
}
