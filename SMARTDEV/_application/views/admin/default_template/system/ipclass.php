<?php 
//$page_css[] = "";

//include left panel (navigation)
//follow the tree in inc/config.ui.php
require_once realpath(dirname(__FILE__).'/../').'/inc/config.ui.php';



$mode = 'list';
$active_key = array(
    "system",               // 1 Depth menu
    "ipclass",              // 2(Sub) Depth menu
); 
$page_nav[$active_key[0]]["active"] = true;
$page_nav[$active_key[0]]["sub"][$active_key[1]]["active"] = true;

include realpath(dirname(__FILE__).'/../').'/inc/nav.php';

$title_symbol = 'fa-network-wired';
?>

<style type="text/css">
tbody > tr > td:nth-child(6) {
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
                        <a href="/<?=SHOP_INFO_ADMIN_DIR?>/system/ipclass_detail" class="btn btn-sm btn-success waves-effect waves-themed">
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
                                    <th data-name="ipc_location_id" data-type="text" data-op="cn">Location</th>
                                    <?php if( isset($ipc_type) && strlen($ipc_type) > 0 ) : ?>
                                    <th data-name="ipc_type" data-type="none">Type</th>
                                    <?php else : ?>
                                    <th data-name="ipc_type" data-type="select" data-value="<?=$type_data?>" data-op="eq">Type</th>
                                    <?php endif; ?>
                                    <th data-name="ipc_name" data-type="text" data-op="cn">Name</th>
                                    <th data-name="ipc_cidr" data-type="text" data-op="cn">CIDR</th>
                                    <th data-name="ipc_start" data-type="text" data-op="cn">Start</th>
                                    <th data-name="ipc_end" data-type="text" data-op="cn">End</th>
                                    <th data-name="ipc_memo" data-type="text" data-op="cn">Memo</th>
                                    <th style="width:48px;"></th>
                                </tr>
                            </thead>


                            <tfoot class="bg-warning-200">
                                <tr>
                                    <th>Location</th>
                                    <th>Type</th>
                                    <th>Name</th>
                                    <th>CIDR</th>
                                    <th>Start</th>
                                    <th>End</th>
                                    <th>Memo</th>
                                    <th>Controls</th>
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

<script src="<?=$assets_dir?>/js/colfilter.datatable.js"></script>
<script type="text/javascript">

$(document).ready(function() {

    // Setup - add a text input to each footer cell
    $('#data-table thead tr').clone(true).appendTo('#data-table thead');

    var table = $('#data-table').DataTable({
        //"pageLength"    : 25,               // Paging Unit
        //"lengthMenu"    : dtLengthMenu,
        "paging"        : false,
        "orderCellsTop" : true, 
        "fixedHeader"   : true, 
        "processing"    : true,
        "serverSide"    : true,
        "searching"     : true,
        "responsive"    : true,
        "select"        : "single",
        "order"         : [[0, "desc"]],    // Default Sorting
        "rowGroup"      : { 
            "dataSrc"   : "ipc_location_id",
        },
        "ajax": {
            "url": "/<?=SHOP_INFO_ADMIN_DIR?>/system/ipclass",
            "type": "POST",
            "dataType": "JSON",
            "data": {
                "mode"      : "list",
                "ipc_type"  : "<?=isset($ipc_type)? $ipc_type : ''?>",
            }
        },
        "columns": [
            {"data": "ipc_location_id"},
            {"data": "ipc_type"},
            {"data": "ipc_name"},
            {"data": "ipc_cidr"},
            {"data": "ipc_start"},
            {"data": "ipc_end"},
            {"data": "ipc_memo"},
            {
                "data": null,
                "className": "text-center",
                "defaultContent": ''
            }
        ],
        // Define columns class 
        "columnDefs": [
            { "className": "text-center fs-nano", "targets": "_all" },
            { "className": "text-left fs-nano", "targets": 6 },
            {
                responsivePriority: 1,
                targets: -1,
                title: '',
                orderable: false,
                render: function(data, type, full, meta) {

                    var btn_act = ''; 
                    var base_url = '/<?=SHOP_INFO_ADMIN_DIR?>/system/ipclass_detail';

                    // Clone 
                    btn_act += dtCloneButton(base_url+'/'+data.ipc_id+'/clone');

                    // Edit/Detail 
                    btn_act += dtEditButton(base_url+'/'+data.ipc_id);

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


});
</script>
