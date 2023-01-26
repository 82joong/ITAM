<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include_once dirname(__FILE__).'/Base_shop.php';

class Account extends Base_shop {
    public function join() {
		$this->_view('join', $data=array(), $is_popup=true);
    }
    public function login() {
        // header 별도 위해서 is_popup true 로 설정 
		$this->_view('login', $data=array(), $is_popup=true);
    }
}

