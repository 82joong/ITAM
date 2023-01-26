<?php 
//$page_css[] = "";

//include left panel (navigation)
//follow the tree in inc/config.ui.php
require_once realpath(dirname(__FILE__).'/../').'/inc/config.ui.php';


$mode = 'list';
$active_key = array(
    "people",               // 1 Depth menu
    "employee"          // 2(Sub) Depth menu
);
$page_nav[$active_key[0]]["active"] = true;
$page_nav[$active_key[0]]["sub"][$active_key[1]]["active"] = true;
include realpath(dirname(__FILE__).'/../').'/inc/nav.php';

$title_symbol = 'fa-users';
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
                        <?php /* ====== 자동동기화로 인한 수기 추가 제거 ====== ?>
                        <a href="/<?=SHOP_INFO_ADMIN_DIR?>/people/employee_detail" class="btn btn-sm btn-success waves-effect waves-themed">
                            <span class="fas fa-plus-square mr-1"></span> Add New Row
                        </a>
                        <?php */?>
                        <?=genFullButton();?>
                    </div>
                </div>

                <div class="panel-container show">
                    <div class="panel-content">

                    <div class="panel-tag fs-md">
                        <code>@ [Daily 01:30] 동기화 - 그룹웨어 [MEMBER] API 내에 존재하지 않는 직원 자동 [OUTMEMBER = 퇴사] 처리</code><br />
                        <code>@ 퇴사 처리시, 할당된 IP 자산 회수(삭제) -> [ip_history] 내에 저장, 상세 페이지 내 확인 가능</code><br />
                        <code>@ [New] : 신규 입사자, (7일 이후 자동 표식 제거) </code>
                    </div>

                        <table id="data-table" class="table table-sm table-striped table-bordered table-hover" style="width:100%">
                            <thead class="bg-warning-200">
                                <tr>
                                    <th class="width-10" data-name="pp_id" data-type="text" data-op="eq">ID</th>
                                    <th data-name="pp_name" data-type="text" data-op="eq">Name</th>
                                    <th data-name="pp_title" data-type="text" data-op="cn">Title</th>
                                    <th data-name="pp_login_id" data-type="text" data-op="eq">Login ID</th>
                                    <th data-name="pp_emp_number" data-type="text" data-op="eq">EMP No.</th>
                                    <th data-name="pp_email" data-type="text" data-op="eq">Email</th>
                                    <th data-name="pp_dept" data-type="text" data-op="cn">Department</th>
                                    <th data-name="pp_company_id" data-type="select" data-value="<?=$company_data?>" data-op="eq">Company</th>
                                    <th data-name="pp_status" data-type="select" data-value="<?=$status_data?>" data-op="eq">Status</th>
                                    <th data-name="pp_ips" data-type="none" >IP</th>
                                    <th data-name="pp_admin_id" data-type="select" data-value="<?=$is_admin_data?>" data-op="eq">Is Admin</th>
                                    <th data-name="pp_created_at" data-type="range" data-datepicker="use" data-op="bt">Created At</th>
                                    <th data-name="pp_updated_at" data-type="range" data-datepicker="use" data-op="bt">Updated At</th>
                                    <th data-name="pp_outed_at" data-type="range" data-datepicker="use" data-op="bt">Outed At</th>
                                    <th data-name="pp_info_chpasswd" data-type="range" data-datepicker="use" data-op="bt">PW Changed At</th>
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
            "url": "/<?=SHOP_INFO_ADMIN_DIR?>/people/employee",
            "type": "POST",
            "dataType": "JSON",
            "data": {
                "mode": "list",
            }
        },
        "createdrow": function(row, data, dataindex){
            $('td', row).css('min-width', '70px');
        },
        "columns": [
            {"data": "pp_id"},
            {"data": "pp_name"},
            {"data": "pp_title"},
            {"data": "pp_login_id"},
            {"data": "pp_emp_number"},
            {"data": "pp_email"},
            {"data": "pp_dept"},
            {"data": "pp_company_id"},
            {"data": "pp_status"},
            {"data": "pp_ips"},
            {"data": "pp_admin_id"},
            {"data": "pp_created_at"},
            {"data": "pp_updated_at"},
            {"data": "pp_outed_at"},
            {"data": "pp_info_chpasswd"},
            {
                "data": null,
                "className": "text-center",
                "defaultContent": ''
            }
        ],
        // Define columns class 
        "columnDefs": [
            { "className": "text-center fs-nano", "targets": "_all"},
            {
                responsivePriority: 1,
                targets: -1,
                title: '',
                orderable: false,
                render: function(data, type, full, meta) {

                    var btn_act = ''; 
                    var base_url = '/<?=SHOP_INFO_ADMIN_DIR?>/people/employee_detail';

                    // Edit/Detail 
                    btn_act += dtEditButton(base_url+'/'+data.pp_id);

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
