<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Company_tb_business extends MY_Model
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
        if($is_active !== 'ALL') {
            $params['=']['c_is_active'] = $is_active;
        }

        $extras = array();
        $extras['fields'] = array('c_id', 'c_name');
        $extras['order_by'] = array('c_id ASC');

        $rows = $this->getList($params, $extras)->getData();
        
        $data = array();
        foreach($rows as $v) {
            $data[$v['c_id']] = $v['c_name'];
        } 
        return $data;
    }

    public function getMap($key='c_id', $value='c_name', $is_active='ALL') {

        $params = array();
        if($is_active !== 'ALL') {
            $params['=']['c_is_active'] = $is_active;
        }

        $extras = array();
        $extras['fields'] = array($key, $value);
        $extras['order_by'] = array($key.' ASC');
        $rows = $this->getList($params, $extras)->getData();
        
        $data = array();
        foreach($rows as $v) {
            $data[$v[$key]] = $v[$value];
        } 
        return $data;
    }



}
