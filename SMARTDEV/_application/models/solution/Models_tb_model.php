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
 * Models_tb Model
 *
 * @package MakeShop Platinum
 * @author ROOT TF Team
 * @created
 */

class Models_tb_model extends MY_Model
{

    protected $pk = 'm_id';

    protected $emptycheck_keys = array(
        'm_vendor_id' => 'm_vendor_id 값이 누락되었습니다.',
        'm_model_name' => 'm_model_name 값이 누락되었습니다.',
        /*
        'm_model_no' => 'm_model_no 값이 누락되었습니다.',
        'm_serial_no' => 'm_serial_no 값이 누락되었습니다.',
        'm_supplier_id' => 'm_supplier_id 값이 누락되었습니다.',
        'm_type' => 'm_type 값이 누락되었습니다.',
        'm_eos_rate' => 'm_eos_rate 값이 누락되었습니다.',
        'm_eos_expited_at' => 'm_eos_expited_at 값이 누락되었습니다.',
        'm_description' => 'm_description 값이 누락되었습니다.',
        'm_url' => 'm_url 값이 누락되었습니다.',
        'm_thumb_img' => 'm_thumb_img 값이 누락되었습니다.',
        'm_tags' => 'm_tags 값이 누락되었습니다.',
        'm_certification' => 'm_certification 값이 누락되었습니다.',
        'm_updated_at' => 'm_updated_at 값이 누락되었습니다.',
        'm_created_at' => 'm_created_at 값이 누락되었습니다.',
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



	public static $is_active = array(
			'YES'	=> 'YES',
			'NO'	=> 'NO',
	);
    public function getIsActiveMap() {
        return self::$is_active;
    }



    protected function __filter($params)
    {
        $params['m_updated_at'] = date('Y-m-d H:i:s');
        $params['m_created_at'] = date('Y-m-d H:i:s');
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
        $params['m_updated_at'] = date('Y-m-d H:i:s');
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
