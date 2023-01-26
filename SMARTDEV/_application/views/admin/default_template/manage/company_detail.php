<?php 
//include left panel (navigation)
//follow the tree in inc/config.ui.php
require_once realpath(dirname(__FILE__).'/../').'/inc/config.ui.php';


$active_key = array(
    "manage",               // 1 Depth menu
    "company"          // 2(Sub) Depth menu
);
$page_nav[$active_key[0]]["active"] = true;
$page_nav[$active_key[0]]["sub"][$active_key[1]]["active"] = true;
include realpath(dirname(__FILE__).'/../').'/inc/nav.php';

$title_symbol = 'fa-builing';
?>



<link rel="stylesheet" media="screen, print" href="<?=$assets_dir?>/css/formplugins/bootstrap-colorpicker/bootstrap-colorpicker.css">
<link rel="stylesheet" media="screen, print" href="<?=$assets_dir?>/css/formplugins/dropzone/dropzone.css">



<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL -->
<style type="text/css">
</style>


<main id="js-page-content" role="main" class="page-content">

    <?php include realpath(dirname(__FILE__).'/../').'/inc/breadcrumbs.php'; ?>

    <div class="row">
        <form name='edit_form' id="edit_form" class="needs-validation w-100" action="/<?=SHOP_INFO_ADMIN_DIR?>/manage/company_process" method="POST" novalidate autocomplete="off" encrypt="multipart/form-data">
        <input type="hidden" name="mode" value="<?=$mode?>">
        <input type="hidden" name="c_id" value="<?=$row['c_id']?>">

        <div class="col-xl-12">
            <div class="panel">
                <div class="panel-hdr">
                    <h2>Row <?=ucfirst($mode)?></h2>
                   
                    <div class="panel-toolbar">
                        <?=genDetailButton('company', $mode)?>    
                    </div>
                </div>

                <div class="panel-container show">
                    <div class="panel-content">

                        <div class="form-row form-group justify-content-md-center">

                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="c_code">
                                    <span data-i18n="content.company_code">Company Code</span>
                                    <span class="text-danger">*</span>
                                </label>
                                <?=getInputMask($type='upper', $name='c_code', $row['c_code'], 'required')?>
                            </div>


                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="c_name">
                                    <span data-i18n="content.company_name">Company Name</span> 
                                    <span class="text-danger">*</span>
                                </label>
                                <?=getInputMask($type='normal', $name='c_name', $row['c_name'], 'required')?>
                            </div>


                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="c_tel" data-i18n="content.tel">
                                   Company Tel 
                                </label>
                                <?=getInputMask('tel', $name='c_tel', $set_value=$row['c_tel'])?>
                            </div>


                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="c_biz_number" data-i18n="content.biz_no">
                                    Biz No.
                                </label>
                                <?=getInputMask('biz_number', $name='c_biz_number', $set_value=$row['c_biz_number'])?>
                            </div>


                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="c_biz_owner" data-i18n="content.owner">
                                    Owner
                                </label>
                                <?=getInputMask($type='normal', $name='c_biz_owner', $row['c_biz_owner'])?>

                            </div>


                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="c_is_active" data-i18n="content.is_active">
                                    Is Active 
                                </label>
                                <?=$select_active?>
                            </div>



                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="c_filename" data-i18n="content.image">
                                    Image 
                                </label>
                                <?=genDropzone()?> 
                            </div>

                            <?php if($mode == 'update') : ?>
                            <div class="col-12 col-lg-8 mb-3" data-i18n="content.updated_at">
                                <label class="form-label">
                                    Updated At 
                                </label>
                                <?=getInputMask('datetime', 'c_updated_at', $row['c_updated_at'], 'disabled')?>
                            </div>
                            
                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" data-i18n="content.created_at">
                                    Created At 
                                </label>
                                <?=getInputMask('datetime', 'c_created_at', $row['c_created_at'], 'disabled')?>
                            </div>
                            <?php endif;?>

                        </div>

                    </div>



                    <div class="panel-content py-2 rounded-bottom border-faded border-left-0 border-right-0 border-bottom-0 text-muted d-flex">
                        <?=genDetailButton('company', $mode)?>    
                    </div>


                <div>
            </div>
        </div>

        </form>


    </div>
</main>



<form name='del_form' id="del_form" action="/<?=SHOP_INFO_ADMIN_DIR?>/manage/company_process" method="POST">
    <input type='hidden' name='mode' value='delete' />
    <input type="hidden" name="c_id" value="<?=$row['c_id']?>">
</form>




<script type="text/javascript">
$(document).ready(function() {

    var _dz = new Dropzone('div#div_dropzone', dzOptions);
    
    <?php if($mode == 'update' && strlen($row['c_filename']) > 0) : ?>
    var mockFile = { name:"<?=$row['c_filename']?>", size:<?=$img_size?>, accepted:true};
    _dz.files.push(mockFile);
    _dz.emit("addedfile", mockFile);
    _dz.emit("thumbnail", mockFile, "<?=$img_url?>");
    _dz.emit("complete", mockFile)
    <?php endif; ?>

});
</script>
