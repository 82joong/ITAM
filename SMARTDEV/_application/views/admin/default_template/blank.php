<?php 

$page_css[] = "";
include realpath(dirname(__FILE__)).'/header.php';

//include left panel (navigation)
//follow the tree in inc/config.ui.php
//$page_nav["customer"]["sub"]["contact"]["active"] = true;
include realpath(dirname(__FILE__)).'/inc/nav.php';
?>

<main id="js-page-content" role="main" class="page-content">
    <ol class="breadcrumb page-breadcrumb">
        <li class="breadcrumb-item"><a href="javascript:void(0);">SmartAdmin</a></li>
        <li class="breadcrumb-item">Documentation</li>
        <li class="breadcrumb-item active">General Docs</li>
        <li class="position-absolute pos-top pos-right d-none d-sm-block"><span class="js-get-date"></span></li>
    </ol>
    <div class="subheader">
        <h1 class="subheader-title">
            <i class='subheader-icon fal fa-book'></i> Title: <span class='fw-300'>Subject</span>
            <small>
            Sub description
            </small>
        </h1>
    </div>
</main>
<!-- this overlay is activated only when mobile menu is triggered -->
<div class="page-content-overlay" data-action="toggle" data-class="mobile-nav-on"></div> <!-- END Page Content -->
