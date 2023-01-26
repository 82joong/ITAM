<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Images extends CI_Controller {

    
    public function __construct() {
        parent::__construct();

    }


    public function qrcode($code='') {

        $data = array(
            'is_success'    => FALSE,
            'msg'           => '',
            'page_title'    => ADMIN_SITE_NAME,
            'assets_dir'    => ADMIN_ASSETS_DIR,
        );

        if( ! isset($code) ||  strlen($code) < 1) {
            $data['is_success'] = FALSE; 
            $data['msg'] = '만료된 QRCode 입니다.(During 24h)';
            $this->load->view('api/default_template/images/qrcode.php', $data);
            return;
        }

        $this->load->library('encrypt');
        $split = '_%%_%%_';
        $decode = urldecode($code);

        $this->encrypt->set_cipher(MCRYPT_BLOWFISH);
        $decode = $this->encrypt->decode($decode);
        //echo $decode.PHP_EOL.'<BR />';

        $code_data = explode($split, $decode);
        if( (time() - $code_data[1]) > 86400 ) {
            // Expired QRCode;
            $data['is_success'] = FALSE; 
            $data['msg'] = '만료된 QRCode 입니다.';
        }else {
            $mime = 'jpg';
            $filename = DISPLAY_PATH.'/qrcode/'.$code_data[0];
            if(file_exists($filename)){
              $mime = mime_content_type($filename); //<-- detect file type
              header('Content-Length: '.filesize($filename)); //<-- sends filesize header
              header("Content-Type: $mime"); //<-- send mime-type header
              header('Content-Disposition: inline; filename="'.$filename.'";'); //<-- sends filename header
              readfile($filename); //<--reads and outputs the file onto the output buffer
              exit(); // or die()
            }
                 
        }
        //echo print_r($data);
        $this->load->view('api/default_template/images/qrcode.php', $data);
    }

}
