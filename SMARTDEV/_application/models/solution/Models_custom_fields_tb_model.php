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
 * Models_custom_fields_tb Model
 *
 * @package MakeShop Platinum
 * @author ROOT TF Team
 * @created
 */

class Models_custom_fields_tb_model extends MY_Model
{

    protected $pk = 'mcf_id';

    protected $emptycheck_keys = array(
        'mcf_models_id' => 'mcf_models_id 값이 누락되었습니다.',
        'mcf_fieldset_id' => 'mcf_fieldset_id 값이 누락되었습니다.',
        /*
        'cf_fieldset_name' => 'mcf_fieldset_name 값이 누락되었습니다.',
        'mcf_format' => 'mcf_format 값이 누락되었습니다.',
        'mcf_format_element' => 'mcf_format_element 값이 누락되었습니다.',
        'mcf_help_text' => 'mcf_help_text 값이 누락되었습니다.',
        'mcf_element_value' => 'mcf_element_value 값이 누락되었습니다.',
        'mcf_value' => 'mcf_value 값이 누락되었습니다.',
        'mcf_order' => 'mcf_order 값이 누락되었습니다.',
        'mcf_created_at' => 'mcf_created_at 값이 누락되었습니다.',
        'mcf_updated_at' => 'mcf_updated_at 값이 누락되었습니다.',
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
        $params['mcf_created_at'] = date('Y-m-d H:i:s');
        $params['mcf_updated_at'] = date('Y-m-d H:i:s');
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
        $params['mcf_updated_at'] = date('Y-m-d H:i:s');
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
