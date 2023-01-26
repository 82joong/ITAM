<?php 
//include left panel (navigation)
//follow the tree in inc/config.ui.php
require_once realpath(dirname(__FILE__).'/../').'/inc/config.ui.php';


$active_key = array(
    "make",               // 1 Depth menu
    "fieldset"          // 2(Sub) Depth menu
);
$page_nav[$active_key[0]]["active"] = true;
$page_nav[$active_key[0]]["sub"][$active_key[1]]["active"] = true;
include realpath(dirname(__FILE__).'/../').'/inc/nav.php';

$title_symbol = 'fa-folder-tree';
?>



<link rel="stylesheet" media="screen, print" href="<?=$assets_dir?>/css/formplugins/bootstrap-colorpicker/bootstrap-colorpicker.css">
<link rel="stylesheet" media="screen, print" href="<?=$assets_dir?>/css/formplugins/dropzone/dropzone.css">



<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL -->
<style type="text/css">
/*.select2-container {margin-right:-61px;} */

</style>

<main id="js-page-content" role="main" class="page-content">

    <?php include realpath(dirname(__FILE__).'/../').'/inc/breadcrumbs.php'; ?>

    <div class="row">
        <form name='edit_form' id="edit_form" class="needs-validation w-100" action="/<?=SHOP_INFO_ADMIN_DIR?>/manage/fieldset_process" method="POST" novalidate autocomplete="off" encrypt="multipart/form-data">
        <input type="hidden" name="mode" value="<?=$mode?>">
        <input type="hidden" name="fs_id" value="<?=$row['fs_id']?>">

        <div class="col-xl-12">
            <div class="panel">
                <div class="panel-hdr">
                    <h2>Row <?=ucfirst($mode)?></h2>
                   
                    <div class="panel-toolbar">
                        <?=genDetailButton('fieldset', $mode)?>                        
                    </div>
                </div>

                <div class="panel-container show">
                    <div class="panel-content">

                        <div class="form-row form-group justify-content-md-center">


                           <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="validation_fs_name">
                                    FieldSet Name 
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="validation_fs_name" name="fs_name" placeholder="FieldSet Name" required value="<?=$row['fs_name']?>">
                                <div class="invalid-feedback"><?=getInvalidMsg('BLANK')?></div>
                            </div>


                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="fs_is_active">
                                   Is Active 
                                </label>
                                <?=$select_active?>
                            </div>


                            <?php if($mode == 'update') : ?>
                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label">
                                    Updated At 
                                </label>
                                <input type="text" class="form-control" name="fs_updated_at" value="<?=$row['fs_updated_at']?>" disabled >
                            </div>
                            
                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label">
                                    Created At 
                                </label>
                                <input type="text" class="form-control" name="fs_created_at" value="<?=$row['fs_created_at']?>" disabled >
                            </div>
                            <?php endif;?>
                        </div>
                    </div>


                    <div class="panel-content py-2 rounded-bottom border-faded border-left-0 border-right-0 border-bottom-0 text-muted d-flex">
                        <?=genDetailButton('fieldset', $mode)?>                        
                    </div>
                <div>
            </div>
        </div>
        </form>
    </div>




    <form id="area-spec">
    <div class="row" id="area-cutom-fields" style="display:none;">
        <input type="hidden" name="cfm_fieldset_id" value="<?=$row['fs_id']?>">
        <input type="hidden" name="mode" value="insert">
        <input type="hidden" name="request" value="ajax">
        <div class="col-xl-12">
            <div class="panel">
                <div class="panel-hdr">
                    <h2><?=$row['fs_name']?> Custom fields</h2>
                </div>

                <div class="panel-container show">
                    <div class="panel-content">
                        <div class="form-row form-group justify-content-md-center">

                            <div class="form-group col-12 col-lg-8 mb-1">
                                <?=$select_custom_fields?>
                            </div>
                            <div class="form-group col-12 col-lg-8 mb-3" id="area-add-row" style="display:none;">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        <div class="input-group-text">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="chk_required" name="cfm_required" value="YES">
                                                <label class="custom-control-label" for="chk_required">Field Required</label>
                                            </div>
                                        </div>
                                    </div>

                                    <input type="text" id="simpleinput-disabled" class="form-control" disabled="" value="Add Custom Fields">

                                    <div class="input-group-append">
                                        <div type="button" class="btn btn-primary shadow-0 waves-effect waves-themed" id="btn_addrow">
                                            <i class="fal fa-plus-square"></i> Add Row
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="col-12 col-lg-8 mb-3">
                                <table id="data-table" class="table table-striped table-bordered table-hover" style="width:100%">
                                    <thead class="bg-info-600">
                                        <tr>
                                            <th data-name="cfm_id"></th>
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
                    </div>
                </div>
            </div> <!-- .panel-->
        </div>
    </div> <!-- .row -->
    </form>

</main>



<form name='del_form' id="del_form" action="/<?=SHOP_INFO_ADMIN_DIR?>/manage/fieldset_process" method="POST">
    <input type='hidden' name='mode' value='delete' />
    <input type="hidden" name="fs_id" value="<?=$row['fs_id']?>">
</form>




<script type="text/javascript">    

$(document).ready(function() {

<?php if($mode !== 'insert'): ?>
    $('#area-cutom-fields').show();
<?php endif;?>


    $('.input-mask').inputmask();
    $('.select2').select2();

    $('#btn_addrow').click(function(e) {
        if($('[name="cfm_custom_field_id"]').val() < 1) {
            $('[name="cfm_custom_field_id"]').select2('open');
            return;
        }

        var params = $('#area-spec').serialize();
        $.post('/<?=SHOP_INFO_ADMIN_DIR?>/manage/custom_field_map_process', params, function(res) {
            if(res.is_success) {
                table.clear().draw();
            } else {
                Swal.fire("Submit Error !", res.msg, "error");
            }
        },'json');
    });


    $('[name="cfm_custom_field_id"]').on('select2:select', function(e) {
        if($(this).val()) {
            $('#area-add-row').show();
        }else {
            $('#area-add-row').hide();
        }
    });


    // Column Definitions
    var columnSet = [
        {
            "title": "ID",
            "data": "cfm_id",
            "className": "text-center",
            "type": "readonly",
            "orderable": false,
            "render": function(data, type, full, meta) {
                var btn_class = "btn btn-secondary btn-sm btn-icon waves-effect waves-themed";
                var btn_act = '<span class="'+btn_class+'"><i class="fal fa-line-height">&nbsp;</i></span>';
                return btn_act;
            },
        }, 
        {
            "data": "cfm_order",
            "type": "readonly",
        },
        {
            "data": "cf_name",
            "type": "readonly", 
        },
        {
            "data": "cf_format",
            "type": "select2",
            "options": [<?=$format_type?>],
            "type": "readonly",
        },
        {
            "data": "cf_format_element",
            "type": "select",
            "options": [<?=$element_type?>],
            "type": "readonly",
        },
        {
            "data": "cf_encrypt",
            "type": "select",
            "options": [<?=$encrypt_type?>],
            "type": "readonly",
        },
        {
            "data": "cfm_required",
            "type": "select",
            "options": [<?=$required_type?>]
        },

    ]
    
    var table = $('#data-table').DataTable({
        "bPaginate"     : false,
        "orderCellsTop" : false, 
        "ordering"      : false,
        "fixedHeader"   : true, 
        "processing"    : true,
        "serverSide"    : true,
        "searching"     : false,
        "responsive"    : true,
        "select"        : "single",
        "altEditor"     : true,
        "order"         : [[0, "asc"]],    // Default Sorting
        "filter"        : false,
        "lengthChange"  : false,
        "rowReorder": {
            "selector": "tr td:first-child",
            "dataSrc": "cfm_order"
        },
        "ajax": {
            "url": "/<?=SHOP_INFO_ADMIN_DIR?>/manage/custom_field_map",
            "type": "POST",
            "dataType": "JSON",
            "data": {
                "mode": "list",
                "fs_id": "<?=$row['fs_id']?>",
            }
        },



        <?php //if($row['o_order_status'] == 'WRITING') : ?>

        "dom":
              "<'row mb-1 mt-3'"
                + "<'col-sm-12 col-md-4 d-flex align-items-center justify-content-start'f>"
                + "<'col-sm-12 col-md-8 d-flex align-items-center justify-content-end'B>"
            + ">" 
            + "<'row'<'col-sm-12'tr>>"
            + "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",

        "buttons": [
            {
                extend: 'selected',
                text: '<i class="fal fa-times mr-1"></i> Delete',
                name: 'delete',
                className: 'btn-danger btn-sm mr-1'
            },
            {
                extend: 'selected',
                text: '<i class="fal fa-edit mr-1"></i> Edit',
                name: 'edit',
                className: 'btn-primary btn-sm mr-1'
            },
            {
                text: '<i class="fal fa-plus"></i> Add',
                name: 'add_custom',                         // add -> add_custom : action 가능
                className: 'btn-success btn-sm',
                action: function(e, dt, button, config) {
                    window.open('/admin/manage/custom_detail', '_blank');
                }
            }
        ],
        <?php //endif; ?>



        "columns": columnSet,
        // Define columns class 
        "columnDefs": [
            { "className": "text-center", "targets": [1,2,3,4,5,6] },
        ],

        onEditRow: function(dt, rowdata, success, error) {
            rowdata.mode = 'update';
            rowdata.request= 'ajax';
            rowdata.o_id = '<?=$row['o_id']?>';
            $.post('/<?=SHOP_INFO_ADMIN_DIR?>/manage/custom_field_map_process', rowdata, function(res) {
                if(res.is_success) {
                    //table.clear().draw();
                    success();
                } else {
                    Swal.fire("Submit Error !", res.msg, "error");
                }
            },'json');
        },
        onDeleteRow: function(dt, rowdata, success, error) {
            rowdata.mode = 'delete';
            rowdata.request= 'ajax';
            rowdata.fs_id = '<?=$row['fs_id']?>';
            $.post('/<?=SHOP_INFO_ADMIN_DIR?>/manage/custom_field_map_process', rowdata, function(res) {
                if(res.is_success) {
                    //table.clear().draw();
                    success();
                } else {
                    Swal.fire("Submit Error !", res.msg, "error");
                }
            },'json');
        },
    });

    table.on( 'responsive-resize', function ( e, datatable, columns ) {
        var count = columns.reduce( function (a,b) {
            return b === false ? a+1 : a;
        }, 0 );

        $('.dataTable thead tr:last-child th').show();
        if(count > 0) {
            $.each(columns, function(k, v) {
                if(v === false) {
                    $('.dataTable thead tr:last-child th:nth-child('+(k+1)+')').css('display', 'none');
                }
            });
        }
        //console.log( count +' column(s) are hidden' );
    });



    // ReOrder Trigger Ajax Update
    table.on('row-reorder', function(e, diff, edit) {
        var rows = [];
        for ( var i=0, ien=diff.length ; i<ien ; i++ ) {
            var rowData = table.row( diff[i].node ).data();

            rows.push({
                cfm_id: rowData["cfm_id"],
                cfm_order: diff[i].newData
            });
        }
        var params = {data: rows};
        $.post('/<?=SHOP_INFO_ADMIN_DIR?>/manage/ajax_update_cfm_order', params, function(res) {},'json');
    });

});
</script>
