<?php 
//$page_css[] = "";

//include left panel (navigation)
//follow the tree in inc/config.ui.php
require_once realpath(dirname(__FILE__).'/../').'/inc/config.ui.php';


$active_key = array(
    "syslog",               // 1 Depth menu
    "monitor",              // 2(Sub) Depth menu
    "list",                 // 3(Sub) Depth menu
);
$page_nav[$active_key[0]]["active"] = true;
$page_nav[$active_key[0]]["sub"][$active_key[1]]['active'] = true;
$page_nav[$active_key[0]]["sub"][$active_key[1]]['sub'] [$active_key[2]]['active']= true;

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

                        <table id="data-table" class="table table-sm table-striped table-bordered table-hover" style="width:100%">
                            <thead class="bg-warning-200">
                                <tr>
                                    <!--th data-name="_id" data-type="text" data-op="eq">ID</th--> 
                                    <th data-name="host" data-type="text" data-op="cn">Host</th>
                                    
                                    <th data-name="alert" data-type="select" data-value="<?=$select_alert?>" data-op="eq" class="bg-danger-300">Alert</th>

                                    <th data-name="cpu_cnt" data-type="range" data-datepicker="no" data-op="bt">Core</th>
                                    <th data-name="cpu" data-type="range" data-datepicker="no" data-op="bt">CPU</th>
                                    <th data-name="mem" data-type="range" data-datepicker="no" data-op="bt">Memory</th>
                                    <th data-name="swap" data-type="range" data-datepicker="no" data-op="bt">Swap</th>

                                    <th data-name="load1" data-type="range" data-datepicker="no" data-op="bt">LoadAvg.1</th>
                                    <th data-name="load2" data-type="range" data-datepicker="no" data-op="bt">LoadAvg.5</th>
                                    <th data-name="load3" data-type="range" data-datepicker="no" data-op="bt">LoadAvg.15</th>

                                    <th data-name="disk" data-type="text" data-op="cn">Disk Space</th>
                                    <th data-name="top" data-type="text" data-op="cn">Top(5)</th>
                                    <th data-name="cpu_model" data-type="text" data-op="cn">CPU Model</th>
                                    <th data-name="mysql_version" data-type="text" data-op="cn">MySQL Ver.</th>
                                    <th data-name="mysql_size" data-type="text" date-op="cn">MySQL Size</th>

                                    <th data-name="listen_port" data-type="text" data-op="cn">Port</th>
                                    <th data-name="live" data-type="range" data-datepicker="no" data-op="bt">Up Days(Live)</th>

                                    <th data-name="regdate" data-type="range" data-datepicker="use" data-op="bt">Created At</th>
                                    <th data-name="@timestamp" data-type="range" data-datepicker="use" data-op="bt">@Timestamp</th>
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

    /*
    $('#btn-toggle-group').on('click', function() {
        $('#btn-toggle-group').toogleText('Group to Host', 'Change to default');
    });
    var btn_group = '<span id="btn-toggle-group" class="btn btn-outline-danger mb-g">';
    btn_group += 'Grouping Host';
    btn_group += '</span>';
    var add_btn = {
        'text': '최근 데이터만',
        'className': 'btn btn-outline-danger mb-g'
    };
    dtButtons.push(add_btn);
    */

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
        "order"         : [[17, "desc"]],    // Default Sorting
        "ajax": {
            "url": "/<?=SHOP_INFO_ADMIN_DIR?>/syslog/top",
            "type": "POST",
            "dataType": "JSON",
            "data": {
                "mode": "list",
            }
        },
        "columns": [
            /*
            {
                "data": "_id",
                "visible": false,
            },
            */
            {"data": "host"},
            {
                "data": "alert",
            },
            {"data": "cpu_cnt"},
            {"data": "cpu"},
            {"data": "mem"},
            {"data": "swap"},
            {"data": "load1"},
            {"data": "load2"},
            {"data": "load3"},
            {
                "data": "disk",
                "className": "text-center fs-nano width-sm p-1"
            },
            {
                "data": "top",
                "className": "text-center fs-nano width-sm p-1"
            },
            {"data": "cpu_model"},
            {"data": "mysql_version"},
            {"data": "mysql_size"},
            {"data": "listen_port"},
            {"data": "live"},
            {"data": "regdate"},
            {"data": "@timestamp"},
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


    $.post('/api/syslog/listHost', {}, function (data) {
        if(data.length > 0) {
            $('#sbh_count_host').html(data.length);
        }else {
            $('#sbh_count_host').html('False');
        }
    }, 'json'); 


});
</script>
