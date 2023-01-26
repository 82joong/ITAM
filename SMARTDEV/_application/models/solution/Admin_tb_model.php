<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Admin_tb_model extends MY_Model
{

    protected $pk = 'a_id';

    protected $emptycheck_keys = array(
        'a_firstname'   => 'a_firstname 값이 누락되었습니다.',
        'a_lastname'    => 'a_lastname 값이 누락되었습니다.',
        'a_email'       => 'a_email 값이 누락되었습니다.',
        'a_loginid'     => 'a_loginid 값이 누락되었습니다.',
        'a_passwd'      => 'a_passwd 값이 누락되었습니다.',
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


	public static $admin_level  = array(
			'1'	=> '1LEVEL',
			'2'	=> '2LEVEL',
			'3'	=> '3LEVEL',
			'4'	=> '4LEVEL',
			'5'	=> '5LEVEL',
			'6'	=> '6LEVEL',
			'7'	=> '7LEVEL',
			'8'	=> '8LEVEL',
			'9'	=> '9LEVEL',
	);
    public function getAdminLevelMap() {
        return self::$admin_level;
    }



	public static $changed_pw  = array(
			'YES'	=> 'YES',
			'NO'	=> 'NO',
	);
    public function getChangedPWMap() {
        return self::$changed_pw;
    }

	public static $admin_permission  = array(
			'pg_setting'	    => 'PG Setting',
			'cost_setting'	    => 'Cost Setting',
			'staff_rest_time_view'	    => 'Staff Rest Time View',
            'order_list_complete'		=> 'OrderList Complete',        // 주문리스트에서 일괄 Complete처리할 수 있는 권한
            'cost_edit'					=> 'Order Cost Edit Permission',       // 주문서 cost 및 기타 금액필드 수정권한
            'direct_complete'			=> 'Direct Complete',       // 직배 처리 권한
            'modify_tracking_number'	=> 'Modify Tracking Number',       // 송장번호 수정권한 
            'complete_to_closed'        => 'Complete Order to Closed',     // Complete 상태인 주문서 Closed 처리할 수 있는 권한
            'change_income_status'      => 'Change Income Status',         // 정산완료 주문건 정산상태 변경할 수 있는 권한 
            'emp_status_exchange'      => 'Emp Status Exchange',           // 정산완료 주문건 정산상태 변경할 수 있는 권한 
			'config_access_ip'			=> 'Config Access IP',				// 어드민페이지에 접근허용 설정 권한
	);
    public function getAdminPermissionMap() {
        return self::$admin_permission;
    }

    // 권한이 있을 때만 노출
    // * 여기에 추가 시, $admin_permission에도 반드시 추가.
    public static $admin_secret_permission  = array(
        'cost_setting', 
        'staff_rest_time_view',
        'emp_status_exchange',
		'config_access_ip',
	);
    
    public function getAdminSecretPermissionKeys() {
        return self::$admin_secret_permission;
    }




    protected function __filter($params)
    {
        $params['a_created_at'] = date('Y-m-d H:i:s');
        $params['a_updated_at'] = date('Y-m-d H:i:s');
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
        $params['a_updated_at'] = date('Y-m-d H:i:s');
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
