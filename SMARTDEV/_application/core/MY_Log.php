<?php
/**
 * MakeShop Platinum
 *
 * @package MakeShop Platinum
 * @author ROOT TF Team
 * @copyright Copyright (c) 2016, KOREACENTER.COM, Inc. (http://makeshop.co.kr/)
 */
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * MY_Log Class - CI_Log를 DB에 저장할 수 있는 Log class로 확장
 *
 * @package MakeShop Platinum
 * @author zzong
 * @created 2016-09-07
 */
class MY_Log extends CI_Log
{
    protected $_levels = array(
        'CRITICAL' => 1,
        'ERROR'    => 2,
        'WARNING'  => 3,
        'INFO'     => 4,
        'DEBUG'    => 5,
    );
    protected $_log_prefix = array(
        'PRODUCT' => 'lpr', // 상품 관련 로그
        'MEMBER'  => 'lmb', // 회원 관련 로그
        'ORDER'   => 'lor', // 주문 관련 로그
        'ADMIN'   => 'lad', // 기타 관리자 로그
    );

    /**
     * Save Log at Database
     *
     * @param array $params
     * @return boolean
     */
    public function save($table, $params)
    {
        $CI =& get_instance();
        $CI->load->model($_tb = "log_" . strtolower($table));
        if (isset($params['level']) && array_key_exists($params['level'], $this->_levels)) {
            $params['level'] = $this->_levels[$params['level']];
        } else {
            $params['level'] = 4;
        }
        $params = array_combine(array_map(create_function('$k', 'return "'.$this->_log_prefix[strtoupper($table)].'_".$k;'), array_keys($params)), $params);
        $CI->{"{$_tb}_model"}->doInsert($params);  //@notice 로그 저장하다가 발생한 에러는 뭐 굳이 Exception은 필요 없잖아?
    }
}
