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
 * Rack_tb Model
 *
 * @package MakeShop Platinum
 * @author ROOT TF Team
 * @created
 */

class Lab_tb_model extends MY_Model
{

    protected $pk = 'lab_id';

    protected $emptycheck_keys = array(
        'lab_ip' => 'lab_ip 값이 누락되었습니다.',
        //'lab_asset_code' => 'lab_asset_code 값이 누락되었습니다.',
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
        $params['lab_created_at'] = date('Y-m-d H:i:s');
        $params['lab_updated_at'] = date('Y-m-d H:i:s');
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
        $params['lab_updated_at'] = date('Y-m-d H:i:s');
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
