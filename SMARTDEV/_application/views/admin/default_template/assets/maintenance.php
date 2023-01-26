<?php 
//$page_css[] = "";

//include left panel (navigation)
//follow the tree in inc/config.ui.php
require_once realpath(dirname(__FILE__).'/../').'/inc/config.ui.php';


$mode = 'list';
$active_key = array(
    "assets",               // 1 Depth menu
    "maintenance"          // 2(Sub) Depth menu
);
$page_nav[$active_key[0]]["active"] = true;
$page_nav[$active_key[0]]["sub"][$active_key[1]]["active"] = true;
include realpath(dirname(__FILE__).'/../').'/inc/nav.php';

$title_symbol = 'fa-tools';
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
                        <a href="/<?=SHOP_INFO_ADMIN_DIR?>/assets/maintenance_detail" class="btn btn-sm btn-success waves-effect waves-themed">
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
                                    <th class="width-10" data-name="mtn_id" data-type="text" data-op="eq">ID</th>
                                    <th data-name="am_models_name" data-type="text" data-op="cn">Assets</th>
                                    <th data-name="mtn_name" data-type="text" data-op="cn">Assets Name</th>
                                    <th data-name="mtn_type" data-type="text" data-op="eq">Type</th>
                                    <th data-name="mtn_title" data-type="text" data-op="cn">Title</th>
                                    <th data-name="mtn_memo" data-type="text" data-op="cn">Memo</th>
                                    <th data-name="mtn_start_at" data-type="range" data-datepicker data-op="bt">Start At</th>
                                    <th data-name="mtn_end_at" data-type="range" data-datepicker data-op="bt" data-type="text" data-op="eq">End At</th>

                                    <th data-name="mtn_writer_name" data-type="text" data-op="cn">Writer</th>
                                    <th data-name="mtn_created_at" data-type="range" data-datepicker="use" data-op="bt">Created At</th>
                                    <th data-name="mtn_updated_at" data-type="range" data-datepicker="use" data-op="bt">Updated At</th>
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
        "pageLength"    : 25,               // Paging Unit
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
            "url": "/<?=SHOP_INFO_ADMIN_DIR?>/assets/maintenance",
            "type": "POST",
            "dataType": "JSON",
            "data": {
                "mode": "list",
            }
        },
        "columns": [
            {"data": "mtn_id"},
            {"data": "am_models_name"},
            {"data": "am_name"},
            {"data": "mtn_type"},
            {"data": "mtn_title"},
            {"data": "mtn_memo", "className": "text-left"},
            {"data": "mtn_start_at"},
            {"data": "mtn_end_at"},
            {"data": "mtn_writer_name",},
            {"data": "mtn_created_at"},
            {"data": "mtn_updated_at"},
            {
                "data": null,
                "className": "text-center",
                "defaultContent": ''
            }
        ],
        // Define columns class 
        "columnDefs": [
            { "className": "text-center fs-nano", "targets": "_all" },
            { 
                "className": "fs-nano",
                "responsivePriority": 1,
                "targets": 6 
            },
            {
                responsivePriority: 1,
                targets: -1,
                title: '',
                orderable: false,
                render: function(data, type, full, meta) {

                    var base_url = '/<?=SHOP_INFO_ADMIN_DIR?>/assets/maintenance_detail';
                    var btn_act = ''; 

                    // Clone 
                    btn_act += dtCloneButton(base_url+'/'+data.mtn_id+'/clone');

                    // Edit/Detail 
                    btn_act += dtEditButton(base_url+'/'+data.mtn_id);
                    return btn_act;
                }
            }
        ],
 
        "dom": dtDoms,
        "buttons": dtButtons,

        "initComplete": function(settings, json) {
            $('pre:not(:has(code))').each(function(i, block) {
                hljs.highlightBlock(block);
            });
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
        }
    });


    // Generate Search Filter 
    generatorColFilter('data-table', table);

});
</script>
