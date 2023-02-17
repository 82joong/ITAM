<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Ip_class_tb_business extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->db_name = _SHOP_INFO_DATABASE_;
        $this->table   = strtolower(substr(__CLASS__, 0, -9));
        $this->fields  = $this->db->list_fields($this->table);
    }



    public function getLocationGroup() {

		$this->load->model('location_tb_model');

        $params = array();
        $params['join']['location_tb'] = 'ipc_location_id = l_id'; 
        $extras = array();
        $extras['fields'] = array('l_name', 'l_code', 'ipc_id', 'ipc_location_id', 'ipc_name', 'ipc_cidr');
        $extras['order_by'] = array('ipc_location_id ASC', 'ipc_id DESC');

        $ipc_data = $this->getList($params, $extras)->getData();

        $data = array();
        foreach($ipc_data as $ipc) {
            $data[] = array(
                'grp_name'  => $ipc['l_name'].' ['.$ipc['l_code'].']',
                'grp_id'    => $ipc['ipc_location_id'],
                'opt_name'  => $ipc['ipc_cidr'], 
                'opt_id'    => $ipc['ipc_id'], 
            );
        }
        $data = $this->common->getDataByDuplPK($data, 'grp_id');
        return $data;
    }


    public function getNameMap($ids=array()) {
        $params = array();

        if(sizeof($ids) > 0) {
            $params['in']['ipc_id'] = $ids;
        } 

        $extras = array();
        $extras['fields'] = array('ipc_id', 'ipc_name', 'ipc_cidr');
        $extras['order_by'] = array('ipc_id ASC');

        $rows = $this->getList($params, $extras)->getData();
        
        $data = array();
        foreach($rows as $v) {
            $data[$v['ipc_id']] = $v['ipc_name'].' ['.$v['ipc_cidr'].']';
        } 
        return $data;
    }


    public function checkIPinClass($req) {

        $json_data = array(
            'is_success' => FALSE,
            'msg'       => ''
        );

        if( ! isset($req['ip']) || strlen($req['ip']) < 1 ) {
            $json_data['msg'] = getAlertMsg('INVALID_SUBMIT');    
            return $json_data; 
        }

        $this->load->library('CIDR');   // 대문자
        if($this->cidr->validIP($req['ip']) !== $req['ip']) {
            $json_data['msg'] = 'Invalid IP Address.';
            return $json_data;
        }

		$this->load->model(array(
            'assets_model_tb_model',
            'ip_tb_model',
            'ip_class_tb_model'
        ));
        $this->load->business(array(
            'location_tb_business',
        ));


        $location_id = 0;
        // 자산위치에 따라 IP 할당 (IDRAC 172. 다른 지역에 동일 할당 존재)
        if(isset($req['assets_model_id']) && strlen($req['assets_model_id']) > 0) {
            $am_data = $this->assets_model_tb_model->get($req['assets_model_id'])->getData();
            $location_id = $am_data['am_location_id'];
        }

        // 중복 확인
        $params = array();
        if(isset($req['ip_id']) && strlen($req['ip_id']) > 0) {
            $params['!=']['ip_id'] = $req['ip_id'];
        }
        if( $location_id > 0 ) {
            $params['=']['ipc_location_id'] = $location_id;
            $params['join']['ip_class_tb'] = 'ip_class_id = ipc_id';
        }
        $params['=']['ip_address'] = $req['ip'];

        $cnt = $this->ip_tb_model->getCount($params)->getData();
        //echo $this->ip_tb_model->getLastQuery(); exit;
        if($cnt > 0) {
            $json_data['msg'] = getAlertMsg('DUPLICATE_IP');  
            return $json_data;
        }

        $location_data = $this->location_tb_business->getNameMap();
        //echo print_r($location_data);

        $params = array();
        // Valid IP 검증시, 해당 Category 내에 있는지 추가 정책 
        $cate_msg = '';
        if(isset($req['set_valid'])) {
            switch($req['set_valid']) {
                case 'people':
                    $cate_msg .= '( LOCAL )';
                    $params['=']['ipc_type'] = 'LOCAL';
                    //$params['in']['ipc_category'] = array('DIRECT', 'PRIVATE');
                    break;
                case 'vmservice':
                case 'direct':
                    $cate_msg .= '( PUBLIC or PRIVATE )';
                    $params['=']['ipc_type'] = 'IDC';
                    $params['in']['ipc_category'] = array('PUBLIC', 'PRIVATE');
                    break;
                case 'vmware':
                    $vmware_category = array('VMWARE', 'PUBLIC', 'PRIVATE');
                    $cate_msg .= '( '.implode(' or ', $vmware_category).' )';
                    $params['=']['ipc_type'] = 'IDC';
                    $params['in']['ipc_category'] = $vmware_category;
                    break;
                default:    // idrac, vmware
                    $cate_msg .= '( '.strtoupper($req['set_valid']).' )';
                    //$params['=']['ipc_type'] = 'IDC';
                    $params['=']['ipc_category'] = strtoupper($req['set_valid']);
                    break;
            }
        }else {
            // 기본값
            $params['=']['ipc_type'] = 'IDC';
            $params['in']['ipc_category'] = array('PUBLIC', 'PRIVATE');
        }

        // 자산위치에 따라 IP 할당 (IDRAC 172. 다른 지역에 동일 할당 존재)
        if( $location_id > 0 ) {
            $params['=']['ipc_location_id'] = $location_id;
        }
        //echo print_r($params);
        //exit;

        $extras = array();
        $row = $this->ip_class_tb_model->getList($params, $extras)->getData();
        //echo $this->ip_class_tb_model->getLastQuery(); exit;
        //echo print_r($row);

        foreach($row as $v) {
            $is_cidr = $this->cidr->IPisWithinCIDR($req['ip'], $v['ipc_cidr']); 
            if($is_cidr == true) {
                $json_data['is_success'] = true;  

                $v['ipc_location_id'] = $location_data[$v['ipc_location_id']];
                $json_data['msg'] = $v;  

                return $json_data;
            }
        }

        $json_data['msg'] = '등록된 '.$cate_msg.' IP Class 에 포함되지 않은 값입니다. CIDR 등록이 필요합니다.';  
        return $json_data;
    } 


    public function genIPUsedPie($ipc_data, $used_cnt=0, $title='TOTAL', $type='normal') {
    
        $this->load->library('CIDR');
        $ipc = $this->cidr->cidrToRange($ipc_data['ipc_cidr']);    
        $ip_total = (ip2long($ipc[1]) - ip2long($ipc[0])) + 1;
        //echo $ip_total; exit;
        $pct = ($used_cnt / $ip_total) * 100;

        $class = array(
            'border' => '',
            'color'  => '',
            'scale'  => ''
        );
        if($type == 'normal') {
            $class = array(
                'border' => 'border-faded border-top-0 border-bottom-0 pl-3 pr-3',
                'color'  => 'color-danger-500',
                'scale'  => 'data-scalelength="2"'
            );
        }
        $ip_total = $used_cnt.'/'.$ip_total;

        $pie_chart_html = '<div class="subheader-block d-none d-sm-flex align-items-center '.$class['border'].'">';
        $pie_chart_html .= '<div class="d-inline-flex flex-column justify-content-center mr-3">';
        $pie_chart_html .= '<span class="fw-300 fs-xs d-block opacity-50"><small>'.$title.'</small></span>';
        $pie_chart_html .= '<span class="fw-500 fs-xl d-block color-info-500">'.$ip_total.'</span>';
        $pie_chart_html .= '</div>';
        $pie_chart_html .= '<span class="js-easy-pie-chart position-relative d-flex align-items-center justify-content-center '.$class['color'].'" data-percent="'.$pct.'" '.$class['scale'].'>';
        $pie_chart_html .= '<span class="js-percent d-flex align-items-center justify-content-center position-absolute pos-left pos-right pos-toppos-bottom">'.round($pct).'</span>';

        $pie_chart_html .= '</div>';

        return $pie_chart_html;
    } 
}
