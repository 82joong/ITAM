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
 * Vendor_tb Model
 *
 * @package MakeShop Platinum
 * @author ROOT TF Team
 * @created
 */

class Vendor_tb_model extends MY_Model
{

    protected $pk = 'vd_id';

    protected $emptycheck_keys = array(
        'vd_name' => 'vd_name 값이 누락되었습니다.',
        /*
        'vd_url' => 'vd_url 값이 누락되었습니다.',
        'vd_support_url' => 'vd_support_url 값이 누락되었습니다.',
        'vd_support_tel' => 'vd_support_tel 값이 누락되었습니다.',
        'vd_created_at' => 'vd_created_at 값이 누락되었습니다.',
        'vd_updated_at' => 'vd_updated_at 값이 누락되었습니다.',
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
        $params['vd_created_at'] = date('Y-m-d H:i:s');
        $params['vd_updated_at'] = date('Y-m-d H:i:s');
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
        $params['vd_updated_at'] = date('Y-m-d H:i:s');
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
