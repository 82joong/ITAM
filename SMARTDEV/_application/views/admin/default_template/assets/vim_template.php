<div class="row">
    <form name='edit_form' id="edit_form" class="needs-validation w-100" method="POST" novalidate autocomplete="off" >

    <input type="hidden" name="set_valid" value="vmservice">
    <input type="hidden" name="type" value="vim">
    <input type="hidden" name="mode" value="<?=$mode?>">
    <input type="hidden" name="vim_id" value="<?=$vim_data['vim_id']?>">
    <input type="hidden" name="vms_id" value="<?=$vim_data['vms_id']?>">
    <input type="hidden" name="vms_vmware_ip" value="<?=$vm_data['ip_address']?>">

    <div class="col-xl-12">
        <div class="panel">

            <div class="panel-container show">
                <div class="panel-content">
                    <div class="form-row form-group justify-content-md-center">

                    <?php
                    /*
                    1. WMWare 선택 (assets_model_tb.am_vmware_name)
                        - 자산정보/ VMWare IP / 
                    2. VMService 입력 란 Show
                        - VMService IP (Row IP 고정)
                        - VMService Name / Memo / IP Memo 입력
                    */
                    ?>

                        <div class="col-12 form-group area-type" id="area-assets">
                            <label class="form-label" for="vms_assets_model_id">
                                VMWare 
                                <span class="text-danger">*</span>
                            </label>
                            <select class="select2-ajax form-control sel-ajax-vim" id="vms_assets_model_id" name="vms_assets_model_id" required>
                                <?php if( isset($vm_data) && sizeof($vm_data['am_id']) > 0 ) : ?>
                                <option value="<?=$vm_data['am_id']?>">
                                    <?=$vm_data['am_vmware_name']?>
                                </option>
                                <?php endif; ?>
                            </select>
                            <span class="help-block">vmware명 or 모델명 or 시리얼 검색 (vmware ip 할당된 기기만 검색)</span>
                            <div class="invalid-feedback"></div>
                        </div>



                        <div class="form-group col-12 mb-3 area-vms" style="display:none;">
                            <label class="form-label" for="vim_ip_address">
                                VMService IPv4 Address 
                                <span class="text-danger">*</span>
                                <?=genNewButton('ipclass')?>
                            </label>
                            <?=getInputMask('ipv4', 'vms_ip_address', $row['ip_address'], $opt='readonly')?>
                        </div>

                        <div class="form-group col-12 mb-3 area-vms" style="display:none;">
                            <label class="form-label" for="vms_name">
                                VMService Name 
                                <span class="text-danger">*</span>
                            </label>
                            <?=getInputMask('normal', 'vms_name', $vim_data['vms_name'], $opt='required')?>
                        </div>

                        <div class="form-group col-12 mb-3 area-vms" style="display:none;">
                            <label class="form-label" for="vms_memo">
                                VMService Memo 
                            </label>
                            <?=getTextarea('vms_memo', $vim_data['vms_memo'], $opt='')?>
                        </div>


                        <div class="form-group col-12 mb-3 area-vms" style="display:none;">
                            <label class="form-label" for="ip_memo">
                                IP Memo 
                            </label>
                            <?=getTextarea('ip_memo', $vim_data['ip_memo'], $opt='')?>
                        </div>


                    </div>
                </div> <!-- END .panel-content -->
            <div>
        </div>
    </div>
    </form>
</div>





<script src="/admin_assets/js/select2.ajax.js?<?=filemtime(FCPATH.'admin_assets/js/select2.ajax.js')?>"></script>
<script type="text/javascript">
$(document).ready(function() {

    <?php if($mode == 'update') : ?>
    $('.area-vms').show();
    <?php endif ; ?>


    $('[name="vms_assets_model_id"]').on('change', function(e) {
        e.preventDefault();
        $('.area-vms').show();
    });
});
</script>
