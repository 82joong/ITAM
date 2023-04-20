<?php

$admindir = SHOP_INFO_ADMIN_DIR;

//CONFIGURATION for SmartAdmin UI

//ribbon breadcrumbs config
//array("Display Name" => "URL");
/*
$breadcrumbs = array(
	"Home" => '/main/blank'
);
*/

$page_nav = array(
	'dashboard' => array(
		'title' => 'Dashboard',  // dashboard
		'icon' => 'fa-chart-area',
		'url' => '/'.$admindir,
	),


	'assets' => array(
		'level'	=> '7',
		'title' => 'Assets',
		'icon' => 'fa-box-full',
		'sub' => array(
            'status' => array(
                'title' => 'Status',
                'sub' => array(
                    // 하단에서 생성
                    'all' => array(
                        'title' => 'List All',
                        'url' => '/'.$admindir.'/assets/status'
                    ),
                )
            ),
            'type' => array(
                'title' => 'Type',
                'sub' => array(
                    // 하단에서 생성
                )
            ),

            'maintenance' => array(
				'title' => 'Maintenance',
				'url' => '/'.$admindir.'/assets/maintenance'
			),

            'rackview' => array(
				'title' => 'RackView',
				'url' => '/'.$admindir.'/assets/rackview'
            )
		)
	),


    'service' => array(
		'level'	=> '7',
		'title' => 'VMServices',
		'icon' => 'fa-layer-group',
		'sub' => array(
            'vmware' => array(
                'title' => 'VMware Lists',
                'url' => '/'.$admindir.'/vmservice/lists'
            ),

            'direct' => array(
                'title' => 'Direct Lists',
                'url' => '/'.$admindir.'/assets/direct'
            ),
		)
	),



	'ips' => array(
		'level'	=> '7',
		'title' => 'IPs',
		'icon' => 'fa-ethernet',
		'sub' => array(

            /*
            'local' => array(
                'title' => 'Local (사내IP)',
                'url' => '/'.$admindir.'/system/ipclass/local',
            ),
            'idc' => array(
                'title' => 'IDC',
                'url' => '/'.$admindir.'/system/ipclass/idc',
            ),
            */

            'iptotal' => array(
                'title' => 'IP Total',
                'url' => '/'.$admindir.'/assets/ip_total'
            ),
            'iplist' => array(
                'title' => 'IP List',
                'url' => '/'.$admindir.'/people/ip_list'
            ),

        )
    ),

    'people' => array(
        'level' => '7',
		'title' => 'People',   
		'icon' => 'fa-users-class',
        'sub' => array(
			'employee' => array(
				'title' => 'Employee',
				'url' => '/'.$admindir.'/people/employee'
			),
            'partners' => array(
				'title' => 'Partners',
				'url' => '/'.$admindir.'/people/partners'
			)
        )
	),



 
	'purchase' => array(
		'level'	=> '7',
		'title' => 'Purchase',
		'icon' => 'fa-money-check',
		'sub' => array(
            'order' => array(
				'title' => 'Orders',
				'url' => '/'.$admindir.'/purchase/orders'
			),
            'items' => array(
				'title' => 'Order Items',
				'url' => '/'.$admindir.'/purchase/order_items'
			),
		)
	),



    /*
       :: Make Models Step
       1) Custom fields : Model에 cutom 입력 항목 ex) OS, CPU, RAM, GPU ...
       2) Fieldset      : custom field 에 대한 Orchastration(조합/그룹) 생성 ex) Mac Book ...
       3) Models        : fieldset 매칭 & 세부 제푼군 ex) Mac Book Air / Mac Book Pro ...
     */
 
	'make' => array(
		'level'	=> '7',
		'title' => 'Make Models',
		'icon'  => 'fa-project-diagram',
		'sub'   => array(
            'custom' => array(
				'title' => 'Custom Fields',
				'url' => '/'.$admindir.'/manage/custom'
			),
            'fieldset' => array(
				'title' => 'FieldSet',
				'url' => '/'.$admindir.'/manage/fieldset'
			),
            'models' => array(
				'title' => 'Models',
				'url' => '/'.$admindir.'/manage/models'
            ),
        )
    ),


	/*
    'syslog' => array(
        'level'	=> '7',
		'title' => 'SYSLOG',
		'icon'  => 'fa-list-alt',
        //'i18n'  => 'nav.make_models',
		'sub'   => array(
            'server' => array(
				'title' => 'SYSLOG Server',
                'sub' => array(

                    'dashboard' => array(
                        'title' => 'ES Dashboard',
                        'url' => '/'.$admindir.'/syslog/server_dashboard'
                    ),

                    // 하단에서 생성
                    'list' => array(
                        'title' => 'ALL Category',
				        'url' => '/'.$admindir.'/syslog/server'
                    ),

                    'kernel' => array(
                        'title' => '- KERNEL',
				        'url' => '/'.$admindir.'/syslog/kernel'
                    ),
                    'vmware' => array(
                        'title' => '- VMWARE',
				        'url' => '/'.$admindir.'/syslog/vmware'
                    ),
                    'virus' => array(
                        'title' => '- VIRUS',
				        'url' => '/'.$admindir.'/syslog/virus'
                    ),
                    'attack' => array(
                        'title' => '- ATTACK',
				        'url' => '/'.$admindir.'/syslog/attack'
                    ),
                    'radius' => array(
                        'title' => '- RADIUS',
				        'url' => '/'.$admindir.'/syslog/radius'
                    ),
                    'secure' => array(
                        'title' => '- SECURE',
				        'url' => '/'.$admindir.'/syslog/secure'
                    ),
                    'phperror' => array(
                        'title' => '- PHPERROR',
				        'url' => '/'.$admindir.'/syslog/phperror'
                    ),
                    'syslogtag' => array(
                        'title' => '- SYSLOGTAG',
				        'url' => '/'.$admindir.'/syslog/syslogtag'
                    ),
                )
			),

            'monitor' => array(
                'title' => 'Server Monitor(TOP)',
                'sub' => array(

                    'total_dashboard' => array(
                        'title' => 'ES Total Dashboard',
                        'url' => '/'.$admindir.'/syslog/total_dashboard'
                    ),

                    'dashboard' => array(
                        'title' => 'ES Dashboard',
                        'url' => '/'.$admindir.'/syslog/top_dashboard'
                    ),

                    // 하단에서 생성
                    'list' => array(
                        'title' => 'Search & List',
                        'url' => '/'.$admindir.'/syslog/top'
                    ),
                    
                )
            ),

            'ssh' => array(
				'title' => 'SSH Server',
                'sub' => array(

                    'dashboard' => array(
                        'title' => 'ES Dashboard',
                        'url' => '/'.$admindir.'/syslog/ssh_dashboard'
                    ),
                    // 하단에서 생성
                    'list' => array(
                        'title' => 'Search & List',
                        'url' => '/'.$admindir.'/syslog/ssh'
                    ),
                    
                )
			),
        )
    ),

	 */
 

	'manage' => array(
		'level'	=> '7',
		'title' => 'Manage',
		'icon' => 'fa-cog',
		'sub' => array(
            'category' => array(
				'title' => 'Category',
				'url' => '/'.$admindir.'/manage/category'
			),
            'company' => array(
				'title' => 'Company',
				'url' => '/'.$admindir.'/manage/company'
			),
			'location' => array(
				'title' => 'Location',
				'url' => '/'.$admindir.'/manage/location'
			),
            'rack' => array(
				'title' => 'Rack Space',
				'url' => '/'.$admindir.'/manage/rack'
			),
			'vendor' => array(
				'title' => 'Vendor',
				'url' => '/'.$admindir.'/manage/vendor'
			),
            'supplier' => array(
				'title' => 'Supplier',
				'url' => '/'.$admindir.'/manage/supplier'
			),
		)
	),

    


	'system' => array(
		'level'	=> '7',
		'title' => 'System',
		'icon' => 'fa-cogs',
		'sub' => array(
            'type' => array(
				'title' => 'Assets Type',
				'url' => '/'.$admindir.'/system/type',
			),
            'status' => array(
				'title' => 'Status Labels',
				'url' => '/'.$admindir.'/system/status',
			),
            'ipclass' => array(
				'title' => 'IPs Class',
				'url' => '/'.$admindir.'/system/ipclass',
			),
	    /*
            'hostmap' => array(
				'title' => 'Host Mapping',
				'url' => '/'.$admindir.'/system/hostmap',
			),
	     */
			'setting' => array(
				'title' => 'Setting',
				'url' => '/'.$admindir.'/system/setting'
			),
			'manager' => array(
				'title' => 'Manager',
				'url' => '/'.$admindir.'/system/manager',
			),
			'history' => array(
				'title' => 'History',
				'url' => '/'.$admindir.'/system/history',
			),
		)
	), 


    /*
	'logs' => array(
		'level'	=> '7',
		'title' => 'Logs',
		'icon' => 'fa-database',

		'sub' => array(
            'admin' => array(
				'title' => '[SMENU] Logs',
				'url' => '/'.$admindir.'/logs/admin'
			),
            'menu' => array(
				'title' => '[SMENU] Menu Auth Logs',
				'url' => '/'.$admindir.'/logs/manage'
			),
		)
	),


	'report' => array(
		'level'	=> '7',
		'title' => 'Report',
		'icon' => 'fa-analytics',

		'sub' => array(
            'service' => array(
				'title' => 'Service',
				'url' => '/'.$admindir.'/report/service'
			),
		)
	),

 
	'statics' => array(
		'level'	=> '7',
		'title' => 'Statics',
		'icon' => 'fa-file-spreadsheet',
		'sub' => array(
            'company' => array(
				'title' => 'Company',
				'url' => '/'.$admindir.'/manage/company'
			),
		)
	),
    */


);



if(is_array($this->_ASSETS_TYPE) && sizeof($this->_ASSETS_TYPE) > 0) {
    foreach($this->_ASSETS_TYPE as $v) {
        $page_nav['assets']['sub']['type']['sub'][strtolower($v['at_name'])] = array(
            'title' => ucfirst($v['at_name']),
            'icon'  => $v['at_icon'],
            'url'   =>  '/'.$admindir.'/assets/type/'.strtolower($v['at_name']),
            'count' => $v['count'],
        );
    }
}


//configuration variables
$page_title = '';
$page_css = array();
$no_main_header = false; //set true for lock.php and login.php
$page_body_prop = array(); //optional properties for <body>
$page_html_prop = array(); //optional properties for <html>
?>
