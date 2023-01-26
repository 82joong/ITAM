<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class PosSyncer{
	private $POS_API;		// POS API URL
	private $CI;
	private $SERVICE_ID;	// POS SERVICE ID
	private $SERVICE_NAME = ''; 

    function __construct($lib_params) {
		$this->CI =& get_instance();

        $this->POS_API = POS_URL.'/smapi';

        if( ! isset($lib_params['market']) || strlen($lib_params['market']) < 1 ) {
            echo 'Invalid POSSyncer Library Params!!';
            return;
        }

        $pos_service_map = $this->CI->config->item('pos_service_map');
		
        $shop_key = strtoupper($lib_params['market']);        
        if( ! isset($pos_service_map[$shop_key]) && $pos_service_map[$shop_key] < 1) {
            echo 'Invalid POS service key!!!! / Confirm pos service id'; 
            return;
        }
        $this->SERVICE_ID = $pos_service_map[$shop_key];
		$this->SERVICE_NAME = $lib_params['market'];
    }

    public function getServiceID() {
        return $this->SERVICE_ID;
    }

    public function getServiceName() {
        return $this->SERVICE_NAME;
    }


	/* 상품 POS에 생성. 
	 * SERVICE -> POS 
	*/
	public function createPOSGoods($gids=array()) {

		$opt_gids = $this->_gids_to_opt_gids($gids);
		if( ! is_array($opt_gids) || sizeof($opt_gids) < 1 ) return;

		$params = array();
		$params['service_id'] = $this->SERVICE_ID;
		$params['goods_id'] = $opt_gids;
		$this->CI->common->restful_curl($this->POS_API.'/create_pos_goods', http_build_query($params));
		$this->CI->common->logWrite('smpos_hub', print_r($params, true),'create_pos_goods');
		return;
	}

	/* 상품 POS에 업데이트. 
	 * SERVICE -> POS 
	*/
	public function updatePOSGoods($gids=array()) {

		$opt_gids = $this->_gids_to_opt_gids($gids);
		if( ! is_array($opt_gids) || sizeof($opt_gids) < 1 ) return; 

		$params = array();
		$params['service_id'] = $this->SERVICE_ID;
		$params['goods_id'] = $opt_gids;
		$this->CI->common->restful_curl($this->POS_API.'/update_pos_goods', http_build_query($params));
		$this->CI->common->logWrite('smpos_hub', print_r($params, true),'update_pos_goods');
		return;
	}


	// 부모상품 gids -> opt gids array 로 변환 :: POS 쪽에 service_goods_id 매칭
	private function _gids_to_opt_gids($gids) {

		$this->CI->load->library('elastic');
        $goods_map = $this->CI->elastic->get_opt_gids_map($this->SERVICE_NAME, $gids, $return_map=TRUE);
        if( sizeof($goods_map) < 1 ) {
            echo 'Fail Query';
            return;
        }
 
        $opt_gids = array();
        foreach($goods_map as $mg_id=>$mg) {
            $opt_gids = array_merge($opt_gids, array_values($mg));
        }
		return $opt_gids;
	}

   

    /*
    | -------------------------------------------------------------------
    | @POS에 상품이 존재 하는 지 map으로 리턴
    | -------------------------------------------------------------------
    |
	|
	|
	|	@return => 
	|		Array (
	|			[lafayette-12877231] => 1   ... POS에 존재하는 상품
	|			[lafayette-12877480] => 	... POS에 존재하지 않는 상품
	|		)
    |
    */


    public function pos_exists_map($market_index, $gids) {

        $this->CI->load->library('elastic');
        $goods_map = $this->CI->elastic->get_opt_gids_map($market_index, $gids, $return_map=TRUE);
        if( sizeof($goods_map) < 1 ) {
            echo 'Fail Query';
            return;
        }
 
        $opt_gids = array();
        foreach($goods_map as $mg_id=>$mg) {
            $opt_gids = array_merge($opt_gids, array_values($mg));
        }

        $api_params = array();
        $api_params['g_ids'] = $opt_gids;
        $api_params['service_id'] = $this->SERVICE_ID;
        //echo print_r($api_params);
        
        $api_res = $this->CI->common->restful_curl($this->POS_API.'/is_goods_existence', http_build_query($api_params));
        $api_res = json_decode($api_res, true);
        //echo print_r($api_res); //exit;
        
        if( ! isset($api_res['is_success']) || ! isset($api_res['exists_gids']) ) {
            echo 'Fail to connect pos !!!';
            return;
        }


        // 옵션상품 하나하나 체크는 하지 않음, 옵션중 하나라도 싱크이력 있으면 TRUE
        $res = array();
        foreach($goods_map as $gid=>$opt) {
            $is_sync = sizeof(array_diff($api_res['exists_gids'], $opt)) < sizeof($api_res['exists_gids']) ? TRUE : FALSE;
            $res[$gid] = $is_sync;
        }
        //echo print_r($res);
        return $res;

    }


  
}
?>
