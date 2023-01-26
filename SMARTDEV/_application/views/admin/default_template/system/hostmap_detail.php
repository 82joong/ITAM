<?php 
//include left panel (navigation)
//follow the tree in inc/config.ui.php
require_once realpath(dirname(__FILE__).'/../').'/inc/config.ui.php';


$active_key = array(
    "system",               // 1 Depth menu
    "hostmap"          // 2(Sub) Depth menu
);
$page_nav[$active_key[0]]["active"] = true;
$page_nav[$active_key[0]]["sub"][$active_key[1]]["active"] = true;
include realpath(dirname(__FILE__).'/../').'/inc/nav.php';

$title_symbol = 'fa-cog';
?>



<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL -->
<style type="text/css">
</style>


<main id="js-page-content" role="main" class="page-content">

    <?php include realpath(dirname(__FILE__).'/../').'/inc/breadcrumbs.php'; ?>

    <div class="row">
        <form name='edit_form' id="edit_form" class="needs-validation w-100" action="/<?=SHOP_INFO_ADMIN_DIR?>/system/hostmap_process" method="POST" novalidate autocomplete="off" >
        <input type="hidden" name="mode" value="<?=$mode?>">
        <input type="hidden" name="vhm_id" value="<?=$row['vhm_id']?>">
        <input type="hidden" name="vhm_vmservice_name" value="<?=$row['vhm_vmservice_name']?>">

        <div class="col-xl-12">
            <div class="panel">
                <div class="panel-hdr">
                    <h2>Row <?=ucfirst($mode)?></h2>
                   
                    <div class="panel-toolbar">
                        <?=genDetailButton('hostmap', $mode);?>
                    </div>
                </div>

                <div class="panel-container show">
                    <div class="panel-content">

                        <div class="form-row form-group justify-content-md-center">

                            <div class="col-12 col-lg-8 mb-3 alert alert-success alert-dismissible fade show pl-5" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                                </button>
                                <strong>Syslog Host Name 와 VMService Name 매핑</strong> <br />
                                - ElasticSearch에 수집된 syslog 데이터와 매핑위해 서비스를 선택. <br />
                                - ELK Host Name은 Cron의 해주기적으로 추가된 Host Insert 됨.
                            </div>

                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="validation_vhm_vmservice_name">
                                    <span data-i18n="content.vmservice_name">VMService Name</span>
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="select2-ajax form-control sel-ajax-service" id="vhm_vmservice_id" name="vhm_vmservice_id" required>
                                    <?php if( isset($row['vhm_service_id']) && sizeof($row['vhm_vmservice_id']) > 0 ) : ?>
                                    <option value="<?=$row['vhm_vmservice_id']?>">
                                        <?=$row['vhm_vmservice_name']?>
                                    </option>
                                    <?php endif; ?>
                                </select>

                                <div class="invalid-feedback">
                                    Please provide a VMService Name.
                                </div>

                                <code>서비스현황 > 서비스리스트 [Service Name] 검색해서 매핑 -> 서비스상세 Syslog 데이터 노출</code>
                            </div>


                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="validation_vhm_elk_host_name">
                                    <span data-i18n="content.elk_host_name">ELK Host Name</span>
                                </label>
                                <input type="text" class="form-control" id="vhm_elk_host_name" name="vhm_elk_host_name" placeholder="ELK Host Name" required value="<?=$row['vhm_elk_host_name']?>" disabled >
                            </div>


                            <?php if($mode == 'update') : ?>
                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label">
                                    Updated At 
                                </label>
                                <input type="text" class="form-control" name="vhm_updated_at" value="<?=$row['vhm_updated_at']?>" disabled >
                            </div>
                            
                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label">
                                    Created At 
                                </label>
                                <input type="text" class="form-control" name="vhm_created_at" value="<?=$row['vhm_created_at']?>" disabled >
                            </div>
                            <?php endif;?>


                        </div>

                    </div>



                    <div class="panel-content py-2 rounded-bottom border-faded border-left-0 border-right-0 border-bottom-0 text-muted d-flex">
                        <a href="javascript:delRow();" class="btn btn-sm btn-warning waves-effect waves-themed">
                            <span class="fal fa-times-square mr-1"></span> Delete 
                        </a>
                        <?=genDetailButton('hostmap', $mode);?>
                    </div>


                <div>
            </div>
        </div>

        </form>
    </div>
</main>



<form name='del_form' id="del_form" action="/<?=SHOP_INFO_ADMIN_DIR?>/system/hostmap_process" method="POST">
    <input type='hidden' name='mode' value='delete' />
    <input type="hidden" name="vhm_id" value="<?=$row['vhm_id']?>">
</form>


<script type="text/javascript">
$(document).ready(function() {
	
    $('[name="vhm_vmservice_id"]').on('change', function(e) {
        e.preventDefault();
        $('[name="vhm_vmservice_name"]').val($('#select2-vhm_vmservice_id-container').text());
    });
});

function delRow() {

    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!"
    }).then(function(result) {
        if (result.value) {
            $('#del_form').submit(); 
            Swal.fire("Deleted!", "Your file has been deleted.", "success");
        }
    });

}
</script>
