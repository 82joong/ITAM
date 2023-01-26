<?php 
//include left panel (navigation)
//follow the tree in inc/config.ui.php
require_once realpath(dirname(__FILE__).'/../').'/inc/config.ui.php';


$active_key = array(
    "manage",               // 1 Depth menu
    "location"          // 2(Sub) Depth menu
);
$page_nav[$active_key[0]]["active"] = true;
$page_nav[$active_key[0]]["sub"][$active_key[1]]["active"] = true;
include realpath(dirname(__FILE__).'/../').'/inc/nav.php';

$title_symbol = 'fa-location-circle';
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
        <form name='edit_form' id="edit_form" class="needs-validation w-100" action="/<?=SHOP_INFO_ADMIN_DIR?>/manage/location_process" method="POST" novalidate autocomplete="off" encrypt="multipart/form-data">
        <input type="hidden" name="mode" value="<?=$mode?>">
        <input type="hidden" name="l_id" value="<?=$row['l_id']?>">

        <div class="col-xl-12">
            <div class="panel">
                <div class="panel-hdr">
                    <h2>Row <?=ucfirst($mode)?></h2>
                   
                    <div class="panel-toolbar">
                        <?=genDetailButton('location', $mode)?>    
                    </div>
                </div>

                <div class="panel-container show">
                    <div class="panel-content">

                        <div class="form-row form-group justify-content-md-center">

                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="l_name">
                                    <span data-i18n="content.location_name">Location Name</span>
                                    <span class="text-danger">*</span>
                                </label>
                                <?=getInputMask('normal', 'l_name', $row['l_name'], 'required')?>
                            </div>


                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="l_code">
                                    <span data-i18n="content.location_code">Location Code</span>
                                    <span class="text-danger">*</span>
                                </label>
                                <?=getInputMask('upper', 'l_code', $row['l_code'], 'required')?>
                            </div>



                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="l_manager_name" data-i18n="content.manager_name">
                                    Manager Name 
                                </label>
                                <?=getInputMask('normal', 'l_manager_name', $row['l_manager_name'])?>
                            </div>


                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="validation_l_address" data-i18n="content.address">
                                    Address 
                                </label>
                                <input type="text" class="form-control mb-1" name="l_address[]" placeholder="Address1" value="<?=$row['l_address'][0]?>">
                                <input type="text" class="form-control" name="l_address[]" placeholder="Address2" value="<?=$row['l_address'][1]?>">
                            </div>


                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" data-i18n="content.city">
                                    City 
                                </label>
                                <?=getInputMask('normal', 'l_city', $row['l_city'])?>
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
                                <?=getInputMask('zip', $name='l_zip', $set_value=$row['l_zip'])?>
                            </div>


                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label">
                                    Geo. Position
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text text-success">
                                            <i class="ni ni-map fs-xl"></i>
                                        </span>
                                    </div>
                                    <input type="number" aria-label="Latitude" class="form-control" placeholder="Latitude" name="l_lat" value="<?=$row['l_lat']?>">
                                    <input type="number" aria-label="Longitude" class="form-control" placeholder="Longitude" name="l_ong" value="<?=$row['l_long']?>">
                                </div>
                            </div>


                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="validation_l_tel" data-i18n="content.tel">
                                    Tel.
                                </label>
                                <?=getInputMask('tel', $name='l_tel', $set_value=$row['l_tel'])?>
                            </div>


                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" data-i18n="content.memo">
                                    Memo 
                                </label>
                                <textarea class="form-control" name="l_memo"><?=$row['l_memo']?></textarea>
                            </div>


                            <?php if($mode == 'update') : ?>
                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" data-i18n="content.updated_at">
                                    Updated At 
                                </label>
                                <?=getInputMask('datetime', 'l_updated_at', $row['l_updated_at'], 'disabled')?>
                            </div>
                            
                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" data-i18n="content.created_at">
                                    Created At 
                                </label>
                                <?=getInputMask('datetime', 'l_created_at', $row['l_created_at'], 'disabled')?>
                            </div>
                            <?php endif;?>

                        </div>
                    </div>



                    <div class="panel-content py-2 rounded-bottom border-faded border-left-0 border-right-0 border-bottom-0 text-muted d-flex">
                        <?=genDetailButton('location', $mode)?>    
                    </div>


                <div>
            </div>
        </div>

        </form>


    </div>
</main>



<form name='del_form' id="del_form" action="/<?=SHOP_INFO_ADMIN_DIR?>/manage/location_process" method="POST">
    <input type='hidden' name='mode' value='delete' />
    <input type="hidden" name="l_id" value="<?=$row['l_id']?>">
</form>


<script type="text/javascript">
$(document).ready(function() {
});
</script>
