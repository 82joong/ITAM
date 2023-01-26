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
 * MY_Config Class - CI_Config를 DB로 제어할 수도 있게끔 Config class로 확장
 *
 * @package MakeShop Platinum
 * @author zzong
 * @created 2016-09-07
 */
class MY_Config extends CI_Config
{

    private $_data    = array();
    private $_default = array();
    private $CI;

    /**
     * 설정값 메소드
     *
     * @author zzong
     * @param string $urn
     * @return mixed
     */
    public function get($urn)
    {
        $CI =& get_instance();
        list($cate, $ctrl, $var, $idx) = array_pad(explode('/', $urn), 4, '');
        if (empty($cate) || empty($ctrl)) {
            return '';
        }
        if (! isset($this->_data[$cate])) {
            $this->_data[$cate] = array();
        }
        if (! isset($this->_data[$cate][$ctrl])) {
            if (! count($this->_default)) {
                // shop_config 는 모든 category / controller / method 에 대한 기본값이 정의되어있으므로,
                // Lazy 패턴으로 최초 호출시 load 하도록 함.
                $CI->load->config('shop_config');
                $this->_default = $this->item('shop_config');
            }
            // 관리되지 않는 값으로 조회 들어왔을때
            if (! isset($this->_default[$cate]) || ! isset($this->_default[$cate][$ctrl])) {
                return '';
            }
            $CI->load->model('config_tb_model');
            $params = array('=' => array('cfg_cate' => $cate, 'cfg_ctrl' => $ctrl));
            $extra  = array('fields' => array('cfg_var', 'cfg_val', 'cfg_mixed'), 'order_by' => '');
            $list = $CI->config_tb_model->getList($params, $extra)->getData();
            for ($i = 0, $_d = array(), $_c = count($list); $i < $_c; $i++) {
                $_d[$list[$i]['cfg_var']] = $list[$i]['cfg_mixed'] === 'Y' ? json_decode($list[$i]['cfg_val'], true) : $list[$i]['cfg_val'];
            }
            // DB에 없는 값 config/shop_config.php 에 정의된 기본값으로 채우기.
            foreach (array_diff_key($this->_default[$cate][$ctrl], $_d) as $k => $v) {
                $_d[$k] = $v;
            }
            $this->_data[$cate][$ctrl] = $_d;
        }
        if (! empty($idx)) {
            return $this->_data[$cate][$ctrl][$var][$idx];
        }
        if (! empty($var)) {
            return $this->_data[$cate][$ctrl][$var];
        }
        return $this->_data[$cate][$ctrl];
    }

    /**
     * 설정 저장 메소드
     *
     * @author zzong
     * @param string $urn
     * @param mixed $data
     * @return void
     */
    public function setup($urn, $data)
    {
        list($cate, $ctrl) = explode('/', $urn);
        if (empty($cate) || empty($ctrl)) {
            return;
        }
        $CI =& get_instance();
        $CI->load->model('config_tb_model');
        $params = array('=' => array('cfg_cate' => $cate, 'cfg_ctrl' => $ctrl));
        $extra  = array('fields' => array('cfg_id', 'cfg_var', 'cfg_val', 'cfg_mixed'), 'order_by' => '');
        $list = $CI->config_tb_model->getList($params, $extra)->getData();
        /*
        $_before = $_after = array_values(
                array_filter(
                    $list,
                    function ($v, $k) use (&$data) {
                    if (false === ($_chk = array_key_exists($v['cfg_var'], $data))) {
                    return false;
                    }
                    if ($v['cfg_val'] === $data[$v['cfg_var']]) {
                    unset($data[$v['cfg_var']]);
                    return false;
                    }
                    return true;
                    },
                    ARRAY_FILTER_USE_BOTH
                    )
                );

        */

        $filter_list = array();
        foreach($list as $k => $v) {
            // db로 불러온 로우들 중 unit이 아닌 항목은 넘김
            if(array_key_exists($v['cfg_var'], $data) === false) {
                continue;
            }
            // 여기는 unit만 통과했음. 이제 같은 값인지 비교
            if($v['cfg_val'] === $data[$v['cfg_var']]) {
                unset($data[$v['cfg_var']]);
                continue;
            }

            $filter_list[] = $v;
        }
        $_before = $_after = $filter_list;

        // data로는 Insert로직 태울것이고, _after는 update할려고 변수세팅하는 것
        
        for ($i = 0, $_c = count($_after); $i < $_c; $i++) {
            $_after[$i]['cfg_val'] = $data[$_after[$i]['cfg_var']];
            if($CI->config_tb_model->doUpdate(
                    $_after[$i]['cfg_id'],
                    array(
                    'cfg_var'   => $_after[$i]['cfg_var'],
                    'cfg_val'   => is_array($_after[$i]['cfg_val']) ? json_encode($_after[$i]['cfg_val']) : $_after[$i]['cfg_val'],
                    'cfg_mixed' => is_array($_after[$i]['cfg_val']) ? 'Y' : 'N',
                    )
            )->isSuccess() == false) {
                if ($CI->common->isDeveloper()) {
                    $CI->common->alert('수정실패', $CI->config_tb_model->getErrorMsg());
                }
            }
            unset($data[$_after[$i]['cfg_var']]);
            if(isset($this->_data[$cate]) && isset($this->_data[$cate][$ctrl])) {
                unset($this->_data[$cate][$ctrl]);
            }
        }
        for ($i = 0, $_k = array_keys($data), $_c = count($_k); $i < $_c; $i++) {
            $_t = array(
                'cfg_var'   => $_k[$i],
                'cfg_val'   => is_array($data[$_k[$i]]) ? json_encode($data[$_k[$i]]) : $data[$_k[$i]],
                'cfg_mixed' => is_array($data[$_k[$i]]) ? 'Y' : 'N',
                );
                $CI->config_tb_model->doInsert($_t + array('cfg_cate' => $cate, 'cfg_ctrl' => $ctrl));
                $_after[] = $_t;
        }
    }
}
