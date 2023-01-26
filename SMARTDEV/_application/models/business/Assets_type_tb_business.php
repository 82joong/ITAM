<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Assets_type_tb_business extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->db_name = _SHOP_INFO_DATABASE_;
        $this->table   = strtolower(substr(__CLASS__, 0, -9));
        $this->fields  = $this->db->list_fields($this->table);
    }


	public function get_assets_type($is_active='ALL') {

        $params = array();
        if($is_active !== 'ALL') {
            $params['=']['at_is_active'] = $is_active;
        }

        $extras = array();
        $extras['order_by'] = array('at_id ASC');

		$rows = $this->getList($params, $extras)->getData();
        $rows = $this->common->getDataByPK($rows, 'at_id');

        // Assets type별 Count
        $this->load->model('assets_model_tb_model');
        $params = array();
        $extras = array();
        $extras['group_by'] = array('am_assets_type_id');
        $extras['fields'] = array('am_assets_type_id', 'COUNT(am_id) AS cnt');
        $assets_type_cnt = $this->assets_model_tb_model->getList($params, $extras)->getData();
        $assets_type_cnt = $this->common->getDataByPK($assets_type_cnt, 'am_assets_type_id');


        $res = array();
        foreach($rows as $k=>$v) {
            if(isset($assets_type_cnt[$v['at_id']])) {
                $v['count'] = $assets_type_cnt[$v['at_id']]['cnt'];
            }else {
                $v['count'] = 0;
            }
            $res[$v['at_id']] = $v;
        }
        return $res; 
    }

    public function assets_type_map() {
        $type = $this->get_assets_type();

        $data = array();
        foreach($type as $k=>$v) {
            $data[$k] = $v['at_name'];
        }
        return $data;
    }


    public function getNameMap($is_active='ALL') {

        $params = array();
        if($is_active !== 'ALL') {
            $params['=']['at_is_active'] = $is_active;
        }

        $extras = array();
        $extras['fields'] = array('at_id', 'at_name');
        $extras['order_by'] = array('at_id DESC');

        $rows = $this->getList($params, $extras)->getData();
        
        $data = array();
        foreach($rows as $v) {
            $data[$v['at_id']] = $v['at_name'];
        } 
        return $data;
    }



    public function getOptionMap($is_active='ALL') {

        $params = array();
        if($is_active !== 'ALL') {
            $params['=']['at_is_active'] = $is_active;
        }

        $extras = array();
        $extras['fields'] = array('at_id', 'at_name', 'at_icon', 'at_color');
        $extras['order_by'] = array('at_id DESC');

        $rows = $this->getList($params, $extras)->getData();
        
        $data = array();
        foreach($rows as $v) {
            $data[$v['at_id']] = array(
                'opt_name'   => $v['at_name'], 
                'opt_icon'   => 'fas '.$v['at_icon'],
                'opt_color'  => $v['at_color']
            );
        } 
        return $data;
    }


    public function iconTypeName($data) {

        $html = '';
        $html .= '<span class="badge ml-auto text-white" style="background-color:'.$data['at_color'].'">';
        $html .= '<i class="mr-1 fal '.$data['at_icon'].'"></i>';
        $html .= '<span class="hidden-md-down">'.$data['at_name'].'</span>';
        $html .= '</span>';

        return $html;
    }

}
