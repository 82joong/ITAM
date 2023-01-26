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
 * Location_tb Model
 *
 * @package MakeShop Platinum
 * @author ROOT TF Team
 * @created
 */

class Location_tb_model extends MY_Model
{

    protected $pk = 'l_id';

    protected $emptycheck_keys = array(
        'l_name' => 'l_name 값이 누락되었습니다.',
        /*
        'l_manager_name' => 'l_manager_name 값이 누락되었습니다.',
        'l_tel' => 'l_tel 값이 누락되었습니다.',
        'l_country' => 'l_country 값이 누락되었습니다.',
        'l_city' => 'l_city 값이 누락되었습니다.',
        'l_address' => 'l_address 값이 누락되었습니다.',
        'l_zip' => 'l_zip 값이 누락되었습니다.',
        'l_memo' => 'l_memo 값이 누락되었습니다.',
        'l_lat' => 'l_lat 값이 누락되었습니다.',
        'l_long' => 'l_long 값이 누락되었습니다.',
        'l_created_at' => 'l_created_at 값이 누락되었습니다.',
        'l_updated_at' => 'l_updated_at 값이 누락되었습니다.',
        */
    );

    protected $enumcheck_keys = array(
    );

    protected $code_text_map = array();

    public function __construct()
    {
        parent::__construct();
        $this->db_name = _SHOP_INFO_DATABASE_;
        $this->table   = strtolower(substr(__CLASS__, 0, -6));
        $this->fields  = $this->db->list_fields($this->table);
    }

    protected function __filter($params)
    {
        $params['l_created_at'] = date('Y-m-d H:i:s');
        $params['l_updated_at'] = date('Y-m-d H:i:s');
        return $params;
    }

    protected function __validate($params)
    {
        $success = parent::__validate($params);
        if ($success === true) {
        }
        return $success;
    }

    protected function __updateFilter($params)
    {
        $params['l_updated_at'] = date('Y-m-d H:i:s');
        return $params;
    }

    protected function __updateValidate($params)
    {
        $success = parent::__updateValidate($params);
        if ($success === true) {
        }
        return $success;
    }
}
