<?php
class CheckSSL {

    function checkSSL() {
        /*
        $CI =& get_instance();
        $class = $CI->router->fetch_class();
        $exclude =  array('client');  // add more controller name to exclude ssl.
        if(!in_array($class,$exclude)) {
            // redirecting to ssl.
            $CI->config->config['base_url'] = str_replace('http://', 'https://', ADMIN_DOMAIN);
            if ($_SERVER['SERVER_PORT'] != 443) redirect($CI->uri->uri_string());

            die(header("Location: ".HTTPS_SHOP_URL."/".$CI->uri->uri_string().$str_get));


        } else {
            // redirecting with no ssl.
            $CI->config->config['base_url'] = str_replace('https://', 'http://', ADMIN_DOMAIN);
            if ($_SERVER['SERVER_PORT'] == 443) redirect($CI->uri->uri_string());
        }
        */


        $CI =& get_instance();

        $http_host = '';
        if($CI->input->is_cli_request() == false && isset($_SERVER['HTTP_HOST'])) {
            $http_host = strtolower($_SERVER['HTTP_HOST']);
        } else {
            return;
        }

        if(isset($_SERVER['HTTP_USER_AGENT']) == true && strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'taillist') !== false) {
            // 앱에서 요청.
            return;
        }

        /*
        if($http_host == 'renewal.taillist.com' || $http_host == 'renewalm.taillist.com') {
            // 개발중 임시 실서버 주소. SSL이 없다.
            return;
        }
        */

        $segs = $CI->uri->segment_array();
        if(isset($segs[2]) && $segs[2] == 'securein') return;

        if(IS_REAL_SERVER != true) {
            // TEST SERVER
            return;
        }

        //if(IS_REAL_SERVER == true && $http_host == ADMIN_DOMAIN) return;

        if($CI->input->is_ajax_request() === true) return;

        //if(IS_REAL_SERVER == true && ! (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')) {
        if( ! (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ) {

            // PC 전체 ssl화
            $all_ok_ctrls = array(
                'smapi',
                'api',
                'i',
                'notify',
                'syslog',
            );


            if(isset($segs[2]) && in_array($segs[2], $all_ok_ctrls)) {

                /*
                $dash_uri = array(
                    'total_dashboard',
                    'top_dashboard',
                    'server_dashboard',
                    'ssh_dashboard'
                );

                if(isset($segs[3]) && in_array($segs[3], $dash_uri)) {
                    $this->removeSSL();
                    echo 'TEST';
                    return;
                }
                */

                return;
            } else {
                $this->forceSSL();
                return;
            }
        }
    }


    public function forceSSL() {
        $CI =& get_instance();

        $str_get = http_build_query($_GET);
                if(strlen(trim($str_get)) > 0) $str_get = '?'.$str_get;

        die(header("Location: https://".ADMIN_DOMAIN."/".$CI->uri->uri_string().$str_get));
    }

    public function removeSSL() {
        $CI =& get_instance();

        $str_get = http_build_query($_GET);
                if(strlen(trim($str_get)) > 0) $str_get = '?'.$str_get;

        die(header("Location: http://".ADMIN_DOMAIN."/".$CI->uri->uri_string().$str_get));
    }
}
