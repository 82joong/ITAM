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
 * Ip_tb Model
 *
 * @package MakeShop Platinum
 * @author ROOT TF Team
 * @created
 */

class Ip_tb_model extends MY_Model
{

    protected $pk = 'ip_id';

    protected $emptycheck_keys = array(
        'ip_address' => 'ip_address 값이 누락되었습니다.',
        /*
        'ip_people_id' => 'ip_people_id 값이 누락되었습니다.',
        'ip_memo' => 'ip_memo 값이 누락되었습니다.',
        'ip_created_at' => 'ip_created_at 값이 누락되었습니다.',
        'ip_updated_at' => 'ip_updated_at 값이 누락되었습니다.',
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


    /*
	public static $allocation_type = array(
			'PEOPLE'	=> '[People] 직원 및 직원 장비 할당',
			'ASSETS'	=> '[Assets] 물리적 자산 할당',
			'ETC'	    => '[ETC] 그외 외부 장비 등',
	);
    public function getAllocationTypeMap() {
        return self::$allocation_type;
    }
    */



    protected function __filter($params)
    {
        $params['ip_created_at'] = date('Y-m-d H:i:s');
        $params['ip_updated_at'] = date('Y-m-d H:i:s');
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
        $params['ip_updated_at'] = date('Y-m-d H:i:s');
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
