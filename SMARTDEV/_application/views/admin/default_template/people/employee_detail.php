<?php 
//include left panel (navigation)
//follow the tree in inc/config.ui.php
require_once realpath(dirname(__FILE__).'/../').'/inc/config.ui.php';


$active_key = array(
    "people",               // 1 Depth menu
    "employee"          // 2(Sub) Depth menu
);
$page_nav[$active_key[0]]["active"] = true;
$page_nav[$active_key[0]]["sub"][$active_key[1]]["active"] = true;
include realpath(dirname(__FILE__).'/../').'/inc/nav.php';

$title_symbol = 'fa-users';
?>



<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL -->
<style type="text/css">
</style>


<main id="js-page-content" role="main" class="page-content">

    <?php include realpath(dirname(__FILE__).'/../').'/inc/breadcrumbs.php'; ?>

    <div class="row">
        <div class="col-xl-12">
            <div class="panel">
                <div class="panel-hdr">
                    <h2>Row <?=ucfirst($mode)?></h2>
                </div>

                <div class="panel-container show">
                    <div class="panel-content">

                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <a href="#tab_profile" class="nav-link active" data-toggle="tab" role="tab" aria-selected="true">
                                    <i class="fal fa-user text-success"></i>
                                    <span class="hidden-sm-down ml-1" data-i18n="content.profile">Profile</span>
                                </a>
                            </li>

                            <?php if($mode == 'update' && $row['pp_status'] == 'ACTIVE') : ?>
                            <li class="nav-item">
                                <a href="#tab_ips" class="nav-link" data-toggle="tab" role="tab" aria-selected="false">
                                    <i class="fal fa-ethernet text-primary"></i>
                                    <span class="hidden-sm-down ml-1" data-i18n="content.ips">IPs</span>
                                </a>
                            </li>


                            <li class="nav-item">
                                <a href="#tab_authinfo" class="nav-link" data-toggle="tab" role="tab" aria-selected="false">
                                    <i class="fal fa-user-secret text-danger"></i>
                                    <span class="hidden-sm-down ml-1" data-i18n="content.authinfo">AuthInfo</span>
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>


                        <div class="tab-content border border-top-0 p-3">

                            <!-- START #tab_profile -->
                            <div class="tab-pane fade active show" id="tab_profile" role="tabpanel">

                                <form name='edit_form' id="edit_form" class="needs-validation" action="/<?=SHOP_INFO_ADMIN_DIR?>/people/employee_process" method="POST" novalidate autocomplete="off" >
                                <input type="hidden" name="mode" value="<?=$mode?>">
                                <input type="hidden" name="pp_id" value="<?=$row['pp_id']?>">


                                <div class="panel-content py-2 rounded-bottom border-faded border-top-0 border-left-0 border-right-0 text-muted d-flex mb-5">
                                    <?=genDetailButton('employee', $mode)?>                        
                                </div>

                                <div class="form-row form-group justify-content-md-center">

                                    <div class="col-12 col-lg-8 mb-3">
                                        <label class="form-label" for="pp_login_id" data-i18n="content.manager_id">
                                            Manager ID 
                                        </label>
                                        <?=$select_loginid?>
                                    </div>


                                    <div id="area-company" class="col-12 col-lg-8 mb-3">
                                        <label class="form-label" for="pp_company_id" data-i18n="content.company">
                                            Company 
                                        </label>
                                        <?=$select_company?>
                                    </div>


                                    <div id="area-supplier" class="col-12 col-lg-8 mb-3" style="display:none;">
                                        <label class="form-label" for="pp_supplier_id" data-i18n="content.supplier">
                                            Supply Company 
                                        </label>
                                        <?=$select_supplier?>
                                    </div>


                                    <div class="row col-12 col-lg-8 mb-1">
                                        <div class="form-group col-4 pl-0">
                                            <label class="form-label" for="name-f" data-i18n="content.user_name">
                                                Name
                                            </label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text text-success">
                                                        <i class="ni ni-user fs-xl"></i>
                                                    </span>
                                                </div>

                                                <input type="text" aria-label="name" class="form-control" name="pp_name" placeholder="name" id="namel" value="<?=$row['pp_name']?>">
                                            </div>
                                        </div>


                                        <div class="form-group col-4">
                                            <label class="form-label" for="pp_title" data-i18n="content.user_title">
                                                Title 
                                            </label>
                                            <?=getInputMask('normal', 'pp_title', $row['pp_title'])?>
                                        </div>


                                        <div class="form-group col-4 pr-0">
                                            <label class="form-label" for="pp_dept" data-i18n="content.department">
                                                Department (Team)
                                            </label>
                                            <?=getInputMask('normal', 'pp_dept', $row['pp_dept'])?>
                                        </div>
                                    </div>



                                    <div class="row col-12 col-lg-8 mb-1">
                                        <div class="form-group col-6 pl-0">
                                            <label class="form-label" for="pp_login_id" data-i18n="content.login_id">
                                                Login ID 
                                                <span class="text-danger">*</span>
                                            </label>
                                            <?=getInputMask('normal', 'pp_login_id', $row['pp_login_id'], 'required')?>
                                            <span class="invalid-feedback">Please provide a LoginID.</span>
                                        </div>

                                        <div class="form-group col-6 pr-0">
                                            <label class="form-label" for="pp_email" data-i18n="content.email">
                                                Email 
                                                <span class="text-danger">*</span>
                                            </label>
                                            <?=getInputMask('email', 'pp_email', $row['pp_email'], 'required')?>
                                            <span class="invalid-feedback">Please provide a Email.</span>
                                        </div>
                                    </div>


                                    <div class="row col-12 col-lg-8 mb-1">
                                        <div class="form-group col-6 pl-0">
                                            <label class="form-label" for="pp_tel" data-i18n="content.tel">
                                                Tel. 
                                            </label>
                                            <?=getInputMask('tel', 'pp_tel', $row['pp_tel'])?>
                                        </div>

                                        <div class="form-group col-6 pr-0">
                                            <label class="form-label" for="pp_mobile" data-i18n="content.mobile">
                                                 Mobile 
                                            </label>
                                            <?=getInputMask('tel', 'pp_mobile', $row['pp_mobile'])?>
                                        </div>
                                    </div>



                                    <div class="row col-12 col-lg-8 mb-1">
                                        <div class="form-group col-6 pl-0">
                                            <label class="form-label" for="pp_status" data-i18n="content.status">
                                                Status    
                                            </label>
                                            <?=$select_status?>
                                        </div>

                                        <div class="form-group col-6 pr-0">
                                            <label class="form-label" for="pp_emp_number" data-i18n="content.emp_number">
                                                EMP No. 
                                            </label>
                                            <?=getInputMask('normal', 'pp_emp_number', $row['pp_emp_number'])?>
                                        </div>
                                    </div>



                                    <?php if($mode == 'update') : ?>

                                    <?php if($row['pp_status'] == 'OUTMEMBER') : ?>
                                    <div class="col-12 col-lg-8 mb-3">
                                        <label class="form-label" data-i18n="outed_at">
                                            Outed At 
                                        </label>
                                        <input type="text" class="form-control" name="pp_outed_at" value="<?=$row['pp_outed_at']?>" disabled >
                                    </div>

                                    <?php if( sizeof($row['pp_ip_history']) > 0 ): ?>
                                    <div class="col-12 col-lg-8 mb-3">
                                        <div class="card border">
                                            <div class="card-header bg-warning-50">
                                                <label class="form-label">퇴사시, 회수된 IP 주소 정보 </label>
                                            </div>
                                            <ul class="list-group list-group-flush">
                                                <?php foreach($row['pp_ip_history'] as $value): ?>
                                                <li class="list-group-item">
                                                    <span class="badge badge-primary"><?=$value['ip_address']?></span>
                                                    <code class="ml-1"><?=$value['ip_class_type']?></code>
                                                    <code class="ml-1"><?=$value['ip_class_category']?></code>
                                                    <span class="fs-nano ml-1"><?=$value['ip_memo']?></span>
                                                </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                    <?php endif; // END_IF OUTMEMBER  ?>

                                    

                                    <div class="col-12 col-lg-8 mb-3">
                                        <label class="form-label" data-i18n="updated_at">
                                            Updated At 
                                        </label>
                                        <input type="text" class="form-control" name="pp_updated_at" value="<?=$row['pp_updated_at']?>" disabled >
                                    </div>
                                    
                                    <div class="col-12 col-lg-8 mb-3">
                                        <label class="form-label" data-i18n="created_at">
                                            Created At 
                                        </label>
                                        <input type="text" class="form-control" name="pp_created_at" value="<?=$row['pp_created_at']?>" disabled >
                                    </div>


                                    <?php endif; // END_IF update?>

                                </div> <!-- END .form-row -->


                                <div class="panel-content py-2 rounded-bottom border-faded border-left-0 border-right-0 border-bottom-0 text-muted d-flex">
                                    <?=genDetailButton('employee', $mode)?>                        
                                </div>

                                </form>



                            </div> <!-- END #tab_profile -->



                            <!-- START #tab_ips --> 
                            <div class="tab-pane fade" id="tab_ips" role="tabpanel">
                                <form id="area-spec" class="needs-validation" >
                                <input type="hidden" name="ip_id" value="<?=$row['ip_id']?>">
                                <input type="hidden" name="ip_class_id" value="<?=$row['ip_class_id']?>">
                                <input type="hidden" name="ip_class_type" value="<?=$row['ip_class_type']?>">
                                <input type="hidden" name="ip_class_category" value="<?=$row['ip_class_category']?>">
                                <input type="hidden" name="pim_people_id" value="<?=$row['pp_id']?>">
                                <input type="hidden" name="mode" value="insert">
                                <input type="hidden" name="request" value="ajax">
                                <input type="hidden" name="set_valid" value="people">

                                <div class="form-row form-group justify-content-md-center">
                                    
                                    <div class="form-group col-12 mb-1">
                                        <label class="form-label" for="ip_address">
                                            IPv4 Address 
                                            <span class="text-danger">*</span>
                                        </label>
                                        <?=getInputMask('ipv4', 'ip_address', $set_value='', $opt='required')?>
                                        <span class="invalid-feedback">Please provide a IP Address.</span>
                                    </div>


                                    <div class="row col-12 mb-3">
                                        <div class="form-group col-4 pl-0">
                                            <label class="form-label" for="ipc_location_id" data-id="content.location">
                                                Location 
                                            </label>
                                            <?=getInputMask('normal', 'ipc_location_id', $row['ipc_location_id'], 'readonly')?>
                                        </div>

                                        <div class="form-group col-4 pr-0">
                                            <label class="form-label" for="ipc_cidr">
                                                CIDR 
                                            </label>
                                            <?=getInputMask('normal', 'ipc_cidr', $row['ipc_cidr'], 'readonly')?>
                                        </div>

                                        <div class="form-group col-4 pr-0">
                                            <label class="form-label" for="ipc_name">
                                                IP Class Name 
                                            </label>
                                            <?=getInputMask('normal', 'ipc_name', $row['ipc_name'], 'readonly')?>
                                        </div>
                                    </div>


                                    <div class="form-group col-12 mb-1">
                                        <?=getTextArea('ip_memo', $set_value='', $opt='')?>
                                        <span class="help-block">개인에게 할당되는 IP에 대한 장비(device)정보 상세 입력<span>
                                    </div>

                                    <div class="form-group col-12 mb-1">
                                        <div id="btn_addrow" class="btn btn-success btn-sm btn-block waves-effect waves-themed">
                                            <span><i class="fal fa-arrow-alt-to-bottom mr-2"></i>Add Row</span>
                                        </div>
                                    </div>


                                    <?php if($mode == 'update') : ?>
                                    <div class="col-12 mb-3">
                                        <table id="data-table" class="table table-sm table-striped table-bordered table-hover" style="width:100%">
                                            <thead class="bg-info-600">
                                                <tr>
                                                    <th data-name="pim_id">ID(pim)</th>
                                                    <th data-name="ip_id">ID(ip)</th>
                                                    <th data-name="ip_address">IP Address</th>
                                                    <th data-name="ip_memo">Memo</th>
                                                    <th data-name="ip_class_id">Class Name</th>
                                                    <th data-name="ip_class_type">Class Type</th>
                                                    <th data-name="ip_class_category">Class Cat.</th>
                                                    <th data-name="ip_created_at">Created At</th>
                                                    <th data-name="ip_updated_at">Updated At</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                    <?php endif; ?>

                                </div>
                                </form>
                            </div> <!-- END #tab_ips -->



                            <!-- START #tab_authinfo -->
                            <div class="tab-pane fade" id="tab_authinfo" role="tabpanel">

                                <form  >
                                <input type="hidden" name="mode" value="<?=$mode?>">
                                <input type="hidden" name="pp_id" value="<?=$row['pp_id']?>">



                                <div class="form-row form-group justify-content-md-center">
                                    
                                    <div class="row col-12 col-lg-8 mb-1">
                                        <div class="form-group col-6 pl-0">
                                            <label class="form-label" for="pp_info_userpw" data-i18n="content.info_userpw">
                                                User PW 
                                            </label>
                                            <?=getInputMask('normal', 'pp_info_userpw', $row['pp_info_userpw'], 'readonly')?>
                                        </div>

                                        <div class="form-group col-6 pr-0">
                                            <label class="form-label" for="pp_info_chpasswd" data-i18n="content.info_chpasswd">
                                                PW Changed At 
                                            </label>
                                            <?=getInputMask('normal', 'pp_info_chpasswd', $row['pp_info_chpasswd'], 'readonly')?>
                                        </div>
                                    </div>
                                    

                                    <div class="form-group col-12 mb-1">
                                        <div id="btn_addrow" class="btn btn-danger btn-sm btn-block waves-effect waves-themed">
                                            <span><i class="fal fa-arrow-alt-to-bottom mr-2"></i>Reset Password</span>
                                        </div>
                                    </div>
                                </div>
                                </form>
                            </div>
                            <!-- END #tab_authinfo -->


                        </div>
                    </div>
                <div>
            </div>
        </div>
    </div>
</main>



<form name='del_form' id="del_form" action="/<?=SHOP_INFO_ADMIN_DIR?>/people/employee_process" method="POST">
    <input type='hidden' name='mode' value='delete' />
    <input type="hidden" name="pp_id" value="<?=$row['pp_id']?>">
</form>




<script type="text/javascript">
$(document).ready(function() {

    // Tab 전환
    var _tab = location.hash;
    if(_tab.length > 0) {
        localStorage['lastTab'] = _tab;
    }
    $('[name="ip_address"]').focusout(function(e) {
        setCIDR('#tab_ips '); 
    });
    if($('[name="ip_address"]').val()) {
        setCIDR('#tab_ips '); 
    }


    $('[name="pp_admin_id"]').on('select2:select', function(e) {
        if($(this).val()) {
            var params = {'a_id':$(this).val()};
            $.post('/<?=SHOP_INFO_ADMIN_DIR?>/system/ajax_get_manager', params, function(res) {
                if(res.is_success) {
                    var data = res.data;
                    $('[name="pp_name"]').val(data.a_lastname + data.a_firstname);
                    $('[name="pp_email"]').val(data.a_email);
                } else {
                    Swal.fire("Submit Error !", res.msg, "error");
                }
            },'json');
        }
    });

    $('#btn_addrow').click(function(e) {
        if( ! $('[name="ip_address"]').val()) {
            $('[name="ip_address"]').focus();
            return;
        }
        if( ! $('[name="ipc_location_id"]').val()) {
            setCIDR('#tab_ips '); 
        }

        var params = $('#area-spec').serialize();
        $.post('/<?=SHOP_INFO_ADMIN_DIR?>/people/pim_process', params, function(res) {
            if(res.is_success) {
                table.clear().draw();
                $('[name="ip_address"]').val('').removeClass('is-valid');
                $('[name="ip_memo"]').val('');
                $('[name="ipc_location_id"]').val('');
                $('[name="ipc_cidr"]').val('');
                $('[name="ipc_name"]').val('');
            } else {
                Swal.fire("Submit Error !", res.msg, "error");
            }
        },'json');
    });



    var table = $('#data-table').DataTable({
        "bPaginate"     : false,
        "orderCellsTop" : false, 
        "ordering"      : false,
        "fixedHeader"   : false, 
        "processing"    : true,
        "serverSide"    : true,
        "searching"     : false,
        "responsive"    : true,
        "select"        : "single",
        "altEditor"     : true,
        "order"         : [[0, "desc"]],    // Default Sorting
        "filter"        : false,
        "lengthChange"  : false,
        "ajax": {
            "url": "/<?=SHOP_INFO_ADMIN_DIR?>/people/pim_list",
            "type": "POST",
            "dataType": "JSON",
            "data": {
                "mode": "list",
                "pim_people_id": "<?=$row['pp_id']?>",
                "request": "ajax",
            }
        },

        "dom":
              "<'row mb-1 mt-3'"
                + "<'col-sm-12 col-md-4 d-flex align-items-center justify-content-start'f>"
                + "<'col-sm-12 col-md-8 d-flex align-items-center justify-content-end'B>"
            + ">" 
            + "<'row'<'col-sm-12'tr>>"
            + "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",

        "buttons": [
            {
                extend: 'selected',
                text: '<i class="fal fa-times mr-1"></i> Delete',
                name: 'delete',
                className: 'btn-danger btn-sm mr-1'
            },
        ],


        "columns": [
            {"data": "pim_id"},
            {"data": "ip_id"},
            {"data": "ip_address",},
            {"data": "ip_memo",},
            {"data": "ip_class_id"},
            {"data": "ip_class_type"},
            {"data": "ip_class_category"},
            {
                "data": "ip_created_at",
            },
            {
                "data": "ip_updated_at",
            },
        ],
        // Define columns class 
        "columnDefs": [
            { "className": "text-center fs-nano", "targets": "_all" },
        ],
        onDeleteRow: function(dt, rowdata, success, error) {
            rowdata.mode = 'delete';
            rowdata.request = 'ajax';
            $.post('/<?=SHOP_INFO_ADMIN_DIR?>/people/pim_process', rowdata, function(res) {
                if(res.is_success) {
                    //table.clear().draw();
                    success();
                } else {
                    Swal.fire("Submit Error !", res.msg, "error");
                }
            },'json');
        },
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
