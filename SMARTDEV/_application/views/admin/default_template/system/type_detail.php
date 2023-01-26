<?php 
//include left panel (navigation)
//follow the tree in inc/config.ui.php
require_once realpath(dirname(__FILE__).'/../').'/inc/config.ui.php';


$active_key = array(
    "system",               // 1 Depth menu
    "type"          // 2(Sub) Depth menu
);
$page_nav[$active_key[0]]["active"] = true;
$page_nav[$active_key[0]]["sub"][$active_key[1]]["active"] = true;
include realpath(dirname(__FILE__).'/../').'/inc/nav.php';

$title_symbol = 'fa-folder-tree';
?>



<link rel="stylesheet" media="screen, print" href="<?=$assets_dir?>/css/formplugins/bootstrap-colorpicker/bootstrap-colorpicker.css">



<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL -->
<style type="text/css">
</style>


<main id="js-page-content" role="main" class="page-content">

    <?php include realpath(dirname(__FILE__).'/../').'/inc/breadcrumbs.php'; ?>

    <div class="row">
        <form name='edit_form' id="edit_form" class="needs-validation w-100" action="/<?=SHOP_INFO_ADMIN_DIR?>/system/type_process" method="POST" novalidate autocomplete="off" >
        <input type="hidden" name="mode" value="<?=$mode?>">
        <input type="hidden" name="at_id" value="<?=$row['at_id']?>">

        <div class="col-xl-12">
            <div class="panel">
                <div class="panel-hdr">
                    <h2>Row <?=ucfirst($mode)?></h2>
                   
                    <div class="panel-toolbar">
                        <?=genDetailButton('type', $mode)?>    
                    </div>
                </div>

                <div class="panel-container show">
                    <div class="panel-content">

                        <div class="form-row form-group justify-content-md-center">


                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="at_name">
                                    <span data-i18n="content.at_name">Name</span> 
                                    <span class="text-danger">*</span>
                                </label>
                                <?=getInputMask($type='normal', $name='at_name', $row['at_name'], 'required')?>
                            </div>



                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="at_description" data-i18n="content.description">
                                    Description 
                                </label>
                                <?=getTextArea($name='at_description', $row['at_description'])?>
                            </div>


                            <div class="col-12 col-lg-8 form-group">
                                <label class="form-label" for="name-f">
                                    <span data-i18n="content.icon">Icon</span>
                                    <?=genIconFindButton();?>
                                </label>
                                <?=getInputMask($type='normal', $name='at_icon', $row['at_icon'])?>
                                <span class="help-block">ex> fa-alicorn</span>
                            </div>


                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="at_color" data-i18n="content.color">
                                    Color 
                                </label>

                                <div id="cp1" class="input-group colorpicker-element" title="Using input value" data-colorpicker-id="2">
                                    <input type="text" class="form-control input-lg" name="at_color" value="<?=$row['at_color']?>">
                                    <span class="input-group-append">
                                        <span class="input-group-text colorpicker-input-addon" data-original-title="" title="" tabindex="0">
                                            <i style="background: rgb(255, 255, 255);"></i>
                                        </span>
                                    </span>
                                </div>
                                <span class="help-block">글자, 아이콘 색상 구분</span>
                            </div>



                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="at_is_active" data-i18n="content.is_active">
                                    Is Active 
                                </label>
                                <select class="custom-select" name="at_is_active" required="">
                                <?php foreach($active_data as $key=>$name): ?>
                                    <?php
                                    $selected = '';
                                    if($row['at_is_active'] == $key) $selected = 'selected';
                                    ?>
                                    <option value="<?=$key?>" <?=$selected?>><?=$name?></option>
                                <?php endforeach;?>
                                </select>
                            </div>



                            <?php if($mode == 'update') : ?>
                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" data-i18n="content.updated_at">
                                    Updated At 
                                </label>
                                <?=getInputMask('datetime', 'at_updated_at', $row['at_updated_at'], 'disabled')?>
                            </div>
                            
                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" data-i18n="content.created_at">
                                    Created At 
                                </label>
                                <?=getInputMask('datetime', 'at_created_at', $row['at_created_at'], 'disabled')?>
                            </div>
                            <?php endif;?>

                        </div>
                    </div>


                    <div class="panel-content py-2 rounded-bottom border-faded border-left-0 border-right-0 border-bottom-0 text-muted d-flex">
                        <?=genDetailButton('type', $mode)?>    
                    </div>

                <div>
            </div>
        </div>

        </form>
    </div>
</main>



<form name='del_form' id="del_form" action="/<?=SHOP_INFO_ADMIN_DIR?>/system/type_process" method="POST">
    <input type='hidden' name='mode' value='delete' />
    <input type="hidden" name="at_id" value="<?=$row['at_id']?>">
</form>




<script type="text/javascript">
$(document).ready(function() {
    $('#cp1').colorpicker();
});
</script>
