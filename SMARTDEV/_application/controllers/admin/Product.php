<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include_once dirname(__FILE__).'/Base_admin.php';

class Product extends Base_admin {


	public function market_goods($shop) {
        
        $market_data = array(
            'nav_code'      => $shop,
            'index_name'    => ''
       );

        switch($shop) {

            case 'vvic':
                $this->load->helper('vvic');
                $market_data['index_name'] = ELASTIC_MARKET_GOODS_VVIC_INDEX;
                break;


            case 'atelier':
                $this->load->helper('atelier');
                $market_data['index_name'] = ELASTIC_MARKET_GOODS_ATELIER_INDEX;
                break;


            case 'lafayette':
                $this->load->helper('lafayette');
                $market_data['index_name'] = ELASTIC_MARKET_GOODS_LAFAYETTE_INDEX;
                break;


            case 'qeeboo':
                $this->load->helper('qeeboo');
                $market_data['index_name'] = ELASTIC_MARKET_GOODS_QEEBOO_INDEX;
                break;

        }

        $this->_market_logic($market_data);
    }


    private function _market_logic($market_data) {

        $req = $this->input->post();
		//echo print_r($req); //exit;


        $lib_params = array('market' => $market_data['nav_code']);
        $this->load->library('PosSyncer', $lib_params);


        $data = array();
        $data['nav_code'] = $market_data['nav_code'];

        $site_code = getMarket();
        $site_code_options = $this->common->genJqgridOption($site_code);
        $data['site_code_options'] = $site_code_options;

        // 카테고리 검색을 위해 구분자 SPLITER 로 추가
        $category_tree_map = getCategoryByName('name_en', '_%AND%_', true); // tree 형식으로 카테고리 노출
        //$data['category_options'] = $this->common->genJqgridOption($category_tree_map);
        $category_options = getCategoryToSelectOpt();
        $data['category_options'] = json_encode($category_options);
        $category = getCategoryByName('name_en');
        //echo print_r($category); exit;
        
		if(isset($req['mode']) && $req['mode'] == 'list') {

            $this->load->library('elastic');
            
            $params = array();

            //$index_name = ELASTIC_MARKET_GOODS_VVIC_INDEX;
            $index_name = $market_data['index_name'];

            $multiple_filters = array(
                'mg_name' => array(
                    'mg_trans_names.ko', 
                    'mg_trans_names.cn', 
                    'mg_trans_names.en', 
                ),
            );

            $params = $this->elastic->datatable_filter_to_params($req, $index_name, $multiple_filters);
            //echo print_r($params); //exit;

            $params['track_total_hits'] = true; // total default 10000 => 해제
            if($params['from'] >= 10000) {
                $error_msg = '해당 리스트는 [10,000]개 이상 항목을 지원하지 않습니다.'.PHP_EOL;
                $error_msg .= '검색 조건 추가를 통해 리스트의 범위를 줄여주세요!';

                $json_data = new stdClass;
                $json_data->rows = array();
                $json_data->error_msg = $error_msg;
                echo json_encode($json_data);
                return;
            }


            if(isset($request['sidx']) && strlen($request['sidx']) > 0) {
                $params['sort'] = array(
                    $request['sidx'] => array('order' => $request['sord']),
                );
            }
            //echo print_r($params); exit;
            $json_params = json_encode($params);
            //echo $json_params;    exit;
            
            $el_header = $this->elastic->get_auth_header();
            $el_url = ELASTIC_HOST.'/'.$index_name.'/_search';
			$el_result = $this->elastic->restful_curl($el_url, $json_params, 'GET', $timeout=10, $el_header);
            
			$el_result = json_decode($el_result, true);
            //echo print_r($el_result); exit;


            $json_data = new stdClass;
            $json_data->draw = $req['draw'];
            if(isset($el_result['error'])) {
                //echo 'QUERY ERROR';
                //$json_data->error_msg = $el_result['error']['type'];
                //$json_data->status  = $el_result['status'];

                $json_data->recordsTotal = 0;
                $json_data->recordsFiltered = 0;
                $json_data->data = array();
                echo json_encode($json_data);
                return;

            }else if(isset($el_result['hits']) && ($this->elastic->get_hits_total($el_result) > 0) === FALSE) {
                //echo 'ROWS 0';
                $json_data->recordsTotal = 0;
                $json_data->recordsFiltered = 0;
                $json_data->data = array();
                echo json_encode($json_data);
                return;
            }
		
			$mg_ids = array();
            foreach($el_result['hits']['hits'] as $k=>$r){
				$mg_ids[] = $r['_source']['mg_id'];
			}
			//echo print_r($mg_ids);


            $pos_service_id = $this->possyncer->getServiceID();
    
            $pos_exists_gids = array();
            if($pos_service_id > 0) {
        	    $pos_exists_gids = $this->possyncer->pos_exists_map($data['nav_code'], $mg_ids);
            }

			//echo print_r($pos_exists_gids); exit;
			
			$data = array();
            foreach($el_result['hits']['hits'] as $k=>$r){
                
				if (!isset($r['_source']['mg_import_enname']))
				$r['_source']['mg_import_enname'] = '';

                $r['_source']['mg_list_img'] = '<img src="'.$r['_source']['mg_list_img'].'" class="border-sucess rounded width-10">';
                $r['_source']['mg_quantity'] = number_format($r['_source']['mg_quantity']); 
                $r['_source']['mg_catepath_id'] = getCategoryToHTML($r['_source']['mg_catepath_id'], $category);

				if (isset($r['_source']['mg_trans_names.ko']))
				$r['_source']['mg_name'] = $r['_source']['mg_trans_names.ko'].'<br />('.$r['_source']['mg_name'].')';

                $mg_id = $r['_source']['mg_id'];
                if( isset($pos_exists_gids[$mg_id]) && $pos_exists_gids[$mg_id] == TRUE) {
                    $r['_source']['is_pos_sync'] = '<span class="badge badge-success badge-pill">YES</span>'; 
                }else {

                    $button_html = '<a href="javascript:sync_to_pos(\''.$mg_id.'\');" class="btn btn-sm btn-icon btn-danger">';
                    $button_html .= '<i class="fal fa-external-link"></i>';
                    $button_html .= '</a>';
                    $r['_source']['is_pos_sync'] = $button_html;
                }

                if( $r['_source']['mg_status'] == 1 ) $r['_source']['mg_status'] = 'YES';
                else $r['_source']['mg_status'] = 'NO';


                if( ! isset($r['_source']['mg_expired_time_date']) ) {
                    $r['_source']['mg_expired_time_date'] = '';
                }

				
				$data[] = $r['_source'];
            }
            //echo print_r($json_data);
			
            $count = $this->elastic->get_hits_total($el_result);

            $json_data->recordsTotal = $count;
            $json_data->recordsFiltered = $count;
            $json_data->data = $data;

            echo json_encode($json_data);
            return;
        }

		$this->_view('product/market_goods', $data);
    }


    private function _atelier() {


		$this->_view('product/market_goods', $data);
    }


    public function market_goods_detail($shop, $id=0) {
        if( strlen($id) < 1 ) {
            $this->common->alert('No Data');
            $this->common->locationhref('/'.SHOP_INFO_ADMIN_DIR.'/product/market_goods/'.$shop.'?keep=yes');
            return;
        }
        
        $this->load->library('elastic');
        $shop_index_data = array_flip($this->elastic->get_elastic_indexes());

        if( ! (strlen($shop) > 0 && in_array(strtoupper($shop), array_keys($shop_index_data))) ) {
            $this->common->alert('No SHOP');
            $this->common->locationhref('/'.SHOP_INFO_ADMIN_DIR);
            return;
        }
            
        $this->load->helper($shop);
        $market_data = array(
            'nav_code'      => $shop,
            'index_name'    => $shop_index_data[strtoupper($shop)],
            'option_fields' => getOptionFields(),
        );
		
        $assign = array();
        $assign = $market_data;
        $assign['pk'] = 'mg_id';
        $assign['mode'] = 'view';

        $params = array();
        $params['query']['bool']['must']['term']['mg_id']['value'] = $id;

        $json_params = json_encode($params);

        $el_header = $this->elastic->get_auth_header();
        $el_url = ELASTIC_HOST.'/'.$market_data['index_name'].'/_search';
        $el_result = $this->elastic->restful_curl($el_url, $json_params, 'GET', $timeout=10, $el_header);
        $el_result = json_decode($el_result, true);
        
		if(sizeof($el_result['hits']['hits']) < 1) {
            $this->common->alert('No Data');
            $this->common->locationhref('/'.SHOP_INFO_ADMIN_DIR.'/product/market_goods/'.$market_data['nav_code'].'?keep=yes');
            return;
        }
		
        $category = getCategoryByName('name_ko');
		$values = $el_result['hits']['hits'][0]['_source'];
		$assign['values'] = $values;
		
		// 카테고리 한글 치환
		for ($depth=0; $depth <=4; $depth++) {			
			
			if (isset($values['mg_cate'.$depth.'_id'])) {
				$assign['values']['mg_cate'.$depth.'_name.ko'] = str_replace('.', '', $category[$values['mg_cate'.$depth.'_id']]);
			}
		}
		
		$this->_view('/product/market_goods_detail', $assign);
    }


    public function  ajax_send_to_pos() {

		$req = $this->input->post();
		//echo print_r($req); exit;

        /*
		// DEBUG DATA
		$req = array(
			'shop' 	=> 'lafayette',
			'ids' 	=> array(
				"lafayette-16139024", 
				"lafayette-13704839", 
				"lafayette-14508833", 
				"lafayette-41169657",
				"lafayette-85020691",
			),
		);
        */

		// TODO DATA VALIDATION
        if( ! isset($req['shop']) || (! isset($req['ids'])) ) {
            echo 'Invalid Params!!'; 
            return;
        }

        
        if( ! is_array($req['ids']) ) {
            $req['ids'] = array($req['ids']);
        }


        $lib_params = array('market' => $req['shop']);
        $this->load->library('PosSyncer', $lib_params);
        $pos_service_id = $this->possyncer->getServiceID();
        //echo $pos_service_id.PHP_EOL; exit;
        if($pos_service_id < 1) {
            echo 'POS Service ID 가 지정된지 않은 서비스 입니다.'.PHP_EOL;
            echo 'POS Syncer 지원 불가 서비스';
            return;
        }


        $pos_exists_gids = $this->possyncer->pos_exists_map($req['shop'], $req['ids']);
        if( ! is_array($pos_exists_gids) || sizeof($pos_exists_gids) < 1) {
            echo 'Fail pos_exitsts_map Array Data';
            return;
        }
        //echo print_r($pos_exists_gids);

        $create_pos_gids = array();
        foreach($pos_exists_gids as $k=>$val) {
            if($val == FALSE) {
                $create_pos_gids[] = $k;
            }
        }
        if( ! is_array($create_pos_gids) || sizeof($create_pos_gids) < 1) {
            echo '(이미 등록된 상품 포함) POS로 등록할 상품이 없습니다.';
            return;
        }
        //echo print_r($create_pos_gids); exit;


        $this->possyncer->createPOSGoods($create_pos_gids);
		$msg = sizeof($create_pos_gids).'건 POS 연동'.PHP_EOL;
		$msg .= '등록된 상품은 POS에서 확인 가능합니다.';
		echo $msg;
		return;
    }

}
