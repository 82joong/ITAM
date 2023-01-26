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
 * Status_tb Model
 *
 * @package MakeShop Platinum
 * @author ROOT TF Team
 * @created
 */

class Status_tb_model extends MY_Model
{

    protected $pk = 's_id';

    protected $emptycheck_keys = array(
        's_name' => 's_name 값이 누락되었습니다.',
        's_code' => 's_code 값이 누락되었습니다.',
        /*
        's_color_code' => 's_color_code 값이 누락되었습니다.',
        's_description' => 's_description 값이 누락되었습니다.',
        's_created_at' => 's_created_at 값이 누락되었습니다.',
        's_updated_at' => 's_updated_at 값이 누락되었습니다.',
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


	public static $show_nav = array(
			'YES'	=> 'YES',
			'NO'	=> 'NO',
	);
    public function getShowNavMap() {
        return self::$show_nav;
    }



    protected function __filter($params)
    {
        $params['s_created_at'] = date('Y-m-d H:i:s');
        $params['s_updated_at'] = date('Y-m-d H:i:s');
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
        $params['s_updated_at'] = date('Y-m-d H:i:s');
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
