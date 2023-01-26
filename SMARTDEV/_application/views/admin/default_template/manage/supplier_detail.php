<?php 
//include left panel (navigation)
//follow the tree in inc/config.ui.php
require_once realpath(dirname(__FILE__).'/../').'/inc/config.ui.php';


$active_key = array(
    "manage",               // 1 Depth menu
    "supplier"          // 2(Sub) Depth menu
);
$page_nav[$active_key[0]]["active"] = true;
$page_nav[$active_key[0]]["sub"][$active_key[1]]["active"] = true;
include realpath(dirname(__FILE__).'/../').'/inc/nav.php';

$title_symbol = 'fa-hand-holding-box';
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
        <form name='edit_form' id="edit_form" class="needs-validation w-100" action="/<?=SHOP_INFO_ADMIN_DIR?>/manage/supplier_process" method="POST" novalidate autocomplete="off" encrypt="multipart/form-data">
        <input type="hidden" name="mode" value="<?=$mode?>">
        <input type="hidden" name="sp_id" value="<?=$row['sp_id']?>">

        <div class="col-xl-12">
            <div class="panel">
                <div class="panel-hdr">
                    <h2>Row <?=ucfirst($mode)?></h2>
                   
                    <div class="panel-toolbar">
                        <?=genDetailButton('supplier', $mode)?>    
                    </div>
                </div>

                <div class="panel-container show">
                    <div class="panel-content">

                        <div class="form-row form-group justify-content-md-center">

                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="sp_name">
                                    <span data-i18n="content.supplier_name">Supplier Name</span> 
                                    <span class="text-danger">*</span>
                                </label>
                                <?=getInputMask($type='normal', $name='sp_name', $row['sp_name'], 'required')?>
                            </div>

                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="sp_address" data-i18n="content.address">
                                    Address 
                                </label>
                                <input type="text" class="form-control mb-1" name="sp_address[]" placeholder="Address1" value="<?=$row['sp_address'][0]?>">
                                <input type="text" class="form-control" name="sp_address[]" placeholder="Address2" value="<?=$row['sp_address'][1]?>">
                            </div>


                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" data-i18n="content.city">
                                    City 
                                </label>
                                <?=getInputMask($type='normal', $name='sp_city', $row['sp_city'])?>
                            </div>


                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" data-i18n="content.country">
                                    Country 
                                </label>
                                <?=$select_country?>
                            </div>


                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" data-i18n="content.zip">
                                    Zip
                                </label>
                                <?=getInputMask('zip', $name='sp_zip', $set_value=$row['sp_zip'])?>
                            </div>


                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" data-i18n="content.contact_name">
                                    Contact Name 
                                </label>
                                <?=getInputMask($type='normal', $name='sp_contact_name', $row['sp_contact_name'])?>
                            </div>



                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="validation_sp_tel" data-i18n="content.tel">
                                    Tel.
                                </label>
                                <?=getInputMask('tel', $name='sp_tel', $set_value=$row['sp_tel'])?>
                            </div>


                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="validation_sp_fax" data-i18n="content.fax">
                                    Fax.
                                </label>
                                <?=getInputMask('fax', $name='sp_fax', $set_value=$row['sp_fax'])?>
                            </div>



                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="validation_sp_email" data-i18n="content.email">
                                    Email 
                                </label>
                                <?=getInputMask('email', $name='sp_email', $set_value=$row['sp_email'])?>
                            </div>




                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="validation_sp_url">
                                   URL 
                                </label>
                                <?=getInputMask('url', $name='sp_url', $set_value=$row['sp_url'])?>
                            </div>



                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" data-i18n="content.memo">
                                    Memo 
                                </label>
                                <?=getTextArea($name='sp_memo', $row['sp_memo'])?>
                            </div>


                            <?php if($mode == 'update') : ?>
                            <div class="col-12 col-lg-8 mb-3" data-i18n="content.updated_at">
                                <label class="form-label">
                                    Updated At 
                                </label>
                                <?=getInputMask('datetime', 'sp_updated_at', $row['sp_updated_at'], 'disabled')?>
                            </div>
                            
                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" data-i18n="content.created_at">
                                    Created At 
                                </label>
                                <?=getInputMask('datetime', 'sp_created_at', $row['sp_created_at'], 'disabled')?>
                            </div>
                            <?php endif;?>

                        </div>

                    </div>


                    <div class="panel-content py-2 rounded-bottom border-faded border-left-0 border-right-0 border-bottom-0 text-muted d-flex">
                        <?=genDetailButton('supplier', $mode)?>    
                    </div>

                <div>
            </div>
        </div>

        </form>


    </div>
</main>



<form name='del_form' id="del_form" action="/<?=SHOP_INFO_ADMIN_DIR?>/manage/supplier_process" method="POST">
    <input type='hidden' name='mode' value='delete' />
    <input type="hidden" name="sp_id" value="<?=$row['sp_id']?>">
</form>


<script type="text/javascript">
$(document).ready(function() {
});
</script>
