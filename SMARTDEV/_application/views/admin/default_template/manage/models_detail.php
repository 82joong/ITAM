<?php 
//include left panel (navigation)
//follow the tree in inc/config.ui.php
require_once realpath(dirname(__FILE__).'/../').'/inc/config.ui.php';


$active_key = array(
    "make",               // 1 Depth menu
    "models"          // 2(Sub) Depth menu
);
$page_nav[$active_key[0]]["active"] = true;
$page_nav[$active_key[0]]["sub"][$active_key[1]]["active"] = true;
include realpath(dirname(__FILE__).'/../').'/inc/nav.php';

$title_symbol = 'fa-desktop-alt';
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
        <form name='edit_form' id="edit_form" class="needs-validation w-100" action="/<?=SHOP_INFO_ADMIN_DIR?>/manage/models_process" method="POST" novalidate autocomplete="off" encrypt="multipart/form-data">
        <input type="hidden" name="mode" value="<?=$mode?>">
        <input type="hidden" name="m_id" value="<?=$row['m_id']?>">

        <div class="col-xl-12">
            <div class="panel">
                <div class="panel-hdr">
                    <h2>Row <?=ucfirst($mode)?></h2>
                   
                    <div class="panel-toolbar">
                        <?=genDetailButton('models', $mode)?>
                    </div>
                </div>

                <div class="panel-container show">
                    <div class="panel-content">

                        <div class="form-row form-group justify-content-md-center">


                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="m_model_name">
                                    Models Name 
                                    <span class="text-danger">*</span>
                                </label>
                                <?=getInputMask($type='normal', $name='m_model_name', $row['m_model_name'], 'required')?>
                            </div>


                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="m_category_id">
                                    Category
                                    <span class="text-danger">*</span>
                                    <?=genNewButton('category')?>
                                </label>
                                <?=$select_category?>  
                            </div>


                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="m_vendor_id">
                                   Vendor 
                                    <span class="text-danger">*</span>
                                </label>
                                <?=genNewButton('vendor')?>
                                <?=$select_vendor?>  
                            </div>



                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="m_model_no">
                                    Model No. 
                                </label>
                                <?=getInputMask($type='normal', $name='m_model_no', $row['m_model_no'])?>
                            </div>


                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="m_eos_rate">
                                    EOS (End Of Service) 
                                </label>

                                <div class="input-group">
                                    <input id="basic-addon2" type="text" class="form-control col-4 col-lg-2" name="m_eos_rate" value="<?=$row['m_eos_rate']?>">
                                    <div class="input-group-append">
                                        <span class="input-group-text">months</span>
                                    </div>
                                </div>
                            </div>



                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="m_eos_expired_at">
                                    EOS Expired At 
                                </label>

                                <div class="input-group">
                                    <input type="text" class="form-control" id="m_eos_expired_at" name="m_eos_expired_at" value="<?=$row['m_eos_expired_at']?>">
                                    <div class="input-group-append">
                                        <span class="input-group-text fs-xl">
                                            <i class="fal fa-calendar-check"></i>
                                        </span>
                                    </div>
                                </div>

                            </div>


                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="m_fieldset_id">
                                    Fieldset 
                                    <span class="text-danger">*</span>
                                </label>
                                <?=genLinkButton('fieldset', $row['m_fieldset_id'])?>
                                <?=genNewButton('fieldset')?>
                                <?=$select_fieldset?>  
                            </div>



                            <div class="col-12 col-lg-8 mb-3 border border-success rounded-plus bg-highlight shadow-sm p-5" id="area-custom-fields" style="display:none">

                                <?php if($mode == 'update') : ?>
                                <?=$row['view_data']?>
                                <?php endif; ?>

                            </div>


                                <?php /*?>
                                <div class="col-12 p-3 mt-3 bg-highlight">

                                    <div class="row pl-3">
                                        <h5 class="frame-heading mb-0">Custom fields</h5>

                                        <?php if($mode == 'update') : ?>
                                        <a href="/admin/manage/fieldset_detail/<?=$row['m_fieldset_id']?>" target="_blank" class="btn btn-info btn-xs waves-effect waves-themed ml-3 ">
                                            <i class="fal fa-external-link"></i> Detail
                                        </a>
                                        <?php endif; ?>
                                    </div>

                                    <table id="data-table" class="table table-sm table-striped table-bordered table-hover bg-primary-50" style="width:100%">
                                        <thead class="bg-primary-600">
                                            <tr>
                                                <th data-name="cfm_order">Order</th>
                                                <th data-name="cf_name">FieldSet Name</th>
                                                <th data-name="cf_format">Format</th>
                                                <th data-name="cf_format_element">Element</th>
                                                <th data-name="cf_encrypt">Encrypt</th>
                                                <th data-name="cfm_required">Required</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                            <?php */?>




                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="m_is_active">
                                    Is Active 
                                </label>
                                <?=$select_active?>  
                            </div>



                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="m_description">
                                    Model Description 
                                </label>
                                <?=getTextArea('m_description', $row['m_description'], $opt='')?>
                            </div>


                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="m_filename">
                                    Image 
                                </label>
                                <?=genDropzone()?>  
                            </div>



                            <?php if($mode == 'update') : ?>
                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label">
                                    Updated At 
                                </label>
                                <?=getInputMask($type='normal', $name='m_updated_at', $row['m_updated_at'], 'disabled')?>
                            </div>
                            
                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label">
                                    Created At 
                                </label>
                                <?=getInputMask($type='normal', $name='m_created_at', $row['m_created_at'], 'disabled')?>
                            </div>
                            <?php endif;?>

                        </div>

                    </div>



                    <div class="panel-content py-2 rounded-bottom border-faded border-left-0 border-right-0 border-bottom-0 text-muted d-flex">
                        <?=genDetailButton('models', $mode)?>
                    </div>


                <div>
            </div>
        </div>

        </form>


    </div>
</main>



<form name='del_form' id="del_form" action="/<?=SHOP_INFO_ADMIN_DIR?>/manage/models_process" method="POST">
    <input type='hidden' name='mode' value='delete' />
    <input type="hidden" name="m_id" value="<?=$row['m_id']?>">
</form>




<script type="text/javascript">


$(document).ready(function() {
    $('.select2').select2();

    <?php /*?>
    var controls = {
        leftArrow: '<i class="fal fa-angle-left" style="font-size: 1.25rem"></i>',
        rightArrow: '<i class="fal fa-angle-right" style="font-size: 1.25rem"></i>'
    }
    $('#m_eos_expired_at').datepicker({
        autoclose: true,
        format: "yyyy-mm-dd",
        orientation: "top left",
        todayHighlight: true,
        templates: controls
    });

    var table = $('#data-table').DataTable({
        "bPaginate"     : false,
        "orderCellsTop" : false, 
        "ordering"      : false,
        "processing"    : true,
        "serverSide"    : true,
        "searching"     : false,
        "order"         : [[0, "asc"]],    // Default Sorting
        "filter"        : false,
        "lengthChange"  : false,
        "rowReorder": {
            "selector": "tr td:first-child"
        },
        "ajax": {
            "url": "/<?=SHOP_INFO_ADMIN_DIR?>/manage/custom_field_map",
            "type": "POST",
            "dataType": "JSON",
            "data": {
                "mode": "list",
                "fs_id": function() { return $('[name="m_fieldset_id"]').val() },
            }
        },
        "columns": [
            {"data": "cfm_order"},
            {"data": "cf_name"},
            {"data": "cf_format"},
            {"data": "cf_format_element"},
            {"data": "cf_encrypt"},
            {"data": "cfm_required"},
        ],
        // Define columns class 
        "columnDefs": [
            { "className": "text-center", "targets": [0,1,2,3,4,5] },
        ]
    });


    $('[name="m_fieldset_id"]').on('select2:select', function(e) {
        var _val = $(this).val();
        table.ajax.reload();
    });
    <?php */?>



    $('[name="m_fieldset_id"]').on('select2:select', function(e) {
        $.post('/<?=SHOP_INFO_ADMIN_DIR?>/manage/ajax_get_custom_fields', {"fieldset_id": $(this).val()}, function(res) {
            if(res.is_success) {
                //if(res.data != undefined) {
                    $('#area-custom-fields').html(res.data).show().find('.input-mask').inputmask();
                //}
            } else {
                Swal.fire("Submit Error !", res.msg, "error");
            }
        },'json');
    });


    var _dz = new Dropzone('div#div_dropzone', dzOptions);
    
    <?php if($mode == 'update' && strlen($row['m_filename']) > 0) : ?>
    var mockFile = { name:"<?=$row['m_filename']?>", size:<?=$img_size?>, accepted:true};
    _dz.files.push(mockFile);
    _dz.emit("addedfile", mockFile);
    _dz.emit("thumbnail", mockFile, "<?=$img_url?>");
    _dz.emit("complete", mockFile)
    <?php endif; ?>



    <?php if($mode == 'update' && isset($row['view_data'])) : ?>
    $('#area-custom-fields').show().find('.input-mask').inputmask();
    <?php endif;?>



});
</script>
