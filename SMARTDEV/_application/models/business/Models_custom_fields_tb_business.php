<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Models_custom_fields_tb_business extends MY_Model
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


    public function insertCustomValue($req, $models_id) {

		$this->load->model(array(
            'custom_field_map_tb_model',
            'custom_field_tb_model',
            'fieldset_tb_model',
        ));


        $fs_data = $this->fieldset_tb_model->get($req['fieldset_id'])->getData();

        $params = array();
        $params['=']['cfm_fieldset_id'] = $req['fieldset_id'];
        $cfm_data = $this->custom_field_map_tb_model->getList($params)->getData();
        $cfm_data = $this->common->getDataByPK($cfm_data, 'cfm_custom_field_id');


        $cf_ids = array_keys($req['fields']);
        $params = array();
        $params['in']['cf_id'] = $cf_ids;
        $cf_data = $this->custom_field_tb_model->getList($params)->getData();
        $cf_data = $this->common->getDataByPK($cf_data, 'cf_id');

	$res = array();
        foreach($req['fields'] as $cf_id=>$cf) {

            if( ! isset($cf_data[$cf_id]) ) {
                // TODO.
                continue;

            }
            $row = $cf_data[$cf_id];

            $in_params = array(
                'mcf_models_id'       => $models_id,
                'mcf_fieldset_id'     => $req['fieldset_id'],
                'mcf_fieldset_name'   => $fs_data['fs_name'],
                'mcf_name'            => $row['cf_name'],
                'mcf_format'          => $row['cf_format'],
                'mcf_format_element'  => $row['cf_format_element'],
                'mcf_help_text'       => $row['cf_help_text'],
                'mcf_element_value'   => $row['cf_element_value'],
                'mcf_encrypt'         => $row['cf_encrypt'],
                'mcf_required'        => $cfm_data[$cf_id]['cfm_required'],
                'mcf_order'           => $cfm_data[$cf_id]['cfm_order'],
                'mcf_created_at'      => date('Y-m-d H:i:s'),
                'mcf_udpated_at'      => date('Y-m-d H:i:s'),
            );


            if(is_array($cf)) {
                $in_params['mcf_value'] = implode(',', $cf);
            }else {
                $in_params['mcf_value'] = trim($cf);
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

        // TODO. 추가된 fields 추가하기

        $res = array();
        foreach($req['fields'] as $mcf_id=>$mcf) {
            $up_params = array(
                'mcf_udpated_at' => date('Y-m-d H:i:s'),
            );

            if(is_array($mcf)) {
                $up_params['mcf_value'] = implode(',', $mcf);
            }else {
                $up_params['mcf_value'] = trim($mcf);
            }

            if( ! $this->doUpdate($mcf_id, $up_params)->isSuccess()) {
                $res[] = array(
                    'up_params' => $up_params,
                    'error_msg' => $this->getErrorMsg()
                );
            }
        } // END_FOREACH
        return $res;
    }
}
