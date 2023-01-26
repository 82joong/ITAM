<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Vendor_tb_business extends MY_Model
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
        $extras['fields'] = array('vd_id', 'vd_name');
        $extras['order_by'] = array('vd_id DESC');

        $rows = $this->getList($params, $extras)->getData();
        
        $data = array();
        foreach($rows as $v) {
            $data[$v['vd_id']] = $v['vd_name'];
        } 
        return $data;
    }


    public function getVendorIcon($vd_id, $vd_filename='') {

        $icon = '';
        if(strlen($vd_filename) > 0) {
            $img_path = $this->common->getImgUrl('vendor', $vd_id);
            $icon .= '<img src="'.$img_path.'/'.$vd_filename.'" class="profile-image profile-image-md rounded-circle border">';
        }
        return $icon;
    }
}
