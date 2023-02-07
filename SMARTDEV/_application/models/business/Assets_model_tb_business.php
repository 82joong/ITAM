<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Assets_model_tb_business extends MY_Model
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
        $extras = array();
        $extras['fields'] = array('am_id', 'am_models_name', 'am_serial_no');
        $extras['order_by'] = array('am_id ASC');

        $rows = $this->getList($params, $extras)->getData();
        
        $data = array();
        foreach($rows as $v) {
            $data[$v['am_id']] = $v['am_models_name'].' (#'.$v['am_serial_no'].')';
        } 
        return $data;
    }


    public function getVMWareIP($am_ids) {

        if(sizeof($am_ids) < 1) return;

        $this->load->model(array(
            'ip_tb_model',
            'assets_ip_map_tb_model',
        ));

        $params = array();
        $params['join']['ip_tb'] = 'aim_ip_id = ip_id';
        $params['in']['aim_assets_model_id'] = $am_ids;
        $params['in']['ip_class_category'] = array('VMWARE', 'PUBLIC');
        $extras = array();
        $extras['fields'] = array(
            'aim_id','aim_assets_model_id','aim_ip_id',
            'ip_id','ip_address','ip_class_type','ip_class_category','ip_memo'
        );

        $data = $this->assets_ip_map_tb_model->getList($params, $extras)->getData();
        $data = $this->common->getDataByPK($data, 'aim_assets_model_id');

        return $data;
    }



    public function getAssetsIPType($am_id=0) {

        if($am_id < 1) return;

        $this->load->model(array(
            'ip_tb_model',
            'assets_ip_map_tb_model',
        ));

        $params = array();
        $params['join']['ip_tb'] = 'aim_ip_id = ip_id';
        $params['=']['aim_assets_model_id'] = $am_id;
        $extras = array();
        $extras['fields'] = array(
            'aim_id','aim_assets_model_id','aim_ip_id',
            'ip_id','ip_address','ip_class_id','ip_class_type','ip_class_category','ip_memo'
        );

        $data = $this->assets_ip_map_tb_model->getList($params, $extras)->getData();
        $data = $this->common->getDataByPK($data, 'ip_class_category');

        return $data;
    }



    public function getAssetsIPTypeSearch($am_ids=array(), $params=array()) {

        if(sizeof($am_ids) < 1) return;

        $this->load->model(array(
            'ip_tb_model',
            'assets_ip_map_tb_model',
        ));

        $params['join']['ip_tb'] = 'aim_ip_id = ip_id';
        $params['in']['aim_assets_model_id'] = $am_ids;

        $extras = array();
        $extras['fields'] = array(
            'aim_id','aim_assets_model_id','aim_ip_id',
            'ip_id','ip_address','ip_class_type','ip_class_category','ip_memo'
        );

        $data = $this->assets_ip_map_tb_model->getList($params, $extras)->getData();
        $data = $this->common->getDataByPK($data, 'ip_class_category');

        return $data;
    }




    public function getRelationTblData($am_data) {


		$this->load->model(array(
            'vmservice_ip_map_tb_model',
            'vmservice_tb_model',
            'ip_tb_model',
            'direct_ip_map_tb_model',
            'assets_ip_map_tb_model',
            'out_backup_tb_model',
            'custom_value_tb_model',
        ));

		$this->load->business(array(
            'assets_ip_map_tb_business',
            'direct_ip_map_tb_business',
            'rack_tb_business',
        ));
        //echo print_r($am_data);

        $custom_data = array();
        $params = array();
        $params['=']['cv_assets_model_id'] = $am_data['am_id'];
        $custom_data = $this->custom_value_tb_model->getList($params)->getData();
        //echo print_r($custom_data);

        $aim_data = array();
        $dim_data = array();

        $ip_ids = array();
        $aim_data = $this->assets_ip_map_tb_business->getIPListByAssets($am_data['am_id']);
        //echo print_r($aim_data); exit;
        if( sizeof($aim_data) > 0 ) {
            $aim_ips = $this->common->getDataByPk($aim_data, 'aim_ip_id');
            $aim_ips = array_keys($aim_ips);
            $ip_ids = array_merge($ip_ids, $aim_ips);
        }
        $dim_data = $this->direct_ip_map_tb_business->getIPListByAssets($am_data['am_id']);
        if( sizeof($dim_data) > 0 ) {
            $dim_ips = $this->common->getDataByPk($dim_data, 'dim_ip_id');
            $dim_ips = array_keys($dim_ips);
            $ip_ids = array_merge($ip_ids, $dim_ips);
        }


        $vim_data = array();
        $params = array();
        $params['=']['vms_assets_model_id'] = $am_data['am_id'];
        $vms_data = $this->vmservice_tb_model->getList($params)->getData();
        //eco print_r($vms_data);
        if( sizeof($vms_data) > 0 )  {
            $vms_data = $this->common->getDataByPK($vms_data, 'vms_id');
            $params = array();
            $params['in']['vim_vmservice_id'] = array_keys($vms_data);
            $vim_data = $this->vmservice_ip_map_tb_model->getList($params)->getData();
            $vim_ips = array_keys($this->common->getDataByPK($vim_data, 'vim_ip_id'));
            $ip_ids = array_merge($ip_ids, $vim_ips);
        }
        $map_data['vim'] = $vim_data;




        $ip_data = array();
        if(sizeof($ip_ids) > 0) {
            $params = array();
            $params['in']['ip_id'] = $ip_ids;
            $ip_data = $this->ip_tb_model->getList($params)->getData();
        }

        $rack_data = $this->rack_tb_model->get($am_data['am_rack_id'])->getData();

        $map_data = array(
            'aim' => $aim_data,
            'dim' => $dim_data,
            'vim' => $vim_data,
        );

        $res = array(
            'custom'    => $custom_data,
            'vmservice' => $vms_data,
            'rack'      => $rack_data,
            'ip'        => $ip_data,
            'map'       => $map_data,
        );

        return $res;
    }

}
