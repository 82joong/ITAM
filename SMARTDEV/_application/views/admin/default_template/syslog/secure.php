<?php 
//$page_css[] = "";

//include left panel (navigation)
//follow the tree in inc/config.ui.php
require_once realpath(dirname(__FILE__).'/../').'/inc/config.ui.php';


$mode = 'list';
$active_key = array(
    "syslog",               // 1 Depth menu
    "server",               // 2(Sub) Depth menu
    "secure",               // 3(Sub) Depth menu
);
$page_nav[$active_key[0]]["active"] = true;
$page_nav[$active_key[0]]["sub"][$active_key[1]]['active'] = true;
$page_nav[$active_key[0]]["sub"][$active_key[1]]['sub'] [$active_key[2]]['active']= true;

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
                    <h2>
                        <span class="icon-stack fs-xxl mr-2">
                            <i class="base base-7 icon-stack-3x opacity-100 color-primary-500"></i>
                            <i class="base base-7 icon-stack-2x opacity-100 color-primary-300"></i>
                            <i class="fal fa-cog icon-stack-1x opacity-100 color-white" id="ico-spin"></i>
                        </span>
                        SECURE 
                    </h2>

                    <div class="panel-toolbar">

                        <span id="interval_time" class="badge badge-pill badge-danger fw-400 l-h-n mr-2">
                            60s
                        </span>

                        <div class="custom-control d-flex custom-switch">
                            <input id="eventlog-switch" type="checkbox" class="custom-control-input" checked="checked">
                            <label class="custom-control-label fw-500" for="eventlog-switch"></label>
                        </div>


                        <div class="btn btn-sm btn-info waves-effect waves-themed ml-1 text-white" id="btn_refresh" >
                            <i class="fas fa-spinner fa-spin mr-1"></i>Refresh Data
                        </div>

                        <?=genFullButton();?>
                    </div>
                </div>

                <div class="panel-container show">
                    <div class="panel-content">
                        
                        <table id="data-table" class="table table-sm table-striped table-bordered table-hover" style="width:100%">
                            <thead class="bg-warning-200">
                                <tr>
                                    <th data-name="id" data-type="text" data-op="eq">ID</th> 
                                    <th data-name="facility" data-type="select" data-value="<?=$facility_map?>" data-op="eq">Facility</th>
                                    <th data-name="priority" data-type="select" data-value="<?=$severity_map?>" data-op="eq">Severity</th>
                                    <th data-name="fromhost" data-type="text" data-op="eq">Host</th>
                                    <th data-name="hostname" data-type="text" data-op="cn">HostName</th>
                                    <th data-name="eventmsg" data-type="text" data-op="cn">EventMsg</th>
                                    <th data-name="message" data-type="text" data-op="cn">Message</th>
                                    <th data-name="devicereportedtime" data-type="range" data-datepicker="use" data-op="bt">Device ReportedAt</th>
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

var timer;
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
            "url": "/<?=SHOP_INFO_ADMIN_DIR?>/syslog/secure",
            "type": "POST",
            "dataType": "JSON",
            "data": {
                "mode": "list",
            }
        },
        "columns": [
            {
                "data": "id",
                "className": "width-10 text-center fs-nano",
            },
            {
                "data": "facility",
                "className": "width-5 text-center fs-nano"
            },
            {
                "data": "priority",
                "className": "width-5 text-center fs-nano"
            },
            {"data": "fromhost"},
            {"data": "hostname"},
            {"data": "eventmsg"},
            {
                "data": "message",
                "className": "text-left fs-nano",
            },
            {
                "data": "devicereportedtime",
                "className": "width-xs text-center fs-nano"
            },
            {
                "data": "@timestamp",
                "className": "width-xs text-center fs-nano"
            },
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


    start_timer(60000);

    function start_timer(time) {
        console.log('start timer : ' + time);
        timer = setInterval( function () {
            table.ajax.reload();
        }, time);
    }
    function stop_timer() {
        console.log('stop timer');
        clearInterval(timer);
    }
    $("#set-interval").on("input change", function() {
        console.log('set interval : ')
        console.log($(this).val());

        var intv = ($(this).val() * 1) / 1000;
        var intv_text = '';

        stop_timer();
        if(intv == 0) {
            intv_text = 'stop';
        }else {
            intv_text = intv+'s';
            interval_time = intv;
            //start_timer(intv);
        }
        $('#interval_time').html(intv_text);
    });

    $('#btn_refresh').click(function() {
        table.ajax.reload();
    });
});
</script>
