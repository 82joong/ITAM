        <div class="blankpage-form-field">
            <div class="page-logo m-0 w-100 align-items-center justify-content-center rounded border-bottom-left-radius-0 border-bottom-right-radius-0 px-4">
                <a href="javascript:void(0)" class="page-logo-link press-scale-down d-flex align-items-center">
                    <img src="<?=$assets_dir?>/img/<?=LOGO?>" alt="<?=ADMIN_SITE_NAME?>" aria-roledescription="logo" style=width:40px;"">
                    <span class="page-logo-text mr-1"><?=ADMIN_SITE_NAME?></span>
                    <i class="fal fa-angle-down d-inline-block ml-1 fs-lg color-primary-300"></i>
                </a>
            </div>
            <div class="card p-4 border-top-left-radius-0 border-top-right-radius-0">
                <form name="loginActionForm" action="/<?=SHOP_INFO_ADMIN_DIR?>/main/login_action" method="POST">
		            <input type='hidden' name='url' value="<?=isset($url) ? $url : ''?>" />
                    <div class="form-group">
                        <label class="form-label" for="username">Admin ID</label>
                        <input type="text" id="username" class="form-control" name="admin_id" required placeholder="Your ID" value="">
                        <?php /*?>
                        <span class="help-block">
                            Your unique admin id to app
                        </span>
                        <?php */?>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <input type="password" id="password" class="form-control" name="admin_pw" required placeholder="Password" value="">
                        <?php /*?>
                        <span class="help-block">
                            Your password
                        </span>
                        <?php */?>
                    </div>

                    <?php if(USE_OTP == TRUE) : ?>
                    <div class="form-group">
                        <label class="form-label" for="otp">OTP</label>
                        <input type="password" id="otp" class="form-control" name="otp" required placeholder="OTP" value="" maxlength="6">
                    </div>
                    <?php endif; ?>


                    <?php /* ========== ?>
                    <div class="form-group text-left">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="rememberme">
                            <label class="custom-control-label" for="rememberme"> Remember me for the next 30 days</label>
                        </div>
                    </div>
                    */ ?>

                    <button type="submit" class="btn btn-danger float-right">Secure login</button>
                </form>
            </div>
            <?php /*?>
            <div class="blankpage-footer text-center">
                <a href="#"><strong>Recover Password</strong></a> | <a href="/account/join"><strong>Register Account</strong></a>
            </div>
            <?php */?>


            <div class="blankpage-footer text-center">
                <div class="text-white">
                    <div class="fs-xl fw-500 mb-3">[담당] 정보보안인프라팀</div>
                    <div class="opacity-60">
                    - 접근권한 설정 및 계정 생성 문의 -<br /> 
                    - OTP 인증 생성 및 재발급 -<br />
                    </div>
                </div>
            </div>
        </div>


        <?php /*?>
        <div class="login-footer p-2">
            <div class="row">
                <div class="col col-sm-12 text-center">
                    <i><strong>System Message:</strong> You were logged out from 198.164.246.1 on Saturday, March, 2017 at 10.56AM</i>
                </div>
            </div>
        </div>
        <?php */?>

    </body>
</html>


<script src="<?=$assets_dir?>/js/notifications/sweetalert2/sweetalert2.bundle.js"></script>
<script type="text/javascript">
$(document).ready(function(){
    $("input[name='admin_id']").focus();


    $("input[name='admin_pw']").keyup(function(e) {
        if(e.keyCode == 13) { 
            loginActionForm.submit(); 
        }
    });

});
</script>
