<?php

//CONFIGURATION for SmartAdmin UI

//ribbon breadcrumbs config
//array("Display Name" => "URL");
$breadcrumbs = array(
	"Home" => '/main/blank'
);

$page_nav = array(
	'dashboard' => array(
		'title' => '대시보드',  // dashboard
		'icon' => 'fa-info-circle',
		'url' => '/',
	),

    'customer' => array(
		'title' => '회원관리',  // user_tb 
		'icon' => 'fa-user',
        'sub' => array(
			'manage_users' => array(
				'title' => '회원리스트',
				'url' => '/'
			),
			'manage_group' => array(
				'title' => '회원그룹',
				'url' => '/'
			),
            'contact' => array(
                'title' => '고객문의',
                'url' => '/'
            ),
        )
	),

    'products' => array(
		'level'	=> '9',
		'title' => '상품관리',
		'icon' => 'fa-boxes',
        'sub' => array(
			'manage_products' => array(
				'title' => '전체 상품리스트',
				'url' => '/'
			),
        )
	),

	'system' => array(
		'level'	=> '9',
		'title' => '시스템',
		'icon' => 'fa-cog',
        'sub' => array(
			'manage_admin' => array(
				'title' => '관리자 계정관리',
				'url' => '/'
			),
			'manage_setting' => array(
				'title' => '관리자 설정(config)관리',
				'url' => '/'
			),
			'manage_notice' => array(
				'title' => '공지사항 관리',
				'url' => '/'
			),
            'manage_faq' => array(
				'title' => 'FAQ 관리',
				'url' => '/'
			),
		)
	), 
);

//configuration variables
$page_title = "";
$page_css = array();
$no_main_header = false; //set true for lock.php and login.php
$page_body_prop = array(); //optional properties for <body>
$page_html_prop = array(); //optional properties for <html>
?>
