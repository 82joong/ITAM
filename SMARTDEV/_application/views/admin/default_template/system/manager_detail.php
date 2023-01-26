<?php 
//include left panel (navigation)
//follow the tree in inc/config.ui.php
require_once realpath(dirname(__FILE__).'/../').'/inc/config.ui.php';


$active_key = array(
    "system",               // 1 Depth menu
    "manager"          // 2(Sub) Depth menu
);
$page_nav[$active_key[0]]["active"] = true;
$page_nav[$active_key[0]]["sub"][$active_key[1]]["active"] = true;
include realpath(dirname(__FILE__).'/../').'/inc/nav.php';

$title_symbol = 'fa-cog';


if($this->_ADMIN_DATA['level'] < 9) {
    $req_passwd = 'required';
}else {
    $req_passwd = '';
}


?>



<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL -->
<style type="text/css">
</style>


<main id="js-page-content" role="main" class="page-content">

    <?php include realpath(dirname(__FILE__).'/../').'/inc/breadcrumbs.php'; ?>

    <div class="row">
        <form name='edit_form' id="edit_form" class="needs-validation" action="/<?=SHOP_INFO_ADMIN_DIR?>/system/manager_process" method="POST" novalidate autocomplete="off" >
        <input type="hidden" name="mode" value="<?=$mode?>">
        <input type="hidden" name="a_id" value="<?=$row['a_id']?>">

        <div class="col-xl-12">
            <div class="panel">
                <div class="panel-hdr">
                    <h2>Row <?=ucfirst($mode)?></h2>
                   
                    <div class="panel-toolbar">
                        <?=genDetailButton('manager', $mode);?>
                    </div>
                </div>

                <div class="panel-container show">
                    <div class="panel-content">

                        <div class="form-row form-group justify-content-md-center">


                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="validation_a_loginid">
                                    <span data-i18n="content.manager_id">Login ID </span>
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="validation_a_loginid" name="a_loginid" placeholder="Login ID" required value="<?=$row['a_loginid']?>">
                                <div class="invalid-feedback">
                                    Please provide a Login ID.
                                </div>
                            </div>


                            <div class="col-12 col-lg-8 form-group">
                                <label class="form-label" for="name-f" data-i18n="content.name">
                                    Admin Name
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text text-success">
                                            <i class="ni ni-user fs-xl"></i>
                                        </span>
                                    </div>
                                    <input type="text" aria-label="First name" class="form-control" name="a_firstname" placeholder="First name" id="name-f" value="<?=$row['a_firstname']?>">
                                    <input type="text" aria-label="Last name" class="form-control" name="a_lastname" placeholder="Last name" id="name-l" value="<?=$row['a_lastname']?>">
                                </div>
                            </div>


                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="validation_a_lastname">
                                    <span data-i18n="content.email">Email</span> 
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="validation_a_email" name="a_email" placeholder="Email" required="" value="<?=$row['a_email']?>">
                                <div class="invalid-feedback">
                                    Please provide a Email.
                                </div>
                            </div>


                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="validation_a_password">
                                    <span data-i18n="content.password">Password</span> 
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="password" class="form-control" id="validation_a_password" name="a_passwd" placeholder="Password" <?=$req_passwd?>>
                                <div class="invalid-feedback">
                                    Please provide a Password.
                                </div>
                            </div>


                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label">
                                    <span data-i18n="content.is_changed_password">Is Changed Password</span>
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <div class="custom-control custom-radio mb-2 mr-3">
                                        <input type="radio" class="custom-control-input" id="a_is_changed_pw_yes" name="a_is_changed_pw" value="YES" required <?php if($row['a_is_changed_pw'] == 'YES'): echo 'checked'; endif;?> >
                                        <label class="custom-control-label" for="a_is_changed_pw_yes">YES</label>
                                    </div>
                                    <div class="custom-control custom-radio mb-2">
                                        <input type="radio" class="custom-control-input" id="a_is_changed_pw_no" name="a_is_changed_pw" value="NO" required <?php if($row['a_is_changed_pw'] == 'NO'): echo 'checked'; endif;?> >
                                        <label class="custom-control-label" for="a_is_changed_pw_no">NO</label>
                                    </div>
                                </div>
                            </div>



                            <?php if($row['a_is_changed_pw'] == 'YES') : ?>
                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" data-i18n="content.changed_password_at">
                                    Changed Password At 
                                </label>
                                <input type="text" class="form-control" name="a_changed_pw_at" value="<?=$row['a_changed_pw_at']?>" disabled >
                            </div>
                            <?php endif; ?>




                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label" for="validation_a_level">
                                    Level 
                                    <span class="text-danger">*</span>
                                </label>
                                <?php if($_IS_SUPER == TRUE) : ?>
                                <select class="custom-select" name="a_level" required="">
                                <?php foreach($level_data as $level=>$name): ?>
                                    <?php
                                    $selected = '';
                                    if($row['a_level'] == $level) $selected = 'selected';
                                    ?>
                                    <option value="<?=$level?>" <?=$selected?>><?=$name?></option>
                                <?php endforeach;?>
                                </select>
                                <div class="invalid-feedback">
                                    Please provide a level.
                                </div>
                                <?php else :?>
                                <input type="text" class="form-control" name="a_level" value="<?=$row['a_level']?>" disabled >
                                <?php endif;?>
                            </div>


                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label">
                                    IP Filter 
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <div class="custom-control custom-radio mb-2 mr-3">
                                        <input type="radio" class="custom-control-input" id="a_ip_filter_yes" name="a_ip_filter" value="YES" required <?php if($row['a_ip_filter'] == 'YES'): echo 'checked'; endif;?> >
                                        <label class="custom-control-label" for="a_ip_filter_yes">YES</label>
                                    </div>
                                    <div class="custom-control custom-radio mb-2">
                                        <input type="radio" class="custom-control-input" id="a_ip_filter_no" name="a_ip_filter" value="NO" required <?php if($row['a_ip_filter'] == 'NO'): echo 'checked'; endif;?> >
                                        <label class="custom-control-label" for="a_ip_filter_no">NO</label>
                                    </div>
                                </div>
                            </div>



                            <div id="set_allow_ips" class="col-12 col-lg-8 mb-4 input-group" style="display:none;">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Allow IPs</span>
                                </div>
                                <textarea class="form-control" name="a_allow_ips" aria-label="With textarea"><?=$row['a_allow_ips']?></textarea>
                            </div>

                            <?php if($_IS_SUPER == TRUE) : ?>
                            <div class="col-12 mb-3 col-lg-8">
                                <label class="form-label">
                                    Auth Secret Code 
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend" style="width:60%;" >
                                        <input type="text" class="form-control" name="a_auth_secret" value="<?=$row['a_auth_secret']?>" disabled>
                                    </div>
                                    <div class="input-group-append">
                                        <div class="btn btn-danger shadow-0 waves-effect waves-themed" id="btn_reset_code">
                                            <i class="fal fa-undo"></i>
                                            Reset
                                        </div>
                                    </div>


                                    <div class="input-group-append">
                                        <div class="btn btn-info shadow-0 waves-effect waves-themed" id="btn_send_mail">
                                            <i class="fal fa-qrcode"></i>
                                            Send Email 
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label">
                                    Auth Created At 
                                </label>
                                <input type="text" class="form-control" name="a_auth_created_at" value="<?=$row['a_auth_created_at']?>" disabled >
                            </div>
                            <?php endif;?>



                            <?php if($mode == 'update') : ?>
                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label">
                                    Updated At 
                                </label>
                                <input type="text" class="form-control" name="a_updated_at" value="<?=$row['a_updated_at']?>" disabled >
                            </div>
                            
                            <div class="col-12 col-lg-8 mb-3">
                                <label class="form-label">
                                    Created At 
                                </label>
                                <input type="text" class="form-control" name="a_created_at" value="<?=$row['a_created_at']?>" disabled >
                            </div>
                            <?php endif;?>


                        </div>

                    </div>



                    <div class="panel-content py-2 rounded-bottom border-faded border-left-0 border-right-0 border-bottom-0 text-muted d-flex">

                        <?php if($mode == 'update') : ?>
                        <a href="javascript:outmember_admin();" class="btn btn-sm btn-warning waves-effect waves-themed">
                            <span class="fal fa-user-slash mr-1"></span> 탈퇴처리 
                        </a>
                        <?php endif;?>

                        <?=genDetailButton('manager', $mode);?>
                    </div>


                <div>
            </div>
        </div>

        </form>
    </div>
</main>



<form name='del_form' id="del_form" action="/<?=SHOP_INFO_ADMIN_DIR?>/system/manager_process" method="POST">
    <input type='hidden' name='mode' value='delete' />
    <input type="hidden" name="a_id" value="<?=$row['a_id']?>">
</form>




<script type="text/javascript">
$(document).ready(function() {
	$('[name="a_ip_filter"]').on('click', function() {
		if($(this).val() == 'YES') {
			$('#set_allow_ips').show();
		}else{
			$('#set_allow_ips').hide();
		}
	});


    $("#btn_reset_code").on("click", function() {
        Swal.fire({
            title: "OTP 비밀코드를 재생성 하시겠습니까?",
            text: "재생성된 비밀코드로 QRCode가 발급됩니다. 발급된 QRCode를 [Email] 버튼을 통해 사용자에게 발송하세요!",
            type: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes"
        }).then(function(result) {
            if (result.value) {

                var params = {'a_id':'<?=$row['a_id']?>'};
                $.post('/<?=SHOP_INFO_ADMIN_DIR?>/system/ajax_reset_code', params, function(res) {
                    if(res.is_success) {
                        $('[name="a_auth_secret"]').val(res.msg);
                        Swal.fire("Success!", "비밀코드가 재발급되었습니다.", "success");
                    } else {
                        Swal.fire("Submit Error !", res.msg, "error");
                    }
                },'json');
            }
        });
    });



    $("#btn_send_mail").on("click", function() {
        Swal.fire({
            title: "OTP QRCode를 발급하시겠습니까?",
            text: "발급된 QRCode를 등록된 이메일(<?=$row['a_email']?>)로 사용자에게 발송됩니다!",
            type: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes"
        }).then(function(result) {
            if (result.value) {

                var params = {'a_id':'<?=$row['a_id']?>'};
                $.post('/<?=SHOP_INFO_ADMIN_DIR?>/system/ajax_send_qrcode', params, function(res) {
                    if(res.is_success) {
                        Swal.fire("Success!", "메일 발송이 완료 되었습니다.", "success");
                    } else {
                        Swal.fire("Submit Error !", res.msg, "error");
                    }
                },'json');
            }
        });
    });

});

function outmember_admin() {
	if(confirm('탈퇴처리 하시겠습니까?')) {
		$('#del_form').submit();
	}
	return;
}

</script>
