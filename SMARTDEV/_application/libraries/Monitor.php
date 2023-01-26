<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Monitor{

    public function __construct() {
    }


    function get_alert_fields() {

        $fields = array(
            'ALL'  => 'ALL',
            'CPU'  => 'CPU',
            'SWAP' => 'SWAP',
            'MEM'  => 'MEM',
            'DISK' => 'DISK',
            'PORT' => 'PORT'
        );
        return $fields;
    }



    public function disk_alert_script($disk_limit) {

        $script = <<<SCRIPT
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
        return $script;
    }

    public function port_alert_script($default_port) {

        $script = <<<SCRIPT
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

SCRIPT;
        return $script;
    }


    public function set_script($source) {

        $res = array(
            'script' => array(
                'script' => array(
                    'lang'      => 'painless',
                    'source'    => $source
                )
            )
        );

        return $res;
    } 


    public function set_should_limit($field, $limit) {
        $res = array(
            'range' => array(
                $field => array(
                    'gt' => $limit
                )
            )
        );
        return $res;
    }

}
?>
