<?php 
//$page_css[] = "";

//include left panel (navigation)
//follow the tree in inc/config.ui.php
require_once realpath(dirname(__FILE__).'/../').'/inc/config.ui.php';


$active_key = array(
    "logs",               // 1 Depth menu
    "menu",             // 2(Sub) Depth menu
);
$page_nav[$active_key[0]]["active"] = true;
$page_nav[$active_key[0]]["sub"][$active_key[1]]['active'] = true;

include realpath(dirname(__FILE__).'/../').'/inc/nav.php';
$title_symbol = 'fa-server';



$subheader_contents = '<div class="subheader-block d-lg-flex align-items-center">';
$subheader_contents .= '<div class="d-inline-flex flex-column justify-content-center mr-3 text-right">';
$subheader_contents .= '<span class="fw-300 fs-xs d-block opacity-50"><small>Host 개수(24시간 기준)</small></span>';
$subheader_contents .= '<span class="fw-500 fs-xl d-block color-primary-500" id="sbh_count_host">0</span>';
$subheader_contents .= '</div>';
$subheader_contents .= '</div>';


?>

<style type="text/css">
.badge-ip {color: #fff; background-color: dimgray;}       /* fusion-300  */
.badge-sslvpn {color: #fff; background-color: #ffd274;}     /* warning-300 */
.badge-gateway {color: #fff; background-color: #886ab5;}        /* primary-500 */

.fs-break {
    min-width: 200px;
    overflow: hidden;
    word-break: break-all;
    text-overflow: ellipsis;
}
</style>


<main id="js-page-content" role="main" class="page-content">
    <?php include realpath(dirname(__FILE__).'/../').'/inc/breadcrumbs.php'; ?>

    <div class="row">
        <div class="col-xl-12">
            <div class="panel">
                <div class="panel-hdr">
                    <h2>Server Monitor</h2>
                   
                    <div class="panel-toolbar">
                        <?=genFullButton();?>
                    </div>
                </div>

                <div class="panel-container show">
                    <div class="panel-content">
                    
                        <?php /*?>
                        <div class="alert alert-info" role="alert">
                            <div class="fs-nano text-dark">

                                <span class="text-info fs-md">
                                    <i class="fal fa-info-circle mr-2 fw-900 "></i>
                                </span>

                                <span class="mr-2"><span class="fw-900">CPU</span> 사용률 <code><?=CPU_MAX?></code> 초과,</span>
                                <span class="mr-2"><span class="fw-900">SWAP</span> 사용률 <code><?=SWAP_MAX?></code> 초과,</span>
                                <span class="mr-2"><span class="fw-900">MEM</span> 사용률 <code><?=MEM_MAX?></code> 초과,</span>
                                <span class="mr-2"><span class="fw-900">DISK</span> 사용률 <code><?=DISK_MAX?></code> 초과,</span>
                                <span class="mr-2"><span class="fw-900">Default PORT</span> <code>(<?=implode(',', PORT_MAX)?>)</code> 제외</span>
                                <span class="text-danger fw-900">ALERT(red)</span> 처리
                            </div>


                            <div class="fs-nano text-dark mt-2">
                                <span class="text-danger fw-900">Alert</span> Field를 통해 각각 초과되는 항목에 대해 검색 가능합니다.
                            </div>

                        </div>
                        <?php */?>

                        <table id="data-table" class="table table-sm table-striped table-bordered table-hover" style="width:100%">
                            <thead class="bg-warning-200">
                                <tr>
                                    <th data-name="date_timestamp" data-type="range" data-datepicker="use" data-op="bt" style="min-width:110px;">DATE</th>
                                    <th data-name="ip" data-type="text" data-op="cn" style="min-width:80px;">IP</th>
                                    <th data-name="login_id" data-type="text" data-op="cn" style="min-width:60px;">LOGINID</th>
                                    <th data-name="login_hname" data-type="text" data-op="cn" style="min-width:60px;">NAME</th>
                                    <th data-name="page" data-type="text" data-op="cn" style="min-width:220px;">ACTION PAGE</th>
                                    <th data-name="content" data-type="text" data-op="cn">TXT</th>

                                    <th data-name="auth_tag" data-type="text" data-op="cn" style="min-width:110px;">AUTH TAG</th>
                                    <th data-name="auth_site" data-type="text" data-op="cn" style="min-width:280px;">AUTH URL</th>
                                    <th data-name="auth_add" data-type="text" data-op="cn" style="min-width:150px;">ADD[추가]</th>
                                    <th data-name="auth_del" data-type="text" data-op="cn" style="min-width:150px;">DEL[삭제]</th>
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
        "pageLength"    : 100,               // Paging Unit
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
            "url": "/<?=SHOP_INFO_ADMIN_DIR?>/logs/manage",
            "type": "POST",
            "dataType": "JSON",
            "data": {
                "mode": "list",
            }
        },
        "columns": [
            {"data": "date_timestamp", "className": "text-center fs-nano"},
            {"data": "ip"},
            {"data": "login_id"},
            {"data": "login_hname"},
            {"data": "page", "className": "text-left fs-nano fs-break"},
            {"data": "content", "className": "text-left fs-nano fs-break"},

            {"data": "auth_tag", "className": "text-center fs-nano"},
            {"data": "auth_site", "className": "text-left fs-nano fs-break"},
            {"data": "auth_add", "className": "text-left fs-nano"},
            {"data": "auth_del", "className": "text-left fs-nano"},
        ],
        // Define columns class 
        "columnDefs": [
            { "className": "text-center fs-nano", "targets": "_all" },
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
