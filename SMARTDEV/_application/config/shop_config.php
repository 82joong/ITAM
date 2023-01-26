<?php
if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}



/////////// 건들지 말것 !! /////////////
$config['shop_config'] = array();
$shop_config =& $config['shop_config'];
/////////// 건들지 말것 !! /////////////

$shop_config['service'] = array(
	'define' => array(
		'unit'		=> '$',
		'branches'	=> array('LA'),
		'nations'	=> array('US', 'KR', 'CN', 'JP'),
	),
);
