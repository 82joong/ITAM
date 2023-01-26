<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Custom_value_tb_business extends MY_Model
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


    public function insertCustomValue($req, $data_params, $am_id) {

		$this->load->model(array(
            'models_custom_fields_tb_model',
        ));

        if( ! isset($req['fields']) ) return;

        /*
        $params = array();
        $params['=']['cfm_fieldset_id'] = $req['fieldset_id'];
        $cfm_data = $this->custom_field_map_tb_model->getList($params)->getData();
        $cfm_data = $this->common->getDataByPK($cfm_data, 'cfm_custom_field_id');
        echo print_r($cfm_data);

        $cf_ids = array_keys($req['fields']);
        $params = array();
        $params['in']['cf_id'] = $cf_ids;
        $cf_data = $this->custom_field_tb_model->getList($params)->getData();
        $cf_data = $this->common->getDataByPK($cf_data, 'cf_id');
        echo print_r($cf_data);
        */

        $params = array();
        $params['=']['mcf_fieldset_id'] = $req['fieldset_id'];
        $mcf_data = $this->models_custom_fields_tb_model->getList($params)->getData();
        $mcf_data = $this->common->getDataByPK($mcf_data, 'mcf_id');

        foreach($req['fields'] as $mcf_id=>$cf) {

            if( ! isset($mcf_data[$mcf_id]) ) {
                // TODO.
                continue;

            }
            $row = $mcf_data[$mcf_id];

            $in_params = array(
                'cv_assets_model_id' => $am_id,
                'cv_models_id'       => $data_params['am_models_id'],
                'cv_fieldset_id'     => $req['fieldset_id'],
                'cv_name'            => $row['mcf_name'],
                'cv_format'          => $row['mcf_format'],
                'cv_format_element'  => $row['mcf_format_element'],
                'cv_help_text'       => $row['mcf_help_text'],
                'cv_element_value'   => $row['mcf_element_value'],
                'cv_encrypt'         => $row['mcf_encrypt'],
                'cv_required'        => $row['mcf_required'],
                'cv_order'           => $row['mcf_order'],
                'cv_created_at'      => date('Y-m-d H:i:s'),
                'cv_udpated_at'      => date('Y-m-d H:i:s'),
            );

            if(is_array($cf)) {
                $in_params['cv_value'] = implode(',', $cf);
            }else {
                $in_params['cv_value'] = trim($cf);
            }

            if( ! $this->doInsert($in_params)->isSuccess()) {
                $res[] = array(
                    'in_params' => $in_params,
                    'error_msg' => $this->getErrorMsg()
                );
            }

        } // END_FOREACH

        return $res;
    }



    public function updateCustomValue($req) {

        $res = array();
        foreach($req['fields'] as $cv_id=>$cv) {
            $up_params = array(
                'cv_udpated_at' => date('Y-m-d H:i:s'),
            );

            if(is_array($cv)) {
                $up_params['cv_value'] = implode(',', $cv);
            }else {
                $up_params['cv_value'] = trim($cv);
            }

            if( ! $this->doUpdate($cv_id, $up_params)->isSuccess()) {
                $res[] = array(
                    'up_params' => $up_params,
                    'error_msg' => $this->getErrorMsg()
                );
            }
        } // END_FOREACH
        return $res;
    }
}
