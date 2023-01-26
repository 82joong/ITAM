<?php 
//$page_css[] = "";

//include left panel (navigation)
//follow the tree in inc/config.ui.php
require_once realpath(dirname(__FILE__).'/../').'/inc/config.ui.php';


$mode = 'list';
$active_key = array(
    "assets",                       // 1 Depth menu
    "ips",                          // 2(Sub) Depth menu
    strtolower($row['ipc_type'])    // 3(Sub) Depth menu
);
$page_nav[$active_key[0]]["active"] = true;
$page_nav[$active_key[0]]["sub"][$active_key[1]]["active"] = true;
$page_nav[$active_key[0]]["sub"][$active_key[1]]["sub"][$active_key[2]]["active"] = true;

include realpath(dirname(__FILE__).'/../').'/inc/nav.php';

$title_symbol = 'fa-ethernet';
?>

<style type="text/css">
.select2-dropdown {  
    z-index: 10060 !important;/*1051;*/
}
</style>


<main id="js-page-content" role="main" class="page-content">

    <?php include realpath(dirname(__FILE__).'/../').'/inc/breadcrumbs.php'; ?>

    <div class="row">
        <div class="col-xl-12">
            <div class="panel bg-info-500 pattern-0 shadow-3">
                 <div class="panel-container show">
                    <div class="panel-content">
                        <div class="form-group">
                            <h3>
                                <span class="badge badge-success shadow ">
                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                    <?=$row['ipc_location_id']?> 
                                </span>


                                <span class="badge badge-primary shadow ">
                                    <i class="fas fa-hashtag mr-1"></i>
                                    <?=$row['ipc_name']?> 
                                </span>


                            </h3>
                            <?=$select_cidr?>
                        </div>
                    </div>
                <div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-xl-12">
            <div class="panel">
                <div class="panel-hdr">
                    <h2>Table</h2>
                   
                    <div class="panel-toolbar">
                        <a href="/<?=SHOP_INFO_ADMIN_DIR?>/system/ipclass/<?=strtolower($row['ipc_type'])?>" class="btn btn-sm btn-danger waves-effect waves-themed">
                            <span class="fas fa-th-list mr-1"></span> CIDR List 
                        </a>
                        <?php /*?>
                        <a href="/<?=SHOP_INFO_ADMIN_DIR?>/people/ip_detail" class="btn btn-sm btn-success waves-effect waves-themed">
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
                                    <th data-name="ip_address" data-type="text" data-op="eq">IP Address</th>
                                    <th data-name="remote_ip" data-type="none">Remote IP</th>
                                    <th data-name="ip_allocation_type" data-type="select" data-value="<?=$type_data?>" data-op="eq">Allocation Type</th>
                                    <th data-name="ip_allocation_name" data-type="text" data-op="eq">Allocated Name</th>
                                    <th data-name="ip_memo" data-type="text" data-op="cn">Memo</th>
                                    <th data-name="am_rack_code" data-type="text" data-op="cn">Rack Code</th>
                                    <th style="width:48px;"></th>
                                </tr>
                            </thead>
                            <tfoot class="bg-warning-200">
                                <tr>
                                    <th>IP Address</th>
                                    <th>Remote IP</th>
                                    <th>Allocation_type</th>
                                    <th>Allocated Name</th>
                                    <th>Memo</th>
                                    <th>Rack Code</th>
                                    <th></th>
                                </tr>
                            </tfoot>
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
                <p>Modal body text goes here.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="btn-save">Save</button>
            </div>
        </div>
    </div>
</div>


<!-- this overlay is activated only when mobile menu is triggered -->
<div class="page-content-overlay" data-action="toggle" data-class="mobile-nav-on"></div> 
<!-- END Page Content -->

<script type="text/javascript">



$(document).ready(function() {

    $('[name="ipc_id"]').on('select2:select', function(e) {
        location.href = '/<?=SHOP_INFO_ADMIN_DIR?>/system/iplist/' + $(this).val();
    });


    // Setup - add a text input to each footer cell
    $('#data-table thead tr').clone(true).appendTo('#data-table thead');

    var table = $('#data-table').DataTable({
        "paging"        : false,
        "orderCellsTop" : true, 
        "fixedHeader"   : true, 
        "processing"    : true,
        "serverSide"    : true,
        "searching"     : true,
        "responsive"    : true,
        "select"        : "single",
        "order"         : [[0, "desc"]],    // Default Sorting
        "ajax": {
            "url": "/<?=SHOP_INFO_ADMIN_DIR?>/system/iplist/<?=$id?>",
            "type": "POST",
            "dataType": "JSON",
            "data": {
                "mode": "list",
            }
        },
        "columns": [
            {"data": "ip_address"},
            {"data": "remote_ip"},
            {"data": "ip_allocation_type"},
            {"data": "ip_allocation_name"},
            {"data": "ip_memo"},
            {"data": "am_rack_code"},
            {"data": null},
        ],
        // Define columns class 
        "columnDefs": [
            { "className": "fs-xs text-center", "targets": '_all' },
            {
                targets: -1,
                title: 'Controls',
                orderable: false,
                render: function(data, type, full, meta) {
                    var btn_act = ''; 
                    var base_url = 'javascript:editRow(\''+data.ip_id+'\',\''+data.ip_address+'\');';

                    // Edit/Detail 
                    btn_act += dtEditButton(base_url);
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
        }
    });


    // Generate Search Filter 
    generatorColFilter('data-table', table);


    $('#btn-save').click(function(e) {
        e.preventDefault();

        if( ! $('[name="ip_allocation_type"]').val() ) {
            toggleValid($('[name="ip_allocation_type"]'), 'invalid', 'IP 할당 대상 유형을 선택해주세요!');
            return;
        }

        var ip_type = $('[name="ip_allocation_type"]').val();
        switch(ip_type) {
            case 'PEOPLE':
                if( ! $('[name="ip_people_id"]').val() ) {
                    toggleValid($('[name="ip_people_id"]'), 'invalid', '<?=getAlertMsg('REQUIRED_VALUES')?>');
                    return;
                }
                var opt_data = $('[name="ip_people_id"]').select2('data');
                $('[name="ip_allocation_name"]').val(opt_data[0].text);
                break;
            case 'ASSETS':
                if( ! $('[name="ip_assets_model_id"]').val() ) {
                    toggleValid($('[name="ip_assets_model_id"]'), 'invalid', '<?=getAlertMsg('REQUIRED_VALUES')?>');
                    return;
                }
                var opt_data = $('[name="ip_assets_model_id"]').select2('data');
                $('[name="ip_allocation_name"]').val(opt_data[0].am_name);
                break;
            case 'ETC':
                if( ! $('[name="ip_allocation_etc"]').val() ) {
                    toggleValid($('[name="ip_allocation_etc"]'), 'invalid', '<?=getAlertMsg('REQUIRED_VALUES')?>');
                    return;
                }
                $('[name="ip_allocation_name"]').val($('[name="ip_allocation_etc"]').val());
                break;
        }

        var params = $('#modal-edit').find('form').serialize();
        params += '&request=ajax';
        $.post('/<?=SHOP_INFO_ADMIN_DIR?>/people/ip_process', params, function(res) {
            if(res.is_success) {
                // AND CLOSE
                $('#modal-edit').modal('hide');
                table.clear().draw();
            } else {
                Swal.fire("Submit Error !", res.msg, "error");
            }
        },'json');
    });
});



function editRow(id, ip) {
    var params = {'ip_id':id, 'ip': ip, 'class_type': '<?=strtolower($row['ipc_type'])?>'};
    $.post('/<?=SHOP_INFO_ADMIN_DIR?>/system/ajax_ip_template', params, function(res) {
        if(res.is_success) {
            $('#modal-edit').find('.modal-body').html(res.msg);
            $('#modal-edit').modal('show');
        } else {
             
        }
    },'json');
}
</script>
