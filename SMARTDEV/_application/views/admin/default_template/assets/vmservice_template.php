<style type="text/css">
.select2-container {z-index:9999 !important;}
</style>



<div class="row">

    <form name='vms_edit_form' id="vms_edit_form" class="needs-validation" method="POST" action="/<?=SHOP_INFO_ADMIN_DIR?>/assets/vmservice_process"  >

        <input type="hidden" name="am_id" value="<?=$am_data['am_id']?>">
        <input type="hidden" name="vms_assets_model_id" value="<?=$am_data['am_id']?>">
        <input type="hidden" name="am_assets_type_id" value="<?=$am_data['am_assets_type_id']?>">
        <input type="hidden" name="vms_id" value="<?=$row['vms_id']?>">
        <input type="hidden" name="ip_id" value="<?=$row['ip_id']?>">
        <input type="hidden" name="ip_class_id" value="">
        <input type="hidden" name="ip_class_type" value="">
        <input type="hidden" name="ip_class_category" value="">
        <input type="hidden" name="mode" value="<?=$mode?>">
        <input type="hidden" name="set_valid" value="vmservice">

        <div class="col-xl-12">
            <div class="panel">

                <div class="panel-container show">
                    <div class="panel-content">


                        <div class="form-row form-group justify-content-md-center">


                            <div class="row col-12 mb-3">

                                <div class="form-group col-8 pl-0">
                                    <label class="form-label" for="vms_name">
                                        VMService Name 
                                        <span class="text-danger">*</span>
                                    </label>
                                    <?=getInputMask('normal', 'vms_name', $row['vms_name'], 'required')?>
                                    <span class="invalid-feedback">Please insert VMService Name.</span>
                                </div>

                                <div class="form-group col-4 pl-0">
                                    <label class="form-label" for="vms_status">
                                        Status 
                                        <span class="text-danger">*</span>
                                    </label>
                                    <?=getSelect($status_type, 'vms_status', $row['vms_status'], 'required')?>
                                    <span class="invalid-feedback">Please insert VMService Status.</span>
                                </div>
                            </div>


                            <div class="form-group col-12 mb-3">
                                <label class="form-label" for="vms_memo" data-i18n="content.vms_memo">
                                    VMS Memo 
                                </label>
                                <?=getTextArea($name='vms_memo', $row['vms_memo'])?>
                            </div>



                            <div class="form-group col-12 mb-3">
                                <label class="form-label" for="vms_ip_address">
                                    VMService IP 
                                    <span class="text-danger">*</span>
                                </label>
                                <?=getInputMask('ipv4', 'vms_ip_address', $row['ip_address'], 'required')?>
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

                            <div class="form-group col-12 mb-3">
                                <label class="form-label" for="ip_memo" data-i18n="content.memo">
                                    IP Memo 
                                </label>
                                <?=getTextArea($name='ip_memo', $row['ip_memo'])?>
                                <span class="help-block">자산에 대한 역할 및 기능에 대한 상세 서술</span>
                            </div>



                            <div class="form-group col-12 mb-3">
                                <label class="form-label" for="sm_usage" data-i18n="content.usage">
                                    VMService Usage 
                                </label>
                                <?=getInputMask('normal', 'sm_usage', $sm_data['sm_usage'])?>
                            </div>



                            <div class="row col-12 mb-1">
                                <div class="form-group col-6 pl-0">
                                    <label class="form-label" for="sm_os_info">
                                        OS. Info 
                                    </label>
                                    <?=getInputMask('normal', 'sm_os_info', $sm_data['sm_os_info'])?>
                                </div>
                                <div class="form-group col-6 pr-0">
                                    <label class="form-label" for="sm_db_info">
                                        DB. Info
                                    </label>
                                    <?=getInputMask('normal', 'sm_db_info', $sm_data['sm_db_info'])?>
                                </div>
                            </div>


                            <div class="row col-12 mb-1">
                                <div class="form-group col-6 pl-0">
                                    <label class="form-label" for="sm_lang_info">
                                        LANG. Info 
                                    </label>
                                    <?=getInputMask('normal', 'sm_lang_info', $sm_data['sm_lang_info'])?>
                                </div>
                                <div class="form-group col-6 pr-0">
                                    <label class="form-label" for="sm_was_info">
                                        WAS. Info
                                    </label>
                                    <?=getInputMask('normal', 'sm_was_info', $sm_data['sm_was_info'])?>
                                </div>
                            </div>


                            <div class="row col-12 mb-1">
                                <div class="form-group col-4 pl-0">
                                    <label class="form-label" for="sm_manage_team" data-i18n="content.manage_team">
                                        Manage Team 
                                    </label>

                                    <select class="select2-ajax form-control sel-ajax-dept" id="sm_mangae_team" name="sm_manage_team">
                                        <?php if( isset($sm_data['sm_manage_team']) && strlen($sm_data['sm_manage_team']) > 0 ) : ?>
                                        <option value="<?=$sm_data['sm_manage_team']?>">
                                            <?=$sm_data['sm_manage_team']?>
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
                                        <?php if( isset($sm_data['sm_master_manager']) && strlen($sm_data['sm_master_manager']) > 0 ) : ?>
                                        <option value="<?=$sm_data['sm_master_manager']?>">
                                            <?=$sm_data['sm_master_manager']?>
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
                                        <?php if( isset($sm_data['sm_sub_manager']) && strlen($sm_data['sm_sub_manager']) > 0 ) : ?>
                                        <option value="<?=$sm_data['sm_sub_manager']?>">
                                            <?=$sm_data['sm_sub_manager']?>
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

                            <div class="row col-12 border-bottom border-success pt-3">

                                <div class="form-group col-2 pl-0 pr-0">
                                    <label class="form-label" for="sm_secure_conf" data-i18n="secure.confidentiality">
                                        Confidentiality
                                    </label>
                                    <?=getInputMask('unit_number', 'sm_secure_conf', $sm_data['sm_secure_conf'])?>
                                </div>
                                <div class="form-group col-2 pl-0 pr-0">
                                    <label class="form-label" for="sm_secure_inte" data-i18n="secure.integrity">
                                        Integrity
                                    </label>
                                    <?=getInputMask('unit_number', 'sm_secure_inte', $sm_data['sm_secure_inte'])?>
                                </div>
                                <div class="form-group col-2 pl-0 pr-0">
                                    <label class="form-label" for="sm_secure_avail" data-i18n="secure.availability">
                                        Availability  
                                    </label>
                                    <?=getInputMask('unit_number', 'sm_secure_avail', $sm_data['sm_secure_avail'])?>
                                </div>
                                <div class="form-group col-2 pl-0 pr-0">
                                    <label class="form-label" for="sm_important_score" data-i18n="secure.important_score">
                                        Important Score 
                                    </label>
                                    <?=getInputMask('decimal_number', 'sm_important_score', $sm_data['sm_important_score'], 'readonly')?>
                                </div>
                                <div class="form-group col-4 pr-0">
                                    <label class="form-label" for="important_level" data-i18n="secure.important_level">
                                        Important Level 
                                    </label>
                                    <?=getInputMask('normal', 'sm_important_level', $sm_data['sm_important_level'], 'readonly')?>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>


<script src="/admin_assets/js/i18n/i18n.js"></script>
<script src="/admin_assets/js/select2.ajax.js?<?=filemtime(FCPATH.'admin_assets/js/select2.ajax.js')?>"></script>
<script type="text/javascript">
$(document).ready(function() {


    $.i18n.init({
        resGetPath: '/admin_assets/js/i18n/locals/__lng__.json',
        load: 'unspecific',
        fallbackLng: false,
        //lng: 'en' 
        lng: 'ko' 
    }, function (t){
        $('[data-i18n]').i18n();
    });



    $('.input-mask').inputmask();

    $('#modal-edit [name="vms_ip_address"]').focusout(function(e) {
        setCIDR('#modal-edit ', 'vms_ip_address'); 
    });

    if($('#modal-edit [name="vms_ip_address"]').val()) {
        setCIDR('#modal-edit ', 'vms_ip_address'); 
    }

    $('[name="sm_secure_conf"],[name="sm_secure_inte"],[name="sm_secure_avail"]').on('change', function(e) {
        calAssetLevel();
    });
    calAssetLevel();
});
</script>
