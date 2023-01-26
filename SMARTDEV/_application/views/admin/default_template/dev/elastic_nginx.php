<?php 
//$page_css[] = "";

//include left panel (navigation)
//follow the tree in inc/config.ui.php
require_once realpath(dirname(__FILE__).'/../').'/inc/config.ui.php';


$mode = 'list';
$active_key = array(
    "assets",               // 1 Depth menu
    "type",                 // 2(Sub) Depth menu
    $assets_type_uri
);
/*
$page_nav[$active_key[0]]["active"] = true;
$page_nav[$active_key[0]]["sub"]['type']['active'] = true;
$page_nav[$active_key[0]]["sub"]['type']['sub'][$active_key[2]]["active"] = true;
*/

include realpath(dirname(__FILE__).'/../').'/inc/nav.php';
$title_symbol = 'fa-server';
?>

<style type="text/css">
</style>


<main id="js-page-content" role="main" class="page-content">
    <?php include realpath(dirname(__FILE__).'/../').'/inc/breadcrumbs.php'; ?>

    <div class="row">
        <div class="col-xl-12">
            <div class="panel">
                <div class="panel-hdr">
                    <h2>Sample Nginx Access Log</h2>
                   
                    <div class="panel-toolbar">
                        <?=genFullButton();?>
                    </div>
                </div>

                <div class="panel-container show">
                    <div class="panel-content">
                        
                        <table id="data-table" class="table table-sm table-striped table-bordered table-hover" style="width:100%">
                            <thead class="bg-warning-200">
                                <tr>
                                    <th data-name="_id" data-type="text" data-op="eq">ID</th> 
                                    <th data-name="clientip" data-type="text" data-op="cn">Client IP</th>
                                    <th data-name="os" data-type="text" data-op="cn">OS</th>
                                    <th data-name="os_version" data-type="text" data-op="cn">OS Ver.</th>
                                    <th data-name="browser" data-type="text" data-op="cn">Browser</th>
                                    <th data-name="country_code" data-type="text" data-op="cn">Country Code</th>
                                    <th data-name="country_name" data-type="text" data-op="cn">Country</th>
                                    <th data-name="method" data-type="text" data-op="cn">Method</th>
                                    <th data-name="host" data-type="text" data-op="cn">Host</th>
                                    <th data-name="hostname" data-type="text" data-op="cn">Host Name</th>
                                    <th data-name="uri_path" data-type="text" data-op="cn">URI</th>
                                    <th data-name="response" data-type="text" data-op="cn">response</th>
                                    <th data-name="@timestamp" data-type="range" data-datepicker="use" data-op="bt">Created At</th>
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
        "order"         : [[12, "desc"]],    // Default Sorting
        "ajax": {
            "url": "/<?=SHOP_INFO_ADMIN_DIR?>/dev/elastic_nginx",
            "type": "POST",
            "dataType": "JSON",
            "data": {
                "mode": "list",
            }
        },
        "columns": [
            {
                "data": "_id",
                "visible": false,
            },
            {"data": "clientip"},
            {"data": "os"},
            {"data": "os_version"},
            {"data": "browser"},
            {"data": "country_code"},
            {"data": "country_name"},
            {"data": "method"},
            {"data": "host"},
            {"data": "hostname"},
            {"data": "uri_path"},
            {"data": "response"},
            {"data": "@timestamp"},
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
                targets: -1,
                title: '',
                orderable: false,
                render: function(data, type, full, meta) {

                    var base_url = '/<?=SHOP_INFO_ADMIN_DIR?>/assets';
                    var btn_act = ''; 

                    // View 
                    //btn_act += dtViewButton(base_url+'/status_detail/'+data.am_id);
                    // Edit/Detail 
                    //btn_act += dtEditButton(base_url+'/detail/<?=$assets_type_uri?>/'+data.am_id);

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
        }
    });


    // Generate Search Filter 
    generatorColFilter('data-table', table);

});
</script>
