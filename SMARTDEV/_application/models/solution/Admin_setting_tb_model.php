<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Admin_setting_tb_model extends MY_Model
{

    protected $pk = 'as_id';

    protected $emptycheck_keys = array(
		'as_created_at'					=> 'as_created_at value is empty.',
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


	public function get_setting_data() {
		return $this->get(array('as_id' => '1'))->getData();
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
