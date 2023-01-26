<?php 
//$page_css[] = "";

//include left panel (navigation)
//follow the tree in inc/config.ui.php
require_once realpath(dirname(__FILE__).'/../').'/inc/config.ui.php';


$mode = 'list';
$active_key = array(
    "syslog",               // 1 Depth menu
    "ssh",               // 2(Sub) Depth menu
);
$page_nav[$active_key[0]]["active"] = true;
$page_nav[$active_key[0]]["sub"][$active_key[1]]['active'] = true;

include realpath(dirname(__FILE__).'/../').'/inc/nav.php';
$title_symbol = 'fa-server';
?>

<style type="text/css">
.bg-command {background:#2d2d2d;}
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
                        SSH Server
                    </h2>


                    <div class="panel-toolbar">

                        <span id="interval_time" class="badge badge-pill badge-danger fw-400 l-h-n mr-2">
                            Interval 60s
                        </span>

                        <div class="custom-control d-flex custom-switch">
                            <input id="timer-switch" type="checkbox" class="custom-control-input" checked="checked">
                            <label class="custom-control-label fw-500" for="timer-switch"></label>
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
                                    <th data-name="type_keyword" data-type="select" data-value="<?=$type_map?>" data-op="eq">Tag Type</th>
                                    <th data-name="user" data-type="text" data-op="cn">Host</th>
                                    <th data-name="name" data-type="none">Name</th>
                                    <th data-name="fromhost" data-type="text" data-op="eq">Host IP</th>
                                    <th data-name="userip" data-type="text" data-op="cn">User IP</th>
                                    <th data-name="syslogtag" data-type="text" data-op="cn">Syslog Tag</th>
                                    <th data-name="command" data-type="text" data-op="cn" style="background:#ffdb8e !important;" data-priority="1">Command</th>
                                    <!--th data-name="message" data-type="text" data-op="cn" data-priority="3">Message</th-->
                                    <th data-name="regdate" data-type="range" data-datepicker="use" data-op="bt" data-priority="2">Created At</th>
                                    <!--th data-name="@timestamp" data-type="range" data-datepicker="use" data-op="bt">@Timestamp</th-->


                                    <th data-name="track_code" data-type="text" data-op="eq" data-priority="1">Track</th>
                                    <th data-name="memo" data-type="text" data-op="cn" data-priority="1">Memo</th>
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
        "order"         : [[8, "desc"]],    // Default Sorting
        "ajax": {
            "url": "/<?=SHOP_INFO_ADMIN_DIR?>/syslog/ssh",
            "type": "POST",
            "dataType": "JSON",
            "data": {
                "mode": "list",
            }
        },
        "createdRow": function(row, data, dataIndex){
            $('td:eq(3)', row).css('min-width', '100px');
        },
        "columns": [
            {
                "data": "id",
                "className": "width-10 text-center fs-nano",
                /*"visible": false,*/
            },
            {"data": "type_keyword"},
            {"data": "user"},
            {"data": "name"},
            {"data": "fromhost"},
            {"data": "userip"},
            {
                "data": "syslogtag",
                "className": "width-xs text-left fs-nano",
            },
            {
                "data": "command",
                "className": "text-left fs-nano bg-command",
            },
            /*
            {
                "data": "message",
                "className": "text-left fs-nano",
            },
            */
            {
                "data": "regdate",
                "className": "width-xs text-center fs-nano"
            },
            {
                "data": "track_code",
                "className": "width-xs text-center fs-nano"
            },
            {
                "data": "memo",
                "className": "width-xs text-center fs-nano"
            },
            /*
            {
                "data": "@timestamp",
                "className": "width-xs text-center fs-nano"
            },
            */
        ],
        // Define columns class 
        "columnDefs": [
            { "className": "text-center fs-nano", "targets": "_all" },
            {
                "targets": -1,
                "title": 'Controls',
                "orderable": false,
                "className": 'text-center fs-xs p-0',
                "render": function(data, type, full, meta) {
                    var btn_act = ''; 

                    // Edit/Detail 
                    var base_url = 'javascript:editRow(\''+data.id+'\');';
                    btn_act += dtEditButton(base_url);

                    // Delete 
                    var base_url = 'javascript:delRow(\''+data.id+'\');';
                    btn_act += dtDeleteButton(base_url);
                    return btn_act;
                }
            }

        ],
        "dom": dtDoms,
        "buttons": dtButtons,
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

    $('#timer-switch').change(function() {

        if(this.checked) {
            //console.log('checked');
            start_timer(60000);
            $('#interval_time').html('Interval 60s');
        }else {
            //console.log('not checked');
            clearInterval(timer);
            $('#interval_time').html('Stopped Interval');
        }
    });

    function start_timer(time) {
        //console.log('start timer : ' + time);
        timer = setInterval( function () {
            table.ajax.reload();
        }, time);
    }
    function stop_timer() {
        //console.log('stop timer');
        clearInterval(timer);
    }
    $("#set-interval").on("input change", function() {
        //console.log('set interval : ')
        //console.log($(this).val());

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
});
</script>
