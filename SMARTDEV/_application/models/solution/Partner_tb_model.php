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
 * Employee_tb Model
 *
 * @package MakeShop Platinum
 * @author ROOT TF Team
 * @created
 */

class Partner_tb_model extends MY_Model
{

    protected $pk = 'pt_id';

    protected $emptycheck_keys = array(
        'pt_firstname'  => 'pt_firstname 값이 누락되었습니다.',
        'pt_lastname'   => 'pt_lastname 값이 누락되었습니다.',
        'pt_email'      => 'pt_email 값이 누락되었습니다.',
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



	public static $is_external = array(
			'YES'	=> 'YES',
			'NO'	=> 'NO',
	);
    public function getIsExternalMap() {
        return self::$is_external;
    }



    protected function __filter($params)
    {
        $params['pt_name'] = $params['pt_lastname'].''.$params['pt_firstname'];
        $params['pt_created_at'] = date('Y-m-d H:i:s');
        $params['pt_updated_at'] = date('Y-m-d H:i:s');
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
        $params['pt_name'] = $params['pt_lastname'].''.$params['pt_firstname'];
        $params['pt_updated_at'] = date('Y-m-d H:i:s');
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
