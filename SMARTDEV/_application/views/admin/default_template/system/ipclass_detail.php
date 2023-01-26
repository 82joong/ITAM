<?php 
//include left panel (navigation)
//follow the tree in inc/config.ui.php
require_once realpath(dirname(__FILE__).'/../').'/inc/config.ui.php';


$active_key = array(
    "system",               // 1 Depth menu
    "ipclass",              // 2(Sub) Depth menu
); 

$page_nav[$active_key[0]]["active"] = true;
$page_nav[$active_key[0]]["sub"][$active_key[1]]["active"] = true;


include realpath(dirname(__FILE__).'/../').'/inc/nav.php';

$title_symbol = 'fa-network-wired';
?>



<link rel="stylesheet" media="screen, print" href="<?=$assets_dir?>/css/formplugins/bootstrap-colorpicker/bootstrap-colorpicker.css">



<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL -->
<style type="text/css">
</style>


<main id="js-page-content" role="main" class="page-content">

    <?php include realpath(dirname(__FILE__).'/../').'/inc/breadcrumbs.php'; ?>

    <div class="row">
        <form name='edit_form' id="edit_form" class="needs-validation w-100" action="/<?=SHOP_INFO_ADMIN_DIR?>/system/ipclass_process" method="POST" novalidate autocomplete="off" >
        <input type="hidden" name="mode" value="<?=$mode?>">
        <input type="hidden" name="ipc_id" value="<?=$row['ipc_id']?>">

        <div class="col-xl-12">
            <div class="panel">
                <div class="panel-hdr">
                    <h2>Row <?=ucfirst($mode)?></h2>

                    <div class="panel-toolbar">
                        <?=genDetailButton('ipclass', $mode)?>    
                    </div>
                </div>

                <div class="panel-container show">
                    <div class="panel-content">

                        <div class="form-row form-group justify-content-md-center">


                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="ipc_location_id">
                                    <span data-i18n="content.location">Location</span> 
                                    <span class="text-danger">*</span>
                                </label>
                                <?=$select_location?>
                            </div>


                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="ipc_type">
                                    <span data-i18n="content.ip_class_type">Type</span> 
                                    <span class="text-danger">*</span>
                                </label>
                                <?=$select_type?>
                            </div>

                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="ipc_type">
                                    <span data-i18n="content.ip_class_category">Category</span> 
                                    <span class="text-danger">*</span>
                                </label>
                                <?=$select_category?>
                                <span class="help-block">Category 추가시 개발팀 문의</span>
                            </div>

                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="ipc_name">
                                    <span data-i18n="content.ip_class_name">Name</span>
                                    <span class="text-danger">*</span>
                                </label>
                                <?=getInputMask('normal', 'ipc_name', $row['ipc_name'], $opt='required')?>
                            </div>



                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="ipc_cidr">
                                    <span data-i18n="content.cidr">CIDR</span>
                                    <span class="text-danger">*</span>
                                </label>
                                <?=getInputMask('cidr', 'ipc_cidr', $row['ipc_cidr'], $opt='required')?>
                            </div>


                            <div class="row col-12 col-lg-8 mb-3 ">
                                <div class="col-6 p-0">
                                    <label class="form-label" for="ipc_start">
                                        Start IP 
                                    </label>
                                    <?=getInputMask('ipv4', 'ipc_start', $row['ipc_start'], $opt='readonly')?>
                                </div>

                                <div class="col-6 pr-0">
                                    <label class="form-label" for="ipc_end">
                                        End IP 
                                    </label>
                                    <?=getInputMask('ipv4', 'ipc_end', $row['ipc_end'], $opt='readonly')?>
                                </div>
                            </div>



                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="ipc_memo" data-i18n="content.memo">
                                    Memo 
                                </label>
                                <?=getTextArea('ipc_memo', $row['ipc_memo'])?>
                            </div>



                            <?php if($mode == 'update') : ?>
                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" data-i18n="content.updated_at">
                                    Updated At 
                                </label>
                                <?=getInputMask('datetime', 'ipc_updated_at', $row['ipc_updated_at'], $opt='disabled')?>
                            </div>
                            
                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" data-i18n="content.created_at">
                                    Created At 
                                </label>
                                <?=getInputMask('datetime', 'ipc_created_at', $row['ipc_created_at'], $opt='disabled')?>
                            </div>
                            <?php endif;?>

                        </div>

                    </div>



                    <div class="panel-content py-2 rounded-bottom border-faded border-left-0 border-right-0 border-bottom-0 text-muted d-flex">
                        <?=genDetailButton('ipclass', $mode)?>    
                    </div>


                <div>
            </div>
        </div>

        </form>
    </div>
</main>



<form name='del_form' id="del_form" action="/<?=SHOP_INFO_ADMIN_DIR?>/system//ipclass_process" method="POST">
    <input type='hidden' name='mode' value='delete' />
    <input type="hidden" name=ipc_id" value="<?=$row['ipc_id']?>">
</form>




<script type="text/javascript">
$(document).ready(function() {
    var location_map = JSON.parse('<?=$location_map?>');

    $('[name="ipc_cidr"]').focusout(function(e) {
        var _val = $(this).val();

        $.post('/<?=SHOP_INFO_ADMIN_DIR?>/system/ajax_valid_cidr', {"cidr": _val}, function(res) {
            if(res.is_success) {
                toggleValid($('[name="ipc_cidr"]'),'valid', '');
                $('[name="ipc_start"]').val(res.msg[0]);
                $('[name="ipc_end"]').val(res.msg[1]);
            } else {
                toggleValid($('[name="ipc_cidr"]'),'invalid', res.msg);
                $('[name="ipc_start"]').val('');
                $('[name="ipc_end"]').val('');
            }
        },'json')
    });

});

</script>
