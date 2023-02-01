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
                                    <th data-name="doc_id" data-type="text" data-op="eq">ID</th> 
                                    <th data-name="type_keyword" data-type="select" data-value="<?=$type_map?>" data-op="eq">Tag Type</th>
                                    <th data-name="user" data-type="text" data-op="cn">Host</th>
                                    <th data-name="name" data-type="none">Name</th>
                                    <th data-name="fromhost" data-type="text" data-op="eq">Host IP</th>
                                    <th data-name="userip" data-type="text" data-op="cn">User IP</th>
                                    <!--th data-name="syslogtag" data-type="text" data-op="cn">Syslog Tag</th-->
                                    <th data-name="command" data-type="text" data-op="cn" style="background:#ffdb8e !important;" data-priority="1">Command</th>
                                    <!--th data-name="message" data-type="text" data-op="cn" data-priority="3">Message</th-->
                                    <th data-name="regdate" data-type="range" data-datepicker="use" data-op="bt" data-priority="2">Created At</th>


                                    <th data-name="track_code" data-type="text" data-op="eq" data-priority="1">Track Code</th>
                                    <th data-name="memo" data-type="text" data-op="cn" data-priority="3">Memo</th>
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


<div class="modal" tabindex="-1" role="dialog" id="modal-edit">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fal fa-comment-alt-edit mr-1"></i>
                    Modal Edit or Add
                </h5>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">

                <form name='stm_edit_form' id="stm_edit_form" class="needs-validation" method="POST" action="/<?=SHOP_INFO_ADMIN_DIR?>/Syslog/stm_process"  
                    <input type="hidden" name="mode" value="insert">
                    <input type="hidden" name="stm_id" value="">
                    <input type="hidden" name="stm_ssh_id" value="">
                    
                    <div class="col-xl-12 col-md-12">
                        <div class="panel">
                            <div class="panel-container show">
                                <div class="panel-content">
                                    <div class="form-row form-group justify-content-md-center">

                                        <div class="row col-12 mb-1">
                                            <div class="form-group col-4 pl-0">
                                                <label class="form-label" for="hostname">
                                                    Host Name 
                                                </label>
                                                <?=getInputMask('normal', 'hostname', '', 'readonly')?>
                                            </div>
                                            <div class="form-group col-4">
                                                <label class="form-label" for="hostip">
                                                    Host IP 
                                                </label>
                                                <?=getInputMask('normal', 'hostip', '', 'readonly')?>
                                            </div>
                                            <div class="form-group col-4 pr-0">
                                                <label class="form-label" for="userip">
                                                    User IP 
                                                </label>
                                                <?=getInputMask('normal', 'userip', '', 'readonly')?>
                                            </div>
                                        </div>
                                        <div class="row col-12 mb-3">
                                            <div class="form-group col-12 pl-0">
                                                <label class="form-label" for="command">
                                                    Command  
                                                </label>
                                                <div class="text-left fs-nano bg-command p-2">
                                                    <i class="fal fa-terminal mr-1 text-white"></i>
                                                    <code class="dark" id="box_cmd"></code>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row col-12 mb-3">
                                            <div class="form-group col-12 pl-0">
                                                <label class="form-label" for="stm_track_code">
                                                    Track Code 
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <?=getInputMask('normal', 'stm_track_code', '', 'required')?>
                                            </div>
                                        </div>

                                        <div class="row col-12 mb-3">
                                            <div class="form-group col-12 pl-0">
                                                <label class="form-label" for="stm_memo">
                                                    Memo 
                                                </label>
                                                <?=getTextArea('stm_memo')?>
                                            </div>
                                        </div>

                                    </div>
                                </div> 
                            </div>
                        </div>
                    </div>

                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="btn-stm-save">Save</button>
            </div>
        </div>
    </div>
</div>




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
        "order"         : [[7, "desc"]],    // Default Sorting
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
                "data": "doc_id",
                "className": "width-10 text-center fs-nano",
                /*"visible": false,*/
            },
            {"data": "type_keyword"},
            {"data": "user"},
	    {
		"data": "name",
		"orderalbe": false
	    },
            {"data": "fromhost"},
            {"data": "userip"},
	    /*
            {
                "data": "syslogtag",
                "className": "width-xs text-left fs-nano",
    	    },
	    */
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
		"orderable": false,
                "className": "width-xs text-center fs-nano"
            },
            {
		"data": "memo",
		"orderable": false,
                "className": "width-xs text-center fs-nano"
            },
            {"data": null} 
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
                    var base_url = 'javascript:editRow(\''+data.doc_id+'\', \''+data.stm_id+'\');';
                    btn_act += dtEditButton(base_url);

                    // Delete
                    var base_url = 'javascript:delRow(\''+data.doc_id+'\', \''+data.stm_id+'\');';
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


    $('#btn-stm-save').on('click', function(e) {
         
        e.preventDefault();

        var _modal = $('#modal-edit');
        if( ! _modal.find('[name="stm_track_code"]').val() || ! _modal.find('[name="stm_track_code"]').val() ) {
            Swal.fire("Confirm your values !", '<?=getAlertMsg('REQUIRED_VALUES')?>', "warning");
            return;
        }

        var params = $('[name="stm_edit_form"]').serialize() 
        $.post('/<?=SHOP_INFO_ADMIN_DIR?>/syslog/stm_process', params, function(res) {
            console.log(res);
            if(res.is_success) {
                $('#modal-edit').modal('hide');
                table.clear().draw();
                return;
            }else {
                Swal.fire("Submit Error !", '<?=getAlertMsg('CONTRACT_MANAGER')?>', "error");
            }
        }, 'json');
    });
});



function editRow(doc_id, stm_id) {

    if( ! doc_id ) return;

    var params = {'doc_id':doc_id};
    $.post('/<?=SHOP_INFO_ADMIN_DIR?>/syslog/ajax_get_trackinfo', params, function(res) {
        if(res.is_success) {

            var data = res.msg;

            var _modal = $('#modal-edit').find('.modal-body');
            _modal.find('[name="hostname"]').val(data.user);
            _modal.find('[name="hostip"]').val(data.fromhost);
            _modal.find('[name="userip"]').val(data.userip);
            _modal.find('#box_cmd').html(data.command);
            _modal.find('[name="stm_ssh_id"]').val(data.doc_id);

            _modal.find('[name="mode"]').val(data.mode);
            _modal.find('[name="stm_id"]').val(data.stm_id);
            _modal.find('[name="stm_track_code"]').val(data.stm_track_code);
            _modal.find('[name="stm_memo"]').val(data.stm_memo);

            $('#modal-edit').modal('show');
        } else {
             
        }
    },'json');
}
function delRow(doc_id, stm_id) {

    if( ! stm_id ) return;

    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!"
    }).then(function(result) {
        if (result.value) {
            var params = {'doc_id':doc_id, 'stm_id':stm_id, 'mode':'delete'};
            $.post('/<?=SHOP_INFO_ADMIN_DIR?>/syslog/stm_process', params, function(res) {
                if(res.is_success) {
                    $('#data-table').DataTable().clear().draw();
                } else {
                    Swal.fire("Submit Error !", '<?=getAlertMsg('CONTRACT_MANAGER')?>', "error");
                }
            },'json');
        }
    });
}
</script>
