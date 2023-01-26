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
 * Order_tb Model
 *
 * @package MakeShop Platinum
 * @author ROOT TF Team
 * @created
 */

class Order_tb_model extends MY_Model
{

    protected $pk = 'o_id';

    protected $emptycheck_keys = array(
        //'o_supplier_id' => 'o_supplier_id 값이 누락되었습니다.',
        'o_writer_id' => 'o_writer_id 값이 누락되었습니다.',
        'o_estimatenum' => 'o_estimatenum 값이 누락되었습니다.',
        /*
        'o_ordernum' => 'o_ordernum 값이 누락되었습니다.',
        'o_count' => 'o_count 값이 누락되었습니다.',
        'o_delivery_price' => 'o_delivery_price 값이 누락되었습니다.',
        'o_etc_price' => 'o_etc_price 값이 누락되었습니다.',
        'o_total_price' => 'o_total_price 값이 누락되었습니다.',
        'o_memo' => 'o_memo 값이 누락되었습니다.',
        'o_process_history' => 'o_process_history 값이 누락되었습니다.',
        'o_ordered_at' => 'o_ordered_at 값이 누락되었습니다.',
        'o_delivered_at' => 'o_delivered_at 값이 누락되었습니다.',
        'o_canceled_at' => 'o_canceled_at 값이 누락되었습니다.',
        'o_invoiced_at' => 'o_invoiced_at 값이 누락되었습니다.',
        'o_created_at' => 'o_created_at 값이 누락되었습니다.',
        'o_updated_at' => 'o_updated_at 값이 누락되었습니다.',
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



	public static $status= array(
			//'WRITING'	=> 'WRITING',       // 작성중
			'ORDERED'	=> 'ORDERED',       // 주문완료
			'DELIVERED'	=> 'DELIVERED',     // 배송중 (송장발부)  
			'CANCELED'	=> 'CANCELED',      // 취소
			//'INVOICED'	=> 'INVOICED',      // 발주완료 (배송완료)
	);
    public function getStatusMap() {
        return self::$status;
    }


	public static $status_color= array(
			//'WRITING'	=> 'secondary',     // 작성중
			'ORDERED'	=> 'primary',       // 주문완료
			'DELIVERED'	=> 'success',       // 배송중 (송장발부)  
			'CANCELED'	=> 'danger',        // 취소
			//'INVOICED'	=> 'info',          // 발주완료 (배송완료)
	);
    public function getStatusColorMap() {
        return self::$status_color;
    }


	public static $status_color_code= array(
			//'WRITING'	=> '#868e96',     // 작성중
			'ORDERED'	=> '#886ab5',       // 주문완료
			'DELIVERED'	=> '#1dc9b7',       // 배송중 (송장발부)  
			'CANCELED'	=> '#fd3995',        // 취소
			//'INVOICED'	=> '#2196F3',          // 발주완료 (배송완료)
	);
    public function getStatusColorCodeMap() {
        return self::$status_color_code;
    }




    protected function __filter($params)
    {
        $params['o_created_at'] = date('Y-m-d H:i:s');
        $params['o_updated_at'] = date('Y-m-d H:i:s');
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
        $params['o_updated_at'] = date('Y-m-d H:i:s');
        return $params;
    }

    protected function __updateValidate($params)
    {
        $success = parent::__updateValidate($params);
        if ($success === true) {
        }
        return $success;
    }

}
