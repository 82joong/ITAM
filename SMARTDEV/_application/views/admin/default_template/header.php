<!DOCTYPE html>
<!-- 
Template Name:  SmartAdmin Responsive WebApp - Template build with Twitter Bootstrap 4
Version: 4.4.5
Author: Sunnyat Ahmmed
Website: http://gootbootstrap.com
Purchase: https://wrapbootstrap.com/theme/smartadmin-responsive-webapp-WB0573SK0
License: You must have a valid license purchased only from wrapbootstrap.com (link above) in order to legally use this theme for your project.
-->
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title><?=$page_title?></title>
        <meta name="description" content="">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, user-scalable=no, minimal-ui">
        <!-- Call App Mode on ios devices -->
        <meta name="apple-mobile-web-app-capable" content="yes" />
        <!-- Remove Tap Highlight on Windows Phone IE -->
        <meta name="msapplication-tap-highlight" content="no">
        <!-- base css -->
        <link rel="stylesheet" media="screen, print" href="<?=$assets_dir?>/css/vendors.bundle.css">
        <link rel="stylesheet" media="screen, print" href="<?=$assets_dir?>/css/app.bundle.css">
        <link rel="stylesheet" media="screen, print" href="<?=$assets_dir?>/css/datagrid/datatables/datatables.bundle.css">
        <link rel="stylesheet" media="screen, print" href="<?=$assets_dir?>/css/formplugins/bootstrap-datepicker/bootstrap-datepicker.css">
        <link rel="stylesheet" media="screen, print" href="<?=$assets_dir?>/css/clockpicker.css">
        <link rel="stylesheet" media="screen, print" href="<?=$assets_dir?>/css/notifications/sweetalert2/sweetalert2.bundle.css">


        <link rel="stylesheet" media="screen, print" href="<?=$assets_dir?>/css/miscellaneous/reactions/reactions.css">
        <link rel="stylesheet" media="screen, print" href="<?=$assets_dir?>/css/miscellaneous/fullcalendar/fullcalendar.bundle.css">
        <link rel="stylesheet" media="screen, print" href="<?=$assets_dir?>/css/miscellaneous/jqvmap/jqvmap.bundle.css">


        <!-- Place favicon.ico in the root directory -->
        <link rel="apple-touch-icon" sizes="180x180" href="<?=$assets_dir?>/img/favicon/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="<?=$assets_dir?>/img/favicon/favicon-32x32.png">
        <link rel="mask-icon" href="<?=$assets_dir?>/img/favicon/safari-pinned-tab.svg" color="#5bbad5">
        
        <link rel="stylesheet" media="screen, print" href="<?=$assets_dir?>/css/statistics/c3/c3.css">


        <?php
        if(isset($custom_css['page_css']) && is_array($custom_css['page_css']) && sizeof($custom_css['page_css']) > 0) {
            foreach ($custom_css['page_css'] as $css) {
                if(strlen($css) < 1) continue;
                echo '<link rel="stylesheet" type="text/css" media="screen" href="'.$assets_dir.'/css/'.$css.'">';
            }
        }
        ?>

        <link rel="stylesheet" media="screen, print" href="<?=$assets_dir?>/css/font-awesome.min.css">
        <link rel="stylesheet" media="screen, print" href="<?=$assets_dir?>/css/fa-light.css">
        <link rel="stylesheet" media="screen, print" href="<?=$assets_dir?>/css/fa-regular.css">
        <link rel="stylesheet" media="screen, print" href="<?=$assets_dir?>/css/fa-solid.css">
        <link rel="stylesheet" media="screen, print" href="<?=$assets_dir?>/css/fa-duotone.css">
        <link rel="stylesheet" media="screen, print" href="<?=$assets_dir?>/css/fa-brands.css">


        <link rel="stylesheet" media="screen, print" href="<?=$assets_dir?>/css/admin_override.css?v=<?=filemtime(FCPATH.'admin_assets/css/admin_override.css')?>">
        <link rel="stylesheet" media="screen, print" href="<?=$assets_dir?>/css/formplugins/select2/select2.bundle.css">
        <link rel="stylesheet" media="screen, print" href="<?=$assets_dir?>/css/formplugins/dropzone/dropzone.css">


        <link rel="stylesheet" media="screen, print" href="<?=$assets_dir?>/lib/tagsinput/bootstrap-tagsinput.css?v=<?=filemtime(FCPATH.'admin_assets/lib/tagsinput/bootstrap-tagsinput.css')?>">
        <link rel="stylesheet" media="screen, print" href="<?=$assets_dir?>/css/formplugins/bootstrap-daterangepicker/bootstrap-daterangepicker.css">

        <link rel="stylesheet" media="screen, print" href="<?=$assets_dir?>/css/formplugins/summernote/summernote.css">

        <script src="<?=$assets_dir?>/js/browser.js"></script>

        <?php if(IS_REAL_SERVER) : ?>
        <script src="<?=$assets_dir?>/js/vue.min.js"></script>
        <?php else : ?>
        <script src="<?=$assets_dir?>/js/vue.js"></script>
        <?php endif; ?>

		<script src="<?=$assets_dir?>/js/libs/jquery-2.0.2.min.js"></script>
		<script src="<?=$assets_dir?>/js/libs/jquery-ui-1.10.3.min.js"></script>
		<script src="<?=$assets_dir?>/js/common.js?v=<?=filemtime(FCPATH.'admin_assets/js/common.js')?>"></script>
        <script src="<?=$assets_dir?>/js/lodash.min.js"></script>


        <link rel="stylesheet" media="screen, print" href="<?=$assets_dir?>/css/formplugins/highlight/agate.css">
        <script src="<?=$assets_dir?>/js/formplugins/highlight/highlight.min.js"></script>


        <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-23WG1HDWC0"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', 'G-23WG1HDWC0');
        </script>

    </head>
    <body class="mod-bg-1 mod-nav-link ">
        <!-- DOC: script to save and load page settings -->
        <script>
            /**
             *	This script should be placed right after the body tag for fast execution 
             *	Note: the script is written in pure javascript and does not depend on thirdparty library
             **/
            'use strict';

            var classHolder = document.getElementsByTagName("BODY")[0],
                /** 
                 * Load from localstorage
                 **/
                themeSettings = (localStorage.getItem('themeSettings')) ? JSON.parse(localStorage.getItem('themeSettings')) :
                {},
                themeURL = themeSettings.themeURL || '',
                themeOptions = themeSettings.themeOptions || '';
            /** 
             * Load theme options
             **/
            if (themeSettings.themeOptions)
            {
                classHolder.className = themeSettings.themeOptions;
                console.log("%c✔ Theme settings loaded", "color: #148f32");
            }
            else
            {
                console.log("Heads up! Theme settings is empty or does not exist, loading default settings...");
            }
            if (themeSettings.themeURL && !document.getElementById('mytheme'))
            {
                var cssfile = document.createElement('link');
                cssfile.id = 'mytheme';
                cssfile.rel = 'stylesheet';
                cssfile.href = themeURL;
                document.getElementsByTagName('head')[0].appendChild(cssfile);
            }
            /** 
             * Save to localstorage 
             **/
            var saveSettings = function()
            {
                themeSettings.themeOptions = String(classHolder.className).split(/[^\w-]+/).filter(function(item)
                {
                    return /^(nav|header|mod|display)-/i.test(item);
                }).join(' ');
                if (document.getElementById('mytheme'))
                {
                    themeSettings.themeURL = document.getElementById('mytheme').getAttribute("href");
                };
                localStorage.setItem('themeSettings', JSON.stringify(themeSettings));
            }
            /** 
             * Reset settings
             **/
            var resetSettings = function()
            {
                localStorage.setItem("themeSettings", "");
            }

        </script>


        <?php if(IS_REAL_SERVER == false) : ?>
        <div class="alert alert-danger alert-dismissible fade show m-2">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true"><i class="fal fa-times"></i></span>
            </button>
            <div class="d-flex align-items-center">
                <div class="alert-icon width-8">
                    <span class="icon-stack icon-stack-xl">
                        <i class="base-2 icon-stack-3x color-danger-400"></i>
                        <i class="base-10 text-white icon-stack-1x"></i>
                        <i class="ni md-profile color-danger-800 icon-stack-2x"></i>
                    </span>
                </div>
                <div class="flex-1 pl-1">
                    <span class="h2">SandBox [ITAM] as a Service</span>
                    <br>
                    You can enter data freely here.
                </div>
            </div>
        </div>
        <?php endif;?>


        <!-- BEGIN Page Wrapper -->
        <div class="page-wrapper">
            <div class="page-inner <?=isset($custom_css['page_inner_class']) ? $custom_css['page_inner_class'] : ""?>">

