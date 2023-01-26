<?php
/**
 * MakeShop Platinum
 *
 * @package MakeShop Platinum
 * @author ROOT TF Team
 * @copyright Copyright (c) 2016, KOREACENTER.COM, Inc. (http://makeshop.co.kr/)
 */
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Order_item_tb Model
 *
 * @package MakeShop Platinum
 * @author ROOT TF Team
 * @created
 */

class Order_item_tb_model extends MY_Model
{

    protected $pk = 'oi_id';

    protected $emptycheck_keys = array(
        'oi_order_id' => 'oi_order_id 값이 누락되었습니다.',
        /*
        'oi_ordernum' => 'oi_ordernum 값이 누락되었습니다.',
        'oi_company_id' => 'oi_company_id 값이 누락되었습니다.',
        'oi_writer_id' => 'oi_writer_id 값이 누락되었습니다.',
        'oi_model_id' => 'oi_model_id 값이 누락되었습니다.',
        'oi_model_name' => 'oi_model_name 값이 누락되었습니다.',
        'oi_unit_price' => 'oi_unit_price 값이 누락되었습니다.',
        'oi_quantity' => 'oi_quantity 값이 누락되었습니다.',
        'oi_tax' => 'oi_tax 값이 누락되었습니다.',
        'oi_total_price' => 'oi_total_price 값이 누락되었습니다.',
        'oi_created_at' => 'oi_created_at 값이 누락되었습니다.',
        'oi_updated_at' => 'oi_updated_at 값이 누락되었습니다.',
        */
    );

    protected $enumcheck_keys = array(
    );

    protected $code_text_map = array();

    public function __construct()
    {
        parent::__construct();
        $this->db_name = _SHOP_INFO_DATABASE_;
        $this->table   = strtolower(substr(__CLASS__, 0, -6));
        $this->fields  = $this->db->list_fields($this->table);
    }

    protected function __filter($params)
    {
        /*
        $params['oi_created_at'] = date('Y-m-d H:i:s');
        $params['oi_updated_at'] = date('Y-m-d H:i:s');
        */
        return $params;
    }

    protected function __validate($params)
    {
        $success = parent::__validate($params);
        if ($success === true) {
        }
        return $success;
    }

    protected function __updateFilter($params)
    {
        //$params['oi_updated_at'] = date('Y-m-d H:i:s');
        return $params;
    }

    protected function __updateValidate($params)
    {
        $success = parent::__updateValidate($params);
        if ($success === true) {
        }
        return $success;
    }



    public function doInsert($params, $skip_escape=array(), $exec_type='exec') {

        if(isset($params['oi_order_id']) && $params['oi_order_id'] > 0) { 
            $this->load->model('order_tb_model'); 
            $order_data = $this->order_tb_model->get($params['oi_order_id'])->getData();
            $params['oi_estimatenum'] = $order_data['o_estimatenum'];
            $params['oi_company_id'] = $order_data['o_company_id'];
            $params['oi_item_status'] = $order_data['o_order_status'];
        }       

        parent::doInsert($params, $skip_escape, $exec_type); 
        return $this;    
    }

}
