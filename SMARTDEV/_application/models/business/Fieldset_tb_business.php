<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Fieldset_tb_business extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->db_name = _SHOP_INFO_DATABASE_;
        $this->table   = strtolower(substr(__CLASS__, 0, -9));
        $this->fields  = $this->db->list_fields($this->table);
    }



	public static $is_active= array(
			'YES'	=> 'YES',
			'NO'	=> 'NO',
	);
    public function getIsActiveMap() {
        return self::$is_active;
    }


    public function getNameMap($is_active='ALL') {

        $params = array();
        if($is_active !== 'ALL') {
            $params['=']['fs_is_active'] = $is_active;
        }

        $extras = array();
        $extras['fields'] = array('fs_id', 'fs_name');
        $extras['order_by'] = array('fs_id DESC');

        $rows = $this->getList($params, $extras)->getData();
        
        $data = array();
        foreach($rows as $v) {
            $data[$v['fs_id']] = $v['fs_name'];
        } 
        return $data;
    }


}
