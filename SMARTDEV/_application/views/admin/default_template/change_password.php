                <div class="page-content-wrapper bg-transparent m-0">
                    <div class="flex-1" style="background: url(/shop_assets/img/svg/pattern-1.svg) no-repeat center bottom fixed; background-size: cover;">
                        <div class="container py-4 py-lg-5 my-lg-5 px-4 px-sm-0">
                            <div class="row">
                                <div class="col-xl-12">
                                    <h2 class="fs-xxl fw-500 mt-4 text-white text-center">
                                        Change Your Password 
                                    </h2>
                                </div>
                                <div class="col-xl-6 ml-auto mr-auto">
                                    <div class="card p-4 rounded-plus bg-faded">
                                        <div class="alert alert-danger" role="alert">
                                            <strong>
                                                <i class="fal fa-engine-warning"></i> 
                                                Your password must be 10-20 characters long, contain letters and numbers, special characters and must not contain spaces, or emoji. <br />
영문, 숫자, 특수문자를 혼합하여 최소 10자리 ~ 최대 20자리 이내로 입력해주세요. (공백, 이모지 등 제외)
                                            </strong>
                                        </div>
                                        <form name="adminLoginForm" action="/<?=SHOP_INFO_ADMIN_DIR?>/main/change_password_action" method="POST">
		                                    <input type='hidden' name='a_id' value="<?=$admin['a_id']?>" />
                                            <div class="form-group">
                                                <label class="form-label" for="new_password">New Password</label>
                                                <input type="password" id="new_password" class="form-control" name="new_password" placeholder="Minimum 10 characters" required autocomplete="off">
                                                <div class="invalid-feedback">Sorry, you missed this one.</div>
                                            </div>

                                            <div class="form-group">
                                                <label class="form-label" for="confirm_password">Confirm Password</label>
                                                <input type="password" id="confirm_password" class="form-control" name="confirm_password" placeholder="Confirm Password" required autocomplete="off">
                                                <div class="invalid-feedback">Sorry, you missed this one.</div>
                                            </div>
                                            
                                            <div class="row no-gutters">
                                                <div class="col-md-12 ml-auto text-right">
                                                    <button id="btn-change-pwd" type="submit" class="btn btn-block btn-danger btn-lg mt-3">
                                                        Change Password
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="position-absolute pos-bottom pos-left pos-right p-3 text-center text-white">
                            2020 © SmartAdmin by&nbsp;<a href='https://www.gotbootstrap.com' class='text-white opacity-40 fw-500' title='gotbootstrap.com' target='_blank'>gotbootstrap.com</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </body>
</html>


<script src="<?=$assets_dir?>/js/notifications/sweetalert2/sweetalert2.bundle.js"></script>
<script type="text/javascript">


function validateConfirmPW() {
    if( ! $('#confirm_password').val() ) return false;

    if($('#confirm_password').val() !== $('#new_password').val()) {
        toggleValid($('#confirm_password'),'invalid', '입력하신 비밀번호가 일치 하지 않습니다.');
        return true;
    }else {
        toggleValid($('#confirm_password'),'valid', '');
        return false;
    }
}

function validatePW() {
    var in_pw = $('#new_password').val();
    if(in_pw.length < 1) return false;
    
    $.post('/<?=SHOP_INFO_ADMIN_DIR?>/main/ajax_validate_password', {'in_pw':in_pw}, function(res) {
        if(res.is_success) {
            toggleValid($('#new_password'),'valid', '');
            return true;
        } else {
            toggleValid($('#new_password'),'invalid', res.msg);
            return false;
        }
    },'json');
}



$(document).ready(function(){

    $('#new_password').focusout(function(e) {
        validatePW(); 
    });
    $('#confirm_password').focusout(function(e) {
        validateConfirmPW(); 
    });
    /*
    $('#btn-change-pwd').click(function(e) {
        //e.preventDefault();

        console.log(validatePW());
        console.log(validateConfirmPW());

        if(validatePW() == false || validateConfirmPW() == false) {
            console.log('FALSE');
            return;
        }
         
        console.log('Success');
    });
    */
});




</script>
