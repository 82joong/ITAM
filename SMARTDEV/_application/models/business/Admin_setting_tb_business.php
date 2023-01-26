<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Admin_setting_tb_business extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->db_name = _SHOP_INFO_DATABASE_;
        $this->table   = strtolower(substr(__CLASS__, 0, -9));
        $this->fields  = $this->db->list_fields($this->table);
    }


	public function get_setting_data() {
		return $this->get(array('as_id' => '1'))->getData();
    }

    public function get_assets_type() {
        $set_data = $this->get_setting_data();
        return @unserialize($set_data['as_assets_type']);
    }
}
