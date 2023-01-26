<?php 
//$page_css[] = "";

//include left panel (navigation)
//follow the tree in inc/config.ui.php
require_once realpath(dirname(__FILE__).'/../').'/inc/config.ui.php';


$mode = 'list';
$active_key = array(
    "manage",               // 1 Depth menu
    "supplier"          // 2(Sub) Depth menu
);
$page_nav[$active_key[0]]["active"] = true;
$page_nav[$active_key[0]]["sub"][$active_key[1]]["active"] = true;
include realpath(dirname(__FILE__).'/../').'/inc/nav.php';

$title_symbol = 'fa-hand-holding-box';
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
                        <a href="/<?=SHOP_INFO_ADMIN_DIR?>/manage/supplier_detail" class="btn btn-sm btn-success waves-effect waves-themed">
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
                                    <th class="width-10" data-name="sp_id" data-type="text" data-op="eq">ID</th>
                                    <th data-name="sp_name" data-type="text" data-op="eq">Name</th>
                                    <th data-name="sp_address" data-type="text" data-op="cn">Address</th>
                                    <th data-name="sp_contact_name" data-type="text" data-op="cn">Contact Name</th>
                                    <th data-name="sp_url" data-type="text" data-op="cn">URL</th>
                                    <th data-name="sp_tel" data-type="text" data-op="eq">Tel.</th>
                                    <th data-name="sp_email" data-type="text" data-op="eq">Email</th>
                                    <th style="width:80px;"></th>
                                </tr>
                            </thead>
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
            "url": "/<?=SHOP_INFO_ADMIN_DIR?>/manage/supplier",
            "type": "POST",
            "dataType": "JSON",
            "data": {
                "mode": "list",
            }
        },
        "columns": [
            {"data": "sp_id"},
            {"data": "sp_name"},
            {"data": "sp_address"},
            {"data": "sp_contact_name"},
            {"data": "sp_url"},
            {"data": "sp_tel"},
            {"data": "sp_email"},
            {
                "data": null,
                "className": "text-center",
                "defaultContent": ''
            }
        ],
        // Define columns class 
        "columnDefs": [
            { "className": "text-center fs-nano", "targets": [0,1,3,5] },
            { "className": "text-left fs-nano", "targets": [2,4] },
            {
                responsivePriority: 1,
                targets: -1,
                title: '',
                orderable: false,
                render: function(data, type, full, meta) {

                    var btn_act = ''; 
                    var base_url = '/<?=SHOP_INFO_ADMIN_DIR?>/manage/supplier_detail';

                    // Clone 
                    btn_act += dtCloneButton(base_url+'/'+data.sp_id+'/clone');

                    // Edit/Detail 
                    btn_act += dtEditButton(base_url+'/'+data.sp_id);
                    // Delete 
                    btn_act += dtDeleteButton('javascript:delRow('+data.sp_id+');');

                    return btn_act;
                }
            }
        ],

        "dom": dtDoms,
        "buttons": dtButtons 
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

function delRow(id) {
    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!"
    }).then(function(result) {
        if (result.value) {
            var url = '/<?=SHOP_INFO_ADMIN_DIR?>/manage/supplier_process';
            var params = {'sp_id':id, 'mode':'delete'};
            $.post(url, params, function(res) {
                if(res.is_success) {
                    $('#data-table').DataTable().clear().draw();
                } else {
                    alert(res.msg);            
                }
            },'json');

        }
    });
}
</script>
