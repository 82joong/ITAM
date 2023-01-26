<?php 
//$page_css[] = "";

//include left panel (navigation)
//follow the tree in inc/config.ui.php
require_once realpath(dirname(__FILE__).'/../').'/inc/config.ui.php';


$mode = 'list';
$active_key = array(
    "system",               // 1 Depth menu
    "type"          // 2(Sub) Depth menu
);
$page_nav[$active_key[0]]["active"] = true;
$page_nav[$active_key[0]]["sub"][$active_key[1]]["active"] = true;
include realpath(dirname(__FILE__).'/../').'/inc/nav.php';

$title_symbol = 'fa-folder-tree';
?>

<style type="text/css">
tbody > tr > td:nth-child(5) {
    /*background-color: yellow;*/
    font-size: .85em;
}
</style>


<main id="js-page-content" role="main" class="page-content">
    <?php include realpath(dirname(__FILE__).'/../').'/inc/breadcrumbs.php'; ?>
    <div class="row">
        <div class="col-xl-12">
            <div class="panel">
                <div class="panel-hdr">
                    <h2>Table</h2>
                   
                    <div class="panel-toolbar">
                        <a href="/<?=SHOP_INFO_ADMIN_DIR?>/system/type_detail" class="btn btn-sm btn-success waves-effect waves-themed">
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
                                    <th data-name="at_id" data-type="text" data-op="eq">ID</th>
                                    <th data-name="at_name" data-type="text" data-op="cn">Name</th>
                                    <th data-name="at_icon" data-type="text" data-op="eq">Icon</th>
                                    <th data-name="at_color" data-type="text" data-op="eq">Color</th>
                                    <th data-name="at_description" data-type="text" data-op="cn">Description</th>
                                    <th data-name="at_is_active" data-type="select" data-value="<?=$active_data?>" data-op="eq">IsActive</th>
                                    <th data-name="at_created_at" data-type="range" data-datepicker="use" data-op="bt">Created At</th>
                                    <th data-name="at_updated_at" data-type="range" data-datepicker="use" data-op="bt">Updated At</th>
                                    <th style="width:48px;"></th>
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

<script src="<?=$assets_dir?>/js/colfilter.datatable.js"></script>
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
            "url": "/<?=SHOP_INFO_ADMIN_DIR?>/system/type",
            "type": "POST",
            "dataType": "JSON",
            "data": {
                "mode": "list",
            }
        },
        "columns": [
            {"data": "at_id"},
            {"data": "at_name"},
            {"data": "at_icon"},
            {"data": "at_color"},
            {"data": "at_description"},
            {"data": "at_is_active"},
            {"data": "at_created_at"},
            {"data": "at_updated_at"},
            {
                "data": null,
                "className": "text-center",
                "defaultContent": ''
            }
        ],
        "createdRow": function(row, data, dataIndex){
            $('td', row).css('min-width', '70px');
        },
        // Define columns class 
        "columnDefs": [
            { "className": "text-center fs-nano", "targets": [0,1,2,3,5,6,7,8] },
            { "className": "text-left fs-nano", "targets": 4 },
            {
                responsivePriority: 1,
                targets: -1,
                title: '',
                orderable: false,
                render: function(data, type, full, meta) {

                    var btn_act = ''; 
                    var base_url = '/<?=SHOP_INFO_ADMIN_DIR?>/system/type_detail';

                    // Edit/Detail 
                    btn_act += dtEditButton(base_url+'/'+data.at_id);

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
</script>
