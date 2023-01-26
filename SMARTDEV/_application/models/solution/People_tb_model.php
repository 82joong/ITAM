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
 * People_tb Model
 *
 * @package MakeShop Platinum
 * @author ROOT TF Team
 * @created
 */

class People_tb_model extends MY_Model
{

    protected $pk = 'pp_id';

    protected $emptycheck_keys = array(
        'pp_name' => 'pp_name 값이 누락되었습니다.',
        'pp_email' => 'pp_email 값이 누락되었습니다.',

        /*
        'pp_company_id' => 'pp_company_id 값이 누락되었습니다.',
        'pp_tel' => 'pp_tel 값이 누락되었습니다.',
        'pp_mobile' => 'pp_mobile 값이 누락되었습니다.',
        'pp_dept' => 'pp_dept 값이 누락되었습니다.',
        'pp_title' => 'pp_title 값이 누락되었습니다.',
        'pp_company_id' => 'pp_company_id 값이 누락되었습니다.',
        'pp_admin_id' => 'pp_admin_id 값이 누락되었습니다.',
        'pp_memo' => 'pp_memo 값이 누락되었습니다.',
        'pp_created_at' => 'pp_created_at 값이 누락되었습니다.',
        'pp_updated_at' => 'pp_updated_at 값이 누락되었습니다.',
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



	public static $status = array(
        'ACTIVE'	=> '재직',
        'OUTMEMBER'	=> '퇴사',
	);
    public function getStatusMap() {
        return self::$status;
    }



    protected function __filter($params)
    {
        $params['pp_created_at'] = date('Y-m-d H:i:s');
        $params['pp_updated_at'] = date('Y-m-d H:i:s');
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
        $params['pp_updated_at'] = date('Y-m-d H:i:s');
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
