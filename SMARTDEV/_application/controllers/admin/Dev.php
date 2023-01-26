<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include_once dirname(__FILE__).'/Base_admin.php';

class Dev extends Base_admin {


    public function php_info() {

        echo phpinfo();
    }

    public function elastic_test() {

        $res = array();
        echo '<h3>Elasticsearch API TEST</h3><br>'.PHP_EOL;

        $this->load->library('elastic');

        //$res = $this->elastic->generate_snapshot_repository(ELASTIC_REPOSITORY_NAME);
        //$res = $this->elastic->delete_snapshot_repository(ELASTIC_REPOSITORY_NAME, ELASTIC_POS_GOODS_INDEX);
        //$res = $this->elastic->store_snapshot(ELASTIC_REPOSITORY_NAME, 'market-goods');
        //$res = $this->elastic->restore_snapshot(ELASTIC_REPOSITORY_NAME, 'dw-account-summary-2020_snapshot_20200210_112622');
        //$res = $this->elastic->delete_snapshot(ELASTIC_REPOSITORY_NAME, 'dw-account-summary-2020_snapshot_20200318_095151');
        //$res = $this->elastic->clean_snapshot();

        //$res = $this->elastic->get_alias_map(ELASTIC_DW_INDEX);
        //$res = $this->elastic->actions_alias(array(), array('dw-account-summary-2020'), ELASTIC_DW_INDEX);
        //$res = $this->elastic->get_alias_map(ELASTIC_DW_INDEX);
        //$res = $this->elastic->actions_alias(array('pos-goods-20200214120857'), array(), 'pos-goods');

        $res = $this->elastic->delete_template('market-goods');
        $res = $this->elastic->generate_template('market-goods');
        //$res = $this->elastic->get_mapping_fields('dw-account-summary');
        //$res = $this->elastic->get_auth_header();


        //$res = $this->elastic->delete_indices('traffic-*', 10);
        //$res = $this->elastic->delete_indices('metricbeat-*', 10);
        echo print_r($res);


    
        /*
        $params = array(
            's'      => 'index',
            'format' => 'json'
        );
        $res = $this->elastic->cat_indices($params);
        echo print_r($res);
        */
    }


    public function painless() {

        $this->load->library('elastic');

        $index_name = 'test-2022';

//def disks = doc['parsed_disk'];
//Debug.explain( disks );


        $default_port = '[22,25,80,443]';
        $disk_limit = 90;

        $source = <<<SCRIPT
boolean isPortAlert(def listen_port) {
    boolean is_alert = false;
    def default_port = $default_port;
    for (port in listen_port)  {
        if( default_port.contains(Integer.parseInt(port)) == false ) {
            return true;
        }
    }
    return false;
}
boolean isDiskAlert(def disks) {
    boolean res = false;
    def size = disks.length;
    if(size > 0) {
        for(int i=(size/2); i<size; ++i) {
            if( disks[i].indexOf('/') === -1 && Integer.parseInt(disks[i]) > $disk_limit ) {
                return true;
            }
        }
    }
    return res;
}        

SCRIPT;


////////////isPortAlert(doc['parsed_port']) == true && isDiskAlert(doc['parsed_disk']) == true;


        $source .= "isPortAlert(doc['parsed_port']) == true && isDiskAlert(doc['parsed_disk']) == true;";


        $params = array(
            'query' => array(
                'bool' => array(
                    'filter' => array(
                        'script' => array(
                            'script' => array(
                                'lang' => 'painless',
                                'source' => $source
                                //'source' => "def disks = doc['parsed_disk']; Debug.explain( disks );"
                            )
                        )
                    )
                )
            )
        );

        echo print_r($params); //exit;


        $json_params = json_encode($params);
        $el_header = $this->elastic->get_auth_header();
        $el_url = ELASTIC_SYSLOG_HOST.'/'.$index_name.'/_search';
        $el_result = $this->elastic->restful_curl($el_url, $json_params, 'GET', $timeout=10, $el_header);
        $el_result = json_decode($el_result, true);
        echo print_r($el_result); exit;


    }


    public function msearch() {


        $url = ELASTIC_SYSLOG_HOST.'/systemevents-2021/_msearch';

        $el_params = array();

        $tmp = array(
            'index' => 'systemevents-2021'
        );
        $el_params[] = json_encode($tmp);
        $tmp = array(
            'query' => array(
                'bool'  => array(
                    'must' => array(
                        array('match' => array('fromhost' => '169.254.254.17')),
                        array('match' => array('syslogtag' => 'vmkernel')),
                    )
                )
            ),
            'size' => 2
        );    
        $el_params[] = json_encode($tmp);
        $tmp = array(
            'index' => 'systemevents-2021'
        );
        $el_params[] = json_encode($tmp);
        $tmp = array(
            'query' => array(
                'bool'  => array(
                    'must' => array(
                        array('match' => array('fromhost' => 'vmware53')),
                        array('match' => array('syslogtag' => 'GREENSHEILD')),
                    )
                )
            ),
            'size' => 2
        );
        $el_params[] = json_encode($tmp);
        echo print_r($el_parms);


        $el_params[] = '';
        $params = implode("\n", $el_params);
        //$params = json_encode($params);
        echo $params.'<br / >'.PHP_EOL; //exit;


        $ch  = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-ndjson'));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_ENCODING, "gzip");
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        $result = curl_exec($ch);
        $result = json_decode($result, true);
        echo print_r($result);

    }

    
    public function elastic_nginx() {

        $req = $this->input->post();
 
		if(isset($req['mode']) && $req['mode'] == 'list') {

            $this->load->library('elastic');

            
            $multiple_filters = array();
            $index_name = ELASTIC_NGINX_INDEX;
            $params = $this->elastic->datatable_filter_to_params($req, $index_name, $multiple_filters);
            //echo print_r($params); exit;

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
            $el_header = $this->elastic->get_auth_header();
            $el_url = ELASTIC_HOST.'/'.$index_name.'/_search';
			$el_result = $this->elastic->restful_curl($el_url, $json_params, 'GET', $timeout=10, $el_header);
			$el_result = json_decode($el_result, true);

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
		

			$data = array();
            foreach($el_result['hits']['hits'] as $k=>$r){

                $row = array(
                    '_id'           => $r['_id'],
                    'clientip'      => $r['_source']['clientip'],
                    'os'            => $r['_source']['agents']['os'],
                    'os_version'    => $r['_source']['agents']['os_version'],
                    'browser'       => $r['_source']['agents']['name'],
                    'country_code'  => $r['_source']['geoip']['country_code2'],
                    'country_name'  => $r['_source']['geoip']['country_name'],
                    'method'        => $r['_source']['method'],
                    'host'          => $r['_source']['host'],
                    'hostname'      => $r['_source']['hostname'],
                    'uri_path'      => $r['_source']['uri_path'],
                    'response'      => $r['_source']['response'],
                    '@timestamp'    => date('Y-m-d H:i:s', strtotime($r['_source']['@timestamp'])),
                );
                //echo print_r($row); exit;
				$data[] = $row;
            }
            //echo print_r($json_data);
			
            $count = $this->elastic->get_hits_total($el_result);

            $json_data->recordsTotal = $count;
            $json_data->recordsFiltered = $count;
            $json_data->data = $data;

            echo json_encode($json_data);
            return;
        }

        $data = array();
		$this->_view('dev/elastic_nginx', $data);
    }

    
    // 2차원 형식 데이터
    // dotProduct : 내적 (행렬의 곱)
    public function search_image() {

        $req = $this->input->post();
        //echo print_r($req);

        $data = array();
        $data['features'] = isset($req['features']) ? $req['features'] : '';
        $data['rows'] = array();


        if(isset($req['features']) && strlen($req['features']) > 0) {

            $threshold = 2000;

            $this->load->library('elastic');
            //$index_name = 'features';
            $index_name = 'surf';
            //$index_name = 'image_signature';
            $el_header = $this->elastic->get_auth_header();

            // String 데이터 내에 "[]" 제거
            $feature_data = explode('], [', $req['features']);
            //echo print_r($feature_data); exit;

            $res_data = array();
            foreach($feature_data as $k=>$row) {

                //echo $k.PHP_EOL;

                $row = str_replace('[', '', $row);
                $row = str_replace(']', '', $row);
                $array_data = explode(',', $row);
                //echo print_r($array_data);

                
                /*
                $script_text = "\"double value = dotProduct(params.query_vector, 'features');";
                $script_text .= "return sigmoid(1, Math.E, -value);\"";
                */
                

                $params = array();
                $params['_source'] = array('image_id');
                $params['query'] = array(
                    'script_score' => array(
                        'query' => array(
                            'bool' => array(
                                'filter' => array()
                            )
                        ),
                        'script' => array(
                            //'source' => $script_text,
                            //'source' => "dotProduct(params.query_vector, 'feature')",
                            'source' => "cosineSimilarity(params.query_vector, doc['feature'])",
                            //'source' => "1 / (l2norm(params.query_vector, doc['feature']) + 1)",
                            'params' => array(
                                'query_vector' => $array_data
                            )
                        )
                    )
                );
                //echo print_r($params);
                $json_params = json_encode($params, JSON_NUMERIC_CHECK);  // JSON 내에 숫자 데이터 체크
                //echo print_r($json_params); //exit;

                $el_url = ELASTIC_HOST.'/'.$index_name.'/_search';
                $el_result = $this->elastic->restful_curl($el_url, $json_params, 'GET', $timeout=10, $el_header);
                $el_result = json_decode($el_result, true);
                //echo print_r($el_result); exit;

                if(isset($el_result['hits'])) {
                    foreach($el_result['hits']['hits'] as $row) {
                        if($row['_score'] == 1 || $row['_score'] == 0) {
                            continue;
                        }

                        $_id = $row['_source']['image_id'];

                        if(isset($res_data[$_id])) {
                            //$res_data[$_id] = $res_data[$_id] + $row['_score']; 
                            $res_data[$_id]['count'] += 1;  

                        }else {
                            //$res_data[$_id] = $row['_score']; 

                            $res_data[$_id] = array(
                                'score' => array(),
                                'count' => 1
                            );
                        }
                        $res_data[$_id]['score'][] = $row['_score'];  
                        arsort($res_data[$_id]['score']);


                        if(sizeof($res_data[$_id]['score']) > $threshold) {
                            break 2;
                        }

                    }//END_FOREACH
                }//END_IF

            }//END_FOREACH
                
            arsort($res_data);
            $res_data = array_slice($res_data, 0, 60);
            //echo sizeof($res_data);
            //echo print_r($res_data); //exit;
            
            $data['rows'] = $res_data;
            $data['type'] = $index_name;
        }
		$this->_view('search_image', $data);
 
    }


    /*
    public function search_image() {

        $req = $this->input->post();
        //echo print_r($req);

        $data = array();
        $data['features'] = isset($req['features']) ? $req['features'] : '';
        $data['rows'] = array();

        if(isset($req['features']) && strlen($req['features']) > 0) {

            // String 데이터 내에 "[]" 제거
            $features = substr($req['features'], 0, -1);
            $features = substr($req['features'], 1, -1);
            $array_data = explode(', ', $features);

            $params = array();
            $params['_source'] = array('image_id');
            $params['query'] = array(
                'script_score' => array(
                    'query' => array(
                        'bool' => array(
                            'filter' => array()
                        )
                    ),
                    'script' => array(
                        'source' => "cosineSimilarity(params.query_vector, doc['feature'])",
                        //'source' => "dotProduct(params.query_vector, doc['feature'])",
                        'params' => array(
                            'query_vector' => $array_data
                        )
                    )
                )
            );
            //echo print_r($params);
            $json_params = json_encode($params, JSON_NUMERIC_CHECK);  // JSON 내에 숫자 데이터 체크
            //echo print_r($json_params); //exit;

            $this->load->library('elastic');
            $index_name = 'orb';
            $el_header = $this->elastic->get_auth_header();
            $el_url = ELASTIC_HOST.'/'.$index_name.'/_search';
            $el_result = $this->elastic->restful_curl($el_url, $json_params, 'GET', $timeout=10, $el_header);
            $el_result = json_decode($el_result, true);
            //echo print_r($el_result);

            if(isset($el_result['hits']) && sizeof($el_result['hits']['hits'])) {
                $data['rows'] = $el_result['hits']['hits'];
            }

            $data['type'] = $index_name;
        }
		$this->_view('search_image', $data);
    }
    */


    public function scan_swagger() {
        require(APPPATH.'libraries/vendor/autoload.php');

        //echo '<h1>Scan Swagger</h1>'; //exit;

        $openapi = \OpenApi\scan('/home/82joong/html/crawl/SMARTDEV/_application/controllers/api/');
        //header('Content-Type: application/x-yaml');
        header('Content-Type: application/json');
        echo $openapi->toJSON();
    }



    public function kafka() {

        /*
        bootstrap-servers: public-kafka-hp-gl-dsi-kafka-hp.aivencloud.com:17601
        security-protocol: SASL_SSL
        basic-auth-credentials-source: USER_INFO
        schema-registry-url: https://public-kafka-hp-gl-dsi-kafka-hp.aivencloud.com:17594
        sasl-mechanism: PLAIN
        ssl-endpoint-identification-algorithm: https
        topic: demo_streaming_ggl_merchandising_koreacenter
        value-deserializer: io.confluent.kafka.serializers.KafkaAvroDeserializer
        key-deserializer: org.apache.kafka.common.serialization.StringDeserializer
        schema-registry-basic-auth-user-info: partner_koreacenter:mvd4nunp6d3acytw
        sasl-jaas-config: org.apache.kafka.common.security.plain.PlainLoginModule required username="partner_koreacenter" password="mvd4nunp6d3acytw";
        */


        echo '<h2>Kafka Test</h2>';

        set_include_path(
            implode(PATH_SEPARATOR, array(
                realpath(__DIR__ . '/../lib'),
                get_include_path(),
            ))
        );
        require(APPPATH.'libraries/autoloader.php');

        $host   = 'public-kafka-hp-gl-dsi-kafka-hp.aivencloud.com';
        $port   = 17594;
        $topic  = 'demo_streaming_ggl_merchandising_koreacenter';

        $producer = new Kafka_Producer($host, $port, Kafka_Encoder::COMPRESSION_NONE);
        $in = fopen('php://stdin', 'r');
        while (true) {
            echo "\nEnter comma separated messages:\n";
            $messages = explode(',', fgets($in));
            foreach (array_keys($messages) as $k) {
                //$messages[$k] = trim($messages[$k]);
            }
            $bytes = $producer->send($messages, $topic);
            printf("\nSuccessfully sent %d messages (%d bytes)\n\n", count($messages), $bytes);
        }


    }



    public function biz_test() {

        echo 'TEST'.PHP_EOL;

        $lib_params = array('market' => 'lafayette');
        $this->load->library('PosSyncer', $lib_params);

		/*
        $pos_service_id = $this->possyncer->getServiceID();
        echo $pos_service_id.PHP_EOL;

        $this->possyncer->createPOSGoods(array('1231', '23232'));
        */


        $market = 'lafayette';
        $gids = array(
            'lafayette-12877231',
            'lafayette-12877480'
        );

        $data = $this->possyncer->pos_exists_map($market, $gids);
		echo print_r($data);
		exit;


        $this->load->library('elastic');
        $data = $this->elastic->get_opt_gids_map($market, $gids, $return_map=TRUE);
        echo print_r($data);

        $opt_gids = array();
        foreach($data as $mg_id=>$mg) {
            $opt_gids = array_merge($opt_gids, array_values($mg));
        }
        echo print_r($opt_gids);

    }


    public function market_test() {

        //$this->load->helper('vvic');
        $this->load->helper('lafayette');
        //$cate_data = getCategory();
        //echo print_r($cate_data);

        $category_tree_map = getCategoryByName('name_ko', '_%AND%_', true); // tree 형식으로 카테고리 노출
        echo print_r($category_tree_map);
    }


    public function seq_chart() {
        $data = array();

        $data['actor'] = array(
            'Customer',
            'Lafayette KR',
            'Lafayette API',
            'Lafayette\nWherehouse',
            'Lafayette\nWherehouse\nPicker',
            'Europe Delivery',
            'Koreacenter\nGermany\nWherehouse',
            'Koreacenter\nGermany\nManager',
            'Delivery Company\n(International)',
        );

		$this->_view('dev/seq_chart', $data);
    }


    public function add_category() { 

        //$this->load->helper('lafayette');
        $this->load->helper('vvic');

        //$_ID = 'lafayette-87576054';
	    $_ID = 'lafayette-71856081';
        //addCategory($_ID);

        $data = getCategory();
        echo print_r($data);
    }

    public function category() {

        $this->load->library('ShopCrawler');
        //$data = $this->shopcrawler->getCategory('qeeboo');print_r($data);exit;

        
        //set_time_limit(9999); $data = $this->shopcrawler->getCategoryGoodsList('qeeboo', '', array('mode' => 'sync')); // 오래걸림. 풀 크롤링

        $data = $this->shopcrawler->getCategoryGoodsList('qeeboo', '');
        echo '<xmp>';
        echo print_r($data, true);
        echo '</xmp>';

    }

    public function tree_select() {
        $data = array();
        $this->_view('dev/tree_select', $data);
    }



    public function redfish() {

        $idrac_ip = '172.16.118.212';

        $url = 'http://'.$idrac_ip.'/redfish/v1/Systems/1';
        $res = $this->common->restful_curl_get($url);
        echo print_r($res);

    }


    public function imgMkdir() {

        $type = 'vendor';
        $key = 1;
        $path = IMG_PATH;
        
        $first_path = sprintf('%03d', $key);
        $path .= '/'.$type.'/'.$first_path;
        echo $path.PHP_EOL;

        if(!is_dir($path)) { 
            @mkdir($path, 0777, true);
            @chmod($path, 0777);
        }
        $second_path = sprintf('%06d', $key);
        $path .= '/'.$second_path;
        echo $path.PHP_EOL;

        if(!is_dir($path)) { 
            @mkdir($path, 0777, true);
            @chmod($path, 0777);
        }
        echo $path.PHP_EOL;
    }



    // Cache Test

    // config/database.php

    public function cache() {

        // 캐시 드라이버를 로드, 기본 사용 드라이버 APC, APC 불가시, FILE 기반 캐싱
        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
        var_dump($this->cache->cache_info()).'<br />'.PHP_EOL; //exit;

        if ( ! $foo = $this->cache->get('foo')) {
            echo 'Saving to the cache!<br />';
            $foo = 'foobarbaz!';

            // Save into the cache for 5 minutes
            $this->cache->save('foo', $foo, 300);
        }
        echo $foo.'<br />'.PHP_EOL;

        if ($this->cache->apc->is_supported()) {

            echo 'APC Cache Support.<br />'.PHP_EOL;

            if ($data = $this->cache->apc->get('foo')) {
                // do things.
                echo $data.'<br />'.PHP_EOL;
            }
        }else {
            echo 'APC Cache NOT Support.<br />'.PHP_EOL;
        }

        exit;

        echo APPPATH.'<br />'.PHP_EOL;
        echo BASEPATH.'<br />'.PHP_EOL;
        echo FCPATH.'<br />'.PHP_EOL;
        echo SYSDIR.'<br />'.PHP_EOL;
        echo VIEWPATH.'<br />'.PHP_EOL;
        echo realpath(BASEPATH.'/../appdata').'/query_cache.<br />'.PHP_EOL;

        exit;

        $uri = $this->uri->segment_array();
        echo print_r($uri);

        //$this->db->cache_delete($uri[1],$uri[2]);
        $this->db->cache_delete_all();
    }


    public function query_cache() {

        // Turn caching on
        $this->db->cache_on();
        $query = $this->db->query("SELECT * FROM admin_tb");

        // Turn caching off for this one query
        $this->db->cache_off();
        $query = $this->db->query("SELECT * FROM admin_tb WHERE a_id = 1");

        // Turn caching back on
        $this->db->cache_on();
        $query = $this->db->query("SELECT * FROM vendor_tb");

    }


    public function iptest() {

        $this->load->library('CIDR');   // 대문자

        $ip = '192.168.101.123';
        $res = $this->cidr->validIP($ip);   // 소문자
        echo $res.'<br />'.PHP_EOL;

        $ip = '255.255.252.0';
        $res = $this->cidr->maskToCIDR($ip); 
        echo $res.'<br />'.PHP_EOL;


        $ip = '127.0.0.33';
        $cidr = '127.0.0.1/24';
        $res = $this->cidr->IPisWithinCIDR($ip, $cidr); 
        echo 'IPisWithinCIDR : '.$res.'<br />'.PHP_EOL;


        /*
            mask = 27
            32 - 27 = 5
            2^5 = 32  => IP 개수 0~31 
            Network addres  : 127.0.0.0 (대역내의 첫번째 IP)
            Broadcast IP    : 127.0.0.31 (대역내의 마지막 IP)
            Host IP         : 127.0.0.1 ~ 127.0.0.30 
        */
        $ip = '127.0.0.31';
        $cidr = '127.0.0.0/27';
        $res = $this->cidr->IPisWithinCIDR($ip, $cidr); 
        echo 'IPisWithinCIDR : '.$res.'<br />'.PHP_EOL;



        $cidr = '210.217.16.0/25';
        $res = $this->cidr->cidrToRange($cidr); 
        echo 'cidrToRange : '.print_r($res).'<br />'.PHP_EOL;

        $start = $res[0];
        $end = $res[1];
        //echo long2ip(ip2long($start) + 1);

        // CIDR 범위내에 IP List 출력
        for($loop = ip2long($start); $loop <= ip2long($end); $loop++) {
            echo long2ip($loop).'<br />'.PHP_EOL;
        }

        echo $this->cidr->countSetbits(ip2long('255.255.252.0'));
    }



    // http://itam.82joong.joong.co.kr/admin/dev/file_download/order/12
    public function file_download() {

        $this->load->helper('download');


        // TODO. SESSION & history


        $uri = $this->uri->segment_array();
        echo print_r($uri);

        if( ! isset($uri[4]) || ! isset($uri[5]) ) {
            $this->common->alert(getAlertMsg('INVALID_SUBMIT'));
            $this->common->historyback();
            exit;
        }

        $id = $uri[5];
        switch($uri[4]) {

            case 'order':

                $this->load->model(array('order_tb_model'));

                $row = $this->order_tb_model->get($id)->getData();
                $img_path = $this->common->getImgPath('order', $row['o_id']);
                $file_name = $img_path.'/'.$row['o_filename'];
                break;

        } // END_switch


        $data = file_get_contents($file_name);
        $name = 'mytext.txt';
        force_download($name, $data);
    }


    public function updateRackcode() {

		$this->load->model(array(
            'rack_tb_model',
            'assets_model_tb_model',
        ));
 

        $params = array();
        $extras = array();
        $rows = $this->rack_tb_model->getList($params, $extras)->getData();
        //echo print_r($rows);

        
        foreach($rows as $r) {
            $code_data = explode('-', $r['r_code']);
            $code_data[3] = sprintf('%02d', $r['r_frame']);
            $code = implode('-', $code_data);
            echo $code.PHP_EOL;

            $data_params = array();
            $data_params['r_code'] = $code;
            //$this->rack_tb_model->doUpdate($r['r_id'], $data_params);



            // [Assets_model] am_rack_code 데이터 정합성 확보
            $params = array(
                'am_location_id' => $r['r_location_id'],
                'am_rack_code'   => $r['r_code']
            );
            $where_params = array();
            $where_params['=']['am_rack_id'] = $r['r_id'];
            //$this->assets_model_tb_model->doMultiUpdate($params, $where_params);

        }
    }
    


    public function setAssetsRackOrder() {


		$this->load->model(array(
            'rack_tb_model',
            'assets_model_tb_model',
        ));
 

        $params = array();
        $extras = array();
        $extras['order_by'] = array('am_rack_id ASC', 'am_created_at ASC');
        $rows = $this->assets_model_tb_model->getList($params, $extras)->getData();
        //echo print_r($rows);

        $rows = $this->common->getDataByDuplPK($rows, 'am_rack_id');
        echo print_r($rows); exit;

        foreach($rows as $row) {
            $order = 1;
            foreach($row as $r) {
                $params = array();
                $params['am_rack_order'] = $order;

                echo print_r($params);

                $this->assets_model_tb_model->doUpdate($r['am_id'], $params);
                //echo $this->assets_model_tb_model->getLastQuery().PHP_EOL; //exit;
                $order = $order + 1;

            }
        }

    }

}
