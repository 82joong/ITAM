<div class="row">
    <form name='edit_form' id="edit_form" class="needs-validation w-100" method="POST" novalidate autocomplete="off" >

    <input type="hidden" name="mode" value="<?=$mode?>">
    <input type="hidden" name="type" value="dim">
    <input type="hidden" name="dim_id" value="<?=$dim_data['dim_id']?>">
    <input type="hidden" name="set_valid" value="direct">

    <input type="hidden" name="ip_id" value="<?=$row['ip_id']?>">
    <input type="hidden" name="ip_class_id" value="<?=$row['ip_class_id']?>">
    <input type="hidden" name="ip_class_type" value="<?=$row['ip_class_type']?>">
    <input type="hidden" name="ip_class_category" value="<?=$row['ip_class_category']?>">

    <div class="col-xl-12">
        <div class="panel">

            <div class="panel-container show">
                <div class="panel-content">
                    <div class="form-row form-group justify-content-md-center">


                        <div class="form-group col-12 mb-3" >
                            <label class="form-label" for="vim_ip_address">
                                IP Address 
                                <span class="text-danger">*</span>
                                <?=genNewButton('ipclass')?>
                            </label>
                            <?=getInputMask('ipv4', 'ip_address', $row['ip_address'], $opt='readonly')?>
                        </div>


                        <div class="col-12 form-group area-type">
                            <label class="form-label" for="am_id">
                                Assets 
                                <span class="text-danger">*</span>
                            </label>
                            <select class="select2-ajax form-control sel-ajax-dim" id="am_id" name="am_id" required>
                                <?php if( isset($dim_data) && sizeof($dim_data['am_id']) > 0 ) : ?>
                                <option value="<?=$dim_data['am_id']?>">
                                    <?=$dim_data['am_name']?>
                                </option>
                                <?php endif; ?>
                            </select>
                            <span class="help-block">assets 명 or 모델명 or 시리얼 검색 (할당 가능 기기만 검색)</span>
                            <div class="invalid-feedback"></div>
                        </div>


                        <div class="form-group col-12 mb-3">
                            <label class="form-label" for="ip_memo">
                                IP Memo 
                            </label>
                            <?=getTextarea('ip_memo', $dim_data['ip_memo'], $opt='')?>
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
    setCIDR('#edit_form ');
});
</script>
