<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Category_tb_business extends MY_Model
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
            $params['=']['ct_is_active'] = $is_active;
        }
        $extras = array();
        $extras['fields'] = array('ct_id', 'ct_name');
        $extras['order_by'] = array('ct_name ASC');

        $rows = $this->getList($params, $extras)->getData();
        
        $data = array();
        foreach($rows as $v) {
            $data[$v['ct_id']] = $v['ct_name'];
        } 
        return $data;
    }



    /*

        $category_data = $this->category_tb_business->getOptionMap();
        $data['select_category'] = getSearchWithIconSelect($category_data, 'm_category_id', $ct_id, '');

    */
    public function getOptionMap($is_active='ALL') {

        $params = array();
        if($is_active !== 'ALL') {
            $params['=']['ct_is_active'] = $is_active;
        }
        $extras = array();
        $extras['fields'] = array('ct_id', 'ct_name', 'ct_icon');
        $extras['order_by'] = array('ct_order ASC');

        $rows = $this->getList($params, $extras)->getData();
        
        $data = array();
        foreach($rows as $v) {
            $data[] = array(
                'opt_name'  => $v['ct_name'], 
                'opt_id'    => $v['ct_id'], 
                'opt_icon'  => 'fas '.$v['ct_icon'],
                'opt_color' => '', 
            );
        } 
        return $data;
    }



    public function getGroupMap() {

        $this->load->model(array(
            'assets_type_tb_model'
        ));

        $params = array();
        $params['join']['assets_type_tb'] = 'at_id = ct_type_id';

        $extras = array();
        $extras['fields'] = array('ct_id', 'ct_type_id', 'ct_name', 'ct_icon', 'at_name');
        $extras['order_by'] = array('at_id ASC');

        $rows = $this->getList($params, $extras)->getData();
        
        $data = array();
        foreach($rows as $v) {
            //$data[$v['sp_id']] = $v['sp_name'].' @'.$v['sp_contact_name'];

            $data[] = array(
                'grp_name'  => $v['at_name'],
                'grp_id'    => $v['at_name'],
                'opt_name'  => $v['ct_name'], 
                'opt_id'    => $v['ct_id'], 
            );
        } 
        $data = $this->common->getDataByDuplPK($data, 'grp_id');
        return $data;
    }
}
