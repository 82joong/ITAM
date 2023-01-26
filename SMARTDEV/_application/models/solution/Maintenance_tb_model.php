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
 * Maintenance_tb Model
 *
 * @package MakeShop Platinum
 * @author ROOT TF Team
 * @created
 */

class Maintenance_tb_model extends MY_Model
{

    protected $pk = 'mtn_id';

    protected $emptycheck_keys = array(
        'mtn_assets_model_id' => 'mtn_assets_model_id 값이 누락되었습니다.',
        //'mtn_supplier_id' => 'mtn_supplier_id 값이 누락되었습니다.',
        'mtn_type' => 'mtn_type 값이 누락되었습니다.',
        'mtn_title' => 'mtn_title 값이 누락되었습니다.',
        /*
        'mtn_start_date' => 'mtn_start_date 값이 누락되었습니다.',
        'mtn_end_date' => 'mtn_end_date 값이 누락되었습니다.',
        'mtn_price' => 'mtn_price 값이 누락되었습니다.',
        'mtn_memo' => 'mtn_memo 값이 누락되었습니다.',
        'mtn_created_at' => 'mtn_created_at 값이 누락되었습니다.',
        'mtn_updated_at' => 'mtn_updated_at 값이 누락되었습니다.',
        */
    );

    protected $enumcheck_keys = array(
    );

    protected $code_text_map = array();



	public static $type = array(
        'maintenance'       => 'Maintenance (정기점검)',
        'repair'	        => 'Repair (수리)',
        'upgrade'	        => 'Upgrade (업그레이드)',
        'pat test'	        => 'PAT test (안전점검테스트)',              // Portable Appliance Testing test
        'calibration'	    => 'Calibration (교정검정측정)',           // 교정 검정 측정
        'software support'	=> 'Software Support (SW 유지보수)',
        'hardware support'	=> 'Hardware Support (HW 유지보수)',
	);
    public function getTypeMap() {
        return self::$type;
    }



    public function __construct()
    {
        parent::__construct();
        $this->db_name = _SHOP_INFO_DATABASE_;
        $this->table   = strtolower(substr(__CLASS__, 0, -6));
        $this->fields  = $this->db->list_fields($this->table);
    }

    protected function __filter($params)
    {
        $params['mtn_created_at'] = date('Y-m-d H:i:s');
        $params['mtn_updated_at'] = date('Y-m-d H:i:s');
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
        $params['mtn_updated_at'] = date('Y-m-d H:i:s');
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
