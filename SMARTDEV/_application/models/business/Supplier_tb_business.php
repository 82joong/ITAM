<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Supplier_tb_business extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->db_name = _SHOP_INFO_DATABASE_;
        $this->table   = strtolower(substr(__CLASS__, 0, -9));
        $this->fields  = $this->db->list_fields($this->table);
    }


    public function getNameMap() {

        $params = array();
        $extras = array();
        $extras['fields'] = array('sp_id', 'sp_name', 'sp_contact_name');
        $extras['order_by'] = array('sp_id DESC');

        $rows = $this->getList($params, $extras)->getData();
        
        $data = array();
        foreach($rows as $v) {
            $data[$v['sp_id']] = $v['sp_name'].' @'.$v['sp_contact_name'];
        } 
        return $data;
    }


    public function getOptionMap() {

        $params = array();
        $extras = array();
        $extras['fields'] = array('sp_id', 'sp_name', 'sp_contact_name');
        $extras['order_by'] = array('sp_id DESC');

        $rows = $this->getList($params, $extras)->getData();
        
        $data = array();
        foreach($rows as $v) {
            //$data[$v['sp_id']] = $v['sp_name'].' @'.$v['sp_contact_name'];

            $data[] = array(
                'grp_name'  => $v['sp_name'],
                'grp_id'    => $v['sp_name'],
                'opt_name'  => $v['sp_contact_name'], 
                'opt_id'    => $v['sp_id'], 
            );
        } 
        $data = $this->common->getDataByDuplPK($data, 'grp_id');
        return $data;
    }
}
