<?php 
//$page_css[] = "";

//include left panel (navigation)
//follow the tree in inc/config.ui.php
require_once realpath(dirname(__FILE__).'/../').'/inc/config.ui.php';


$mode = 'list';
$active_key = array(
    "system",     // 1 Depth menu
    "history"          // 2(Sub) Depth menu
);
$page_nav[$active_key[0]]["active"] = true;
$page_nav[$active_key[0]]["sub"][$active_key[1]]["active"] = true;
include realpath(dirname(__FILE__).'/../').'/inc/nav.php';


$title_symbol = 'fa-cog';
?>

<style type="text/css">
.tbl-log tr td {word-break: break-all;}
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
                                    <th data-name="h_id" data-type="text" data-op="eq" >ID</th>
                                    <th data-name="h_loginid" data-type="text" data-op="cn" >Admin ID</th>
                                    <th data-name="h_name" data-type="text" data-op="cn" >Admin Name</th>
                                    <th data-name="h_ip" data-type="text" data-op="cn" >Access IP</th>
                                    <th data-name="h_act_table" data-type="text" data-op="cn" >Access Table</th>
                                    <th data-name="h_act_mode" data-type="text" data-op="cn" >Action</th>
                                    <th data-name="h_act_key" data-type="text" data-op="eq" >Access Key</th>
                                    <th data-name="h_created_at" data-type="range" data-op="bt"  data-datepicker="use">Created at</th>
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

<div class="modal fade" id="history_log_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">History Data</h2>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            <div class="modal-body clearfix" style="overflow-y:auto;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="<?=$assets_dir?>/js/colfilter.datatable.js"></script>
<script type="text/javascript">

$(document).ready(function() {

    // Setup - add a text input to each footer cell
    $('#data-table thead tr').clone(true).appendTo('#data-table thead');

    var table = $('#data-table').DataTable({
        "pageLength"    : 50,               // Paging Unit
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
            "url": "/<?=SHOP_INFO_ADMIN_DIR?>/system/history",
            "type": "POST",
            "dataType": "JSON",
            "data": {
                "mode": "list",
            }
        },
        "columns": [
            {"data": "h_id"},
            {"data": "h_loginid"},
            {"data": "h_name"},
            {"data": "h_ip"},
            {"data": "h_act_table"},
            {"data": "h_act_mode"},
            {"data": "h_act_key"},
            {"data": "h_created_at"},
        ],
        // Define columns class 
        "columnDefs": [
            { "className": "text-center fs-nano", "targets": [0,1,2,3,4,5,6,7] },
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

    $('#data-table tbody').on('click', 'tr', function() {
        var _id = table.row(this).data().h_id;
        $.post('/<?=SHOP_INFO_ADMIN_DIR?>/system/ajax_get_history', {'id': _id}, function(res) {
            if(res.is_success) {
                $('#history_log_modal .modal-body').html(res.msg);
                $('#history_log_modal').modal('show');
            } else {
                Swal.fire("Submit Error !", res.msg, "warning"); 
            }
        },'json');
    });

});

function _obj_print(obj) {
    if(typeof obj === 'object') {
        var output = '';
        for (var property in obj) {
            if(obj[property].length > 0) {
                output += '['+property+']'+' => '+obj[property]+'<BR> ';
            }
        }
        return output;
    }else {
        return obj;
    }
}
</script>
