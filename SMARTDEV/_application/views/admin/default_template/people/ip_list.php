<?php 
//$page_css[] = "";

//include left panel (navigation)
//follow the tree in inc/config.ui.php
require_once realpath(dirname(__FILE__).'/../').'/inc/config.ui.php';


$mode = 'list';
$active_key = array(
    "ips",          // 2(Sub) Depth menu
    "iplist"          // 2(Sub) Depth menu
);
$page_nav[$active_key[0]]["active"] = true;
$page_nav[$active_key[0]]["sub"][$active_key[1]]["active"] = true;
include realpath(dirname(__FILE__).'/../').'/inc/nav.php';

$title_symbol = 'fa-ethernet';
?>

<style text/type="css">

</style>


<main id="js-page-content" role="main" class="page-content">


    <?php include realpath(dirname(__FILE__).'/../').'/inc/breadcrumbs.php'; ?>
    

    <div class="row">
        <div class="col-xl-12">
            <div class="panel">
                <div class="panel-hdr">
                    <h2>Table</h2>
                   
                    <div class="panel-toolbar">
                        <?=genFullButton();?>
                    </div>
                </div>

                <div class="panel-container show">
                    <div class="panel-content">
                        
                        <table id="data-table" class="table table-sm table-striped table-bordered table-hover" style="width:100%">
                            <thead class="bg-warning-200">
                                <tr>
                                    <th data-name="ip_id" data-type="text" data-op="eq">ID</th>
                                    <th data-name="ip_address" data-type="text" data-op="eq">IP Address</th>
                                    <th data-name="ipc_name" data-type="text" data-op="cn">CIDR Name</th>
                                    <th data-name="ipc_cidr" data-type="text" data-op="cn">CIDR</th>
                                    <th data-name="ip_class_type" data-type="select" data-value="<?=$type_data?>" data-op="eq">IP Type</th>
                                    <th data-name="ip_class_category" data-type="select" data-value="<?=$category_data?>" data-op="eq">IP Category</th>
                                    <th data-name="ip_memo" data-type="text" data-op="eq">Memo</th>
                                    <th data-name="ip_created_at" data-type="range" data-datepicker="use" data-op="bt">Created At</th>
                                    <th data-name="ip_updated_at" data-type="range" data-datepicker="use" data-op="bt">Updated At</th>
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
            "url": "/<?=SHOP_INFO_ADMIN_DIR?>/people/ip_list",
            "type": "POST",
            "dataType": "JSON",
            "data": {
                "mode": "list",
            }
        },
        "createdRow": function(row, data, dataIndex){
            $('td', row).css('min-width', '70px');
        },
        "columns": [
            {"data": "ip_id"},
            {"data": "ip_address"},
            {"data": "ipc_name"},
            {"data": "ipc_cidr"},
            {"data": "ip_class_type"},
            {"data": "ip_class_category"},
            {"data": "ip_memo"},
            {"data": "ip_created_at"},
            {"data": "ip_updated_at"},
            /*
            {
                "data": null,
                "className": "text-center",
                "defaultContent": ''
            }
            */
        ],
        // Define columns class 
        "columnDefs": [
            { "className": "text-center fs-nano", "targets": "_all" },

            /*
            {
                targets: -1,
                title: '',
                orderable: false,
                render: function(data, type, full, meta) {

                    var btn_act = ''; 
                    var base_url = '/<?=SHOP_INFO_ADMIN_DIR?>/people/ip_detail';

                    // Clone 
                    btn_act += dtCloneButton(base_url+'/'+data.ip_id+'/clone');

                    // Edit/Detail 
                    btn_act += dtEditButton(base_url+'/'+data.ip_id);
                    return btn_act;
                }
            }
            */

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
