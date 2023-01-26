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
 * Supplier_tb Model
 *
 * @package MakeShop Platinum
 * @author ROOT TF Team
 * @created
 */

class Supplier_tb_model extends MY_Model
{

    protected $pk = 'sp_id';

    protected $emptycheck_keys = array(
        'sp_name' => 'sp_name 값이 누락되었습니다.',

        /*
        'sp_city' => 'sp_city 값이 누락되었습니다.',
        'sp_country' => 'sp_country 값이 누락되었습니다.',
        'sp_state' => 'sp_state 값이 누락되었습니다.',
        'sp_street' => 'sp_street 값이 누락되었습니다.',
        'sp_zip' => 'sp_zip 값이 누락되었습니다.',
        'sp_contract_name' => 'sp_contract_name 값이 누락되었습니다.',
        'sp_tel' => 'sp_tel 값이 누락되었습니다.',
        'sp_fax' => 'sp_fax 값이 누락되었습니다.',
        'sp_email' => 'sp_email 값이 누락되었습니다.',
        'sp_url' => 'sp_url 값이 누락되었습니다.',
        'sp_memo' => 'sp_memo 값이 누락되었습니다.',
        'sp_created_at' => 'sp_created_at 값이 누락되었습니다.',
        'sp_updated_at' => 'sp_updated_at 값이 누락되었습니다.',
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
        $params['sp_created_at'] = date('Y-m-d H:i:s');
        $params['sp_updated_at'] = date('Y-m-d H:i:s');
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
        $params['sp_updated_at'] = date('Y-m-d H:i:s');
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
