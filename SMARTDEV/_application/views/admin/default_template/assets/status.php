<?php 
//$page_css[] = "";

//include left panel (navigation)
//follow the tree in inc/config.ui.php
require_once realpath(dirname(__FILE__).'/../').'/inc/config.ui.php';


$mode = 'list';
$active_key = array(
    "assets",                   // 1 Depth menu
    "status",                   // 2(Sub) Depth menu
    "all" 
);
$page_nav[$active_key[0]]["active"] = true;
$page_nav[$active_key[0]]["sub"]['status']['active'] = true;
$page_nav[$active_key[0]]["sub"]['status']['sub'][$active_key[2]]["active"] = true;


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
                                    <th data-name="am_id" data-type="text" data-op="eq">ID</th> 
                                    <th data-name="am_company_id" data-type="select" data-value="<?=$company_type?>" data-op="eq">Company</th> 
                                    <th data-name="am_name" data-type="text" data-op="cn">Name</th>
                                    <th data-name="am_vmware_name" data-type="text" data-op="cn">VMWare</th>
                                    <th data-name="am_tags" data-type="text" data-op="cn">Tags</th>
                                    <th data-name="am_estimatenum" data-type="text" data-op="cn">견적번호</th>
                                    <th data-name="am_serial_no" data-type="text" data-op="cn">Service Tag</th>
                                    <th data-name="am_models_name" data-type="text" data-op="cn">Model</th>
                                    <th data-name="am_status_id" data-type="select" data-value="<?=$status_type?>" data-op="eq">Status</th>
                                    <th data-name="am_location_id" data-type="select" data-value="<?=$location_type?>" data-op="eq">Location</th>
                                    <th data-name="am_rack_code" data-type="text" data-op="cn">Rack Space</th>
                                    <th data-name="am_eos_expired_at" data-type="range" data-datepicker="use" data-op="bt">EOS Expired At</th>
                                    <th data-name="am_created_at" data-type="range" data-datepicker="use" data-op="bt">Created At</th>
                                    <th data-name="am_updated_at" data-type="range" data-datepicker="use" data-op="bt">Updated At</th>
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
            "url": "/<?=SHOP_INFO_ADMIN_DIR?>/assets/status",
            "type": "POST",
            "dataType": "JSON",
            "data": {
                "mode": "list",
            }
        },
        "createdRow": function(row, data, dataIndex){
            $('td', row).css('min-width', '70px');
            $('td:eq(2),td:eq(3),td:eq(9),td:eq(10)', row).css('min-width', '100px');
            $('td:eq(11)', row).css('min-width', '130px');
        },
        "columns": [
            {"data": "am_id"},
            {"data": "am_company_id"},
            {"data": "am_name"},
            {"data": "am_vmware_name"},
            {"data": "am_tags"},
            {"data": "am_estimatenum"},
            {"data": "am_serial_no"},
            {"data": "am_models_name"},
            {"data": "am_status_id"},
            {"data": "am_location_id"},
            {"data": "am_rack_code"},
            {"data": "am_eos_expired_at"},
            {"data": "am_created_at"},
            {"data": "am_updated_at"},
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
                responsivePriority: 1,
                targets: -1,
                title: '',
                orderable: false,
                render: function(data, type, full, meta) {

                    var btn_act = ''; 
                    var base_url = '/<?=SHOP_INFO_ADMIN_DIR?>/assets/status_detail';

                    // View 
                    btn_act += dtViewButton(base_url+'/'+data.am_id);

                    // Edit/Detail 
                    var base_url = '/<?=SHOP_INFO_ADMIN_DIR?>/assets';
                    btn_act += dtEditButton(base_url+'/detail/'+data.am_assets_type_name+'/'+data.am_id);
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
