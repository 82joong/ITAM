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
 * Ip_class_tb Model
 *
 * @package MakeShop Platinum
 * @author ROOT TF Team
 * @created
 */

class Ip_class_tb_model extends MY_Model
{

    protected $pk = 'ipc_id';

    protected $emptycheck_keys = array(
        'ipc_location_id' => 'ipc_location_id 값이 누락되었습니다.',
        'ipc_cidr' => 'ipc_cidr 값이 누락되었습니다.',
        //'ipc_name' => 'ipc_name 값이 누락되었습니다.',
        //'ipc_code' => 'ipc_code 값이 누락되었습니다.',
        /*
        'ipc_memo' => 'ipc_memo 값이 누락되었습니다.',
        'ipc_created_at' => 'ipc_created_at 값이 누락되었습니다.',
        'ipc_updated_at' => 'ipc_updated_at 값이 누락되었습니다.',
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



	public static $type = array(
			'LOCAL'	=> 'LOCAL (사내IP)',
			'IDC'	=> 'IDC IP',
	);
    public function getTypeMap() {
        return self::$type;
    }


    /* ================================

        :: IP 할당 구분 

        @IDRAC : Dell에서 서버별로 관리/모니터링 위한 IP 할당 자산과 1:1 할당
        @VMWARE : 서버에서 가상화 분할 서비스별 관리/모니터링 위한 가상화 메인 IP 할당
        @PUBLIC | @PRIVATE : 공인 IP 할당 -> 단독 서비스형 IP 할당 or VMWARE 로 가상화된 서비스 (vmsercice) 별 IP 할당
        @DIRECT : 사내IP (LOCAL) 내에 직원별 IP 할당 

      ================================ */
	public static $category = array(
            'IDRAC'     => 'iDrac',
            'VMWARE'    => 'vmware',
            'PUBLIC'    => 'public',
            'PRIVATE'   => 'private',
            'DIRECT'    => 'direct',
	);
    public function getCategoryMap() {
        return self::$category;
    }


    protected function __filter($params)
    {
        $params['ipc_created_at'] = date('Y-m-d H:i:s');
        $params['ipc_updated_at'] = date('Y-m-d H:i:s');
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
        $params['ipc_updated_at'] = date('Y-m-d H:i:s');
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
