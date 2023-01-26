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
 * Rack_tb Model
 *
 * @package MakeShop Platinum
 * @author ROOT TF Team
 * @created
 */

class Rack_tb_model extends MY_Model
{

    protected $pk = 'r_id';

    protected $emptycheck_keys = array(
        'r_location_id' => 'r_location_id 값이 누락되었습니다.',
        'r_code' => 'r_code 값이 누락되었습니다.',

        /*
        'r_floor' => 'r_floor 값이 누락되었습니다.',
        'r_section' => 'r_section 값이 누락되었습니다.',
        'r_frame' => 'r_frame 값이 누락되었습니다.',
        'r_created_at' => 'r_created_at 값이 누락되었습니다.',
        'r_updated_at' => 'r_updated_at 값이 누락되었습니다.',
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
        $params['r_created_at'] = date('Y-m-d H:i:s');
        $params['r_updated_at'] = date('Y-m-d H:i:s');
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
        $params['r_updated_at'] = date('Y-m-d H:i:s');
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
