<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH.'libraries/REST_Controller.php';


/**
 * @OA\Schema()
 */

class Goods extends REST_Controller {

    private $ip;

    // RESTAPI 검색 조건 params 매핑 & 기본 검색 조건 
    // ['market', 'page', 'length'] 는 제외
    private $field_mapper = array(
        'id'            =>  array( 'field' => 'mg_id',                  'oper'  => 'in' ),
        'shop'          =>  array( 'field' => 'mg_market',              'oper'  => 'eq' ),
        'name'          =>  array( 'field' => 'mg_name',                'oper'  => 'cn' ),
        'name_en'       =>  array( 'field' => 'mg_trans_names.en',      'oper'  => 'cn' ),
        'name_ko'       =>  array( 'field' => 'mg_trans_names.ko',      'oper'  => 'cn' ),
        'name_cn'       =>  array( 'field' => 'mg_trans_names.cn',      'oper'  => 'cn' ),
        'import'        =>  array( 'field' => 'mg_import_enname',       'oper'  => 'cn' ),
        'description'   =>  array( 'field' => 'mg_description',         'oper'  => 'cn' ),
        'cate1'         =>  array( 'field' => 'mg_cate1_id',            'oper'  => 'in' ),
        'cate2'         =>  array( 'field' => 'mg_cate2_id',            'oper'  => 'in' ),
        'cate3'         =>  array( 'field' => 'mg_cate3_id',            'oper'  => 'in' ),
        'size'          =>  array( 'field' => 'mg_size',                'oper'  => 'in' ),
        'color'         =>  array( 'field' => 'mg_color',               'oper'  => 'in' ),
        'price'         =>  array( 'field' => 'mg_price',               'oper'  => 'bt' ),
        'status'        =>  array( 'field' => 'mg_status',              'oper'  => 'eq' ),
        'is_active'     =>  array( 'field' => 'mg_is_active',           'oper'  => 'eq' ),
        'ni_items'      =>  array( 'field' => 'mg_market_goods_code',   'oper'  => 'ni' ),
        'ni_shop_name'  =>  array( 'field' => 'mg_shop_name_keyword',   'oper'  => 'ni' ),
        'created_at'    =>  array( 'field' => 'mg_created_at',          'oper'  => 'bt' ),
        'updated_at'    =>  array( 'field' => 'mg_updated_at',          'oper'  => 'bt' ),
        'expired_at'    =>  array( 'field' => 'mg_expired_time_date',   'oper'  => 'bt' )
    );

    function __construct() {
        parent::__construct();

        $allow_ips = array(
            '14.129.31.152',    // phoenixq
            '14.129.31.214',    // bwwjh
            '14.129.31.215',    // joong
            '14.129.31.216',    // lsyoung
            '14.129.31.217',    // bigtuna
            '14.129.31.136',    // yun3019
            '14.129.31.137',    // maginc3
            '14.129.31.234',    // yun2kc

            // TEST SERVER
            '14.129.120.215',  // testdev-ham
            '14.129.120.216',  // testdev-ham
            '14.129.120.229',  // testdev15

            '39.7.231.72',     // hamt macbook. todo. delete

            // REAL SERVER
            '14.129.120.183',   // allmall
            '14.129.120.184',   // vita-goods
            '14.129.120.155',   // shiptob
            '14.129.120.200',   // taillist

            // ADD REAL SERVER Lafayette
        );

        $ip_idxs = array('REMOTE_ADDR', 'VPNIP');

        $ip = '';
        foreach($ip_idxs as $idx) {
            if(array_key_exists($idx, $_SERVER)) {
                $ip = $_SERVER[$idx];
                break;
            }
        }
        if(strlen($ip) < 1 || !in_array($ip, $allow_ips)) {
            $this->response([
                'status'    => false, 
                'message'   => 'UNAUTHORIZED'
            ], REST_Controller::HTTP_UNAUTHORIZED);
            return;
        }

        $this->load->library('elastic');


        //$this->ip = $ip;
        //$this->common->logWrite('dev_access_log', $ip, $this->router->fetch_method());

        // TODO. [로그저장하기] elastic insert log 

        /*
        $header = $this->input->request_headers();
        echo print_r($header);
        $get_params = $this->input->get();
        echo print_r($get_params);
        $post_params = $this->input->post();
        echo print_r($post_params);
        echo $this->input->method().PHP_EOL;
        echo $this->router->fetch_method().PHP_EOL;
        echo $this->input->ip_address().PHP_EOL;
        exit;
        */
    }


    /**
     *  마켓에 따라 사용 필드가 다르므로 구분하여 정의 
     */
    private function _init_market_field_mapper($market) {

        switch(strtoupper($market)) {
            case 'QEEBOO':
                $this->field_mapper = array(
                    'id'            =>  array( 'field' => 'mg_id',                  'oper'  => 'in' ),
                    'name'          =>  array( 'field' => 'mg_name',                'oper'  => 'cn' ),
                    'name_en'       =>  array( 'field' => 'mg_trans_names.en',      'oper'  => 'cn' ),
                    'name_ko'       =>  array( 'field' => 'mg_trans_names.ko',      'oper'  => 'cn' ),
                    'name_cn'       =>  array( 'field' => 'mg_trans_names.cn',      'oper'  => 'cn' ),
                    'import'        =>  array( 'field' => 'mg_import_enname',       'oper'  => 'cn' ),
                    'description'   =>  array( 'field' => 'mg_description',         'oper'  => 'cn' ),
                    'cate0'         =>  array( 'field' => 'mg_cate0_id',            'oper'  => 'in' ),
                    'cate0_name'    =>  array( 'field' => 'mg_cate0_name',          'oper'  => 'cn' ), 
                    'price'         =>  array( 'field' => 'mg_price',               'oper'  => 'bt' ),
                    'status'        =>  array( 'field' => 'mg_status',              'oper'  => 'eq' ),
                    'is_active'     =>  array( 'field' => 'mg_is_active',           'oper'  => 'eq' ),
                    'ni_items'      =>  array( 'field' => 'mg_market_goods_code',   'oper'  => 'ni' ),
                    'created_at'    =>  array( 'field' => 'mg_created_at',          'oper'  => 'bt' ),
                    'updated_at'    =>  array( 'field' => 'mg_updated_at',          'oper'  => 'bt' ),
                );
                break;
            default:
                break;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | TEST :: RESTful API 
    |--------------------------------------------------------------------------
    |
    */
    // http://crawl.82joong.hamt.kr/api/goods/test?a=test&b=20201212&c=123.3


    /**
     * @OA\Get(
     *     path="/api/goods/test",
     *     @OA\Response(
     *       response="200", 
     *       description="An test resource"
     *     ),
     *     @OA\Response(
     *       response="default", 
     *       description="An ""unexpected"" Error"
     *     )
     * )
     */
    public function test_get() {
        $req = $this->input->get();
        //$this->response([], REST_Controller::HTTP_OK);

        $this->set_response([
            'status' => FALSE,
            'message' => 'Response could not be found'
        ], REST_Controller::HTTP_NOT_FOUND);
    }
    /**
     * @OA\Post(
     *     path="/api/goods/test",
     *     @OA\Response(response="200", description="An test resource")
     * )
     */
    public function test_post() {
        $this->response([
            'status'    => false, 
            'message'   => 'METHOD NOT ALLOWED'
        ], REST_Controller::HTTP_METHOD_NOT_ALLOWED);
    }
    /**
     * @OA\Put(
     *     path="/api/goods/test",
     *     @OA\Response(response="200", description="An test resource")
     * )
     */
    public function test_put() {
        $this->response([
            'status'    => false, 
            'message'   => 'METHOD NOT ALLOWED'
        ], REST_Controller::HTTP_METHOD_NOT_ALLOWED);
    }
    public function test_delete() {
        $this->response([
            'status'    => false, 
            'message'   => 'METHOD NOT ALLOWED'
        ], REST_Controller::HTTP_METHOD_NOT_ALLOWED);
    }




 /*
    |--------------------------------------------------------------------------
    | COLOR :: RESTful API 
    |--------------------------------------------------------------------------
    |
    */
    // http://crawl.82joong.hamt.kr/api/goods/test?a=test&b=20201212&c=123.3


    
    public function color_get() {
        $this->response([
            'status'    => false, 
            'message'   => 'METHOD NOT ALLOWED'
        ], REST_Controller::HTTP_METHOD_NOT_ALLOWED);
    }
    public function color_post() {

        $req = $this->input->post();
     
        if( ! isset($req['market']) || strlen($req['market']) < 1 ) {
            $this->response([
                'status'    => false, 
                'message'   => 'BAD REQUEST'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        // daemon VVIC/color 에서 주기적으로 생성된 color 정보
        $filepath = DISPLAY_PATH.'/category'; 
        $filename = strtolower($req['market']).'_color.info';
        $fullpath = $filepath.'/'.$filename;

        if( ! file_exists($fullpath) ) {
            // todo. 
        }

        $res = file_get_contents($fullpath);
        $res = unserialize($res);

        $this->response($res, REST_Controller::HTTP_OK);
    }
    public function color_put() {
        $this->response([
            'status'    => false, 
            'message'   => 'METHOD NOT ALLOWED'
        ], REST_Controller::HTTP_METHOD_NOT_ALLOWED);
    }
    public function color_delete() {
        $this->response([
            'status'    => false, 
            'message'   => 'METHOD NOT ALLOWED'
        ], REST_Controller::HTTP_METHOD_NOT_ALLOWED);
    }
    


    /*
    |--------------------------------------------------------------------------
    | [POST Method] 엘라스틱 상품 리스트
    |--------------------------------------------------------------------------
    |
    | :: 검색조건
    |   
    |   1. 뷴류/카테고리 1차/2차/3차
    |   2. 색상 : blue, red, ....
    |   3. 크기/사이즈
    |   4. 스타일 : 모던, 댄디, 스트리트, 등
    |   5. 시즌 : 봄, 여름, 가을, 겨울
    |   6. 가격
    |   7. 이미지 검색
    |   8. 상품명 검색
    |
    |   9. 품 id 복수 검색
    |   10.
    |   
    | :: 필독!!!! 검색 조건 항목 이랑 실제 데이터 매칭 
    |   
    |   - @private $field_mapper 데이터 확인
    |   - 검색 항목 추가시, 키 추가 필요 
    |       ex) style, season
    |   - 항목별 oper [in] 것들 Array 검색 지원 
    |       ex) 'size' => array('S', 'M') :: (mg_size='S' OR mg_size='M')
    |   - @multiple_filters 내에 멀티플 필드검색 정의
    |       ex) 'mg_name' => array('mg_trans_names.ko', 'mg_inport_') 
    |
    | @req = array(
    |           'market'  => 'vvic' 
    |           'id'      => array(),
    |           'cate1'   => array( 1, 2 ),
    |           'cate2'   => array(),
    |           'cate3'   => array(),
    |           'price'     => array('from' => 20, 'to' => 30),
    |           'name_en'   => 'Dress',
    |           'import'    => 'Dress',
    |           'search'    => 'Dress',
    |           'size'      => array('S', 'M')
    |           'color'     => array('白色')
    |           'style'   => array(),       // todo.
    |           'season'  => array(),       // todo.
    |           'price'   => array(
    |               'from'  =>  100      
    |               'to'    =>  200
    |           ),
    |           'name'    => 'search item name',
    |           'search'  => 'search' in all items,
    | );
    |
    | @return 
    |
    */
    public function list_get() {
        $this->response([
            'status'    => false, 
            'message'   => 'METHOD NOT ALLOWED'
        ], REST_Controller::HTTP_METHOD_NOT_ALLOWED);
    }


    /**
     *  마켓용 list
     */
    private function list_post_qeeboo($req) {
        
        // Validation @req
        $req_is_validation = TRUE;
        $mapper_data = $this->field_mapper;
        foreach($req as $k=>&$v) {
            
            if( $k == 'market' || $k == 'page' || $k == 'length' ) continue;
            
            // 사용자가 이미 연동한 상품은 제외하고 Get goods
            if($k == 'ni_items') {
                $v = explode('-', $v);
            } 

            if( ! isset($mapper_data[$k]) ) {
                $req_is_validation = FALSE;
            } else {
                switch($mapper_data[$k]['oper']) {
                    case 'bt':
                        if( ! is_array($v) ) $req_is_validation = FALSE;
                    break;
                }
            }
        }

        if($req_is_validation == FALSE) {
            $this->response([
                'status'    => false, 
                'message'   => 'NOT SUPPORT REQUEST'
            ], REST_Controller::HTTP_BAD_REQUEST);
            //exit;
        }

        $index_name = $this->_get_index_name($req['market']);
        $filters = $this->_query_to_mapper($req);
        //echo print_r($filters); exit;


        // 상품명 검색에 대한 Multiple fields 검색 묶음
        $multiple_filters = array(
             'mg_name' => array(
                 'mg_trans_names.en', 'mg_trans_names.cn', 'mg_trans_names.ko', 'mg_import_enname'
             ),
             'mg_cate0_name' => array(
                 'mg_trans_cate0_name.en', 'mg_trans_cate0_name.ko',
             ),
        );

        $source_fields = array(
            'mg_id', 'mg_market_goods_code',
            'mg_name', 'mg_trans_names.cn', 'mg_trans_names.en', 'mg_trans_names.ko', 'mg_option_display_text',
            'mg_import_enname', 'mg_vendor_keyword',
            'mg_price', 'mg_quantity', 'mg_url_key', 'mg_options', 'mg_display_price_integer', 
            'mg_list_img', 'mg_view_imgs', 'tags', 'mg_weight_kg', 'mg_weight_lbs', 'mg_is_active',
            'mg_description',
            'mg_cate0_id', 
            'mg_cate0_name', 'mg_trans_cate0_name.en', 'mg_trans_cate0_name.ko', 'mg_status',
            'mg_is_active', 'mg_has_options', 'mg_updated_at', 'mg_created_at',
        );
        $params = $this->elastic->datatable_filter_to_params($filters, $index_name, $multiple_filters, $source_fields);
        //echo print_r($params); exit;
        $json_params = json_encode($params);
        //echo $json_params; exit;


        $el_header = $this->elastic->get_auth_header();
        $el_url = ELASTIC_HOST.'/'.$index_name.'/_search';

        $el_result = $this->elastic->restful_curl($el_url, $json_params, 'GET', $timeout=10, $el_header);
        $el_result = json_decode($el_result, true);

        if(isset($el_result['error'])) {
            $this->response([
                'status'    => false, 
                'message'   => 'BAD REQUEST'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }else if(isset($el_result['hits']) && ($this->elastic->get_hits_total($el_result) > 0) === FALSE) {
            $this->response([
                'status'    => true, 
                'message'   => 'NO CONTENT'
            ], REST_Controller::HTTP_NO_CONTENT);
        } else {
            // 전체 count도 같이 넣어서 리턴함.
            $this->response(
                $el_result['hits'], 
                REST_Controller::HTTP_OK
            );
        }

    }

    public function list_post() {
    //public function list_get() {

        $req = $this->input->post();

        // 마켓 필터 및 구현된 메서드 있으면 호출
        if(isset($req['market'])) {
            $this->_init_market_field_mapper($req['market']);

            $method_name = __FUNCTION__.'_'.strtolower($req['market']);

            if(method_exists($this, $method_name)) {
                return $this->{$method_name}($req);
            }
        }


        /* DEBUG data
        $req = array(
            'market' => 'vvic',
            'cate1' => 1,
            'cate2' => '',
            'cate3' => '',
            'size'=> 'M',
            'color' => '',
            'name' => array('여름', '청바지'),
            'ni_items' => '5ed90f7092a095000196db6a-5ed8d70420576c00016523a9',
            'page' => 1,
            'length' => 60,
            'description' => ' src',
            'order' => array(
                'column' => 'name_ko',
                'dir'    => 'desc'
            )
        );
        //echo print_r($req); exit;
        */


        // Validation @req
        $req_is_validation = TRUE;
        $mapper_data = $this->field_mapper;
        foreach($req as $k=>&$v) {
            
            if( $k == 'market' || $k == 'page' || $k == 'length' ) continue;

            
            // 사용자가 이미 연동한 상품은 제외하고 Get goods
            if($k == 'ni_items') {
                $v = explode('-', $v);
            } 


            if( ! isset($mapper_data[$k]) ) {
                $req_is_validation = FALSE;
            } else {
                switch($mapper_data[$k]['oper']) {
                    case 'bt':
                        if( ! is_array($v) ) $req_is_validation = FALSE;
                    break;
                }
            }
        }

        if($req_is_validation == FALSE) {
            $this->response([
                'status'    => false, 
                'message'   => 'NOT SUPPORT REQUEST'
            ], REST_Controller::HTTP_BAD_REQUEST);
            //exit;
        }


        $index_name = $this->_get_index_name($req['market']);
        $filters = $this->_query_to_mapper($req); 
        // echo print_r($filters); exit;


        // 상품명 검색에 대한 Multiple fields 검색 묶음
        $multiple_filters = array(
             'mg_name' => array(
                 'mg_trans_names.en', 'mg_trans_names.cn', 'mg_trans_names.ko', 'mg_import_enname'
             )
        );
        $source_fields = array(
            'mg_id', 'mg_market', 'mg_market_goods_code',
            'mg_name', 'mg_trans_names.cn', 'mg_trans_names.en', 'mg_trans_names.ko',
            'mg_price', 'mg_quantity', 'mg_url_key', 'mg_options', 'mg_size', 'mg_color',
            'mg_list_img', 'mg_weight_kg', 'mg_weight_lbs', 'mg_is_active',
            'mg_cate1_id', 'mg_cate2_id', 'mg_cate3_id', 'mg_catepath_id',
            'mg_cate1_name', 'mg_cate2_name', 'mg_cate3_name', 'mg_status',
            'mg_is_active', 'mg_has_options', 'mg_updated_at', 'mg_created_at', 'mg_expired_time_date'
        );
        $params = $this->elastic->datatable_filter_to_params($filters, $index_name, $multiple_filters, $source_fields);
        //echo print_r($params); //exit;
        $json_params = json_encode($params);
        //echo $json_params; exit;


        $el_header = $this->elastic->get_auth_header();
        $el_url = ELASTIC_HOST.'/'.$index_name.'/_search';
        $el_result = $this->elastic->restful_curl($el_url, $json_params, 'GET', $timeout=10, $el_header);
        $el_result = json_decode($el_result, true);
        //echo print_r($el_result); exit;

        if(isset($el_result['error'])) {
            $this->response([
                'status'    => false, 
                'message'   => 'BAD REQUEST'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }else if(isset($el_result['hits']) && ($this->elastic->get_hits_total($el_result) > 0) === FALSE) {
            $this->response([
                'status'    => true, 
                'message'   => 'NO CONTENT'
            ], REST_Controller::HTTP_NO_CONTENT);
        } else {
            $this->response(
                $el_result['hits']['hits'], 
                REST_Controller::HTTP_OK
            );
        }
    }
    public function list_put() {
        $this->response([
            'status'    => false, 
            'message'   => 'METHOD NOT ALLOWED'
        ], REST_Controller::HTTP_METHOD_NOT_ALLOWED);
    }
    public function list_delete() {
        $this->response([
            'status'    => false, 
            'message'   => 'METHOD NOT ALLOWED'
        ], REST_Controller::HTTP_METHOD_NOT_ALLOWED);
    }


    /*
    |--------------------------------------------------------------------------
    | [POST Method] 엘라스틱 상품 상세
    |--------------------------------------------------------------------------
    |
    | @req = array(
    |           market => 'vvic' 
    |           id     => '5ed839a7b033a20001ebc506'
    | );
    |
    | @return 
    |
    */

    // GET Method
    public function detail_get() {
        $this->response([
            'status'    => false, 
            'message'   => 'METHOD NOT ALLOWED'
        ], REST_Controller::HTTP_METHOD_NOT_ALLOWED);
    }
    // POST Method
    public function detail_post() {

        $req = $this->input->post();


        /* DEBUG Data
        $req = array(
            'market' => 'vvic',
             'id'=> '5ed91a1b4fab35000114e714'
        );
        */
            
        if( ! isset($req['market']) || strlen($req['market']) < 1 ) {
            $this->response([
                'status'    => false, 
                'message'   => 'BAD REQUEST'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
        if( ! isset($req['market']) || strlen($req['market']) < 1 ) {
            $this->response([
                'status'    => false, 
                'message'   => 'BAD REQUEST'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $index_id = strtolower($req['market']).'-'.$req['id'];
        $index_name = $this->_get_index_name($req['market']);
        
        $el_header = $this->elastic->get_auth_header();
        $el_url = ELASTIC_HOST.'/'.$index_name.'/_doc/'.$index_id;
        $el_result = $this->elastic->restful_curl($el_url, '', 'GET', $timeout=10, $el_header);
        $el_result = json_decode($el_result, true);
        //echo print_r($el_result); //exit;

        if( $el_result['found'] == FALSE ) {
            $this->response([
                'status'    => true, 
                'message'   => 'NO CONTENT'
            ], REST_Controller::HTTP_NO_CONTENT);
            die();
        }


        $category_cn_data = array(
                $el_result['_source']['mg_cate1_name'],
                $el_result['_source']['mg_cate2_name'],
                $el_result['_source']['mg_cate3_name'],
        );

        // 번역
        $params = array(
            'from'  => 'zh',    // 중국어
            'to'    => array('en', 'ko'),
            'text'  => $category_cn_data,
        );
        

        // 색상 번역 추가
        $color_cn_data = array();
        if( isset($el_result['_source']['mg_color']) && sizeof($el_result['_source']['mg_color']) > 0 ) {
            $color_cn_data = $el_result['_source']['mg_color'];
            $params['text'] = array_merge($params['text'], $color_cn_data);
        }

        /*
        // 사이즈 번역 추가
        $color_cn_data = array();
        if( isset($el_result['_source']['mg_size']) && sizeof($el_result['_source']['mg_size']) > 0 ) {
            $size_cn_data = $el_result['_source']['mg_size'];
            $params['text'] = array_merge($params['text'], $size_cn_data);
        }
        */

        $trans_res = $this->common->restful_curl(TRANSLATE_API_URL, http_build_query($params), 'POST');
        $trans_data = json_decode($trans_res, true);
        //echo print_r($trans_data); exit;

        if($trans_data['is_success'] == TRUE) {
            foreach($category_cn_data as $k=>$cn) {
                $new_name = 'mg_cate'.($k+1).'_name';
                $el_result['_source'][$new_name.'.ko'] = $trans_data['result'][$k]['ko'];
                $el_result['_source'][$new_name.'.en'] = $trans_data['result'][$k]['en'];
            }
            foreach($color_cn_data as $k=>$cn) {
                $trans_key = $k + 3;
                $el_result['_source']['mg_color.ko'][$cn] = $trans_data['result'][$trans_key]['ko'];
                $el_result['_source']['mg_color.en'][$cn] = $trans_data['result'][$trans_key]['en'];
            }
        }else {
            foreach($category_cn_data as $k=>$cn) {
                $new_name = 'mg_cate'.($k+1).'_name';
                $el_result['_source'][$new_name.'.ko'] = $cn;
                $el_result['_source'][$new_name.'.en'] = $cn;
            }
            foreach($color_cn_data as $k=>$cn) {
                $trans_key = $k + 3;
                $el_result['_source']['mg_color.ko'][$cn] = $cn;
                $el_result['_source']['mg_color.en'][$cn] = $cn;
            }
        }
        //echo print_r($el_result); exit;

        $this->response($el_result['_source'], REST_Controller::HTTP_OK);
    }

    // PUT Method
    public function detail_put() {
        $this->response([
            'status'    => false, 
            'message'   => 'METHOD NOT ALLOWED'
        ], REST_Controller::HTTP_METHOD_NOT_ALLOWED);
    }

    // DELETE Method
    public function detail_delete() {
        $this->response([
            'status'    => false, 
            'message'   => 'METHOD NOT ALLOWED'
        ], REST_Controller::HTTP_METHOD_NOT_ALLOWED);
    }



    /*
    |--------------------------------------------------------------------------
    | STATUS :: RESTful API 
    |--------------------------------------------------------------------------
    |
    */
    // http://crawl.82joong.hamt.kr/api/goods/status


    /**
     * @OA\Get(
     *     path="/api/goods/status",
     *     @OA\Response(
     *       response="200", 
     *       description="Confirm Goods status"
     *     ),
     *     @OA\Response(
     *       response="default", 
     *       description="An ""unexpected"" Error"
     *     )
     * )
     */
    public function status_get() {
        $req = $this->input->get();
        //$this->response([], REST_Controller::HTTP_OK);

        $this->set_response([
            'status' => FALSE,
            'message' => 'Response could not be found'
        ], REST_Controller::HTTP_NOT_FOUND);
    }
    /**
     * @OA\Post(
     *     path="/api/goods/status",
     *     @OA\Response(response="200", description="An status resource")
     * )
     */
    public function status_post() {
        /*
        $this->response([
            'status'    => false, 
            'message'   => 'METHOD NOT ALLOWED'
        ], REST_Controller::HTTP_METHOD_NOT_ALLOWED);
        */

        $req = $this->input->post();
     
        if( ! isset($req['market']) || strlen($req['market']) < 1 ) {
            $this->response([
                'status'    => false, 
                'message'   => 'BAD REQUEST'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        
        // Validation @ids
        if( ! isset($req['ids']) || ! is_array($req['ids']) || sizeof($req['ids']) < 1 ) {
            $this->response([
                'status'    => false, 
                'message'   => 'BAD REQUEST'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
        

        $params = array();
        $params['_source'] = array('mg_id', 'mg_market_goods_code' ,'mg_status');
        if(isset($req['fields']) && sizeof($req['fields']) > 0) {
            $params['_source'] = $req['fields'];
        }
        $params['query']['bool']['must'] = array(
            'terms' => array(
                'mg_market_goods_code' => $req['ids']
            )
        );
        //echo json_encode($params);
        $params = json_encode($params);


        $index_name = $this->_get_index_name($req['market']);
        
        $el_header = $this->elastic->get_auth_header();
        $el_url = ELASTIC_HOST.'/'.$index_name.'/_search';
        $el_result = $this->elastic->restful_curl($el_url, $params, 'GET', $timeout=10, $el_header);
        $el_result = json_decode($el_result, true);
        //echo print_r($el_result); //exit;

        if(isset($el_result['error'])) {
            $this->response([
                'status'    => false, 
                'message'   => 'BAD REQUEST'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }else if(isset($el_result['hits']) && ($this->elastic->get_hits_total($el_result) > 0) === FALSE) {
            $this->response([
                'status'    => true, 
                'message'   => 'NO CONTENT'
            ], REST_Controller::HTTP_NO_CONTENT);
        } else {
                
            $el_res = $el_result['hits']['hits']; 

            $res = array();
            if(sizeof($el_res) > 0) {
                foreach($el_res as $k=>$v) {
                    $field = $v['_source'];
                    $res[$field['mg_market_goods_code']] = $field;
                }
            }
            //echo print_r($res);
            $this->response($res, REST_Controller::HTTP_OK);
        }
    }
    /**
     * @OA\Put(
     *     path="/api/goods/status",
     *     @OA\Response(response="200", description="An status resource")
     * )
     */
    public function status_put() {
        $this->response([
            'status'    => false, 
            'message'   => 'METHOD NOT ALLOWED'
        ], REST_Controller::HTTP_METHOD_NOT_ALLOWED);
    }
    public function status_delete() {
        $this->response([
            'status'    => false, 
            'message'   => 'METHOD NOT ALLOWED'
        ], REST_Controller::HTTP_METHOD_NOT_ALLOWED);
    }



    /*
    |--------------------------------------------------------------------------
    | CATEGORY :: RESTful API 
    |--------------------------------------------------------------------------
    |
    */
    public function category_get() {
        $this->response([
            'status'    => false, 
            'message'   => 'METHOD NOT ALLOWED'
        ], REST_Controller::HTTP_METHOD_NOT_ALLOWED);
    }
    public function category_post() {

        $req = $this->input->post();
     
        if( ! isset($req['market']) || strlen($req['market']) < 1 ) {
            $this->response([
                'status'    => false, 
                'message'   => 'BAD REQUEST'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        
        $filepath = DISPLAY_PATH.'/category'; 
        $filename = strtolower($req['market']).'_category.info';
        $fullpath = $filepath.'/'.$filename;

        if( ! file_exists($fullpath) ) {
            // todo. 
        }

        $res = file_get_contents($fullpath);
        $res = unserialize($res);

        $this->response($res, REST_Controller::HTTP_OK);
    }
    public function category_put() {
        $this->response([
            'status'    => false, 
            'message'   => 'METHOD NOT ALLOWED'
        ], REST_Controller::HTTP_METHOD_NOT_ALLOWED);
    }
    public function category_delete() {
        $this->response([
            'status'    => false, 
            'message'   => 'METHOD NOT ALLOWED'
        ], REST_Controller::HTTP_METHOD_NOT_ALLOWED);
    }





    // #private
    private function _get_index_name($market) {

        $indexes = $this->elastic->get_elastic_indexes();
        $indexes = array_flip($indexes);
        $market = strtoupper($market);
        $index_name = $indexes[$market];
        
        return $index_name;
    }

    private function _query_to_mapper($req) {

        $mapper_data = $this->field_mapper;

        // DEFAULT FILTERS
        $filters = array(
            'columns'   => array(),
            //'order'     => array( array('column' => 0, 'dir' => 'desc') ),
            'order'     => array(),
            'start'     => 1,       // page
            'length'    => 20,
            'mode'      => 'list'
        );

        // transfer @req -> @filters
        $_SPLITER = $this->elastic->get_query_spliter();
        foreach($mapper_data as $field=>$data) {

            $search_value = '';
            if( isset($req[$field]) ) {

                $op = $data['oper'];

                if( is_array($req[$field]) ) {
                    if( sizeof($req[$field]) > 0 ) {

                        switch($op) {
                            case 'bt':
                                $search_value .= $op.$_SPLITER['op'].$req[$field]['from'].$_SPLITER['and'].$req[$field]['to'];
                            break;

                            default:
                                $search_value .= $op.$_SPLITER['op'].implode($_SPLITER['and'], $req[$field]);
                            break;
                        }
                    }
                } else {
                    if( strlen($req[$field]) > 0 ) {

                        // IN 조건일 때, 문자열로 하나 들어오는 경우 처리
                        if( $op == 'in' ) $op = 'eq';
                        
                        $search_value .= $op.$_SPLITER['op'].$req[$field];
                    }
                }
            }

            
            $columns = array(
                'data' => $data['field'],
                'name' => $data['field'],
                'searchable' => true,
                'orderable' => true,
                'search' => array(
                    'value' => $search_value,
                    'regex' => false
                )
            );
            $filters['columns'][] = $columns;

        } // END_FOREACH @mapper_data 


        
        $sort_key = array_search($req['order']['column'], array_keys($mapper_data));
        $filters['order'][] = array(
            'column' => $sort_key,
            'dir'    => $req['order']['dir']
        );



        if( isset($req['search']) && strlen($req['search']) > 1 ) {
            $filters['search']= array(
                'value' => $req['search'],
                'regex' => false,
            );
        } 

        // 최대 호출 갯수 200개 제한 
        if( isset($req['length']) && ( $req['length'] > 0 || $req['length'] <= 200 ) ) {
            $filters['length'] = $req['length'];
        }
        if( isset($req['page']) && $req['page'] > 0 ) {
            $filters['start'] = ($req['page'] - 1) * $req['length'];
        }
        

        return $filters;
    }
    
}
