<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Custom_field_tb_business extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->db_name = _SHOP_INFO_DATABASE_;
        $this->table   = strtolower(substr(__CLASS__, 0, -9));
        $this->fields  = $this->db->list_fields($this->table);
    }



	public static $element_type = array(
			'text'	    => 'Text',
			'textarea'	=> 'Textarea',
			//'list'	    => 'Lists',
			'checkbox'	=> 'Checkbox',
			'radio'	    => 'Radio',
	);
    public function getElementTypeMap() {
        return self::$element_type;
    }

	public static $icon_type = array(
			'text'	    => 'fa-rectangle-wide',
			'textarea'	=> 'fa-rectangle-landscape',
			//'list'	    => 'Lists',
			'checkbox'	=> 'fa-check-square',
			'radio'	    => 'fa-dot-circle',
	);
    public function getIconTypeMap() {
        return self::$icon_type;
    }



	public static $element_format = array(
			'ANY'	    => array('value' => 'ANY',      'inputmask' => 'normal',        'help' => ''),
            //'BOOLEAN'   => array('value' => 'BOOLEAN',  'inputmask' => 'boolean',       'help' => 'YES or NO'),
			'NUMERIC'	=> array('value' => 'NUMERIC',  'inputmask' => 'number',        'help' => '9999999999'),
			'CURRENCY'	=> array('value' => 'CURRENCY', 'inputmask' => 'ko_currency',   'help' => '99,999,999,999'),
			'TEL'	    => array('value' => 'TEL',      'inputmask' => 'tel',           'help' => '010-1234-1234'),
			'DATE'	    => array('value' => 'DATE',     'inputmask' => 'date',          'help' => 'yyyy-mm-dd'),
			'DATETIME'	=> array('value' => 'DATETIME', 'inputmask' => 'datetime',      'help' => 'yyyy-mm-dd hh:mm:ss'),
			'URL'	    => array('value' => 'URL',      'inputmask' => 'url',           'help' => 'http:// or https://'),
			'EMAIL'	    => array('value' => 'EMAIL',    'inputmask' => 'email',         'help' => 'abcd@abcd.com'),
			'MAC'	    => array('value' => 'MAC',      'inputmask' => 'mac',           'help' => '99:99:99:99:99:99'),
			'IPv4'	    => array('value' => 'IPv4',     'inputmask' => 'ipv4',          'help' => '192.168.110.310'),
			'IPv6'	    => array('value' => 'IPv6',     'inputmask' => 'ipv6',          'help' => '2001:00A9:1000:0000:0000:0000:1234:5678'),
	);
    public function getElementFormatMap() {
        return self::$element_format;
    }
    public function getElementFormatValueMap() {
        $res = array();
        foreach(self::$element_format as $k=>$v) {
            $res[$k] = $v['value'];
        }
        return $res;
    }
    public function getElementFormatHelpMap() {
        $res = array();
        foreach(self::$element_format as $k=>$v) {
            $res[$k] = $v['help'];
        }
        return $res;
    }



	public static $required = array(
			'YES'	=> 'YES',
			'NO'	=> 'NO',
	);
    public function getRequiredMap() {
        return self::$required;
    }

	public static $encrypt = array(
			'YES'	=> 'YES',
			'NO'	=> 'NO',
	);
    public function getEncryptMap() {
        return self::$encrypt;
    }



}
