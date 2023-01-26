<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Assets_type_tb_model extends MY_Model
{

    protected $pk = 'at_id';

    protected $emptycheck_keys = array(
		'at_name'					=> 'at_name value is empty.',
		'at_created_at'				=> 'at_created_at value is empty.',
    );

    protected $enumcheck_keys = array(
    );


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
        $params['at_created_at'] = date('Y-m-d H:i:s');
        $params['at_updated_at'] = date('Y-m-d H:i:s');
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
        $params['at_updated_at'] = date('Y-m-d H:i:s');
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
