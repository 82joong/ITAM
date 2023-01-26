<?php 
//$page_css[] = "";

//include left panel (navigation)
//follow the tree in inc/config.ui.php
require_once realpath(dirname(__FILE__).'/../').'/inc/config.ui.php';


$mode = 'list';
$active_key = array(
    "system",               // 1 Depth menu
    "hostmap"          // 2(Sub) Depth menu
);
$page_nav[$active_key[0]]["active"] = true;
$page_nav[$active_key[0]]["sub"][$active_key[1]]["active"] = true;
include realpath(dirname(__FILE__).'/../').'/inc/nav.php';

$title_symbol = 'fa-tags';
?>

<style type="text/css">

tbody > tr > td:nth-child(5) {
    /*background-color: yellow;*/
    font-size: .85em;
}

</style>


<main id="js-page-content" role="main" class="page-content">
    <?php include realpath(dirname(__FILE__).'/../').'/inc/breadcrumbs.php'; ?>


    <div class="alert alert-primary">
        <div class="d-flex flex-start w-100">
            <div class="mr-2 hidden-md-down">
                <span class="icon-stack icon-stack-lg">
                    <i class="base base-2 icon-stack-3x opacity-100 color-primary-500"></i>
                    <i class="base base-2 icon-stack-2x opacity-100 color-primary-300"></i>
                    <i class="fal fa-info icon-stack-1x opacity-100 color-white"></i>
                </span>
            </div>
            <div class="d-flex flex-fill">
                <div class="flex-fill">
                    <span class="h5">About</span>
                    <p>
                        Syslog <code>[elasticsearch index : syslog-*]</code> 데이터와 
                        <code>서비스현황 > 서비스</code> 간 데이터 연결 위한 매핑 정보
                    </p>

                    <p>
                        - <code>Syslog Host Name</code> : ELK index syslog-*.host (엘라스틱 서버내의 host)<br />
                        - <code>Service Name</code> : ITAM vmservice_tb.vmservice_name (Mysql DB 내의 서비스명) 
                    </p>



                    <span class="h5">Description</span>
                    <p>
                        - <code>Cron</code> 의해 주기적으로 Syslog로 부터 추가 되는 host가 해당 row에 자동으로 추가 <br />
                        - 추가된 row 기준으로 수정 페이지에서  서비스현황 > 서비스 에 추가된 host 매핑 
                    </p>
                </div>
            </div>
        </div>
    </div>

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
                                    <th data-name="vhm_id" data-type="text" data-op="eq">ID</th>
                                    <th data-name="vhm_vmservice_name" data-type="text" data-op="cn">Service Name</th>
                                    <th data-name="vhm_elk_host_name" data-type="text" data-op="cn">Syslog Host Name</th>
                                    <th data-name="vhm_mysql_version" data-type="text" data-op="cn">Mysql Version</th>
                                    <th data-name="vhm_created_at" data-type="range" data-datepicker="use" data-op="bt">Created At</th>
                                    <th data-name="vhm_updated_at" data-type="range" data-datepicker="use" data-op="bt">Updated At</th>
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



    /*
    $.fn.DataTable.Api.register('button.exportData()', function(options) {
        var arr = [];
        $.ajax({
            "url": "/<?=SHOP_INFO_ADMIN_DIR?>/system/hostmap",
            "type": "POST",
            "success": function(result) {
                for(var key in result) {
                    arr.push(Object.keys(result[key]).map(function(k) {
                        return result[key][k];
                    }));
                }
            },
            async: false
        });
        return {
            body: arr,
            header: $("#data-table thead tr th").map(function() {
                return this.innerHTML;
            }).get()
        };
    });
    */



    // Setup - add a text input to each footer cell
    $('#data-table thead tr').clone(true).appendTo('#data-table thead');

    dtLengthMenu.push('ALL');

    var table = $('#data-table').DataTable({
        "info"          : true,
        "paging"        : true,
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
            "url": "/<?=SHOP_INFO_ADMIN_DIR?>/system/hostmap",
            "type": "POST",
            "dataType": "JSON",
            "data": {
                "mode": "list",
            }
        },
        "columns": [
            {"data": "vhm_id"},
            {"data": "vhm_vmservice_name"},
            {"data": "vhm_elk_host_name"},
            {"data": "vhm_mysql_version"},
            {"data": "vhm_created_at"},
            {"data": "vhm_updated_at"},
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

                    var base_url = '/<?=SHOP_INFO_ADMIN_DIR?>/system/hostmap_detail';
                    var btn_act = "";
                    
                    // Edit/Detail 
                    btn_act += dtEditButton(base_url+'/'+data.vhm_id);
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



    /*
    var data = table.buttons.exportData( {
        modifier: {
            page: 'all'
        }
    } );
    console.log(data);
    */
});
</script>
