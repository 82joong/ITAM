<?php 
//include left panel (navigation)
//follow the tree in inc/config.ui.php
require_once realpath(dirname(__FILE__).'/../').'/inc/config.ui.php';


$active_key = array(
    "manage",               // 1 Depth menu
    "vendor"          // 2(Sub) Depth menu
);
$page_nav[$active_key[0]]["active"] = true;
$page_nav[$active_key[0]]["sub"][$active_key[1]]["active"] = true;
include realpath(dirname(__FILE__).'/../').'/inc/nav.php';

$title_symbol = 'fa-warehouse';
?>



<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL -->
<style type="text/css">
</style>


<main id="js-page-content" role="main" class="page-content">

    <?php include realpath(dirname(__FILE__).'/../').'/inc/breadcrumbs.php'; ?>

    <div class="row">
        <form name='edit_form' id="edit_form" class="needs-validation w-100" action="/<?=SHOP_INFO_ADMIN_DIR?>/manage/vendor_process" method="POST" novalidate autocomplete="off" encrypt="multipart/form-data">
        <input type="hidden" name="mode" value="<?=$mode?>">
        <input type="hidden" name="vd_id" value="<?=$row['vd_id']?>">

        <div class="col-xl-12">
            <div class="panel">
                <div class="panel-hdr">
                    <h2>Row <?=ucfirst($mode)?></h2>
                   
                    <div class="panel-toolbar">
                        <?=genDetailButton('vendor', $mode)?>    
                    </div>
                </div>

                <div class="panel-container show">
                    <div class="panel-content">

                        <div class="form-row form-group justify-content-md-center">

                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="validation_vd_name">
                                    Vendor Name 
                                    <span class="text-danger">*</span>
                                </label>
                                <?=getInputMask($type='normal', $name='vd_name', $row['vd_name'], 'required')?>
                            </div>


                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="validation_vd_url">
                                   Vendor URL 
                                </label>
                                <?=getInputMask('url', $name='vd_url', $set_value=$row['vd_url'])?>
                            </div>


                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="validation_vd_support_url">
                                   Support URL 
                                </label>
                                <?=getInputMask('url', $name='vd_support_url', $set_value=$row['vd_support_url'])?>
                            </div>



                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="validation_vd_support_tel">
                                    Support Tel.
                                </label>
                                <?=getInputMask('tel', $name='vd_support_tel', $set_value=$row['vd_support_tel'])?>
                            </div>


                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="validation_vd_support_email">
                                    Support Email 
                                </label>
                                <?=getInputMask('email', $name='vd_support_email', $set_value=$row['vd_support_email'])?>
                            </div>



                            <div class="col-12 col-lg-8 form-group">
                                <label class="form-label" for="name-f">
                                    Image 
                                </label>
                                <?=genDropzone()?> 
                            </div>



                            <?php if($mode == 'update') : ?>
                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label">
                                    Updated At 
                                </label>
                                <?=getInputMask('datetime', 'vd_updated_at', $row['vd_updated_at'], 'disabled')?>
                            </div>
                            
                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label">
                                    Created At 
                                </label>
                                <?=getInputMask('datetime', 'vd_created_at', $row['vd_created_at'], 'disabled')?>
                            </div>
                            <?php endif;?>

                        </div>

                    </div>



                    <div class="panel-content py-2 rounded-bottom border-faded border-left-0 border-right-0 border-bottom-0 text-muted d-flex">
                        <?=genDetailButton('vendor', $mode)?>    
                    </div>


                <div>
            </div>
        </div>

        </form>


    </div>
</main>



<form name='del_form' id="del_form" action="/<?=SHOP_INFO_ADMIN_DIR?>/manage/vendor_process" method="POST">
    <input type='hidden' name='mode' value='delete' />
    <input type="hidden" name="vd_id" value="<?=$row['vd_id']?>">
</form>




<script type="text/javascript">
$(document).ready(function() {

    var _dz = new Dropzone('div#div_dropzone', dzOptions);
    
    <?php if($mode == 'update' && strlen($row['vd_filename']) > 0) : ?>
    var mockFile = { name:"<?=$row['vd_filename']?>", size:<?=$img_size?>, accepted:true};
    _dz.files.push(mockFile);
    _dz.emit("addedfile", mockFile);
    _dz.emit("thumbnail", mockFile, "<?=$img_url?>");
    _dz.emit("complete", mockFile)
    <?php endif; ?>

});
</script>

