<div class="row">
    <form name='edit_form' id="edit_form" class="needs-validation w-100" method="POST" novalidate autocomplete="off" >

    <input type="hidden" name="aim" value="<?=$aim_data['aim_id']?>">

    <input type="hidden" name="vm_mode" value="<?=$mode?>">
    <input type="hidden" name="vm_aim_id" value="<?=$aim_data['aim_id']?>">
    <input type="hidden" name="vm_ip_id"  value="<?=$row['ip_id']?>">
    <input type="hidden" name="vm_ip_class_id" value="<?=$row['ip_class_id']?>">
    <input type="hidden" name="vm_ip_class_type" value="<?=$row['ip_class_type']?>">
    <input type="hidden" name="vm_ip_class_category" value="<?=$row['ip_class_category']?>">

    <input type="hidden" name="idrac_mode" value="<?=$idrac['mode']?>">
    <input type="hidden" name="aim_id" value="<?=$idrac['aim_id']?>">
    <input type="hidden" name="ip_id" value="<?=$idrac['ip_id']?>">
    <input type="hidden" name="ip_class_id" value="<?=$idrac['ip_class_id']?>">
    <input type="hidden" name="ip_class_type" value="<?=$idrac['ip_class_type']?>">
    <input type="hidden" name="ip_class_category" value="<?=$idrac['ip_class_category']?>">
    <input type="hidden" name="set_valid" value="idrac">

    <div class="col-xl-12">
        <div class="panel">

            <div class="panel-container show">
                <div class="panel-content">
                    <div class="form-row form-group justify-content-md-center">


                        <div class="form-group col-12 mb-3">
                            <label class="form-label" for="vm_ip_address">
                                VMWare IPv4 Address 
                                <span class="text-danger">*</span>
                                <?=genNewButton('ipclass')?>
                            </label>
                            <?=getInputMask('ipv4', 'vm_ip_address', $row['ip_address'], $opt='readonly')?>
                        </div>


                        <div class="col-12 form-group area-type" id="area-assets">
                            <label class="form-label" for="am_id">
                                <span data-i18n="content.assets_name">Assets</span>
                                <span class="text-danger">*</span>
                                <?=genNewButton('assets')?>
                            </label>
                            <select class="select2-ajax form-control sel-ajax-aim" id="am_id" name="am_id" required>
                                <?php if( isset($aim_data) && sizeof($aim_data['aim_id']) > 0 ) : ?>
                                <option value="<?=$aim_data['am_id']?>">
                                    <?=$aim_data['am_name']?>
                                </option>
                                <?php endif; ?>
                            </select>
                            <span class="help-block">제품명 or 모델명 or 시리얼 검색 (vmware ip 할당되지 않은 기기만 검색)</span>
                            <div class="invalid-feedback"></div>
                        </div>



                        <div class="form-group col-12 mb-3">
                            <label class="form-label" for="am_vmware_name">
                                VMWare Name 
                                <span class="text-danger">*</span>
                            </label>
                            <?=getInputMask('normal', 'am_vmware_name', $aim_data['am_vmware_name'], $opt='required')?>
                            <span class="help-block">서비스 기능 및 역할에 대한 정보 상세 입력<span>
                        </div>
                        

                        <div class="form-group col-12 mb-3">
                            <label class="form-label" for="vm_ip_memo">
                                VMWare IP Memo 
                            </label>
                            <?=getTextArea('vm_ip_memo', $row['ip_memo'], $opt='')?>
                        </div>



                        <div class="form-group col-12 mt-3 mb-3">
                            <hr />
                        </div>



                        <div class="form-group col-12  mb-3">
                            <label class="form-label" for="ip_address">
                                iDrac IP 
                                <?=genNewButton('ipclass')?>
                            </label>
                            <?=getInputMask('ipv4', 'ip_address', $idrac['ip_address'], $opt='required')?>
                        </div>


                        <div class="row col-12 mb-1">
                            <div class="form-group col-4 pl-0">
                                <label class="form-label" for="ipc_location_id" data-i18n="content.location">
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
                            <label class="form-label" for="ip_memo">
                                iDrac IP Memo 
                            </label>
                            <?=gettextarea('ip_memo', $idrac['ip_memo'], $opt='')?>
                        </div>



                        <?php if($mode == 'update') : ?>
                        <div class="row col-12">
                            <div class="form-group col-6 pl-0">
                                <label class="form-label">
                                    Updated At 
                                </label>
                                <input type="text" class="form-control" name="ip_updated_at" value="<?=$row['ip_updated_at']?>" disabled >
                            </div>
                        
                            <div class="form-group col-6 pl-0">
                                <label class="form-label">
                                    Created At 
                                </label>
                                <input type="text" class="form-control" name="ip_created_at" value="<?=$row['ip_created_at']?>" disabled >
                            </div>
                        </div>
                        <?php endif;?>
                    </div>

                </div>

            <div>
        </div>
    </div>
    </form>
</div>





<script src="/admin_assets/js/select2.ajax.js?<?=filemtime(FCPATH.'admin_assets/js/select2.ajax.js')?>"></script>
<script type="text/javascript">


$(document).ready(function() {

    // INPUT MASK (ex. class="input-mask")
    $('.input-mask').inputmask();


    $('#edit_form [name="ip_address"]').focusout(function(e) {
        setCIDR('#edit_form '); 
        $('[name="ip_memo"]').val('');
    });
    <?php if($idrac['mode'] == 'update') : ?>
    if( $('#edit_form [name="ip_address"]').val() ) {
        setCIDR('#edit_form '); 
    }
    <?php endif; ?>


    $('[name="am_id"]').on('change', function(e) {
        e.preventDefault();
        $('[name="am_vmware_name"]').val('');
        $('[name="vm_ip_memo"]').val('');
    });

});
</script>
