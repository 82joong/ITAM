<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Rack_tb_business extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->db_name = _SHOP_INFO_DATABASE_;
        $this->table   = strtolower(substr(__CLASS__, 0, -9));
        $this->fields  = $this->db->list_fields($this->table);
    }



    public function getCodeMap() {

        $params = array();

        $extras = array();
        $extras['fields'] = array('r_id', 'r_code');
        $extras['order_by'] = array('r_id DESC');

        $rows = $this->getList($params, $extras)->getData();
        
        $data = array();
        foreach($rows as $v) {
            $data[$v['r_id']] = $v['r_code'];
        } 
        return $data;
    }


    public function getLocationGroupMap() {


        $this->load->business(array(
            'location_tb_business',
        ));
        $location_map = $this->location_tb_business->getCodeMap();

        $params = array();

        $extras = array();
        $extras['fields'] = array('r_id', 'r_location_id', 'r_code');
        $extras['order_by'] = array('r_id DESC');

        $rows = $this->getList($params, $extras)->getData();
        
        $data = array();
        foreach($rows as $v) {
            $data[] = array(
                'grp_name'  => $location_map[$v['r_location_id']],
                'grp_id'    => $v['r_location_id'],
                'opt_name'  => $v['r_code'], 
                'opt_id'    => $v['r_id'], 
            );
        } 
        $data = $this->common->getDataByDuplPK($data, 'grp_id');
        return $data;
    }




    public function getRackInfo($rack_id) {
            
        $res = array();

		$this->load->model(array(
            'location_tb_model',
        ));


        $params = array();
        $params['join']['location_tb'] = 'r_location_id = l_id';
        $params['=']['r_id'] = $rack_id;
        $extras = array();
        $extras['fields'] = array('rack_tb.*', 'location_tb.*');
        $extras['order_by'] = array('r_id DESC');
        $res = $this->getList($params, $extras)->getData();
        return $res;

    } 

}
