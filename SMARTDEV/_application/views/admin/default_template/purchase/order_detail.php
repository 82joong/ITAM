<?php 
//include left panel (navigation)
//follow the tree in inc/config.ui.php
require_once realpath(dirname(__FILE__).'/../').'/inc/config.ui.php';


$active_key = array(
    "purchase",               // 1 Depth menu
    "order"          // 2(Sub) Depth menu
);
$page_nav[$active_key[0]]["active"] = true;
$page_nav[$active_key[0]]["sub"][$active_key[1]]["active"] = true;
include realpath(dirname(__FILE__).'/../').'/inc/nav.php';

$title_symbol = 'fa-money-check';
?>




<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL -->
<style type="text/css">
/*.select2-container {margin-right:-61px;} */

</style>


<main id="js-page-content" role="main" class="page-content">

    <?php include realpath(dirname(__FILE__).'/../').'/inc/breadcrumbs.php'; ?>

    <div class="row">
        <form name='edit_form' id="edit_form" class="needs-validation w-100" action="/<?=SHOP_INFO_ADMIN_DIR?>/purchase/order_process" method="POST" novalidate autocomplete="off" encrypt="multipart/form-data">
        <input type="hidden" name="mode" value="<?=$mode?>">
        <input type="hidden" name="o_id" value="<?=$row['o_id']?>">

        <div class="col-xl-12">
            <div class="panel">
                <div class="panel-hdr">
                    <h2>Row <?=ucfirst($mode)?></h2>
                   
                    <div class="panel-toolbar">
                        <?=genDetailButton('orders', $mode)?> 
                    </div>
                </div>

                <div class="panel-container show">
                    <div class="panel-content">

                        <div class="form-row form-group justify-content-md-center">

                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="o_ordernum">
                                    <span data-i18n="content.estimation_num">Estimation Num</span>
                                    <span class="text-danger">*</span>
                                </label>
                                <?=getInputMask($type='normal', $name='o_estimatenum', $row['o_estimatenum'], 'required')?>
                                <span class="input-block">
                                    제품발주를 위한 공급사로 부터 발급 받은 <span class="text-danger">[견적번호]</span>
                                </span>
                            </div>

                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="o_order_status" data-i18n="content.status">
                                    Status 
                                </label>
                                <?=$select_status?>
                            </div>

                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="o_company">
                                    <span data-i18n="content.company">Company</span>
                                    <span class="text-danger">*</span>
                                </label>
                                <?=genLinkButton('company', $row['o_company_id'])?>
                                <?=genNewButton('company')?>
                                <?=$select_company?>
                            </div>


                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="o_supplier" data-i18n="content.supplier">
                                    Supplier 
                                </label>
                                <?=genLinkButton('supplier', $row['o_supplier_id'])?>
                                <?=genNewButton('supplier')?>
                                <?=$select_supplier?>
                            </div>



                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" data-i18n="content.memo">
                                    Memo 
                                </label>
                                <?=getTextArea($name='o_memo', $row['o_memo'])?>
                            </div>

                            <?php if($mode == 'update') : ?>
                            <div class="row col-12 col-lg-8 mb-3">
                                <div class="col-6 pl-0">
                                    <label class="form-label" for="o_delivery_price" data-i18n="content.delivery_price">
                                        Delivery Price 
                                    </label>
                                    <?=getInputMask('ko_currency', 'o_delivery_price', $row['o_delivery_price'])?>
                                </div>

                                <div class="col-6 pr-0">
                                    <label class="form-label" for="o_etc_price" data-i18n="content.etc_price">
                                        ETC Price 
                                    </label>
                                    <?=getInputMask('ko_currency', 'o_etc_price', $row['o_etc_price'])?>
                                </div>
                            </div>


                            <div class="row col-12 col-lg-8 mb-3">
                                <div class="col-6 pl-0">
                                    <label class="form-label" for="o_vat_price" data-i18n="content.vat">
                                       VAT (<?=TAX?>%) 
                                    </label>
                                    <?=getInputMask('ko_currency', 'o_vat_price', $row['o_vat_price'], 'readonly')?>
                                </div>


                                <div class="col-6 pr-0">
                                    <label class="form-label" for="o_total_price" data-i18n="content.total">
                                        Total  
                                    </label>
                                    <?=getInputMask('ko_currency', 'o_total_price', $row['o_total_price'], 'readonly')?>
                                    <span class="help-block">(Subtotal+VAT+Delivery+ETC)</span>
                                </div>
                            </div>
                            <?php endif; ?>


                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="o_ordernum" data-i18n="content.order_num">
                                    Order Num 
                                </label>
                                <?=getInputMask($type='normal', $name='o_ordernum', $row['o_ordernum'])?>
                                <span class="input-block">
                                    제품 본사에서 발행한 <span class="text-danger">[주문번호]</span> ex) Dell Invoice 내의 주문번호
                                </span>
                            </div>

                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="o_reportnum" data-i18n="content.report_num">
                                    Report Num 
                                </label>
                                <?=getInputMask($type='normal', $name='o_reportnum', $row['o_reportnum'])?>
                                <span class="input-block">
                                    세금계산서 발행을 위한 <span class="text-danger">[품의번호]</span> ex) 그룹웨어내 작성 문서번호 
                                </span>
                            </div>


                            <?php if($mode == 'update') : ?>

                            <div class="row col-12 col-lg-8 mb-3">
                                <?php 
                                $cnt = 1;
                                unset($status_data['WRITING']);
                                $divnum = intval(12/sizeof($status_data));
                                foreach($status_data as $st) {
                                    $pr = '';
                                    $key = 'o_'.strtolower($st).'_at';

                                    $val = '0000-00-00';
                                    if($row[$key] > 0) {
                                        $val = date('Y-m-d', strtotime($row[$key]));
                                    }
                                    $color_code = $status_color_data[$st];
                                    if(sizeof($status_data) == $cnt) {
                                        $pr = 'pr-0';
                                    }
                                    echo '<div class="col-'.$divnum.' pl-0 '.$pr.'">';
                                    echo '<div class="form-group">';
                                    echo '<label class="form-label">';
                                    echo '<span class="badge badge-'.$color_code.'">'.ucfirst(strtolower($st)).' At</span>';
                                    echo '</label>';
                                    echo '<input type="text" class="form-control border-'.$color_code.'" name="'.$key.'" value="'.$val.'" disabled >';
                                    echo '</div>';
                                    echo '</div>';

                                    $cnt ++;
                                }
                                ?>
                            </div>


                            <div class="row col-12 col-lg-8 mb-3">
                                <div class="col-6 pl-0">
                                    <label class="form-label" data-i18n="content.updated_at">
                                        Updated At 
                                    </label>
                                    <?=getInputMask('datetime', 'o_updated_at', $row['o_updated_at'], 'disabled')?>
                                </div>
                                
                                <div class="col-6 pr-0">
                                    <label class="form-label" data-i18n="content.created_at">
                                        Created At 
                                    </label>
                                    <?=getInputMask('datetime', 'o_created_at', $row['o_created_at'], 'disabled')?>
                                </div>
                            </div>

                            <?php endif;?>


                            <div class="col-12 col-lg-8 form-group">
                                <label class="form-label" for="o_image" data-i18n="content.estimate_file">
                                    Estimate File
                                </label>
                                <?=genDropzone()?> 
                                <span class="help-block">발주서 사본 PDF 파일 첨부</span>
                            </div>
                        </div>

                    </div>


                    <div class="panel-content py-2 rounded-bottom border-faded border-left-0 border-right-0 border-bottom-0 text-muted d-flex">
                        <?=genDetailButton('orders', $mode)?>                        
                    </div>
                <div>
            </div>
        </div>

        </form>


        <?php if($mode == 'insert') : ?>
        <div class="demo-window-content p-3">
            <div class="alert bg-danger-200 text-white fade show p-3" role="alert">
                <div class="d-flex align-items-center">
                    <div class="alert-icon">
                        <i class="fal fa-info-circle"></i>
                    </div>
                    <div class="flex-1">
                        <span class="h5">주문서 [저장] 후 주문서에 포함될 모델을 선택합니다.</span>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>



    <form id="area-spec">
    <input type="hidden" name="o_id" value="<?=$row['o_id']?>">
    <input type="hidden" name="mode" value="insert">
    <div class="row" id="area-cutom-fields" style="display:none;">
        <div class="col-xl-12">
            <div class="panel">
                <div class="panel-hdr">
                    <h2>#.<?=$row['o_estimatenum']?> Specifications</h2>
                </div>

                <div class="panel-container show">
                    <div class="panel-content">

                        <div class="form-row form-group justify-content-md-center">

                            
                            <?php //if($row['o_order_status'] == 'WRITING') : ?>
                            <div class="form-group col-12 col-lg-8 mb-1">
                                <?=$select_model?>
                            </div>
                            <?php //endif; ?>

                            <div class="form-group col-12 col-lg-8 mb-3" id="area-add-row" style="display:none;">

                                <div class="input-group input-group-multi-transition mb-1">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">&#8361;</span>
                                    </div>
                                    <input type="text" class="form-control" placeholder="Unit Price" name="oi_unit_price">
                                    <input type="number" class="form-control" placeholder="Quantity" name="oi_quantity" value="1" readonly>
                                    <input type="text" class="form-control" placeholder="Tax Price" name="oi_tax" readonly>
                                    <input type="text" class="form-control" placeholder="Total Price" name="oi_total_price" readonly>
                                </div>


                                <div class="input-group input-group-multi-transition mb-1">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                    </div>
                                    <input type="text" class="form-control" placeholder="Service Tag" name="oi_service_tag">
                                </div>
                                <div class="input-group input-group-multi-transition mb-1">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-sticky-note"></i></span>
                                    </div>
                                    <input type="text" class="form-control" placeholder="Memo" name="oi_memo">
                                </div>

                                <div id="btn_addrow" class="btn btn-primary btn-sm btn-block waves-effect waves-themed">
                                    <span><i class="fal fa-arrow-alt-to-bottom mr-2"></i>Add Row</span>
                                </div>
                            
                            </div>


                            <div class="col-12 mb-3">
                                <table id="data-table" class="table table-sm table-striped table-bordered table-hover" style="width:100%">
                                    <thead class="bg-<?=strtolower($status_color_data[$row['o_order_status']])?>-600">
                                        <tr>
                                            <th data-name="oi_id">ID.</th>
                                            <th data-name="oi_model_name">Model Name</th>
                                            <th data-name="oi_service_tag"><i class="fas fa-tag mr-1"></i>Service Tag</th>
                                            <th data-name="oi_memo"><i class="fas fa-sticky-note mr-1"></i>Memo</th>
                                            <th data-name="oi_unit_price">Unit Price</th>
                                            <th data-name="oi_quantity">Quantity</th>
                                            <th data-name="oi_tax">VAT (<?=TAX?>%)</th>
                                            <th data-name="oi_total_price">Total</th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr class="bg-danger-100">
                                            <th colspan="4">Total</th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    </form>


</main>



<form name='del_form' id="del_form" action="/<?=SHOP_INFO_ADMIN_DIR?>/purchase/order_process" method="POST">
    <input type='hidden' name='mode' value='delete' />
    <input type="hidden" name="o_id" value="<?=$row['o_id']?>">
</form>




<script type="text/javascript">    

$(document).ready(function() {

    var _dz = new Dropzone('div#div_dropzone', dzOptions);
    
    <?php if($mode == 'update' && strlen($row['o_filename']) > 0) : ?>
    var mockFile = { name:"<?=$row['o_origin_filename']?>", size:"<?=$img_size?>", accepted:true, id:"<?=$row['o_id']?>", type:"<?=$file_type?>", uri:"<?=$file_uri?>"};
    _dz.files.push(mockFile);
    _dz.emit("addedfile", mockFile);
    _dz.emit("thumbnail", mockFile, "<?=$thumbnail?>");
    _dz.emit("complete", mockFile)
    <?php endif; ?>



    $('[name="altEditor-form"] #oi_model_name').attr('disable', true);

    <?php if($mode !== 'insert'): ?>
    $('#area-cutom-fields').show();
    <?php endif;?>


    $('#btn_addrow').click(function(e) {
        if($('[name="sel_model"]').val() < 1) {
            $('[name="sel_model"]').select2('open');
            return;
        }

        var params = $('#area-spec').serialize();
        $.post('/<?=SHOP_INFO_ADMIN_DIR?>/purchase/order_item_process', params, function(res) {
            if(res.is_success) {
                table.clear().draw();
                $('[name="o_total_price"]').val(res.o_total_price);
                $('[name="o_vat_price"]').val(res.o_vat_price);
            } else {
                Swal.fire("Submit Error !", res.msg, "error");
            }
        },'json');
    });


    $('[name="sel_model"]').on('select2:select', function(e) {
        if($(this).val()) {
            $('#area-add-row').show();
        }else {
            $('#area-add-row').hide();
        }
    });
    
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
        "order"         : [[0, "desc"]],    // Default Sorting
        "filter"        : false,
        "lengthChange"  : false,
        "rowReorder": {
            "selector": "tr td:first-child"
        },

        "ajax": {
            "url": "/<?=SHOP_INFO_ADMIN_DIR?>/purchase/ajax_order_items",
            "type": "POST",
            "dataType": "JSON",
            "data": {
                "mode": "list",
                "oi_order_id": "<?=$row['o_id']?>",

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
                text: '<i class="fal fa-edit"></i> Edit',
                name: 'edit',
                className: 'btn-primary btn-sm'
            }
        ],
        <?php //endif; ?>


        "columns": [
            {
                "data": "oi_id",
                "visible": false,
                "type": "readonly"
            },
            {
                "data": "oi_model_name",
                "type": "readonly"
            },
            {
                "data": "oi_service_tag",
                "type": "text"
            },
            {
                "data": "oi_memo",
                "type": "text"
            },
            {
                "data": "oi_unit_price",
                "render": $.fn.dataTable.render.number(',')
            },
            {
                "data": "oi_quantity",
                "type": "readonly",
                "className": "width-5 text-right"
            },
            {
                "data": "oi_tax",
                "type": "readonly",
                "render": $.fn.dataTable.render.number(',')
            },
            {
                "data": "oi_total_price",
                "type": "readonly",
                "render": $.fn.dataTable.render.number(',')
            },
        ],
        // Define columns class 
        "columnDefs": [
            { "visible": false, "targets": [0] },
            { "className": "text-center", "targets": [1,2,3] },
            { "className": "text-right", "targets": [4,5,6,7] },
        ],
        onEditRow: function(dt, rowdata, success, error) {
            rowdata.mode = 'update';
            rowdata.o_id = '<?=$row['o_id']?>';
            $.post('/<?=SHOP_INFO_ADMIN_DIR?>/purchase/order_item_process', rowdata, function(res) {
                if(res.is_success) {
                    //table.clear().draw();
                    success();
                    $('[name="o_total_price"]').val(res.o_total_price);
                    $('[name="o_vat_price"]').val(res.o_vat_price);
                } else {
                    Swal.fire("Submit Error !", res.msg, "error");
                }
            },'json');
        },
        onDeleteRow: function(dt, rowdata, success, error) {
            rowdata.mode = 'delete';
            rowdata.o_id = '<?=$row['o_id']?>';
            $.post('/<?=SHOP_INFO_ADMIN_DIR?>/purchase/order_item_process', rowdata, function(res) {
                if(res.is_success) {
                    //table.clear().draw();
                    success();
                    $('[name="o_total_price"]').val(res.o_total_price);
                    $('[name="o_vat_price"]').val(res.o_vat_price);
                } else {
                    Swal.fire("Submit Error !", res.msg, "error");
                }
            },'json');
        },

        "footerCallback": function ( row, data, start, end, display ) {
            var api = this.api(), data;

            // Remove the formatting to get integer data for summation
            var intVal = function ( i ) {
                return typeof i === 'string' ? i.replace(/[\$,]/g, '')*1 : typeof i === 'number' ? i : 0;
            };


            unit_total = api.column( 4 ).data().reduce( function (a, b) {
                return intVal(a) + intVal(b);
            }, 0 );
            qty_total = api.column( 5 ).data().reduce( function (a, b) {
                return intVal(a) + intVal(b);
            }, 0 );
            vat_total = api.column( 6 ).data().reduce( function (a, b) {
                return intVal(a) + intVal(b);
            }, 0 );
            tt_total = api.column( 7 ).data().reduce( function (a, b) {
                return intVal(a) + intVal(b);
            }, 0 );
            $( api.column( 4 ).footer() ).html( set_number_format(unit_total) );
            $( api.column( 5 ).footer() ).html( set_number_format(qty_total) );
            $( api.column( 6 ).footer() ).html( set_number_format(vat_total) );
            $( api.column( 7 ).footer() ).html( set_number_format(tt_total ) );
        }

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



    // Edit Modal 자동 계산
    $(document).on('change', '#oi_unit_price, #oi_quantity, #oi_tax', function(e) {
        var unit = parseInt($('#oi_unit_price').val());
        var qty =  parseInt($('#oi_quantity').val());
        var tax = (unit * qty) / <?=TAX?>;

        var total = (unit * qty) + tax;

        $('#oi_tax').val(tax);
        $('#oi_total_price').val(total);
    });

    // Add 자동 계산
    $(document).on('change', '[name="oi_unit_price"], [name="oi_quantity"], [name="oi_tax"]', function(e) {
        var unit = parseInt($('[name="oi_unit_price"]').val());
        var qty =  parseInt($('[name="oi_quantity"]').val());
        var tax = (unit * qty) / <?=TAX?>;

        var total = (unit * qty) + tax;

        $('[name="oi_tax"]').val(tax);
        $('[name="oi_total_price"]').val(total);
    });


});
</script>
