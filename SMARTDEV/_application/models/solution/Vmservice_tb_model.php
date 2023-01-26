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
 * Vmservice_tb Model
 *
 * @package MakeShop Platinum
 * @author ROOT TF Team
 * @created
 */

class Vmservice_tb_model extends MY_Model
{

    protected $pk = 'vms_id';

    protected $emptycheck_keys = array(
        'vms_name' => 'vms_name 값이 누락되었습니다.',
        /*
        'vms_memo' => 'vms_memo 값이 누락되었습니다.',
        'vms_created_at' => 'vms_created_at 값이 누락되었습니다.',
        'vms_updated_at' => 'vms_updated_at 값이 누락되었습니다.',
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



	public static $service_status_map = array(
        'ACTIVE'	=> array(
            'text'      => 'ACTIVE',
            'text_kr'   => '운영중',
            'color'     => 'success',
        ),
        'INACTIVE'	=> array(
            'text'      => 'INAVTICE',
            'text_kr'   => '미운영중',
            'color'     => 'dark',
        ),
        'SHUTDOWN'	=> array(
            'text'      => 'SHUTDOWN',
            'text_kr'   => '미전원',
            'color'     => 'secondary',
        ),
        'TODEL'	=> array(
            'text'      => 'TODEL',
            'text_kr'   => '빼야함',
            'color'     => 'danger',
        ),
	);
    public function getStatusMap() {
        return self::$service_status_map;
    }

    public function getStatusText() {
        $res = array();
        foreach(self::$service_status_map as $k=>$v) {
            $res[$k] = $v['text_kr'];
        }
        return $res;
    }
    public function getStatusTextKR($key) {
        return self::$service_status_map[$key]['text_kr'];
    }
    public function getStatusColor($key) {
        return self::$service_status_map[$key]['color'];
    }
    public function getStatusBadge($key) {

        if( strlen($key) > 0 ) {
            $status_text = $this->getStatusTextKR($key); 
            $status_color = $this->getStatusColor($key); 
            return '<span class="badge border border-'.$status_color.' text-'.$status_color.'">'.$status_text.'</span>';
        }else {
            return '';
        }
    }



    protected function __filter($params)
    {
        $params['vms_created_at'] = date('Y-m-d H:i:s');
        $params['vms_updated_at'] = date('Y-m-d H:i:s');
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
        $params['vms_updated_at'] = date('Y-m-d H:i:s');
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
