<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Admin_tb_business extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->db_name = _SHOP_INFO_DATABASE_;
        $this->table   = strtolower(substr(__CLASS__, 0, -9));
        $this->fields  = $this->db->list_fields($this->table);
    }

    public function getLoginIDMap() {
        $params = array();
        $extras = array();
        $extras['fields'] = array('a_id', 'a_loginid');
        $extras['order_by'] = array('a_id DESC');

        $rows = $this->getList($params, $extras)->getData();
        
        $data = array();
        foreach($rows as $v) {
            $data[$v['a_id']] = $v['a_loginid'];
        } 
        return $data;
    }



}	
