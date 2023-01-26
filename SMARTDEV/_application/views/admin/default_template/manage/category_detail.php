<?php 
//include left panel (navigation)
//follow the tree in inc/config.ui.php
require_once realpath(dirname(__FILE__).'/../').'/inc/config.ui.php';


$active_key = array(
    "manage",               // 1 Depth menu
    "category"          // 2(Sub) Depth menu
);
$page_nav[$active_key[0]]["active"] = true;
$page_nav[$active_key[0]]["sub"][$active_key[1]]["active"] = true;
include realpath(dirname(__FILE__).'/../').'/inc/nav.php';

$title_symbol = 'fa-list-ol';
?>


<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL -->
<style type="text/css">
</style>


<main id="js-page-content" role="main" class="page-content">

    <?php include realpath(dirname(__FILE__).'/../').'/inc/breadcrumbs.php'; ?>

    <div class="row">
        <form name='edit_form' id="edit_form" class="needs-validation w-100" action="/<?=SHOP_INFO_ADMIN_DIR?>/manage/category_process" method="POST" novalidate autocomplete="off" encrypt="multipart/form-data">
        <input type="hidden" name="mode" value="<?=$mode?>">
        <input type="hidden" name="ct_id" value="<?=$row['ct_id']?>">

        <div class="col-xl-12">
            <div class="panel">
                <div class="panel-hdr">
                    <h2>Row <?=ucfirst($mode)?></h2>
                   
                    <div class="panel-toolbar">
                        <?=genDetailButton('category', $mode)?>    
                    </div>
                </div>

                <div class="panel-container show">
                    <div class="panel-content">

                        <div class="form-row form-group justify-content-md-center">



                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="ct_type_id">
                                    <span data-i18n="content.assets_type">Assets Type</span> 
                                    <span class="text-danger">*</span>
                                    <?php if($mode == 'update') : ?>
                                    <?=genLinkButton('type', $row['ct_type_id'])?>
                                    <?php endif; ?>
                                    <?=genNewButton('type')?>
                                </label>
                                <?=$select_type?> 
                            </div>



                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="ct_name">
                                    <span data-i18n="content.ct_name">Category Name</span>
                                    <span class="text-danger">*</span>
                                </label>
                                <?=getInputMask($type='normal', $name='ct_name', $row['ct_name'], 'required')?>
                            </div>



                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="ct_description" data-i18n="content.memo">
                                    Category Description 
                                </label>
                                <?=getTextArea($name='ct_description', $row['ct_description'])?>
                            </div>


                            <div class="col-12 col-lg-8 form-group">
                                <label class="form-label" for="name-f">
                                    <span class="content.icon">Icon</span> 
                                    <?=genIconFindButton();?>
                                </label>
                                <?=getInputMask($type='normal', $name='ct_icon', $row['ct_icon'])?>
                                <span class="help-block">fa-alicorn</span>
                            </div>



                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="ct_is_active" data-i18n="content.is_active">
                                    Is Active 
                                </label>
                                <?=$select_active?> 
                            </div>



                            <div class="col-12 col-lg-8 form-group">
                                <label class="form-label" for="name-f" data-i18n="content.image">
                                    Image 
                                </label>
                                <?=genDropzone()?> 
                            </div>


                            <?php if($mode == 'update') : ?>
                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" data-i18n="content.updated_at">
                                    Updated At 
                                </label>
                                <?=getInputMask('datetime', 'ct_updated_at', $row['ct_updated_at'], 'disabled')?>
                            </div>
                            
                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" data-i18n="content.created_at">
                                    Created At 
                                </label>
                                <?=getInputMask('datetime', 'ct_created_at', $row['ct_created_at'], 'disabled')?>
                            </div>
                            <?php endif;?>

                        </div>

                    </div>



                    <div class="panel-content py-2 rounded-bottom border-faded border-left-0 border-right-0 border-bottom-0 text-muted d-flex">
                        <?=genDetailButton('category', $mode)?>    
                    </div>


                <div>
            </div>
        </div>

        </form>


    </div>
</main>



<form name='del_form' id="del_form" action="/<?=SHOP_INFO_ADMIN_DIR?>/manage/category_process" method="POST">
    <input type='hidden' name='mode' value='delete' />
    <input type="hidden" name="ct_id" value="<?=$row['ct_id']?>">
</form>




<script type="text/javascript">
$(document).ready(function() {

    var _dz = new Dropzone('div#div_dropzone', dzOptions);
    
    <?php if($mode == 'update' && strlen($row['ct_filename']) > 0) : ?>
    var mockFile = { name:"<?=$row['ct_filename']?>", size:<?=$img_size?>, accepted:true};
    _dz.files.push(mockFile);
    _dz.emit("addedfile", mockFile);
    _dz.emit("thumbnail", mockFile, "<?=$img_url?>");
    _dz.emit("complete", mockFile)
    <?php endif; ?>

});
</script>
