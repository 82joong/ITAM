<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Order_item_tb_business extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->db_name = _SHOP_INFO_DATABASE_;
        $this->table   = strtolower(substr(__CLASS__, 0, -9));
        $this->fields  = $this->db->list_fields($this->table);
    }



    public function SyncOrderItem($o_id) {
        $this->load->model(array(
            'order_tb_model',
            'order_item_tb_model'
        )); 

        $order_data = $this->order_tb_model->get($o_id)->getData();

        $where_params = array();
        $where_params['=']['oi_order_id'] = $o_id;
        $data_params = array();
        $data_params['oi_estimatenum'] = $order_data['o_estimatenum'];
        $data__params['oi_company_id'] = $order_data['o_company_id'];
        $data_params['oi_item_status'] = $order_data['o_order_status'];

        $res = $this->order_item_tb_model->doMultiUpdate($data_params, $where_params)->isSuccess();
        return $res;
    }


    public function SyncOrder($o_id, $from='', $history_data=array()) {

        $this->load->model(array(
            'order_tb_model',
            'order_item_tb_model'
        )); 
        
        $order_data = $this->order_tb_model->get($o_id)->getData();
        
        $params = array();
        $params['=']['oi_order_id'] = $o_id;
        $oi_data = $this->order_item_tb_model->getList($params)->getData();


        $oi_total = 0;
        $vat_total = 0;
        foreach($oi_data as $oi) {
            $oi_total += $oi['oi_total_price'];
            $vat_total += $oi['oi_tax']; 
        }

        $o_total = $order_data['o_delivery_price'] + $order_data['o_etc_price'] + $oi_total;
        $o_count = sizeof($oi_data); 

        $up_params = array();
        $up_params['o_count'] = $o_count;
        $up_params['o_total_price'] = $o_total;
        $up_params['o_vat_price'] = $vat_total;

        // Add History 
        if(sizeof($history_data) > 0) {
            $history_data['writer'] = $this->_ADMIN_DATA['login_id'];
            $ph_data = unserialize($order_data['o_process_history']);
            $date_at = date('Y-m-d H:i:s');
            $ph_data[$from][$date_at] = $history_data;
            $up_params['o_process_history'] = serialize($ph_data);
        }

        $res = $this->order_tb_model->doUpdate($o_id, $up_params)->isSuccess();
        $json_data = array(
            'o_total_price' => $o_total,
            'o_vat_price'   => $vat_total,
        ); 
        return $json_data;
    }


    public function getOrdersGroup($self_id = '') {

        $this->load->model(array(
            'order_tb_model',
            'assets_model_tb_model',
        ));

        $o_status_map = $this->order_tb_model->getStatusColorCodeMap();

        $params = array();
        $params['left_join']['assets_model_tb'] = 'am_order_item_id = oi_id';

        $raw = '(am_id IS NULL AND oi_item_status = "DELIVERED")';
        if($self_id > 0) {
            $raw .= ' OR oi_id = '.$self_id;
        }

        $params['raw'] = array($raw);
        $params['=']['oi_item_status'] = 'DELIVERED';

        $extras = array();
        //$extras['group_by'] = array('oi_order_id');
        $extras['fields'] = array('am_id', 'oi_order_id', 'oi_estimatenum', 'oi_id', 'oi_model_name', 'oi_service_tag', 'oi_item_status', 'oi_memo');
        $extras['order_by'] = array('oi_id ASC');

        $oi_data = $this->getList($params, $extras)->getData();
        //echo $this->getLastQuery(); exit;
        //echo print_r($oi_data); exit;

        $data = array();
        foreach($oi_data as $oi) {

            $grp_name = '['.$oi['oi_item_status'].'] '.$oi['oi_estimatenum'];

            $opt_name = $oi['oi_model_name'];
            if(strlen($oi['oi_service_tag']) > 0) {
                $opt_name .=  ' ['.$oi['oi_service_tag'].'] ';
            }
            $opt_name .= ' '.$oi['oi_memo'];


            $data[] = array(
                'grp_name'  => $grp_name,
                'grp_id'    => $oi['oi_order_id'],
                'opt_name'  => $opt_name, 
                'opt_id'    => $oi['oi_id'], 
                'opt_icon'  => 'base-10', 
                'opt_color' => $o_status_map[$oi['oi_item_status']], 
            );
        }
        $data = $this->common->getDataByDuplPK($data, 'grp_id');
        return $data;
    }


}
