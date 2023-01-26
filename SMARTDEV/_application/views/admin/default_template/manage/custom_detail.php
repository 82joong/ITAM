<?php 
//include left panel (navigation)
//follow the tree in inc/config.ui.php
require_once realpath(dirname(__FILE__).'/../').'/inc/config.ui.php';


$active_key = array(
    "make",               // 1 Depth menu
    "custom"          // 2(Sub) Depth menu
);
$page_nav[$active_key[0]]["active"] = true;
$page_nav[$active_key[0]]["sub"][$active_key[1]]["active"] = true;
include realpath(dirname(__FILE__).'/../').'/inc/nav.php';

$title_symbol = 'fa-code';
?>



<link rel="stylesheet" media="screen, print" href="<?=$assets_dir?>/css/formplugins/bootstrap-colorpicker/bootstrap-colorpicker.css">
<link rel="stylesheet" media="screen, print" href="<?=$assets_dir?>/css/formplugins/dropzone/dropzone.css">
<link rel="stylesheet" media="screen, print" href="<?=$assets_dir?>/css/formplugins/bootstrap-tagsinput/bootstrap-tagsinput.css">



<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL -->
<style type="text/css">
</style>


<main id="js-page-content" role="main" class="page-content">

    <?php include realpath(dirname(__FILE__).'/../').'/inc/breadcrumbs.php'; ?>

    <div class="row">
        <form name='edit_form' id="edit_form" class="needs-validation w-100" action="/<?=SHOP_INFO_ADMIN_DIR?>/manage/custom_process" method="POST" novalidate autocomplete="off" encrypt="multipart/form-data">
        <input type="hidden" name="mode" value="<?=$mode?>">
        <input type="hidden" name="cf_id" value="<?=$row['cf_id']?>">

        <div class="col-xl-12">
            <div class="panel">
                <div class="panel-hdr">
                    <h2>Row <?=ucfirst($mode)?></h2>
                   
                    <div class="panel-toolbar">
                        <?=genDetailButton('custom', $mode)?>    
                    </div>
                </div>

                <div class="panel-container show">
                    <div class="panel-content">

                        <div class="form-row form-group justify-content-md-center">


                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="cf_name">
                                    Field Name 
                                    <span class="text-danger">*</span>
                                </label>
                                <?=getInputMask('normal', 'cf_name', $row['cf_name'], 'required')?>
                            </div>



                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="cf_format_element">
                                    Element 
                                </label>
                                <?=$select_element?>
                            </div>


                            <div class="col-12 col-lg-8 mb-3" style="display:none;" id="field_value">
                                <label class="form-label">
                                   Field Values 
                                </label>
                                <input type="text" name="cf_element_value" value="<?=$row['cf_element_value']?>" class="form-control tagsinput" data-role="tagsinput" >
                                <div class="help-block">[Name|X] 기준으로 Attribute 생성 <code># Only English</code></div>
                            </div>


                            <div class="col-12 col-lg-8 mb-3" id="area-format">
                                <label class="form-label" for="cf_format">
                                    Format 
                                </label>
                                <?=$select_format?>
                                <div class="help-block" id="cf_format_help"></div>
                            </div>


                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="cf_help_text">
                                    Help Text 
                                </label>
                                <?=getInputMask('normal', 'cf_help_text', $row['cf_help_text'])?>
                            </div>



                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="validation_cf_required">
                                    Is Required 
                                </label>
                                <?=$select_required?>
                            </div>


                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="validation_cf_encrypt">
                                    Is Encrypt 
                                </label>
                                <?=$select_encrypt?>
                            </div>


                            <?php if($mode == 'update') : ?>
                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label">
                                    Updated At 
                                </label>
                                <?=getInputMask('datetime', 'cf_updated_at', $row['cf_updated_at'], 'disabled')?>
                            </div>
                            
                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label">
                                    Created At 
                                </label>
                                <?=getInputMask('datetime', 'cf_created_at', $row['cf_created_at'], 'disabled')?>
                            </div>
                            <?php endif;?>

                        </div>

                    </div>



                    <div class="panel-content py-2 rounded-bottom border-faded border-left-0 border-right-0 border-bottom-0 text-muted d-flex">
                        <?=genDetailButton('custom', $mode)?>    
                    </div>


                <div>
            </div>
        </div>

        </form>


    </div>
</main>



<form name='del_form' id="del_form" action="/<?=SHOP_INFO_ADMIN_DIR?>/manage/custom_process" method="POST">
    <input type='hidden' name='mode' value='delete' />
    <input type="hidden" name="cf_id" value="<?=$row['cf_id']?>">
</form>




<script type="text/javascript">
$(document).ready(function() {

    $('[name="cf_format_element"]').on('select2:select', function(e) {
        if($(this).val() == 'list' || $(this).val() == 'checkbox' || $(this).val() == 'radio') {
            $('#field_value').show();
            $('#area-format').hide();
        }else {
            $('#field_value').hide();
            $('#area-format').show();
            $('[name="cf_element_value"]').tagsinput('removeAll');
        }
    });

<?php
if(
    ($mode == 'update' || $mode == 'clone') && 
    ($row['cf_format_element'] == 'list' || $row['cf_format_element'] == 'checkbox' || $row['cf_format_element'] == 'radio') 
):
?>
    $('#field_value').show();
<?php 
endif;
?>
    
    

    var help_map = JSON.parse('<?=$format_help_map?>');
    $('[name="cf_format"]').on('select2:select', function(e) {
        var _val = $(this).val();
        var help_msg = help_map[_val];
        
        $('#cf_format_help').html(help_msg);
    });
});
</script>
