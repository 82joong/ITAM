<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class DaouData{

    /*

        /daemon/Cron.php sync_daoU

        @ACCOUNT : 계정 정보 조회
        @DEPT : 부서 정보 조회
        @MEMBER : 부서원 정보 조회
        @DUTY : 직책 정보 조회
    */

    private $_API_ID  = 'eee5dedae2d5f1f5';
    private $_API_SECRET = 'acbcd0aba8e4e7bec5fce2fdbaaef5a1';
    private $_GW_URL = 'https://gw.cocen.com';



    function __construct() {
		$this->CI =& get_instance();
    }



    public function getData($mode='ACCOUNT') {
        $file = SYNC_FILE_PATH.'/groupware_'.strtolower($mode).'.info';

        $this->CI->load->library('encryption');

        $ciphertext = file_get_contents($file);
        $data = $this->CI->encryption->decrypt($ciphertext);
        $res = json_decode($data, true);
        return $res['data'];
    }


    public function getCompany($dept_data=array(), $code='') {

        if( sizeof($dept_data) < 1 ) {
            $dept_data = $this->getData('DEPT');
        }
        $dept_data = $this->CI->common->getDataByPK($dept_data, 'code');

        $root_code = '';
        $cnt = 0;
        while(true) {

            $cnt ++;
            if( $dept_data[$code]['parentCode'] == 1000 ) {
                $root_code = $dept_data[$code]['code'];
                break;
            }else {
                $code = $dept_data[$code]['parentCode'];
            }

            if($cnt > 20) break;
        }
        return $root_code; 
    }



    public function sendNotify($sender='', $receiver=array(), $mailtitle='', $mailmsg='', $content='', $link='') {

        $res  = array();

        if(strlen($sender) < 1 || sizeof($receiver) < 1 || strlen($content) < 1) {
            $res['code'] = 999;
            $res['message'] = '필수값 누락';
            return $res; 
        }

        if(strlen($link) < 1) {
            $link = $this->_GW_URL.'/app/mail';
        }

        $params = array(
            "clientId"          => $this->_API_ID,
            "clientSecret"      => $this->_API_SECRET,
            //"productName"       => "제휴서비스/제품 명",
            //"productVersion"    => "제휴서비스/제품 버전",
            //"clientCompanyName" => "제휴서비스/제품 식별번호"
            "sender"            => $sender,
            "receivers"         => $receiver,
            "mailTitle"         => $mailtitle,                  // 메일 제목
            "mailMessage"       => $mailmsg,               // 메일 내용
            "message"           => $content,                    // 알림창 메세지
            "linkUrl"           => $link                    // 알림창 링크 또는 [바로가기] 링크 
        );


        $_API_URL = 'https://api.daouoffice.com/public/v1/noti';
        $cmd = "curl -XPOST '".$_API_URL."' ";
        $cmd .= "-d '".json_encode($params)."' ";
        $cmd .= "-H 'content-type: application/json'";
        //echo $cmd.PHP_EOL.'<br />';


        $res = @shell_exec($cmd);
        $res = json_decode($res, true); 
        //echo print_r($res); exit;

        return $res;
    }
}
