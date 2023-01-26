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
 * Custom_value_tb Model
 *
 * @package MakeShop Platinum
 * @author ROOT TF Team
 * @created
 */

class Custom_value_tb_model extends MY_Model
{

    protected $pk = 'cv_id';

    protected $emptycheck_keys = array(
        'cv_assets_model_id' => 'cv_assets_model_id 값이 누락되었습니다.',
        'cv_models_id' => 'cv_models_id 값이 누락되었습니다.',
        'cv_fieldset_id' => 'cv_fieldset_id 값이 누락되었습니다.',
        'cv_name' => 'cv_name 값이 누락되었습니다.',
        'cv_format' => 'cv_format 값이 누락되었습니다.',
        'cv_format_element' => 'cv_format_element 값이 누락되었습니다.',
        'cv_help_text' => 'cv_help_text 값이 누락되었습니다.',
        //'cv_element_value' => 'cv_element_value 값이 누락되었습니다.',
        //'cv_value' => 'cv_value 값이 누락되었습니다.',
        /*
        'cv_created_at' => 'cv_created_at 값이 누락되었습니다.',
        'cv_updated_at' => 'cv_updated_at 값이 누락되었습니다.',
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
        $params['cv_created_at'] = date('Y-m-d H:i:s');
        $params['cv_updated_at'] = date('Y-m-d H:i:s');
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
        $params['cv_updated_at'] = date('Y-m-d H:i:s');
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
