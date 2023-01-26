<?php 
//$page_css[] = "";

//include left panel (navigation)
//follow the tree in inc/config.ui.php
require_once realpath(dirname(__FILE__).'/../').'/inc/config.ui.php';


$mode = 'list';
$active_key = array(
    "purchase",               // 1 Depth menu
    "order"          // 2(Sub) Depth menu
);
$page_nav[$active_key[0]]["active"] = true;
$page_nav[$active_key[0]]["sub"][$active_key[1]]["active"] = true;
include realpath(dirname(__FILE__).'/../').'/inc/nav.php';

$title_symbol = 'fa-money-check';
?>

<style type="text/css">
</style>


<main id="js-page-content" role="main" class="page-content">
    <?php include realpath(dirname(__FILE__).'/../').'/inc/breadcrumbs.php'; ?>
    <div class="row">
        <div class="col-xl-12">
            <div class="panel">
                <div class="panel-hdr">
                    <h2>Table</h2>
                   
                    <div class="panel-toolbar">
                        <a href="/<?=SHOP_INFO_ADMIN_DIR?>/purchase/order_detail" class="btn btn-sm btn-success waves-effect waves-themed">
                            <span class="fas fa-plus-square mr-1"></span> Add New Row
                        </a>
                        <?=genFullButton();?>
                    </div>
                </div>

                <div class="panel-container show">
                    <div class="panel-content">
                        
                        <table id="data-table" class="table table-sm table-striped table-bordered table-hover" style="width:100%">
                            <thead class="bg-warning-200">
                                <tr>
                                    <th data-name="o_id" data-type="none">ID</th>
                                    <th data-name="o_estimatenum" data-type="text" data-op="cn">견적번호</th>
                                    <th data-name="o_ordernum" data-type="text" data-op="cn">주문번호</th>
                                    <th data-name="o_reportnum" data-type="text" data-op="cn">품의번호</th>
                                    <th data-name="o_company_id" data-type="select" data-value="<?=$company_data?>" data-op="eq">Company</th>
                                    <th data-name="o_supplier_id" data-type="select" data-value="<?=$supplier_data?>" data-op="eq">Supplier</th>
                                    <th data-name="o_delivery_price" data-type="range" data-op="bt">Delivery Price</th>
                                    <th data-name="o_etc_price" data-type="range" data-op="bt">ETC Price</th>
                                    <th data-name="o_vat_price" data-type="range" data-op="bt">VAT. Price</th>
                                    <th data-name="o_total_price" data-type="range" data-op="bt">TotalPrice</th>
                                    <th data-name="o_order_status" data-type="select" data-value="<?=$status_data?>" data-op="eq">Status</th>
                                    <th data-name="o_created_at" data-type="range" data-datepicker="use" data-op="bt">Created At</th>
                                    <th data-name="o_delivered_at" data-type="range" data-datepicker="use" data-op="bt">Delivered At</th>
                                    <th data-name="o_canceled_at" data-type="range" data-datepicker="use" data-op="bt">Canceled At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tfoot class="bg-warning-200">
                                <tr>
                                    <th>ID</th>
                                    <th>견적번호</th>
                                    <th>주문번호</th>
                                    <th>품의번호</th>
                                    <th>Company</th>
                                    <th>Supplier</th>
                                    <th>Delivery Price</th>
                                    <th>ETC Ptice</th>
                                    <th>VAT. Price</th>
                                    <th>VAT. Price</th>
                                    <th>Total Price</th>
                                    <th>Created At</th>
                                    <th>Delivered At</th>
                                    <th>Canceled At</th>
                                    <th>Action</th>
                                </tr>
                            </tfoot>
                        </table>
     
                    </div>
                <div>
            </div>
        </div>
    </div>
</main>
<!-- this overlay is activated only when mobile menu is triggered -->
<div class="page-content-overlay" data-action="toggle" data-class="mobile-nav-on"></div> 
<!-- END Page Content -->

<script type="text/javascript">

$(document).ready(function() {

    // Setup - add a text input to each footer cell
    $('#data-table thead tr').clone(true).appendTo('#data-table thead');

    var table = $('#data-table').DataTable({
        "pageLength"    : getPageLength(),               // Paging Unit
        "lengthMenu"    : dtLengthMenu,
        "orderCellsTop" : true, 
        "fixedHeader"   : true, 
        "processing"    : true,
        "serverSide"    : true,
        "searching"     : true,
        "responsive"    : true,
        "select"        : "single",
        "order"         : [[0, "desc"]],    // Default Sorting
        "ajax": {
            "url": "/<?=SHOP_INFO_ADMIN_DIR?>/purchase/orders",
            "type": "POST",
            "dataType": "JSON",
            "data": {
                "mode": "list",
            }
        },
        "createdRow": function(row, data, dataIndex){
            $('td', row).css('min-width', '70px');
            $('td:eq(3),td:eq(9),td:eq(10)', row).css('min-width', '100px');
            $('td:eq(1),td:eq(5),td:eq(11),td:eq(12),td:eq(13)', row).css('min-width', '130px');
        },
        "columns": [
        {
            "data": "o_id",
                //"visible": false,
            },
            {"data": "o_estimatenum"},
            {"data": "o_ordernum"},
            {"data": "o_reportnum"},
            {"data": "o_company_id"},
            {"data": "o_supplier_id"},
            {
                "data": "o_delivery_price",
                "render": $.fn.dataTable.render.number(',')
            },
            {
                "data": "o_etc_price",
                "render": $.fn.dataTable.render.number(',')
            },
            {
                "data": "o_vat_price",
                "render": $.fn.dataTable.render.number(',')
            },
            {
                "data": "o_total_price",
                "render": $.fn.dataTable.render.number(',')
            },
            {"data": "o_order_status"},
            {"data": "o_created_at"},
            {"data": "o_delivered_at"},
            {"data": "o_canceled_at"},
            {
                "data": null,
                "className": "text-center",
                "defaultContent": ''
            }
            
        ],
        // Define columns class 
        "columnDefs": [
            { "className": "text-center fs-nano", "targets": '_all'},
            {
                responsivePriority: 1,
                targets: -1,
                title: '',
                orderable: false,
                render: function(data, type, full, meta) {

                    var btn_act = '';
                    var base_url = '/<?=SHOP_INFO_ADMIN_DIR?>/purchase/order_detail';

                    // Edit/Detail 
                    btn_act += dtEditButton(base_url+'/'+data.o_id);
                    return btn_act;
                }
            }
        ],
        "dom": dtDoms,
        "buttons": dtButtons,

        "initComplete": function( settings, json ) {
            //console.log('complete');
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




    // Search Box Event
    $('input[type="search"]').unbind();
    $('input[type="search"]').on('keypress', function(e) {
        if(e.keyCode == 13) {
            table.search(this.value).draw();
            setSearch(this.value);
        }
    });


    // Generate Search Filter 
    generatorColFilter('data-table', table);


    if (window.location.search.indexOf('keep=yes') === -1) {
        clearHistory();
    }

    var firstDraw = true;

    // 최초 draw 되는 시점에만 필터검색
    table.one('draw', function() {
        var searchBox = $('input[type="search"]');
        var searchVal = getSearch();
        if (searchBox && (searchVal.length > 0) ) {
            searchBox.val(searchVal);
            table.search(searchVal).draw();
        }
        findingColFilter('data-table', table);
        firstDraw = false;
    });

    // 매번 draw 될 때마다 페이지 보존
    table.on('draw', function() {
        if (firstDraw) return;
        var page = table.page.info().page;
        setPage(page);
    });

    // 데이터 테이블의 값이 변경되었을시 페이지를 보존 
    table.on('page', function() {
        var page = table.page.info().page;
        setPage(page);
    });
    $('[name="data-table_length"]').on('change', function() {
        setPageLength($(this).val());
    });


});
</script>
