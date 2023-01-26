<div class="row">
    <form name='edit_form' id="edit_form" class="needs-validation w-100" method="POST" novalidate autocomplete="off" >
    <input type="hidden" name="mode" value="<?=$mode?>">
    <input type="hidden" name="pim_id" value="<?=$pim_data['pim_id']?>">
    <input type="hidden" name="ip_id" value="<?=$row['ip_id']?>">
    <input type="hidden" name="ip_class_id" value="<?=$row['ip_class_id']?>">
    <input type="hidden" name="ip_class_type" value="<?=$row['ip_class_type']?>">
    <input type="hidden" name="ip_class_category" value="<?=$row['ip_class_category']?>">
    <input type="hidden" name="set_valid" value="direct">

    <div class="col-xl-12">
        <div class="panel">

            <div class="panel-container show">
                <div class="panel-content">
                    <div class="form-row form-group justify-content-md-center">
                        <div class="form-group col-12 mb-3">
                            <label class="form-label" for="ip_address">
                                IPv4 Address 
                                <span class="text-danger">*</span>
                                <?=genNewButton('ipclass')?>
                            </label>
                            <?=getInputMask('ipv4', 'ip_address', $row['ip_address'], $opt='readonly')?>
                        </div>


                        <div class="col-12 form-group">
                            <label class="form-label" for="pim_people_id">
                                Employee
                                <span class="text-danger">*</span>
                                <?=genLinkButton('employee', $row['pim_people_id'])?>
                                <?=genNewButton('employee')?>
                            </label>
                            <select class="select2-ajax form-control sel-ajax-people" id="pim_people_id" name="pim_people_id" required>
                                <?php if( isset($pim_data) && sizeof($pim_data['pim_id']) > 0 ) : ?>
                                <option value="<?=$pim_data['pim_people_id']?>">
                                    <?=$pim_data['pp_name']?>
                                </option>
                                <?php endif; ?>
                            </select>
                            <span class="help-block">아이디 or 이메일 or 이름 검색</span>
                            <div class="invalid-feedback"></div>
                        </div>


                        <div class="form-group col-12 mb-3">
                            <label class="form-label" for="ip_memo">
                                Memo 
                            </label>
                            <?=getTextArea('ip_memo', $row['ip_memo'], $opt='')?>
                            <span class="help-block">개인에게 할당되는 IP에 대한 장비(device)정보 상세 입력<span>
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

});
</script>
