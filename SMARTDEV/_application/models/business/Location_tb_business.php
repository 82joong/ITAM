<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Location_tb_business extends MY_Model
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
        $extras['fields'] = array('l_id', 'l_name', 'l_code');
        $extras['order_by'] = array('l_name ASC');

        $rows = $this->getList($params, $extras)->getData();
        
        $data = array();
        foreach($rows as $v) {
            $data[$v['l_id']] = $v['l_name'].' ['.$v['l_code'].']';
        } 
        return $data;
    }


    public function getCodeMap($key='l_id', $is_active='ALL') {

        $params = array();
        $extras = array();
        $extras['fields'] = array('l_id', 'l_code');
        $extras['order_by'] = array('l_id ASC');

        $rows = $this->getList($params, $extras)->getData();
        
        $data = array();
        foreach($rows as $v) {
            $data[$v[$key]] = $v['l_code'];
        } 
        return $data;
    }
}
