<div class="row">
    <form name='edit_form' id="edit_form" class="needs-validation w-100" method="POST" novalidate autocomplete="off" >

    <input type="hidden" name="set_valid" value="idrac">
    <input type="hidden" name="mode" value="<?=$mode?>">
    <input type="hidden" name="aim_id" value="<?=$aim_data['aim_id']?>">
    <input type="hidden" name="ip_id" value="<?=$row['ip_id']?>">
    <input type="hidden" name="ip_class_id" value="<?=$row['ip_class_id']?>">
    <input type="hidden" name="ip_class_type" value="<?=$row['ip_class_type']?>">
    <input type="hidden" name="ip_class_category" value="<?=$row['ip_class_category']?>">


    <div class="col-xl-12">
        <div class="panel">

            <div class="panel-container show">
                <div class="panel-content">
                    <div class="form-row form-group justify-content-md-center">


                        <div class="form-group col-12  mb-3">
                            <label class="form-label" for="ip_address">
                                iDrac IP 
                                <span class="text-danger">*</span>
                            </label>
                            <?=getInputMask('ipv4', 'ip_address', $row['ip_address'], $opt='readonly')?>
                        </div>


                        <div class="col-12 form-group area-type" id="area-assets">
                            <label class="form-label" for="am_id">
                                <span data-i18n="content.assets_name">Assets</span> 
                                <span class="text-danger">*</span>
                                <?=genNewButton('assets')?>
                            </label>
                            <select class="select2-ajax form-control sel-ajax-idrac" id="am_id" name="am_id" required>
                                <?php if( isset($aim_data) && sizeof($aim_data['am_id']) > 0 ) : ?>
                                <option value="<?=$aim_data['am_id']?>">
                                    <?=$aim_data['am_name']?>
                                </option>
                                <?php endif; ?>
                            </select>
                            <span class="help-block">제품명 or 모델명 or 시리얼 검색 (iDrac IP 할당되지 않은 기기만 검색)</span>
                            <div class="invalid-feedback"></div>
                        </div>



                        <div class="form-group col-12 mb-3">
                            <label class="form-label" for="ip_memo">
                                iDrac IP Memo 
                            </label>
                            <?=getTextArea('ip_memo', $row['ip_memo'], $opt='')?>
                        </div>



                        <?php if($mode == 'update') : ?>
                        <div class="row col-12">
                            <div class="form-group col-6 pl-0">
                                <label class="form-label" data-i18n="content.updated_at">
                                    Updated At 
                                </label>
                                <input type="text" class="form-control" name="ip_updated_at" value="<?=$row['ip_updated_at']?>" disabled >
                            </div>
                        
                            <div class="form-group col-6 pl-0">
                                <label class="form-label" data-i18n="content.created_at">
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

    if( $('#edit_form [name="ip_address"]').val() ) {
        setCIDR('#edit_form '); 
    }
});
</script>
