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
 * Assets_model_tb Model
 *
 * @package MakeShop Platinum
 * @author ROOT TF Team
 * @created
 */

class Assets_model_tb_model extends MY_Model
{

    protected $pk = 'am_id';

    protected $emptycheck_keys = array(
        'am_name' => 'am_name 값이 누락되었습니다.',
        'am_status_id' => 'am_status_id 값이 누락되었습니다.',
        'am_assets_type_id' => 'am_assets_type_id 값이 누락되었습니다.',
        'am_models_id' => 'am_models_id 값이 누락되었습니다.',
        
        /*
        'am_order_item_id' => 'am_order_item_id 값이 누락되었습니다.',
        'am_order_id' => 'am_order_id 값이 누락되었습니다.',
        'am_ordernum' => 'am_ordernum 값이 누락되었습니다.',
        'am_company_id' => 'am_company_id 값이 누락되었습니다.',
        'am_supplier_id' => 'am_supplier_id 값이 누락되었습니다.',
        'am_location_id' => 'am_location_id 값이 누락되었습니다.',
        'am_serial_no' => 'am_serial_no 값이 누락되었습니다.',
        'am_tags' => 'am_tags 값이 누락되었습니다.',
        'am_warranty' => 'am_warranty 값이 누락되었습니다.',
        'am_memo' => 'am_memo 값이 누락되었습니다.',
        'am_created_at' => 'am_created_at 값이 누락되었습니다.',
        'am_updated_at' => 'am_updated_at 값이 누락되었습니다.',
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
        $params['am_created_at'] = date('Y-m-d H:i:s');
        $params['am_updated_at'] = date('Y-m-d H:i:s');
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
        $params['am_updated_at'] = date('Y-m-d H:i:s');
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
