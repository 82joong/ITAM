<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Models_tb_business extends MY_Model
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
            $params['=']['m_is_active'] = $is_active;
        }
        $extras = array();
        $extras['fields'] = array('m_id', 'm_model_name');
        $extras['order_by'] = array('m_id DESC');

        $rows = $this->getList($params, $extras)->getData();
        
        $data = array();
        foreach($rows as $v) {
            $data[$v['m_id']] = $v['m_model_name'];
        } 
        return $data;
    }


}
