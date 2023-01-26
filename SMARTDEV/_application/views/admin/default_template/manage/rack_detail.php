<?php 
//include left panel (navigation)
//follow the tree in inc/config.ui.php
require_once realpath(dirname(__FILE__).'/../').'/inc/config.ui.php';


$active_key = array(
    "manage",               // 1 Depth menu
    "rack"          // 2(Sub) Depth menu
);
$page_nav[$active_key[0]]["active"] = true;
$page_nav[$active_key[0]]["sub"][$active_key[1]]["active"] = true;
include realpath(dirname(__FILE__).'/../').'/inc/nav.php';

$title_symbol = 'fa-container-storage';
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
        <form name='edit_form' id="edit_form" class="needs-validation w-100" action="/<?=SHOP_INFO_ADMIN_DIR?>/manage/rack_process" method="POST" novalidate autocomplete="off" encrypt="multipart/form-data">
        <input type="hidden" name="mode" value="<?=$mode?>">
        <input type="hidden" name="r_id" value="<?=$row['r_id']?>">
        <input type="hidden" name="l_code" value="<?=$l_code?>">

        <div class="col-xl-12">
            <div class="panel">
                <div class="panel-hdr">
                    <h2>Row <?=ucfirst($mode)?></h2>
                   
                    <div class="panel-toolbar">
                        <?=genDetailButton('rack', $mode)?>    
                    </div>
                </div>

                <div class="panel-container show">
                    <div class="panel-content">

                        <div class="form-row form-group justify-content-md-center">

                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="r_location_id">
                                    <span data-i18n="content.location">Location</span>
                                    <span class="text-danger">*</span>
                                    <?=genLinkButton('location', $row['r_location_id'])?>
                                    <?=genNewButton('location')?>
                                </label>
                                <?=$select_location?>
                            </div>



                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="r_floor" data-i18n="content.floor">
                                    Floor 
                                </label>
                                <?=getInputMask('upper', 'r_floor', $row['r_floor'])?>
                            </div>


                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="r_section" data-i18n="content.section">
                                    Section 
                                </label>
                                <?=getInputMask('upper', 'r_section', $row['r_section'])?>
                            </div>



                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="r_section" data-i18n="content.rack">
                                    Rack 
                                </label>
                                <?=getInputMask('decimal_number', 'r_frame', $row['r_frame'])?>
                            </div>


                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="r_code" data-i18n="content.code">
                                    Code 
                                    <span class="text-danger">*</span>
                                </label>
                                <?=getInputMask('normal', 'r_code', $row['r_code'], 'required readonly')?>
                                <div class="help-block">#위 입력에 따른 자동완성 ex) GASAN-B1-A-05 </div>
                            </div>


                            <?php if($mode == 'update') : ?>
                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" data-i18n="content.updated_at">
                                    Updated At 
                                </label>
                                <input type="text" class="form-control" name="r_updated_at" value="<?=$row['r_updated_at']?>" disabled >
                            </div>
                            
                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" data-i18n="content.created_at">
                                    Created At 
                                </label>
                                <input type="text" class="form-control" name="r_created_at" value="<?=$row['r_created_at']?>" disabled >
                            </div>
                            <?php endif;?>

                        </div>
                    </div>



                    <div class="panel-content py-2 rounded-bottom border-faded border-left-0 border-right-0 border-bottom-0 text-muted d-flex">
                        <?=genDetailButton('rack', $mode)?>    
                    </div>


                <div>
            </div>
        </div>

        </form>


    </div>
</main>



<form name='del_form' id="del_form" action="/<?=SHOP_INFO_ADMIN_DIR?>/manage/rack_process" method="POST">
    <input type='hidden' name='mode' value='delete' />
    <input type="hidden" name="r_id" value="<?=$row['r_id']?>">
</form>


<script type="text/javascript">
$(document).ready(function() {
    var location_map = JSON.parse('<?=$location_map?>');
    $('[name="r_location_id"]').on('select2:select', function(e) {
        var _val = $(this).val();
        $('[name="l_code"]').val(location_map[_val]);
        makeCode();
    });

    $('[name="r_floor"], [name="r_section"], [name="r_frame"]').focusout(function(e) {
        makeCode();
    });
});

function makeCode() {

    var code = [];

    code.push($('[name="l_code"]').val());

    var floor = '00';
    if($('[name="r_floor"]').val()) {
        //floor = fillZero(2, $('[name="r_floor"]').val());
        floor = $('[name="r_floor"]').val();
    }
    code.push(floor);

    var section = 'COCEN';
    if($('[name="r_section"]').val()) {
        section = $('[name="r_section"]').val();
    }
    code.push(section);

    var frame = '00';
    if($('[name="r_frame"]').val()) {
        frame = fillZero(2, $('[name="r_frame"]').val());
        //frame = $('[name="r_frame"]').val();
    }
    code.push(frame);

    $('[name="r_code"]').val(code.join('-'));
}
</script>
