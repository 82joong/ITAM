<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Assets_ip_map_tb_business extends MY_Model
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



    public function deleteRowAndIP($aim_id) {


        if( ! isset($aim_id) && $aim_id < 1 ) return;

        $this->load->model(array(
            'ip_tb_model',
        ));

        $aim_data = $this->get($aim_id)->getData();
        if(sizeof($aim_data) > 0) {
            $this->ip_tb_model->doDelete($aim_data['aim_ip_id']);
            $this->doDelete($aim_id);
        }

        return;
    }


    public function getIPListByAssets($am_id) {
        $res = array();

        $params = array();
        $params['=']['aim_assets_model_id'] = $am_id;
        $aim_data = $this->getList($params)->getData();
        if( sizeof($aim_data) > 0 ) {
            //$aim_data = $this->common->getDataByPk($aim_data, 'aim_ip_id');
            //$res = array_keys($aim_data);
            $res = $aim_data;
        }
        return $res;
    }
}
