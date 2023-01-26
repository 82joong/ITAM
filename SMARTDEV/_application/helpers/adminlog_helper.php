<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


function getTagTypeMap($key='') {

    $type= array(
        'ip'        => 'ip',
        'sslvpn'    => 'sslvpn',
        'gateway'   => 'gateway',
    );

    if(strlen($key) > 0) {
        return isset($type[$key]) ? $type[$key] : '';
    }else {
        return $type;
    }
}

?>
