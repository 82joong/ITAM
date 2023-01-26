<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function getFacilityMap($key='') {

    $facility = array(
        0  => 'KERN',
        1  => 'USER',
        2  => 'MAIL',
        3  => 'DAEMON',
        4  => 'AUTH',
        5  => 'SYSLOG',
        6  => 'LPR',
        7  => 'NEWS',
        8  => 'UUCP',
        9  => 'CRON',
        10 => 'SECURITY',
        11 => 'FTP',
        12 => 'NTP',
        13 => 'LOGAUDIT',
        14 => 'LOGALERT',
        15 => 'CLOCK',
        16 => 'LOCAL0',
        17 => 'LOCAL1',
        18 => 'LOCAL2',
        19 => 'LOCAL3',
        20 => 'LOCAL4',
        21 => 'LOCAL5',
        22 => 'LOCAL6',
        23 => 'LOCAL7',
    );


    if(strlen($key) > 0) {
        return isset($facility[$key]) ? $facility[$key] : '';
    }else {
        return $facility;
    }
}



function getSeverityMap($key='') {

    $severity = array(
        0 => 'EMERG',
        1 => 'ALERT',
        2 => 'CRIT',
        3 => 'ERR',
        4 => 'WARNING',
        5 => 'NOTICE',
        6 => 'INFO',
        7 => 'DEBUG',
    );

    if(strlen($key) > 0) {
        return isset($severity[$key]) ? $severity[$key] : '';
    }else {
        return $severity;
    }
}


function getSysCateMap($key='') {

    $category = array(
        'VMKERNEL'      => 'VMKERNEL',
        'GREENSHIELD'   => 'GREENSHIELD',
        'VIRUS'         => 'VIRUS',
        'PHPERROR'      => 'PHPERROR',
        'RADIUS'        => 'RADIUS',
        'SECURE'        => 'SECURE',
        'SYSLOG'        => 'SYSLOG',
        'ETC'           => 'ETC',
    );

    if(strlen($key) > 0) {
        return isset($category[$key]) ? $category[$key] : '';
    }else {
        return $category;
    }
}




function getSSHTypeMap($key='') {

    $type = array(
        'mysql'     => 'mysql',
        'message'   => 'message',
        'sftp'      => 'sftp',
        'syslogd'   => 'syslogd',
        'etc'       => 'etc',
    );

    if(strlen($key) > 0) {
        return isset($type[$key]) ? $type[$key] : '';
    }else {
        return $type;
    }
}


function parseDiskToHtml($source_disk) {

    $res = array(
        'is_alert'  => FALSE,
        'html'      => '',
    );

    $html_disk = '<ul class="list-unstyled mb-0 ml-1">';
    $disk = explode('&', $source_disk);
    foreach($disk as $k=>$d) {
        $df = explode('=', $d); 
        if(strlen(trim($df[1])) < 1) continue;

        $per = str_replace('%', '', trim($df[1]));

        $class = '';
        if( intVal($per) > (DISK_MAX*1) ) {
            $res['is_alert'] = TRUE;
            $class = 'text-danger fw-700';
        }

        $html_disk .= '<li>';
        $html_disk .= '<div class="row">';
        $html_disk .= '<div class="col-6 text-left">'.$df[0].'</div>';
        $html_disk .= '<div class="col-6 '.$class.'">'.$df[1].'</div>';
        $html_disk .= '</div>';
        $html_disk .= '</li>';
    }
    $html_disk .= '</ul>';
    $res['html'] = $html_disk;

    return $res;
}


function parseTopToHtml($source) {

    $res = array(
        'is_alert'  => FALSE,
        'html'      => '',
    );

    $top = '<ul class="list-unstyled mb-0 ml-1">';
    for($i=1; $i<=5; $i++) {
        $name = 'top'.$i.'_name';
        $cpu = 'top'.$i.'_cpu';

        $class = '';
        if( $source[$cpu] > (TOP_MAX*1) ) {
            $res['is_alert'] = TRUE;
            $class = 'text-danger fw-700';
        }

        $top .= '<li>';
        $top .= '<div class="row">';
        $top .= '<div class="col-6 text-right">'.$source[$name].' : </div>';
        $top .= '<div class="col-6 '.$class.'">'.$source[$cpu].'</div>';
        $top .= '</div>';
        $top .= '</li>';
    }
    $top .= '</ul>';
    $res['html'] = $top;

    return $res;
}


function parsePortToHtml($source_port) {

    $res = array(
        'is_alert'  => FALSE,
        'html'      => '',
    );

    $port_data = explode(',', $source_port);
    $alert_port = array_diff($port_data, PORT_MAX);

    $port = '<ul class="list-unstyled mb-0 ml-1">';
    foreach($port_data as $p) {
        $class = "";
        if(in_array($p, $alert_port)) {
            $res['is_alert'] = TRUE;
            $class = "text-danger fw-700";
        }
        
        $port .= '<li class="'.$class.'">'.$p.'</li>';
    }
    $port .= '</ul>';
    $res['html'] = $port;

    return $res;
}


function parseAlertToHtml($alert) {
    $alert_html = '<ul class="list-unstyled mb-0 ml-1">';

    foreach($alert as $a) {
        $alert_html .= '<li class="text-danger fw-700">'.$a.'</li>';
    }
    $alert_html .= '</ul>';
    return $alert_html;
}

?>
