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
 * Custom_field_map_tb Model
 *
 * @package MakeShop Platinum
 * @author ROOT TF Team
 * @created
 */

class Custom_field_map_tb_model extends MY_Model
{

    protected $pk = 'cfm_id';

    protected $emptycheck_keys = array(
        'cfm_fieldset_id' => 'cfm_fieldset_id 값이 누락되었습니다.',
        'cfm_custom_field_id' => 'cfm_custom_field_id 값이 누락되었습니다.',
        'cfm_order' => 'cfm_order 값이 누락되었습니다.',
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
        if(isset($params['cfm_fieldset_id']) == true) { 
            $in_params = array();
            $in_params['=']['cfm_fieldset_id'] = $params['cfm_fieldset_id'];
            $params['cfm_order'] = $this->getCount($in_params)->getData();

            $params['cfm_order'] = $params['cfm_order'] + 1;
        }       

        parent::doInsert($params, $skip_escape, $exec_type); 
        return $this;       
    }      



    public function doDelete($pk, $exec_type='exec') {
        
        $row = $this->get($pk)->getData();


        $_this_del = parent::doDelete($pk, $exec_type); 

        $params = array();
        $params['=']['cfm_fieldset_id'] = $row['cfm_fieldset_id'];
        $extras = array();
        $extras['order_by'] = array('cfm_order ASC');
        $data = $this->getList($params, $extras)->getData();

        $order = 1;
        foreach($data as $r) {

            if($r['cfm_order'] == $order) {

            }else {
                $data_params = array();
                $data_params['cfm_order'] = $order;
                
                $this->doUpdate($r['cfm_id'], $data_params);

            }
            $order = $order + 1;
        }

        return $_this_del;       
    }      

}
