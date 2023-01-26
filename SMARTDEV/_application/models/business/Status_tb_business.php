<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Status_tb_business extends MY_Model
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
            $params['=']['s_is_active'] = $is_active;
        }
        $extras = array();
        $extras['fields'] = array('s_id', 's_name');
        $extras['order_by'] = array('s_id ASC');

        $rows = $this->getList($params, $extras)->getData();
        
        $data = array();
        foreach($rows as $v) {
            $data[$v['s_id']] = $v['s_name'];
        } 
        return $data;
    }


    public function getRowMap($is_active='ALL') {

        $params = array();
        if($is_active !== 'ALL') {
            $params['=']['s_is_active'] = $is_active;
        }
        $extras = array();
        $extras['fields'] = array('s_id', 's_name', 's_color_code', 's_code');
        $extras['order_by'] = array('s_id ASC');

        $rows = $this->getList($params, $extras)->getData();

        $data = array();
        foreach($rows as $v) {
            $data[$v['s_id']] = array(
                'opt_id'    => $v['s_id'],
                'opt_name'  => $v['s_name'],
                'opt_icon'  => 'base-10',
                'opt_color' => $v['s_color_code'],
            );
        } 
        return $data;
    }


    public function iconStatusName($color, $name) {
        return '<i class="base-10 mr-1" style="color:'.$color.'"></i>'.$name;
    }


    public function outStatusText($split='&nbsp;') {

        $res = '';
     
        $params = array();
        $params['=']['s_is_active'] = 'YES';
        $params['in']['s_id'] = OUT_STATUS;
        $extras = array();
        $extras['fields'] = array('s_id', 's_name', 's_color_code');
        $extras['order_by'] = array('s_id ASC');
        $out_status = $this->getList($params, $extras)->getData();
        foreach($out_status as $out) {
            $res .= $this->iconStatusName($out['s_color_code'], $out['s_name']).$split;  
        }
        return $res;
    }
    
}
