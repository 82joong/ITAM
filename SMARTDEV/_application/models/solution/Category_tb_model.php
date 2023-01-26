<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Category_tb_model extends MY_Model
{

    protected $pk = 'ct_id';

    protected $emptycheck_keys = array(
        'ct_type_id' => 'ct_type_id 값이 누락되었습니다.',
        'ct_name' => 'ct_name 값이 누락되었습니다.',

        /*
        'ct_description' => 'ct_description 값이 누락되었습니다.',
        'ct_icon' => 'ct_icon 값이 누락되었습니다.',
        'ct_order' => 'ct_order 값이 누락되었습니다.',
        'ct_image' => 'ct_image 값이 누락되었습니다.',
        'ct_created_at' => 'ct_created_at 값이 누락되었습니다.',
        'ct_updated_at' => 'ct_updated_at 값이 누락되었습니다.',
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
        $params['ct_created_at'] = date('Y-m-d H:i:s');
        $params['ct_updated_at'] = date('Y-m-d H:i:s');
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
        $params['ct_updated_at'] = date('Y-m-d H:i:s');
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
