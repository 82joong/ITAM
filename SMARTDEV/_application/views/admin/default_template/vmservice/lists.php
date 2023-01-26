<?php 
//$page_css[] = "";

//include left panel (navigation)
//follow the tree in inc/config.ui.php
require_once realpath(dirname(__FILE__).'/../').'/inc/config.ui.php';


$mode = 'list';
$active_key = array(
    "service",               // 1 Depth menu
    "vmware"          // 2(Sub) Depth menu
);
$page_nav[$active_key[0]]["active"] = true;
$page_nav[$active_key[0]]["sub"][$active_key[1]]["active"] = true;
include realpath(dirname(__FILE__).'/../').'/inc/nav.php';

$title_symbol = 'fa-warehouse';
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
                        <?php /*?>
                        <a href="/<?=SHOP_INFO_ADMIN_DIR?>/vmservice/detail" class="btn btn-sm btn-success waves-effect waves-themed">
                            <span class="fas fa-plus-square mr-1"></span> Add New Row
                        </a>
                        <?php */?>

                        <?=genFullButton();?>
                    </div>
                </div>

                <div class="panel-container show">
                    <div class="panel-content">
                        <table id="data-table" class="table table-sm table-striped table-bordered table-hover" style="width:100%">
                            <thead class="bg-warning-200">
                                <tr>
                                    <th class="width-10" data-name="am_id" data-type="text" data-op="eq">ID</th>
                                    <th data-name="vms_name" data-type="text" data-op="cn">Service Name</th>
                                    <th data-name="vms_ip" data-type="none" >Service IP</th>
                                    <th data-name="vms_status" data-type="select" data-value="<?=$status_type?>" data-op="eq" >Status</th>

                                    <th data-name="sm_manage_team" data-type="text" data-op="cn">담당팀</th>

                                    <th data-name="am_name" data-type="text" data-op="cn">Assets</th>
                                    <th data-name="am_models_name" data-type="text" data-op="cn" >Model</th>
                                    <th data-name="am_company_id" data-type="select" data-value="<?=$company_type?>" data-op="eq">Company</th> 
                                    <th data-name="remote_ip" data-type="none" >Remote IP</th>
                                    <th data-name="ip_address" data-type="text" data-op="eq">Vmware IP</th>
                                    <th data-name="sm_os_info" data-type="text" data-op="cn">OS</th>
                                    <th data-name="sm_db_info" data-type="text" data-op="cn">DB</th>
                                    <th data-name="sm_lang_info" data-type="text" data-op="cn">LANG</th>
                                    <th data-name="sm_was_info" data-type="text" data-op="cn">WAS</th>
                                    <th data-name="am_serial_no" data-type="text" data-op="eq">Service Tag</th>



                                    <th data-name="am_location_id" data-type="select" data-value="<?=$location_type?>" data-op="eq">Location</th>
                                    <th data-name="am_rack_code" data-type="text" data-op="cn">Rack Code</th>

                                    <th data-name="sm_usage" data-type="text" data-op="cn">사용용도</th>
                                    <th data-name="sm_master_manager" data-type="text" data-op="cn">주 담당자</th>
                                    <th data-name="sm_sub_manager" data-type="text" data-op="cn">부 담당자</th>


                                    <th data-name="vms_memo" data-type="text" data-op="cn" >Memo</th>
                                    <th data-name="ip_memo" data-type="text" data-op="cn">IP Memo</th>

                                    <th data-name="sm_secure_conf" data-type="none">기</th>
                                    <th data-name="sm_secure_inte" data-type="none">무</th>
                                    <th data-name="sm_secure_avail" data-type="none">가</th>
                                    <th data-name="sm_important_score" data-type="none">Score</th>
                                    <th data-name="sm_important_level" data-type="none">Level</th>
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

        /*
        //"scrollY": 400,
        "scrollX": true,
        "scrollCollapse": true,
        "fixedColumns"  : false,
        "fixedCOlumns"  : {leftColumn: 3}, 
        */

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
            "url": "/<?=SHOP_INFO_ADMIN_DIR?>/vmservice/lists",
            "type": "POST",
            "dataType": "JSON",
            "data": {
                "mode": "list",
            }
        },
        "rowGroup"      : { 
            "dataSrc"   : "am_vmware_name",
            "className" : "fs-md",
            "startRender" : function ( rows, group ) {

                var rows_cnt = rows.count();
                var cnt_color = 'bg-primary-500';
                if(rows.data()[0].vms_id == null) {
                    rows_cnt = 0;
                    cnt_color = 'bg-danger-500';
                }

                var tit = '<span class="fw-700"><i class="fal fa-minus-octagon mr-1"></i>'+group+'</span>';
                var cnt = '<span class="badge border-light position-relative ml-2 '+cnt_color+'">'+rows_cnt+'</span>';

                var url = '/<?=SHOP_INFO_ADMIN_DIR?>/assets/detail/servers/'+rows.data()[0].am_id+'#tab_assets';
                var btn = '<a href="'+url+'" class="btn btn-xs btn-outline-info waves-effect waves-themed mr-5 position-absolute pos-right" target="_blank">';
                btn += '<i class="fal fa-edit mr-1"></i>Edit</a>';
                return tit + cnt + btn;
           }
        },
        "createdRow": function(row, data, dataIndex){
            $('td', row).css('min-width', '70px');
            $('td:eq(4)',row).css('min-width', '130px');
            $('td:eq(1)',row).css('min-width', '150px');
        },
        "columns": [
            {"data": "am_id"},
            {"data": "vms_name"},
            {"data": "vms_ip"},
            {"data": "vms_status"},


            {"data": "sm_manage_team"},
            {"data": "am_name"},
            {"data": "am_models_name"},
            {"data": "am_company_id"},
            {"data": "remote_ip"},
            {"data": "ip_address"},
            {"data": "sm_os_info"},
            {"data": "sm_db_info"},
            {"data": "sm_lang_info"},
            {"data": "sm_was_info"},
            {"data": "am_serial_no"},



            {"data": "am_location_id"},
            {"data": "am_rack_code"},

            {"data": "sm_usage"},
            {"data": "sm_master_manager"},
            {"data": "sm_sub_manager"},

            {"data": "vms_memo"},
            {"data": "ip_memo"},

            {"data": "sm_secure_conf"},
            {"data": "sm_secure_inte"},
            {"data": "sm_secure_avail"},
            {"data": "sm_important_score"},
            {"data": "sm_important_level"},
            {
                "data": null,
                "className": "text-center",
                "defaultContent": ''
            }
        ],
        // Define columns class 
        "columnDefs": [
            //{ "className": "text-left fs-nano", "targets": 3 },
            { "className": "text-center fs-nano", "targets": "_all" },
            {
                responsivePriority: 1,
                targets: -1,
                title: '',
                orderable: false,
                render: function(data, type, full, meta) {

                    var btn_act = ''; 
                    var base_url = '/<?=SHOP_INFO_ADMIN_DIR?>/vmservice/detail';

                    // View 
                    btn_act += dtViewButton(base_url+'/'+data.vms_id);
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
