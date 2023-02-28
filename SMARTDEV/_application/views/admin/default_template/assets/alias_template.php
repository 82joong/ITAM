<div class="row">

    <form name='edit_form' id="edit_form" class="needs-validation" method="POST" action="/<?=SHOP_INFO_ADMIN_DIR?>/assets/alias_process"  >

        <input type="hidden" name="am_id" value="<?=$am_data['am_id']?>">
        <input type="hidden" name="vms_assets_model_id" value="<?=$am_data['am_id']?>">
        <input type="hidden" name="am_assets_type_id" value="<?=$am_data['am_assets_type_id']?>">
        <input type="hidden" name="vms_id" value="<?=$row['vms_id']?>">
        <input type="hidden" name="ip_id" value="<?=$row['ip_id']?>">
        <input type="hidden" name="ip_class_id" value="">
        <input type="hidden" name="ip_class_type" value="">
        <input type="hidden" name="ip_class_category" value="">
        <input type="hidden" name="mode" value="<?=$mode?>">
        <input type="hidden" name="type" value="alias">
        <input type="hidden" name="set_valid" value="vmservice">

        <div class="col-xl-12">
            <div class="panel">

                <div class="panel-container show">
                    <div class="panel-content">


                        <div class="form-row form-group justify-content-md-center">


                            <div class="form-group col-12 mb-3">
                                <label class="form-label" for="vms_alias_id">
                                    <span data-i18n="content.vmservice">VMService</span> 
                                    <span class="text-danger">*</span>
                                </label>
                                <?=$sel_services?> 

                                <?php if(isset($from) && $from == 'iptotal') : ?>
                                <select class="select2-ajax form-control sel-ajax-service" id="vms_alias_id" name="vms_alias_id" required>
                                    <?php if( isset($vim_data) && sizeof($vim_data['vms_alias_id']) > 0 ) : ?>
                                    <option value="<?=$vim_data['vms_alias_id']?>">
                                        <?=$alias['vms_name']?>
                                    </option>
                                    <?php endif; ?>
                                </select>
                                <?php endif; ?>

                                <span class="invalid-feedback">Please provide a VMService.</span>
                            </div>



                            <div class="form-group col-12 mb-3">
                                <label class="form-label" for="ip_address">
                                    Alias IP 
                                    <span class="text-danger">*</span>
                                </label>
                                <?=getInputMask('ipv4', 'ip_address', $row['ip_address'], 'required')?>
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
                                <label class="form-label" for="vms_name">
                                    (Alias) Name 
                                    <span class="text-danger">*</span>
                                </label>
                                <?=getInputMask('normal', 'vms_name', $row['vms_name'], 'required')?>
                            </div>


                            <div class="form-group col-12 mb-3">
                                <label class="form-label" for="vms_memo" data-i18n="content.memo">
                                    Memo 
                                </label>
                                <?=getTextArea($name='vms_memo', $row['vms_memo'])?>
                                <span class="help-block">자산에 대한 역할 및 기능에 대한 상세 서술</span>
                            </div>

                            <?php if( ! isset($from) || $from != 'iptotal' ) : ?>
                            <div class="form-group col-12 mb-1">
                                <button id="btn_add_alias" class="btn btn-primary btn-sm btn-block waves-effect waves-themed">
                                    <span><i class="fal fa-arrow-square-down mr-2"></i><?=ucfirst($mode)?> Alias</span>
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>


<?php if(isset($from) && $from == 'iptotal') : ?>
<script src="/admin_assets/js/select2.ajax.js?<?=filemtime(FCPATH.'admin_assets/js/select2.ajax.js')?>"></script>
<?php endif; ?>
<script type="text/javascript">
$(document).ready(function() {

    $('.input-mask').inputmask();

    $('#modal-edit [name="ip_address"]').focusout(function(e) {
        setCIDR('#modal-edit '); 
    });


    if($('#modal-edit [name="ip_address"]').val()) {
        setCIDR('#modal-edit '); 
    }

});
</script>
