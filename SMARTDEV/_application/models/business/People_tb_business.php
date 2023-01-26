<?php

defined('BASEPATH') or exit('No direct script access allowed');

class People_tb_business extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->db_name = _SHOP_INFO_DATABASE_;
        $this->table   = strtolower(substr(__CLASS__, 0, -9));
        $this->fields  = $this->db->list_fields($this->table);
    }


    public function getNameMap($is_active='ALL') {

        $params = array();
        $extras = array();
        $extras['fields'] = array('pp_id', 'pp_name');
        $extras['order_by'] = array('pp_id ASC');

        $rows = $this->getList($params, $extras)->getData();
        
        $data = array();
        foreach($rows as $v) {
            $data[$v['pp_id']] = $v['pp_name'];
        } 
        return $data;
    }
}
