<?php 
//include left panel (navigation)
//follow the tree in inc/config.ui.php
require_once realpath(dirname(__FILE__).'/../').'/inc/config.ui.php';


$active_key = array(
    "assets",               // 1 Depth menu
    "type",          // 2(Sub) Depth menu
    $assets_type_uri
);
$page_nav[$active_key[0]]["active"] = true;
$page_nav[$active_key[0]]["sub"]['type']['active'] = true;
$page_nav[$active_key[0]]["sub"]['type']['sub'][$active_key[2]]["active"] = true;
include realpath(dirname(__FILE__).'/../').'/inc/nav.php';

$title_symbol = 'fa-server';
?>



<link rel="stylesheet" media="screen, print" href="<?=$assets_dir?>/css/formplugins/bootstrap-colorpicker/bootstrap-colorpicker.css">
<link rel="stylesheet" media="screen, print" href="<?=$assets_dir?>/css/formplugins/dropzone/dropzone.css">
<link rel="stylesheet" media="screen, print" href="<?=$assets_dir?>/css/formplugins/bootstrap-tagsinput/bootstrap-tagsinput.css">


<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL -->
<style type="text/css">
.modal-dialog {max-width: 800px;}
</style>


<main id="js-page-content" role="main" class="page-content">

    <?php include realpath(dirname(__FILE__).'/../').'/inc/breadcrumbs.php'; ?>

    <div class="row">
        <div class="col-xl-12">
            <div class="panel">
                <div class="panel-hdr">
                    <h2>Row <?=ucfirst($mode)?></h2>

                    <div>
                        <a href="/admin/assets/type/<?=$assets_type_uri?>?keep=yes" class="btn btn-sm btn-danger waves-effect waves-themed">
                            <span class="fal fa-list-alt mr-1"></span> Lists
                        </a>
                    </div>
                </div>

                <div class="panel-container show">
                    <div class="panel-content">

                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <a href="#tab_assets" class="nav-link active" data-toggle="tab" role="tab" aria-selected="true">
                                    <i class="fal fa-server text-success"></i>
                                    <span class="hidden-sm-down ml-1">Assets</span>
                                </a>
                            </li>


                            <?php if($mode == 'update') : ?>
                            <li class="nav-item">
                                <a href="#tab_ips" class="nav-link" data-toggle="tab" role="tab" aria-selected="false">
                                    <i class="fal fa-ethernet text-info"></i>
                                    <span class="hidden-sm-down ml-1">IPs</span>
                                </a>
                            </li>


                            <li class="nav-item">
                                <a href="#tab_vmware" class="nav-link" data-toggle="tab" role="tab" aria-selected="false">
                                    <i class="fal fa-layer-group text-danger"></i>
                                    <span class="hidden-sm-down ml-1">VMWare</span>
                                    <span class="badge badge-icon position-relative"><?=$vmware_cnt?></span>
                                </a>
                            </li>


                            <li class="nav-item" id="div_tab_alias">
                                <a href="#tab_alias" class="nav-link" data-toggle="tab" role="tab" aria-selected="false">
                                    <i class="fal fa-code-branch text-primary"></i>
                                    <span class="hidden-sm-down ml-1">IP Alias</span>
                                    <span class="badge badge-icon position-relative"><?=$alias_cnt?></span>
                                </a>
                            </li>


                            <?php /*?>
                            <li class="nav-item" id="div_tab_works">
                                <a href="#tab_works" class="nav-link" data-toggle="tab" role="tab" aria-selected="false">
                                    <i class="fal fa-tools text-warning"></i>
                                    <span class="hidden-sm-down ml-1">Maintenance</span>
                                    <span class="badge badge-icon position-relative"><?=$works_cnt?></span>
                                </a>
                            </li>
                            <?php */?>

                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#tab_maintenance" role="tab" aria-selected="false">
                                    <i class="fas fa-tools text-warning"></i>
                                    <span class="hidden-sm-down ml-1" data-1i8n="content.Maintenance">Maintenance</span>
                                    <span class="badge badge-icon position-relative"><?=$works_cnt?></span>
                                </a>
                            </li>

                            <?php endif; ?>

                        </ul>


                        <div class="tab-content border border-top-0 p-3">

                            <!-- START #tab_assets -->
                            <div class="tab-pane fade active show" id="tab_assets" role="tabpanel">

                                <form name='edit_form' id="edit_form" class="needs-validation w-100" action="/<?=SHOP_INFO_ADMIN_DIR?>/assets/type_process" method="POST" novalidate autocomplete="off" encrypt="multipart/form-data">
                                <input type="hidden" name="mode" value="<?=$mode?>">
                                <input type="hidden" name="am_id" value="<?=$row['am_id']?>">
                                <input type="hidden" name="am_order_id" value="<?=$row['am_order_id']?>">
                                <input type="hidden" name="am_estimatenum" value="<?=$row['am_estimatenum']?>">
                                <input type="hidden" name="am_assets_type_id" value="<?=$row['am_assets_type_id']?>">
                                <input type="hidden" name="am_models_id" value="<?=$row['am_models_id']?>">



                                <div class="panel-content py-2 rounded-bottom border-faded border-top-0 border-left-0 border-right-0 text-muted d-flex mb-5">
                                    <?php if($mode == 'update') : ?>
                                    <a href="/<?=SHOP_INFO_ADMIN_DIR?>/assets/status_detail/<?=$row['am_id']?>" class="btn btn-sm btn-warning waves-effect waves-themed text-white mr-1">
                                        <i class="fal fa-window-alt mr-1"></i>View
                                    </a>
                                    <?php endif; ?>
                                    <?=genDetailButton('assets_type', $mode, $assets_type_uri)?>

                                    <?php if($this->common->checkDelete() === TRUE): ?>
                                    <?=genDeleteButton();?>
                                    <?php endif; ?>
                                </div>

                           

                                <div class="form-row form-group justify-content-md-center">

                        
                                    <?php if($mode == 'update') : ?>
                                    <div class="col-12 col-lg-8 mb-3">
                                        <label class="form-label" for="am_estimatenum" data-i18n="content.estimatenum">
                                            Estimatenum
                                        </label>
                                        <?=genNewButton('orders')?> 
                                        <input type="text" class="form-control" name="am_estimatenum" value="<?=$row['am_estimatenum']?>" disabled >
                                        <!-- span class="help-block">노출조건 : 미등록 & Service Tag 존재</span -->
                                    </div>
                                    <?php endif;?>


                                    <div class="col-12 col-lg-8 mb-3">
                                        <label class="form-label" for="am_order_item_id" data-i18n="content.order_item">
                                            Order Item 
                                        </label>
                                        <?=$select_order?>
                                        <span class="help-block">발주 후 <code>[DELIVERED]</code> 된 상태 발주정보만 노출</span>
                                    </div>

                                    <div class="col-12 col-lg-8 mb-3">
                                        <label class="form-label" for="am_company_id" data-i18n="content.company">
                                            Company 
                                        </label>
                                        <?=genNewButton('company')?> 
                                        <?=$select_company?>
                                    </div>

                                    <div class="col-12 col-lg-8 mb-3">
                                        <label class="form-label" for="am_sypplier_id" data-i18n="content.supplier">
                                            Supplier 
                                        </label>
                                        <?=genNewButton('supplier')?> 
                                        <?=$select_supplier?>
                                    </div>

                                    <div class="col-12 col-lg-8 mb-3">
                                        <label class="form-label" for="am_models_id" data-i18n="content.model">
                                            Model 
                                        </label>
                                        <?=$select_model?>
                                    </div>


                                    <div class="col-12 col-lg-8 mb-3">
                                        <label class="form-label" for="am_assets_type_id" data-i18n="content.assets_type">
                                           Assets Type 
                                        </label>
                                        <?=$select_type?>
                                    </div>


                                    <div class="col-12 col-lg-8 mb-3">
                                        <label class="form-label" for="m_category_id" data-i18n="content.category">
                                            Category 
                                        </label>
                                        <?=$select_category?>
                                    </div>



                                    <div class="col-12 col-lg-8 mb-3 border border-success rounded-plus bg-highlight shadow-sm p-5" id="area-custom-fields" style="display:none">

                                    <?php if($mode == 'update') : ?>
                                    <?=$row['view_data']?>
                                    <?php endif; ?>

                                    </div>


                                    <div class="col-12 col-lg-8 mb-3">
                                        <label class="form-label" for="am_name">
                                            <span data-i18n="content.assets_name">Assets Name</span>
                                            <span class="text-danger">*</span>
                                        </label>
                                        <?=getInputMask('normal', 'am_name', $row['am_name'], 'required')?>
                                    </div>


                                    <div class="col-12 col-lg-8 mb-3">
                                        <label class="form-label" for="am_tags" data-i18n="content.tags">
                                            Tags 
                                        </label>
                                        <input class="form-control " type="text" name="am_tags" value="<?=$row['am_tags']?>" data-role="tagsinput" />
                                    </div>


                                    <div class="col-12 col-lg-8 mb-3">
                                        <label class="form-label" for="am_serial_no">
                                            <span data-i18n="content.service_tag">Serial No.</span>
                                            <span class="text-danger">*</span>
                                        </label>
                                        <?=getInputMask('normal', 'am_serial_no', $row['am_serial_no'], 'required')?>
                                    </div>


                                    <div class="col-12 col-lg-8 mb-3">
                                        <label class="form-label" for="am_rack_id" data-i18n="content.rack_space">
                                            Rack Space 
                                        </label>
                                        <?=genNewButton('rack')?> 
                                        <select class="select2-ajax form-control sel-ajax-rack" id="am_rack_id" name="am_rack_id">
                                            <?php if( isset($row['am_rack_id']) && strlen($row['am_rack_id']) > 0 ) : ?>
                                            <option value="<?=$row['am_rack_id']?>">
                                                <?=$row['am_rack_code']?>
                                            </option>
                                            <?php endif; ?>
                                        </select>
                                        <span class="help-block"></span>
                                        <div class="invalid-feedback"></div>
                                    </div>



                                    <?php /*?>
                                    <div class="col-12 col-lg-8 mb-3">
                                        <label class="form-label" for="am_location_id">
                                            Location 
                                        </label>
                                        <?=genNewButton('location')?> 
                                        <?=$select_location?>
                                    </div>
                                    <?php */?>



                                    <div class="col-12 col-lg-8 mb-3">
                                        <label class="form-label" for="am_status" data-i18n="content.status">
                                            Status 
                                        </label>
                                        <span class="text-danger">*</span>
                                        <?=$select_status?>
                                        <div class="invalid-feedback">Please provide a status.</div>

                                        <div class="help-block"><?=$out_comments?></div>
                                    </div>



                                    <div class="col-12 col-lg-8 mb-3">
                                        <label class="form-label" for="am_total_price" data-i18n="content.purchase_price">
                                            Purchase Price (Total Price)
                                        </label>
                                        <?=getInputMask('ko_currency', 'am_total_price', $row['am_total_price'])?>
                                    </div>



                                    <div class="col-12 col-lg-8 mb-3">
                                        <label class="form-label" for="am_ordered_at" data-i18n="content.purchased_at">
                                            Purchased At (Ordered At)
                                        </label>

                                        <div class="input-group">
                                            <input type="text" class="form-control datepicker" id="am_ordered_at" name="am_ordered_at" value="<?=$row['am_ordered_at']?>">
                                            <div class="input-group-append">
                                                <span class="input-group-text fs-xl">
                                                    <i class="fal fa-calendar-check"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>



                                    <div class="col-12 col-lg-8 mb-3">
                                        <label class="form-label" for="am_quantity" data-i18n="content.quantity">
                                            Quantity 
                                        </label>

                                        <div class="input-group">
                                            <input id="basic-addon2" type="number" class="form-control col-4 col-lg-2" name="am_quantity" value="<?=$row['am_quantity']?>">
                                            <div class="input-group-append">
                                                <span class="input-group-text">EA</span>
                                            </div>
                                        </div>
                                    </div>



                                    <div class="form-row col-12 col-lg-8">

                                        <div class="col-6 mb-3 pl-0">
                                            <label class="form-label" for="am_eos_rate" data-i18n="content.eos">
                                                EOS (End Of Service) 
                                            </label>

                                            <div class="input-group">
                                                <input id="basic-addon2" type="text" class="form-control" name="am_eos_rate" value="<?=$row['am_eos_rate']?>">
                                                <div class="input-group-append">
                                                    <span class="input-group-text">months</span>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="col-6 mb-3">
                                            <label class="form-label" for="am_eos_expired_at" data-i18n="content.eos_expired_at">
                                                EOS Expired At 
                                            </label>

                                            <div class="input-group">
                                                <input type="text" class="form-control datepicker" id="am_eos_expired_at" name="am_eos_expired_at" value="<?=$row['am_eos_expired_at']?>">
                                                <div class="input-group-append">
                                                    <span class="input-group-text fs-xl">
                                                        <i class="fal fa-calendar-check"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                    </div>


                                    <div class="col-12 col-lg-8 mb-3">
                                        <label class="form-label" for="am_memo" data-i18n="content.memo">
                                            Memo 
                                        </label>
                                        <?=getTextArea($name='am_memo', $row['am_memo'])?>
                                        <span class="help-block">자산에 대한 역할 및 기능에 대한 상세 서술</span>
                                    </div>


                                    <?php if($mode == 'update') : ?>
                                    <div class="col-12 col-lg-8 mb-3">
                                        <label class="form-label" data-i18n="content.updated_at">
                                            Updated At 
                                        </label>
                                        <?=getInputMask('datetime', 'am_updated_at', $row['am_updated_at'], 'disabled')?>
                                    </div>

                                    <div class="col-12 col-lg-8 mb-3">
                                        <label class="form-label" data-i18n="content.created_at">
                                            Created At 
                                        </label>
                                        <?=getInputMask('datetime', 'am_created_at', $row['am_created_at'], 'disabled')?>
                                    </div>
                                    <?php endif;?>

                                </div>  <!-- .form-row -->

                                <div class="panel-content py-2 rounded-bottom border-faded border-left-0 border-right-0 border-bottom-0 text-muted d-flex">
                                    <?php if($mode == 'update') : ?>
                                    <a href="/<?=SHOP_INFO_ADMIN_DIR?>/assets/status_detail/<?=$row['am_id']?>" class="btn btn-sm btn-warning waves-effect waves-themed text-white mr-1">
                                        <i class="fal fa-window-alt mr-1"></i>View
                                    </a>
                                    <?php endif; ?>
                                    <?=genDetailButton('assets_type', $mode, $assets_type_uri)?>

                                    <?php if($this->common->checkDelete() === TRUE): ?>
                                    <?=genDeleteButton();?>
                                    <?php endif; ?>
                                </div>

                                </form>

                            </div> <!-- END #tab_assets -->




                            <!-- START #tab_ips-->
                            <div class="tab-pane fade" id="tab_ips" role="tabpanel">


                                <div class="card mb-g" id="idrac_ip">

                                    <form id="form-idrac" class="needs-validation">
                                    <input type="hidden" name="aim_id" value="<?=$idrac['aim_id']?>">
                                    <input type="hidden" name="ip_id" value="<?=$idrac['ip_id']?>">
                                    <input type="hidden" name="ip_class_id" value="<?=$idrac['ip_class_id']?>">
                                    <input type="hidden" name="ip_class_type" value="<?=$idrac['ip_class_type']?>">
                                    <input type="hidden" name="ip_class_category" value="<?=$idrac['ip_class_category']?>">
                                    <input type="hidden" name="am_id" value="<?=$row['am_id']?>">
                                    <input type="hidden" name="mode" value="<?=$idrac['mode']?>">
                                    <input type="hidden" name="request" value="ajax">
                                    <input type="hidden" name="set_valid" value="idrac">

                                    <div class="card-body">
                                        <h4 class="mb-g fw-500">
                                            <i class="fa fa-monitor-heart-rate mr-1"></i>iDrac IP 
                                            <small></small>
                                        </h4>
                                        <div class="form-row form-group justify-content-md-center mb-5">

                                            <div class="form-group col-12 mb-3">
                                                <label class="form-label" for="ip_address">
                                                    iDrac IP 
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <?=getInputMask('ipv4', 'ip_address', $idrac['ip_address'], $opt='required')?>
                                                <span class="invalid-feedback">Please provide a IP Address.</span>
                                            </div>


                                            <div class="row col-12 mb-1">
                                                <div class="form-group col-4 pl-0">
                                                    <label class="form-label" for="ipc_location_id" data-i18n="content.location">
                                                        Location 
                                                    </label>
                                                    <?=getInputMask('normal', 'ipc_location_id', '', 'readonly')?>
                                                </div>
                                                <div class="form-group col-4">
                                                    <label class="form-label" for="ipc_cidr" data-i18n="content.cidr">
                                                        CIDR 
                                                    </label>
                                                    <?=getInputMask('normal', 'ipc_cidr', '', 'readonly')?>
                                                </div>
                                                <div class="form-group col-4 pr-0">
                                                    <label class="form-label" for="ipc_name" data-i18n="content.ip_class_name">
                                                        IP Class Name 
                                                    </label>
                                                    <?=getInputMask('normal', 'ipc_name', '', 'readonly')?>
                                                </div>
                                            </div>


                                            <div class="form-group col-12 mb-1">
                                                <?=getTextArea('ip_memo', $idrac['ip_memo'], $opt='')?>
                                            </div>



                                            <div class="form-group col-12 mb-1 row">
                                                <div class="col-6 pl-0">
                                                    <div class="btn btn-success btn-sm btn-block waves-effect waves-themed btn-save-ip" data-id="idrac">
                                                        <span><i class="fal fa-save mr-2"></i>Save</span>
                                                    </div>
                                                </div>


                                                <div class="col-6 pr-0">
                                                    <div class="btn btn-danger btn-sm btn-block waves-effect waves-themed btn-delete-ip" data-id="idrac">
                                                        <span><i class="fal fa-trash mr-2"></i>Delete</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group col-12" >
                                                <div class="alert border-success bg-transparent text-success mt-2" role="alert" id="alert-save" style="display:none;">
                                                    <strong>Success !</strong> You successfully read this important alert message.
                                                </div>
                                            </div>


                                        </div>
                                    </form>
                                    </div>
                                </div>


                                <div class="card mb-g" id="direct_ip">

                                    <form id="form-direct" class="needs-validation">
                                    <input type="hidden" name="dim_id" value="<?=$direct['dim_id']?>">
                                    <input type="hidden" name="ip_id" value="<?=$direct['ip_id']?>">
                                    <input type="hidden" name="ip_class_id" value="<?=$direct['ip_class_id']?>">
                                    <input type="hidden" name="ip_class_type" value="<?=$direct['ip_class_type']?>">
                                    <input type="hidden" name="ip_class_category" value="<?=$direct['ip_class_category']?>">
                                    <input type="hidden" name="am_id" value="<?=$row['am_id']?>">
                                    <input type="hidden" name="mode" value="<?=$direct['mode']?>">
                                    <input type="hidden" name="request" value="ajax">
                                    <input type="hidden" name="set_valid" value="direct">

                                    <div class="card-body">
                                        
                                        <h4 class="mb-g fw-500">
                                            <i class="fa fa-link mr-1"></i>Direct IP 
                                            <span class="text-danger ml-2" style="font-size:0.7em;">VMWare가 아닌 단독서버 운영시</span>
                                        </h4>
                                        <div class="form-row form-group justify-content-md-center mb-5">

                                            <div class="form-group col-12 mb-3">
                                                <label class="form-label" for="ip_address">
                                                    Direct IP 
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <?=getInputMask('ipv4', 'ip_address', $direct['ip_address'], $opt='required')?>
                                                <span class="invalid-feedback">Please provide a IP Address.</span>
                                            </div>


                                            <div class="row col-12 mb-1">
                                                <div class="form-group col-4 pl-0">
                                                    <label class="form-label" for="ipc_location_id">
                                                        Location 
                                                    </label>
                                                    <?=getInputMask('normal', 'ipc_location_id', '', 'readonly')?>
                                                </div>
                                                <div class="form-group col-4">
                                                    <label class="form-label" for="ipc_cidr">
                                                        CIDR 
                                                    </label>
                                                    <?=getInputMask('normal', 'ipc_cidr', '', 'readonly')?>
                                                </div>
                                                <div class="form-group col-4 pr-0">
                                                    <label class="form-label" for="ipc_name">
                                                        IP Class Name 
                                                    </label>
                                                    <?=getInputMask('normal', 'ipc_name', '', 'readonly')?>
                                                </div>
                                            </div>


                                            <div class="form-group col-12 mb-1">
                                                <?=getTextArea('ip_memo', $direct['ip_memo'], $opt='')?>
                                            </div>


                                            <div class="form-group col-12 mb-1 row">
                                                <div class="col-6 pl-0">
                                                    <div class="btn btn-success btn-sm btn-block waves-effect waves-themed btn-save-ip" data-id="direct">
                                                        <span><i class="fal fa-save mr-2"></i>Save</span>
                                                    </div>
                                                </div>


                                                <div class="col-6 pr-0">
                                                    <div class="btn btn-danger btn-sm btn-block waves-effect waves-themed btn-delete-ip" data-id="direct">
                                                        <span><i class="fal fa-trash mr-2"></i>Delete</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group col-12" >
                                                <div class="alert border-success bg-transparent text-success mt-2" role="alert" id="alert-save" style="display:none;">
                                                    <strong>Success !</strong> You successfully read this important alert message.
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    </div>
                                </div>



                                <?php
                                // 서버내에 VM 없이 단독 서비스 운영시 조건, 
                                if( sizeof($direct) > 0 && $direct['dim_id'] > 0 && $vmware_cnt == 0 ):
                                ?>
                                <div class="card mb-g" id="service_ip">
                                    <form id="form-service" class="needs-validation">
                                    <input type="hidden" name="request" value="ajax">
                                    <input type="hidden" name="mode" value="<?=$service['mode']?>">
                                    <input type="hidden" name="sm_id" value="<?=$service['sm_id']?>">
                                    <input type="hidden" name="sm_assets_model_id" value="<?=$row['am_id']?>">

                                    <div class="card-body">
                                        
                                        <h4 class="mb-g fw-500">
                                            <i class="fa fa-link mr-1"></i>Service Information
                                            <span class="text-danger ml-2" style="font-size:0.7em;">단독서버 서비스시, 해당 영역 노출</span>
                                        </h4>
                                        <div class="form-row form-group justify-content-md-center mb-5">

                                            <div class="form-group col-12 mb-3">
                                                <label class="form-label" for="sm_usage" data-i18n="content.usage">
                                                    VMService Usage 
                                                </label>
                                                <?=getInputMask('normal', 'sm_usage', $service['sm_usage'])?>
                                            </div>


                                            <div class="row col-12 mb-1">
                                                <div class="form-group col-6 pl-0">
                                                    <label class="form-label" for="sm_os_info">
                                                        OS. Info 
                                                    </label>
                                                    <?=getInputMask('normal', 'sm_os_info', $service['sm_os_info'])?>
                                                </div>
                                                <div class="form-group col-6 pr-0">
                                                    <label class="form-label" for="sm_db_info">
                                                        DB. Info
                                                    </label>
                                                    <?=getInputMask('normal', 'sm_db_info', $service['sm_db_info'])?>
                                                </div>
                                            </div>


                                            <div class="row col-12 mb-1">
                                                <div class="form-group col-6 pl-0">
                                                    <label class="form-label" for="sm_lang_info">
                                                        LANG. Info 
                                                    </label>
                                                    <?=getInputMask('normal', 'sm_lang_info', $service['sm_lang_info'])?>
                                                </div>
                                                <div class="form-group col-6 pr-0">
                                                    <label class="form-label" for="sm_was_info">
                                                        WAS. Info
                                                    </label>
                                                    <?=getInputMask('normal', 'sm_was_info', $service['sm_was_info'])?>
                                                </div>
                                            </div>


                                            <div class="row col-12 mb-1">
                                                <div class="form-group col-4 pl-0">
                                                    <label class="form-label" for="sm_manage_team" data-i18n="content.manage_team">
                                                        Manage Team 
                                                    </label>

                                                    <select class="select2-ajax form-control sel-ajax-dept" id="sm_mangae_team" name="sm_manage_team">
                                                        <?php if( isset($service['sm_manage_team']) && strlen($service['sm_manage_team']) > 0 ) : ?>
                                                        <option value="<?=$service['sm_manage_team']?>">
                                                            <?=$service['sm_manage_team']?>
                                                        </option>
                                                        <?php endif; ?>
                                                    </select>
                                                    <span class="help-block">부서명 검색</span>
                                                </div>

                                                <div class="form-group col-4 pl-0 pr-0">
                                                    <label class="form-label" for="sm_master_manager">
                                                        <span data-i18n="content.master_manager">
                                                            Master Manager
                                                        </span>
                                                    </label>

                                                    <select class="select2-ajax form-control sel-ajax-account" id="sm_master_manager" name="sm_master_manager">
                                                        <?php if( isset($service['sm_master_manager']) && strlen($service['sm_master_manager']) > 0 ) : ?>
                                                        <option value="<?=$service['sm_master_manager']?>">
                                                            <?=$service['sm_master_manager']?>
                                                        </option>
                                                        <?php endif; ?>
                                                    </select>
                                                    <span class="help-block">직원명 검색</span>
                                                </div>

                                                <div class="form-group col-4 pr-0">
                                                    <label class="form-label" for="sm_sub_manager" data-i18n="content.sub_manager">
                                                        Sub Manager 
                                                    </label>

                                                    <select class="select2-ajax form-control sel-ajax-account" id="sm_sub_manager" name="sm_sub_manager">
                                                        <?php if( isset($service['sm_sub_manager']) && strlen($service['sm_sub_manager']) > 0 ) : ?>
                                                        <option value="<?=$service['sm_sub_manager']?>">
                                                            <?=$service['sm_sub_manager']?>
                                                        </option>
                                                        <?php endif; ?>
                                                    </select>
                                                    <span class="help-block">직원명 검색</span>
                                                </div>
                                            </div>


                                            <div class="row col-12 mt-1 rounded border border-success p-2 bg-faded">
                                                <label class="form-label col-12 text-center" data-i18n="secure.importance_assessment">
                                                    Importance Assessment 
                                                </label>
                                            </div>

                                            <div class="row col-12 border-bottom border-success pt-3 mb-3">

                                                <div class="form-group col-2 pl-0 pr-0">
                                                    <label class="form-label" for="sm_secure_conf" data-i18n="secure.confidentiality">
                                                        Confidentiality
                                                    </label>
                                                    <?=getInputMask('unit_number', 'sm_secure_conf', $service['sm_secure_conf'])?>
                                                </div>
                                                <div class="form-group col-2 pl-0 pr-0">
                                                    <label class="form-label" for="sm_secure_inte" data-i18n="secure.integrity">
                                                        Integrity
                                                    </label>
                                                    <?=getInputMask('unit_number', 'sm_secure_inte', $service['sm_secure_inte'])?>
                                                </div>
                                                <div class="form-group col-2 pl-0 pr-0">
                                                    <label class="form-label" for="sm_secure_avail" data-i18n="secure.availability">
                                                        Availability  
                                                    </label>
                                                    <?=getInputMask('unit_number', 'sm_secure_avail', $service['sm_secure_avail'])?>
                                                </div>
                                                <div class="form-group col-2 pl-0 pr-0">
                                                    <label class="form-label" for="sm_important_score" data-i18n="secure.important_score">
                                                        Important Score 
                                                    </label>
                                                    <?=getInputMask('decimal_number', 'sm_important_score', $service['sm_important_score'], 'readonly')?>
                                                </div>
                                                <div class="form-group col-4 pr-0">
                                                    <label class="form-label" for="important_level" data-i18n="secure.important_level">
                                                        Important Level 
                                                    </label>
                                                    <?=getInputMask('normal', 'sm_important_level', $service['sm_important_level'], 'readonly')?>
                                                </div>
                                            </div>


                                            <div class="form-group col-12 mb-1 row">
                                                <div class="col-6 pl-0">
                                                    <div class="btn btn-success btn-sm btn-block waves-effect waves-themed btn-save-ip" data-id="service">
                                                        <span><i class="fal fa-save mr-2"></i>Save</span>
                                                    </div>
                                                </div>


                                                <div class="col-6 pr-0">
                                                    <div class="btn btn-danger btn-sm btn-block waves-effect waves-themed btn-delete-ip" data-id="service">
                                                        <span><i class="fal fa-trash mr-2"></i>Delete</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group col-12" >
                                                <div class="alert border-success bg-transparent text-success mt-2" role="alert" id="alert-save" style="display:none;">
                                                    <strong>Success !</strong> You successfully read this important alert message.
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    </div>
                                </div>
                                <?php
                                endif;
                                ?>

                            </div> <!-- END #tab_ips -->






                            <!-- START #tab_vmware --> 
                            <div class="tab-pane fade" id="tab_vmware" role="tabpanel">

                                <form id="area-spec" class="needs-validation" >

                                <input type="hidden" name="aim_id" value="<?=$vmware['aim_id']?>">
                                <input type="hidden" name="aim_assets_model_id" value="<?=$vmware['aim_assets_model_id']?>">
                                <input type="hidden" name="ip_id" value="<?=$vmware['ip_id']?>">
                                <input type="hidden" name="ip_class_id" value="<?=$vmware['ip_class_id']?>">
                                <input type="hidden" name="ip_class_type" value="<?=$vmware['ip_class_type']?>">
                                <input type="hidden" name="ip_class_category" value="<?=$vmware['ip_class_category']?>">
                                <input type="hidden" name="am_id" value="<?=$row['am_id']?>">
                                <input type="hidden" name="mode" value="<?=$vmware['mode']?>">
                                <input type="hidden" name="request" value="ajax">
                                <input type="hidden" name="set_valid" value="vmware">


                                <div class="form-row form-group justify-content-md-center mb-5">
                                    <div class="form-group col-12 mb-3">
                                        <label class="form-label" for="ip_address">
                                            VMWare Name 
                                            <span class="text-danger">*</span>
                                        </label>

                                        <?php
                                        if( strlen($row['am_vmware_name']) < 1 ) {
                                            $row['am_vmware_name'] = $row['am_name'];
                                        }
                                        ?>
                                        <?=getInputMask('normal', 'am_vmware_name', $row['am_vmware_name'], $opt='required')?>
                                    </div>

                                    <div class="form-group col-12 mb-3">
                                        <label class="form-label" for="ip_address">
                                            VMWare IP 
                                            <span class="text-danger">*</span>
                                        </label>
                                        <?=getInputMask('ipv4', 'ip_address', $vmware['ip_address'], $opt='required')?>
                                        <span class="invalid-feedback">Please provide a IP Address.</span>
                                    </div>


                                    <div class="row col-12 mb-1">
                                        <div class="form-group col-4 pl-0">
                                            <label class="form-label" for="ipc_location_id">
                                                Location 
                                            </label>
                                            <?=getInputMask('normal', 'ipc_location_id', isset($row['ipc_location_id']) ? $row['ipc_location_id'] : '', 'readonly')?>
                                        </div>
                                        <div class="form-group col-4">
                                            <label class="form-label" for="ipc_cidr">
                                                CIDR 
                                            </label>
                                            <?=getInputMask('normal', 'ipc_cidr', isset($row['ipc_cidr']) ? $row['ipc_cidr'] : '', 'readonly')?>
                                        </div>
                                        <div class="form-group col-4 pr-0">
                                            <label class="form-label" for="ipc_name">
                                                IP Class Name 
                                            </label>
                                            <?=getInputMask('normal', 'ipc_name', isset($row['ipc_name']) ? $row['ipc_name'] : '', 'readonly')?>
                                        </div>
                                    </div>


                                    <div class="form-group col-12 mb-1">
                                        <?=getTextArea('ip_memo', $vmware['ip_memo'], $opt='')?>
                                    </div>

                                    <div class="form-group col-12 mb-1">
                                        <div id="btn_save_vmware" class="btn btn-success btn-sm btn-block waves-effect waves-themed">
                                            <span><i class="fal fa-save mr-2"></i>Save</span>
                                        </div>


                                        <div class="alert border-success bg-transparent text-success mt-3" role="alert" id="alert-save" style="display:none;">
                                            <strong>Success !</strong> You successfully read this important alert message.
                                        </div>
                                    </div>

                                </div>
                                </form>



                                <hr />


                                <?php if($mode == 'update' && $row['am_id'] > 0) : ?>
                                <div class="card border mb-g mt-5" id="area-vmservice" style="display:none;">
                                    <div class="card-header bg-danger-700 py-2 pr-2 d-flex align-items-center flex-wrap">
                                        <div class="card-title text-white">
                                            VMWare Service <span class="fw-300"><i>Specifications</i></span>
                                        </div>
                                    </div>

                                    <div class="col-12 mb-3">
                                        <table id="data-table-vms" class="table table-sm table-striped table-bordered table-hover" style="width:100%">
                                            <thead class="bg-info-600">
                                                <tr>
                                                    <th data-name="vms_id">ID.</th>
                                                    <th data-name="vms_ip_id">IP ID.</th>
                                                    <th data-name="vms_ip_address">VMService IP Address</th>
                                                    <th data-name="vms_vmware_ip">VMWare IP Address</th>
                                                    <th data-name="vms_name">VMService Name</th>
                                                    <th data-name="vms_memo">VMService Memo</th>
                                                    <th data-name="sm_os_info">OS</th>
                                                    <th data-name="sm_db_info">DB</th>
                                                    <th data-name="sm_lang_info">LANG</th>
                                                    <th data-name="sm_was_info">WAS</th>
                                                    <th data-name="sm_usage">사용용도</th>
                                                    <th data-name="ip_memo">IP Memo</th>
                                                    <th data-name="vms_status">Status</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                                <?php endif; ?>


                            </div> <!-- END #tab_vmware -->





                            <!-- START #tab_alias-->
                            <div class="tab-pane fade" id="tab_alias" role="tabpanel">

                                <?php if($mode == 'update' && $row['am_id'] > 0) : ?>
                                <div class="col-12 mb-3">
                                    <table id="data-table-alias" class="table table-sm table-striped table-bordered table-hover" style="width:100%">
                                        <thead class="bg-info-600">
                                            <tr>
                                                <th data-name="vms_id">VMS ID.</th>
                                                <th data-name="vms_alias_id">Main VMService Name</th>
                                                <th data-name="vms_alias_ip">Main VMService IP</th>
                                                <th data-name="vms_name">Alias Name</th>
                                                <th data-name="ip_address">Alias IP</th>
                                                <th data-name="vms_memo">Alias memo</th>
                                                <th data-name="ip_memo">IP Memo</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                                <?php endif; ?>
                            </div>

 

                            <!-- START #tab_works-->

                            <?php /*?>
                            <div class="tab-pane fade" id="tab_works" role="tabpanel">

                                <?php if($mode == 'update' && $row['am_id'] > 0) : ?>
                                <div class="col-12 mb-3">
                                    <table id="data-table-works" class="table table-sm table-striped table-bordered table-hover" style="width:100%">
                                        <thead class="bg-info-600">
                                            <tr>
                                                <th data-name="vms_id">VMS ID.</th>
                                                <th data-name="vms_alias_id">Main VMService Name</th>
                                                <th data-name="vms_alias_ip">Main VMService IP</th>
                                                <th data-name="vms_name">Alias Name</th>
                                                <th data-name="ip_address">Alias IP</th>
                                                <th data-name="vms_memo">Alias memo</th>
                                                <th data-name="ip_memo">IP Memo</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php */ ?>

 


                            <!-- START TAB4 -->
                            <div class="tab-pane fade" id="tab_maintenance" role="tabpanel">
                                <?php
                                $params = array(
                                    'assets_model_id' => $row['am_id'] 
                                );
                                echo $this->load->view('/admin/default_template/assets/maintenance_tab.php', $params, true);
                                ?>
                            </div>
                            <!-- END TAB4 -->






                        </div> <!-- .tab-contet -->
                    </div> <!-- .panel-container -->


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
                <button type="button" class="btn btn-primary" id="btn-save-edit" style="display:none;">Save</button>
            </div>
        </div>
    </div>
</div>

<?php if($this->common->checkDelete() === TRUE): ?>
<form name='del_form' id="del_form" action="/<?=SHOP_INFO_ADMIN_DIR?>/assets/type_process" method="POST">
    <input type='hidden' name='mode' value='delete' />
    <input type="hidden" name="am_id" value="<?=$row['am_id']?>">
    <input type="hidden" name="am_assets_type_id" value="<?=$row['am_assets_type_id']?>">
    <input type="hidden" name="del_msg" value="">
</form>
<?php endif; ?>

<form name='form_del_idrac' id="form_del_idrac" action="/<?=SHOP_INFO_ADMIN_DIR?>/assets/aim_process" method="POST">
    <input type='hidden' name='mode' value='delete' />
    <input type="hidden" name="aim_id" value="<?=$idrac['aim_id']?>">
</form>
<form name='form_del_direct' id="form_del_direct" action="/<?=SHOP_INFO_ADMIN_DIR?>/assets/dim_process" method="POST">
    <input type='hidden' name='mode' value='delete' />
    <input type="hidden" name="dim_id" value="<?=$direct['dim_id']?>">
</form>
<form name='form_del_service' id="form_del_service" action="/<?=SHOP_INFO_ADMIN_DIR?>/assets/service_process" method="POST">
    <input type='hidden' name='mode' value='delete' />
    <input type='hidden' name='request' value='ajax' />
    <input type="hidden" name="sm_id" value="<?=$service['sm_id']?>">
</form>








<script type="text/javascript">

$(document).ready(function() {

    // Tab 전환
    var _tab = location.hash;
    if(_tab.length > 0) {
        localStorage['lastTab'] = _tab;
    }
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (event) {
        console.log($(event.target).attr('href')); // newly activated tab
        _tab = $(event.target).attr('href');
        localStorage['lastTab'] = _tab;
        window.location.hash = _tab;
    })

    var controls = {
        leftArrow: '<i class="fal fa-angle-left" style="font-size: 1.25rem"></i>',
        rightArrow: '<i class="fal fa-angle-right" style="font-size: 1.25rem"></i>'
    }


    $('.datepicker').datepicker({
        autoclose: true,
        format: "yyyy-mm-dd",
        orientation: "top left",
        todayHighlight: true,
        templates: controls,
    });


    $('[name="am_order_item_id"]').on('select2:select', function(e) {
        var _val = $(this).val();
        $.post('/<?=SHOP_INFO_ADMIN_DIR?>/assets/ajax_get_oi', {"oi_id": _val}, function(res) {
            if(res.is_success) {
                var row = res.data;

                $('[name="am_order_id"]').val(row.o_id);
                $('[name="am_estimatenum]').val(row.o_estimatenum);

                $('[name="am_company_id"]').val(row.o_company_id).trigger('change');
                $('[name="am_supplier_id"]').val(row.o_supplier_id).trigger('change');
                $('[name="am_ordered_at"]').val(row.o_ordered_at);

                $('[name="am_models_id"]').val(row.oi_model_id).trigger('change');
                $('[name="am_name"]').val(row.oi_model_name);
                $('[name="am_total_price"]').val(row.oi_total_price);
                $('[name="am_quantity"]').val(row.oi_quantity);
                $('[name="am_serial_no"]').val(row.oi_service_tag);

                $('[name="am_assets_type_id"]').val(row.ct_type_id).trigger('change');
                $('[name="m_category_id"]').val(row.ct_id).trigger('change');

                var name = row.oi_model_name;
                var tags = name.split(" ");
                $('[name="am_tags"]').tagsinput('removeAll');
                $.each(tags, function(index, value) {
                    $('[name="am_tags"]').tagsinput('add', value);
                });

            } else {
                Swal.fire("Submit Error !", res.msg, "error");
            }
        },'json');
    });


    $('[name="am_models_id"]').on('change', function(e) {
        e.preventDefault();
        $.post('/<?=SHOP_INFO_ADMIN_DIR?>/assets/ajax_get_model', {"models_id": $(this).val()}, function(res) {
            if(res.is_success) {
                if(res.data) { 
                    $('#area-custom-fields').html(res.data).show().find('.input-mask').inputmask();
                }
                if(res.type_id > 0) {
                    $('[name="am_assets_type_id"]').val(res.type_id).trigger('change');
                    $('[name="m_category_id"]').val(res.category_id).trigger('change');
                }
            } else {
                Swal.fire("Submit Error !", res.msg, "error");
            }


        },'json');
    });



    <?php if($mode == 'insert') : ?>
    $('#tab_assets [name="am_serial_no"]').on('propertychange change keyup paste input', function(e) {
        if( ! $(this).val() ) return;

        if (
            $('#tab_assets [name="am_estimatenum"]').val() ||
            $('#tab_assets [name="am_order_item_id"]').val()
        ) {
            //console.log('EXI');
        }else {
            $.post('/<?=SHOP_INFO_ADMIN_DIR?>/assets/ajax_check_tag', {"service_tag": $(this).val()}, function(res) {
                if(res.is_success) {
                } else {
                    Swal.fire(
                        "Submit Error !", res.msg, "error"
                    ).then((result) => {
                        $('#tab_assets [name="am_serial_no"]').val('');
                        $('#tab_assets [name="am_order_item_id"]').select2('open');
                    });
                }
            },'json');
        }
    });
    <?php endif; ?>




    <?php if($mode == 'update') : ?>
    $('#area-custom-fields').show().find('.input-mask').inputmask();
    if($('#tab_vmware [name="ip_address"]').val()) {
        setCIDR('#tab_vmware '); 
        $('#area-vmservice').show();
    }
    <?php endif;?>


    $('#tab_vmware [name="ip_address"]').focusout(function(e) {
        setCIDR('#tab_vmware '); 
    });


    $('#btn_save_vmware').click(function(e) {
        if($('#tab_assets [name="mode"]').val() == 'insert') {
            Swal.fire("Submit Error !", 'TAB[Assets] 정보를 먼저 저장 해주세요!', "error");
            return;
        }
        if( ! $('#tab_vmware [name="ip_address"]').val()) {
            $('#tab_vmware [name="ip_address"]').focus();
            return;
        }
        if( ! $('#tab_vmware [name="ipc_location_id"]').val()) {
            setCIDR('#tab_vmware '); 
        }

        var params = $('#area-spec').serialize();
        $.post('/<?=SHOP_INFO_ADMIN_DIR?>/assets/aim_process', params, function(res) {
            if(res.is_success) {
                $('#area-vmservice').show();
                var msg = '<strong>Success !</strong> Saved your Data!';
                successMsg($('#tab_vmware #alert-save'), msg);
            } else {
                var msg = '<strong>Fail !</strong> '+res.msg;
                failMsg($('#tab_vmware #alert-save'), msg);
            }
        },'json');
    });


    var table = $('#data-table-vms').DataTable({
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
            "url": "/<?=SHOP_INFO_ADMIN_DIR?>/assets/vmservice",
            "type": "POST",
            "dataType": "JSON",
            "data": {
                "mode": "list",
                "assets_model_id": "<?=$row['am_id']?>",
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
            /*
            {
                extend: 'selected',
                text: '<i class="fal fa-edit"></i> Edit',
                name: 'edit',
                className: 'btn-primary btn-sm mr-1'
            },
            {
                text: '<i class="fal fa-plus mr-1"></i> Add',
                name: 'add',
                className: 'btn-success btn-sm'
            },
            */
            {
                text: '<i class="fal fa-edit mr-1"></i> Edit',
                name: 'add_custom',
                className: 'btn-primary btn-sm',
                action: function(e, dt, button, config) {

                    if(dt.rows(".selected").data().length < 1) {
                        var msg = "수정할 데이터 행을 선택해주세요!";
                        Swal.fire("Select Row !", msg, "error");
                        return;
                    }

                    var vms_id = dt.rows(".selected").data()[0].vms_id;
                    var params = {'mode':'update', 'am_id':'<?=$row['am_id']?>', 'vms_id':vms_id};
                    $.post('/<?=SHOP_INFO_ADMIN_DIR?>/assets/ajax_popup_vmservice', params, function(res) {
                        if(res.is_success) {
                            $('#modal-edit').find('.modal-body').html(res.msg);
                            $('#modal-edit').modal('show');
                            $('#btn-save-edit').show();
                        } else {
                            Swal.fire("Submit Error !", res.msg, "error");
                        }
                    },'json');
                }
            },
            {
                text: '<i class="fal fa-plus mr-1"></i> Add',
                name: 'add_custom',
                className: 'btn-success btn-sm',
                action: function(e, dt, button, config) {
                    
                    var params = {'mode':'insert', 'am_id':'<?=$row['am_id']?>'};
                    $.post('/<?=SHOP_INFO_ADMIN_DIR?>/assets/ajax_popup_vmservice', params, function(res) {
                        if(res.is_success) {
                            $('#modal-edit').find('.modal-body').html(res.msg);
                            $('#modal-edit').modal('show');
                            $('#btn-save-edit').show();
                        } else {
                            Swal.fire("Submit Error !", res.msg, "error");
                        }
                    },'json');

                }
            },
        ],


        "columns": [
            {
                "data": "vms_id",
                "placeholderMsg": "Server Generated ID",
                "visible": false,
                "type": "readonly"
            },
            {
                "data": "vms_ip_id",
                "placeholderMsg": "Server Generated ID",
                "visible": false,
                "type": "readonly"
            },
            {
                "data": "vms_ip_address",
                "pattern": "((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)(\.|$)){4}",
                "placeholderMsg": "e.g 192.168.119.310",
                "errorMsg": "*Invalid address - Enter valid ip.",
                "hoverMsg": "(Optional) - Ex: 192.168.119.310",
                "unique": true,
                "uniqueMsg": "Already exists. IP must be unique!",
            },
            {
                "data": "vms_vmware_ip",
                "placeholderMsg": "Server Generated ID",
                "type": "readonly",
            },
            {"data": "vms_name",},
            {"data": "vms_memo",},
            {"data": "sm_os_info"},
            {"data": "sm_db_info"},
            {"data": "sm_lang_info"},
            {"data": "sm_was_info"},
            {"data": "sm_usage",},
            {
                "data": "ip_memo",
            },
            {"data": "vms_status"}
            
        ],
        // Define columns class 
        "columnDefs": [
            { "visible": false, "targets": [0] },
            { "className": "text-center fs-nano", "targets": "_all" },
        ],

        onAddRow: function(dt, rowdata, success, error) {
            rowdata.mode = 'insert';
            rowdata.request = 'ajax';
            rowdata.vms_assets_model_id = '<?=$row['am_id']?>';
            $.post('/<?=SHOP_INFO_ADMIN_DIR?>/assets/vmservice_process', rowdata, function(res) {
                if(res.is_success) {
                    $('#data-table').DataTable().draw();
                    $('.modal').modal('hide');
                } else {
                    Swal.fire("Submit Error !", res.msg, "error");
                }
            },'json');
        },
        onEditRow: function(dt, rowdata, success, error) {
            rowdata.mode = 'update';
            rowdata.request = 'ajax';
            $.post('/<?=SHOP_INFO_ADMIN_DIR?>/assets/vmservice_process', rowdata, function(res) {
                if(res.is_success) {
                    $('#data-table').DataTable().draw();
                    success();
                } else {
                    Swal.fire("Submit Error !", res.msg, "error");
                }
            },'json');
        },
        onDeleteRow: function(dt, rowdata, success, error) {
            rowdata.mode = 'delete';
            rowdata.request = 'ajax';
            $.post('/<?=SHOP_INFO_ADMIN_DIR?>/assets/vmservice_process', rowdata, function(res) {
                if(res.is_success) {
                    $('#data-table').DataTable().draw();
                    success();
                } else {
                    Swal.fire("Submit Error !", res.msg, "error");
                }
            },'json');
        },
    });



    <?php if($idrac['mode'] == 'update'): ?>
    if($('#idrac_ip [name="ip_address"]').val()) {
        setCIDR('#idrac_ip '); 
    }
    <?php endif; ?>
    $('#idrac_ip [name="ip_address"]').focusout(function(e) {
        setCIDR('#idrac_ip '); 
    });
    <?php if($direct['mode'] == 'update'): ?>
    if($('#direct_ip [name="ip_address"]').val()) {
        setCIDR('#direct_ip '); 
    }
    <?php endif; ?>
    $('#direct_ip [name="ip_address"]').focusout(function(e) {
        setCIDR('#direct_ip '); 
    });




    $('.btn-save-ip').click(function(e) {
        var _target = $(this).data('id'); 

        if( _target != 'service' ) { 
            if( ! $('#'+_target+'_ip [name="ip_address"]').val()) {
                $('#'+_target+'_ip [name="ip_address"]').focus();
                return;
            }
            if( ! $('#'+_target+'_ip [name="ipc_location_id"]').val()) {
                setCIDR('#'+_target+' '); 
            }
        }

        var url = '/<?=SHOP_INFO_ADMIN_DIR?>/assets/aim_process';
        switch(_target) {
            case 'direct':
                url = '/<?=SHOP_INFO_ADMIN_DIR?>/assets/dim_process';
                break;
            case 'service':
                url = '/<?=SHOP_INFO_ADMIN_DIR?>/assets/service_process';
                /*
                if( ! $('#'+_target+'_ip [name="sm_usage"]').val()) {
                    $('#'+_target+'_ip [name="sm_usage"]').focus();
                    return;
                }
                */
                break;
        }
        
        var params = $('#form-'+_target).serialize();
        $.post(url, params, function(res) {
            if(res.is_success) {
                var msg = '<strong>Success !</strong> Saved your Data!';
                successMsg($('#'+_target+'_ip #alert-save'), msg);
            } else {
                var msg = '<strong>Fail !</strong> '+res.msg;
                failMsg($('#'+_target+'_ip #alert-save'), msg);
            }
        },'json');
    });



    $('.btn-delete-ip').click(function(e) {

        var _target = $(this).data('id'); 

        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            type: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, delete it!"
        }).then(function(result) {
            if (result.value) {
                var params = $('#form_del_'+_target).serialize();
                var url = $('#form_del_'+_target).attr('action');
                $.post(url, params, function(res) {
                    if(res.is_success) {
                        location.reload();
                    } else {
                        var msg = '<strong>Fail !</strong> '+res.msg;
                        failMsg($('#'+_target+'_ip #alert-save'), msg);
                    }
                },'json');
            }
        });
    });



    function successMsg(id, msg) {
        id.removeClass('border-danger text-danger').addClass('border-success text-success').html(msg).show();
    }
    function failMsg(id, msg) {
        id.removeClass('border-success text-success').addClass('border-danger text-danger').html(msg).show();
    }


    $('#btn_sa_alias').click(function(e) {
        if( ! $('#tab_alias [name="ip_address"]').val()) {
            $('#tab_alias [name="ip_address"]').focus();
            return;
        }
        if( ! $('#tab_alias [name="ipc_location_id"]').val()) {
            setCIDR('#tab_alias '); 
        }
    });

    var alias_table = $('#data-table-alias').DataTable({
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
            "url": "/<?=SHOP_INFO_ADMIN_DIR?>/assets/alias",
            "type": "POST",
            "dataType": "JSON",
            "data": {
                "mode": "list",
                "assets_model_id": "<?=$row['am_id']?>",
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
            {
                extend: 'selected',
                text: '<i class="fal fa-edit"></i> Edit',
                name: 'edit-cutom',
                className: 'btn-primary btn-sm mr-1',

                action: function(e, dt, button, config) {

                    var adata = dt.rows({
                        selected: true
                    });
                    var vms_id = adata.data()[0].vms_id;
                    
                    var params = {'mode':'update', 'am_id':'<?=$row['am_id']?>', 'vms_id':vms_id};
                    $.post('/<?=SHOP_INFO_ADMIN_DIR?>/assets/ajax_popup_alias', params, function(res) {
                        if(res.is_success) {
                            $('#modal-edit').find('.modal-body').html(res.msg);
                            $('#modal-edit').modal('show');
                        } else {
                            Swal.fire("Submit Error !", res.msg, "error");
                        }
                    },'json');

                }
            },
            {
                text: '<i class="fal fa-plus mr-1"></i> Add',
                name: 'add_custom',
                className: 'btn-success btn-sm',
                action: function(e, dt, button, config) {
                    
                    var params = {'mode':'insert', 'am_id':'<?=$row['am_id']?>'};
                    $.post('/<?=SHOP_INFO_ADMIN_DIR?>/assets/ajax_popup_alias', params, function(res) {
                        if(res.is_success) {
                            $('#modal-edit').find('.modal-body').html(res.msg);
                            $('#modal-edit').modal('show');
                        } else {
                            Swal.fire("Submit Error !", res.msg, "error");
                        }
                    },'json');

                }
            },
        ],


        "columns": [
            {
                "data": "vms_id",
                "placeholderMsg": "Server Generated ID",
                //"visible": false,
                "type": "readonly"
            },
            {
                "data": "vms_alias_id",
                "type": "select",
            },
            {
                "data": "vms_alias_ip",
                "placeholderMsg": "Server Generated ID",
                "type": "readonly",
            },
            {
                "data": "vms_name",
            },
            {
                "data": "ip_address",
                "pattern": "((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)(\.|$)){4}",
                "placeholderMsg": "e.g 192.168.119.310",
                "errorMsg": "*Invalid address - Enter valid ip.",
                "hoverMsg": "(Optional) - Ex: 192.168.119.310",
                "unique": true,
                "uniqueMsg": "Already exists. IP must be unique!",
            },
            {
                "data": "vms_memo",
            },
            {
                "data": "ip_memo",
            },
            
        ],
        // Define columns class 
        "columnDefs": [
            //{ "visible": false, "targets": [0] },
            { "className": "text-center", "targets": "_all" },
        ],
        onDeleteRow: function(dt, rowdata, success, error) {
            rowdata.mode = 'delete';
            rowdata.request = 'ajax';
            $.post('/<?=SHOP_INFO_ADMIN_DIR?>/assets/alias_process', rowdata, function(res) {
                if(res.is_success) {
                    success();
                } else {
                    Swal.fire("Submit Error !", res.msg, "error");
                }
            },'json');
        },
    });
     
    alias_table.on( 'responsive-resize', function ( e, datatable, columns ) {
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



    $('#btn-save-edit').click(function(e) {
        console.log('vmservice');
        var form = $('#vms_edit_form [name="vms_name"]');
        if( form.val().length < 1 ) {
            toggleValid(form, 'invalid', 'VMServeice 명을 입력해 주세요.');
            form.focus();
            return;
        }else {
            toggleValid(form, 'valid', '');
        }

        var form = $('#vms_edit_form [name="vms_ip_address"]');
        if( form.val().length < 1 ) {
            toggleValid(form, 'invalid', 'VMServeice IP 를 입력해 주세요.');
            form.focus();
            return;
        }else {
            toggleValid(form, 'valid', '');
        }

        /*
        form = $('#vms_edit_form [name="sm_usage"]');
        if( form.val().length < 1 ) {
            toggleValid(form, 'invalid', 'VMServeice 사용 용도를 입력해 주세요.');
            form.focus();
            return;
        }else {
            toggleValid(form, 'valid', '');
        }
        */

        var params = $('#vms_edit_form').serialize();
        $.post('/<?=SHOP_INFO_ADMIN_DIR?>/assets/vmservice_process', params, function(res) {
            if(res.is_success) {
                $('#data-table-vms').DataTable().ajax.reload();
                $('#modal-edit').modal('hide');
            } else {
                var msg = '<strong>Fail !</strong> '+res.msg;
                Swal.fire("Submit Error !", msg, "error");
            }
        },'json');
    });



    // service controller
    $('[name="sm_secure_conf"],[name="sm_secure_inte"],[name="sm_secure_avail"]').on('change', function(e) {
        calAssetLevel();
    });
    calAssetLevel();
});
</script>
