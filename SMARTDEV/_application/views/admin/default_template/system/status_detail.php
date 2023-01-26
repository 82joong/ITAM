<?php 
//include left panel (navigation)
//follow the tree in inc/config.ui.php
require_once realpath(dirname(__FILE__).'/../').'/inc/config.ui.php';


$active_key = array(
    "system",               // 1 Depth menu
    "status"          // 2(Sub) Depth menu
);
$page_nav[$active_key[0]]["active"] = true;
$page_nav[$active_key[0]]["sub"][$active_key[1]]["active"] = true;
include realpath(dirname(__FILE__).'/../').'/inc/nav.php';

$title_symbol = 'fa-tags';
?>



<link rel="stylesheet" media="screen, print" href="<?=$assets_dir?>/css/formplugins/bootstrap-colorpicker/bootstrap-colorpicker.css">



<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL -->
<style type="text/css">
</style>


<main id="js-page-content" role="main" class="page-content">

    <?php include realpath(dirname(__FILE__).'/../').'/inc/breadcrumbs.php'; ?>

    <div class="row">
        <form name='edit_form' id="edit_form" class="needs-validation w-100" action="/<?=SHOP_INFO_ADMIN_DIR?>/system/status_process" method="POST" novalidate autocomplete="off" >
        <input type="hidden" name="mode" value="<?=$mode?>">
        <input type="hidden" name="s_id" value="<?=$row['s_id']?>">

        <div class="col-xl-12">
            <div class="panel">
                <div class="panel-hdr">
                    <h2>Row <?=ucfirst($mode)?></h2>
                   
                    <div class="panel-toolbar">
                        <?=genDetailButton('status', $mode)?>    

                        <?php if($this->common->checkDelete() === TRUE): ?>
                        <?=genDeleteButton();?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="panel-container show">
                    <div class="panel-content">

                        <div class="form-row form-group justify-content-md-center">


                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="s_name">
                                    <span data-i18n="content.as_name">Name</span> 
                                    <span class="text-danger">*</span>
                                </label>
                                <?=getInputMask('normal', 's_name', $row['s_name'], $opt='required')?>
                            </div>


                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="s_code">
                                    <span data-i18n="content.as_code">Code</span> 
                                    <span class="text-danger">*</span>
                                </label>
                                <?=getInputMask('upper', 's_code', $row['s_code'], $opt='required')?>
                            </div>




                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="s_description" data-i18n="content.description">
                                    Description 
                                </label>
                                <?=getTextArea('s_description', $row['s_description'])?>
                            </div>



                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="s_color_code" data-i18n="content.color">
                                    Color 
                                </label>

                                <div id="cp1" class="input-group colorpicker-element" title="Using input value" data-colorpicker-id="2">
                                    <input type="text" class="form-control input-lg" name="s_color_code" value="<?=$row['s_color_code']?>">
                                    <span class="input-group-append">
                                        <span class="input-group-text colorpicker-input-addon" data-original-title="" title="" tabindex="0">
                                            <i style="background: rgb(255, 255, 255);"></i>
                                        </span>
                                    </span>
                                </div>
                                <span class="help-block">글자, 아이콘 색상 구분</span>
                            </div>



                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="s_is_active" data-i18n="content.is_active">
                                    Is Active 
                                </label>
                                <?=$select_active?>
                            </div>


                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="s_show_nav" data-i18n="content.show_nav">
                                    Show Nav 
                                </label>
                                <?=$select_nav?>
                            </div>


                            <?php if($mode == 'update') : ?>
                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" data-i18n="content.updated_at">
                                    Updated At 
                                </label>
                                <input type="text" class="form-control" name="s_updated_at" value="<?=$row['s_updated_at']?>" disabled >
                            </div>
                            
                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" data-i18n="content.created_at">
                                    Created At 
                                </label>
                                <input type="text" class="form-control" name="s_created_at" value="<?=$row['s_created_at']?>" disabled >
                            </div>
                            <?php endif;?>

                        </div>

                    </div>



                    <div class="panel-content py-2 rounded-bottom border-faded border-left-0 border-right-0 border-bottom-0 text-muted d-flex">
                        <?=genDetailButton('status', $mode)?>    
                    </div>


                <div>
            </div>
        </div>

        </form>
    </div>
</main>



<form name='del_form' id="del_form" action="/<?=SHOP_INFO_ADMIN_DIR?>/system/status_process" method="POST">
    <input type='hidden' name='mode' value='delete' />
    <input type="hidden" name="s_id" value="<?=$row['s_id']?>">
    <input type="hidden" name="del_msg" value="">
</form>




<script type="text/javascript">
$(document).ready(function() {
    $('#cp1').colorpicker();
});
</script>
