<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Direct_ip_map_tb_business extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->db_name = _SHOP_INFO_DATABASE_;
        $this->table   = strtolower(substr(__CLASS__, 0, -9));
        $this->fields  = $this->db->list_fields($this->table);

        // FIND Primary KEY 
        $fields = $this->db->field_data($this->table);
        foreach ($fields as $field) {
            if($field->primary_key) {
                $this->pk = $field->name;
            }
        }
    }



    public function getDirectIP($am_id) {

        $dim_data = array();
        if( ! isset($am_id) && $am_id < 1 ) {
            return $dim_data;
        }

        $this->load->model(array(
            'ip_tb_model',
        ));

        $params = array();
        $params['=']['dim_assets_model_id'] = $am_id;
        $params['join']['ip_tb'] = 'dim_ip_id = ip_id';
        $extras = array();
        $extras['fields'] = array('dim_id', 'dim_assets_model_id', 'ip_tb.*');
        $dim_data = $this->getList($params, $extras)->getData();

        if(sizeof($dim_data) > 0) {
            $dim_data = array_shift($dim_data);
        }

        return $dim_data;
    }


    public function getIPListByAssets($am_id) {
        $res = array();

        $params = array();
        $params['=']['dim_assets_model_id'] = $am_id;
        $dim_data = $this->getList($params)->getData();
        if( sizeof($dim_data) > 0 ) {
            //$dim_data = $this->common->getDataByPk($dim_data, 'dim_ip_id');
            //$res = array_keys($dim_data);
            $res = $dim_data;
        }
        return $res;
    }

}
