<?php 
//include left panel (navigation)
//follow the tree in inc/config.ui.php
require_once realpath(dirname(__FILE__).'/../').'/inc/config.ui.php';


$active_key = array(
    "assets",               // 1 Depth menu
    "maintenance"           // 2(Sub) Depth menu
);
$page_nav[$active_key[0]]["active"] = true;
$page_nav[$active_key[0]]["sub"][$active_key[1]]["active"] = true;
include realpath(dirname(__FILE__).'/../').'/inc/nav.php';

$title_symbol = 'fa-tools';
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
        <form name='edit_form' id="edit_form" class="needs-validation w-100" action="/<?=SHOP_INFO_ADMIN_DIR?>/assets/maintenance_process" method="POST" novalidate autocomplete="off" encrypt="multipart/form-data">
        <input type="hidden" name="mode" value="<?=$mode?>">
        <input type="hidden" name="mtn_id" value="<?=$row['mtn_id']?>">

        <div class="col-xl-12">
            <div class="panel">
                <div class="panel-hdr">
                    <h2>Row <?=ucfirst($mode)?></h2>
                   
                    <div class="panel-toolbar">
                        <?=genDetailButton('maintenance', $mode)?>    
                    </div>
                </div>



                <div class="panel-container show">
                    <div class="panel-content">

                        <div class="form-row form-group justify-content-md-center">


                            <div class="form-group col-12 col-lg-8 mb-3">
                                <label class="form-label" for="mtn_title">
                                    <span data-i18n="content.maintenance_title">Title</span> 
                                    <span class="text-danger">*</span>
                                </label>
                                <?=getInputMask('normal', 'mtn_title', $row['mtn_title'], $opt='required')?>
                            </div>


                            <div class="form-group col-12 col-lg-8 mb-3">
                                <label class="form-label" for="mtn_assets_model_id">
                                    <span data-i18n="content.assets_name">Assets</span>
                                    <?=genLinkButton('assets', $row['mtn_assets_model_id'])?> 
                                    <?=genNewButton('assets')?> 
                                    <span class="text-danger">*</span>
                                </label>

                                <select class="select2-ajax form-control sel-ajax-assets" id="mtn_assets_model_id" name="mtn_assets_model_id" required>
                                    <?php if( isset($row['mtn_assets_model_id']) && strlen($row['mtn_assets_model_id']) > 0 ) : ?>
                                    <option value="<?=$row['mtn_assets_model_id']?>">
                                        <?=$row['mtn_assets_model_name']?>
                                    </option>
                                    <?php endif; ?>
                                </select>
                                <span class="help-block text-danger">자산명 or 모델명 or 서비스태그 검색</span>
                                <div class="invalid-feedback"></div>
                            </div>


                            <div class="form-group col-12 col-lg-8 mb-3">
                                <label class="form-label" for="mtn_vmservice">
                                    <span data-i18n="content.vmservice_name">VMService</span>
                                </label>

                                <select class="select2-ajax form-control sel-ajax-service" id="mtn_vmservice_id" name="mtn_vmservice_id">
                                    <?php if($mode == 'update') : ?>
                                    <option value="<?=$row['mtn_vmservice_id']?>">
                                        <?=$row['mtn_vmservice_name']?>
                                    </option>
                                    <?php endif; ?>
                                </select>
                                <span class="help-block text-danger">vmservice (서비스명) or IP 검색</span>
                                <div class="invalid-feedback"></div>
                            </div>



                            <div class="form-group col-12 col-lg-8 mb-3">
                                <label class="form-label" for="mtn_type">
                                    <span data-i18n="content.maintenance_type">Type</span>
                                    <span class="text-danger">*</span>
                                    <?=genNewButton('type')?> 
                                </label>
                                <?=$select_type?>
                            </div>

                            
                            <div class="form-row col-12 col-lg-8">

                                <div class="col-6 mb-3 pl-0">
                                    <label class="form-label" for="mtn_supplier_id">
                                        <span data-i18n="content.supplier">Supplier</span> 
                                        <?=genLinkButton('supplier', $row['mtn_supplier_id'])?> 
                                        <?=genNewButton('supplier')?> 
                                    </label>
                                    <?=$select_supplier?>
                                </div>

                                <div class="col-6 mb-3 pl-0">
                                    <label class="form-label" for="mtn_people_id">
                                        <span data-i18n="content.worker">Worker</span> 
                                        <span class="text-danger">*</span>
                                        <?=genLinkButton('employee', $row['mtn_people_id'])?> 
                                        <?=genNewButton('employee')?> 
                                    </label>


                                    <select class="select2-ajax form-control sel-ajax-people" id="mtn_people_id" name="mtn_people_id" required>
                                        <?php if( isset($row['mtn_people_id']) && strlen($row['mtn_people_id']) > 0 ) : ?>
                                        <option value="<?=$row['mtn_people_id']?>">
                                            <?=$row['mtn_people_name']?>
                                        </option>
                                        <?php endif; ?>
                                    </select>
                                    <span class="help-block">아이디 or 이메일 or 이름 검색</span>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>

                                    
                            <div class="form-row col-12 col-lg-8">
                                <div class="col-6 mb-3 pl-0">
                                    <label class="form-label" for="mtn_start_date" data-i18n="content.work_start_at">
                                        Work Start Time
                                    </label>
                                    <?=getInputMask('datetime', 'mtn_start_at', $row['mtn_start_at'])?>
                                </div>
                                <div class="col-6 mb-3 pl-0">
                                    <label class="form-label" for="mtn_end_at" data-i18n="content.work_end_at">
                                        Work End Time
                                    </label>
                                    <?=getInputMask('datetime', 'mtn_end_at', $row['mtn_end_at'])?>
                                </div>
                            </div>



                            <div class="form-group col-12 col-lg-8 mb-3">
                                <label class="form-label" for="mtn_price" data-i18n="content.maintenance_price">
                                    Price 
                                </label>
                                <?=getInputMask('ko_currency', 'mtn_price', $row['mtn_price'])?>
                            </div>


                            <div class="form-group col-12 col-lg-8 mb-3">
                                <label class="form-label" for="mtn_memo" data-i18n="content.memo">
                                    Memo 
                                </label>

                                <div class="box-summernote" id="box_summernote" style="display: none;"></div>
                                <textarea name="mtn_memo" id="textarea_summernote" style="display: none;"><?=$row['mtn_memo']?></textarea>

                                <div class="mt-3">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="autoSave" checked="checked">
                                        <label class="custom-control-label" for="autoSave">
                                            Autosave changes to LocalStorage <span class="fw-300">(every 3 seconds)</span>
                                        </label>

                                        <a href="javascript:removeFromLocal();" class="btn btn-xs btn-outline-danger waves-effect waves-themed position-absolute pos-right">
                                            <i class="fas fa-trash mr-1"></i> Clear LocalStorage 
                                        </a>
                                    </div>

                                </div>

                            </div>



                            <?php if($mode == 'update') : ?>
                            <div class="form-row col-12 col-lg-8">
                                <div class="col-4 mb-3 pl-0">
                                    <label class="form-label" data-i18n="content.writer">
                                        Writer 
                                    </label>
                                    <?=getInputMask('normal', 'mtn_writer_name', $row['mtn_writer_name'], 'disabled')?>
                                </div>

                                <div class="col-4 mb-3">
                                    <label class="form-label" data-i18n="content.updated_at">
                                        Updated At 
                                    </label>
                                    <?=getInputMask('datetime', 'mtn_updated_at', $row['mtn_updated_at'], 'disabled')?>
                                </div>
                                
                                <div class="col-4 mb-3">
                                    <label class="form-label" data-i18n="content.created_at">
                                        Created At 
                                    </label>
                                    <?=getInputMask('datetime', 'mtn_created_at', $row['mtn_created_at'], 'disabled')?>
                                </div>
                            </div>
                            <?php endif;?>
                        </div>
                    </div>
                    

                    <div class="panel-content py-2 rounded-bottom border-faded border-left-0 border-right-0 border-bottom-0 text-muted d-flex">
                        <?=genDetailButton('maintenance', $mode)?>    
                    </div>

                </div>

            </div>
        </div>

        </form>
    </div>
</main>



<form name='del_form' id="del_form" action="/<?=SHOP_INFO_ADMIN_DIR?>/assets/maintenance_process" method="POST">
    <input type='hidden' name='mode' value='delete' />
    <input type="hidden" name="mtn_id" value="<?=$row['mtn_id']?>">
</form>


<script src="<?=$assets_dir?>/js/custom.summernote.js?<?=filemtime(FCPATH.'admin_assets/js/custom.summernote.js')?>"></script>
<script type="text/javascript">
$(document).ready(function() {

    //init default
    $('.box-summernote').summernote({
        height: 200,
        tabsize: 2,
        placeholder: "Type here...",
        dialogsFade: true,
        toolbar: toolBar,
        callbacks:
        {
            //restore from localStorage
            onInit: function(e) {
                <?php if($mode == 'update') : ?>
                $('.box-summernote').summernote("code", $('#textarea_summernote').val());
                <?php else : ?>
                $('.box-summernote').summernote("code", localStorage.getItem("summernoteData"));
                <?php endif; ?>
            },
            onChange: function(contents, $editable) {
              clearInterval(interval);
              timer();
            },
        }
    });


    $('#mtn_vmservice_id').change(function() {
        var params = {'vms_id': $(this).val()};
        $.post('/api/service/getAssetsByService', params, function(res) {
            if(res.is_success) {
                data = res.data;
                $('#mtn_assets_model_id').html('<option value="'+data.vms_assets_model_id+'">'+data.am_name+'</option>');
            } else {
                Swal.fire("Submit Error !", res.msg, "error");
            }
        },'json');
    });
});
</script>
