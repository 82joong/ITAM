<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Vmservice_tb_business extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->db_name = _SHOP_INFO_DATABASE_;
        $this->table   = strtolower(substr(__CLASS__, 0, -9));
        $this->fields  = $this->db->list_fields($this->table);
    }


    // Alias 선택 할 수 있는 서비스 정보 가져오기
    public function get_service_for_alias($am_id = '') {

		$this->load->model(array(
            'ip_tb_model',
            'vmservice_ip_map_tb_model',
        ));

        $params = array();
        if(strlen($am_id) > 0) {
            $params['=']['vms_assets_model_id'] = $am_id;
        }
        $params['<']['vms_alias_id'] = 1;   // alias service 제외
        $params['join']['vmservice_ip_map_tb'] = 'vim_vmservice_id = vms_id';
        $params['join']['ip_tb'] = 'vim_ip_id = ip_id';
        $extras = array();
        $extras['fields'] = array('vms_id', 'vms_name', 'ip_address');
        $vms_data = $this->vmservice_tb_model->getList($params, $extras)->getData();
        $vms_data = $this->common->getDataByPK($vms_data, 'vms_id');

        $service_data = array();
        foreach($vms_data as $vms) {
            $service_data[$vms['vms_id']] = $vms['vms_name'].'     ( '.$vms['ip_address'].' )';
        }
        
        return $service_data;
    }




}	
