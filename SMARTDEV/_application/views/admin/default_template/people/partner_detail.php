<?php 
//include left panel (navigation)
//follow the tree in inc/config.ui.php
require_once realpath(dirname(__FILE__).'/../').'/inc/config.ui.php';


$active_key = array(
    "people",               // 1 Depth menu
    "partners"          // 2(Sub) Depth menu
);
$page_nav[$active_key[0]]["active"] = true;
$page_nav[$active_key[0]]["sub"][$active_key[1]]["active"] = true;
include realpath(dirname(__FILE__).'/../').'/inc/nav.php';

$title_symbol = 'fa-users';
?>



<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL -->
<style type="text/css">
</style>


<main id="js-page-content" role="main" class="page-content">

    <?php include realpath(dirname(__FILE__).'/../').'/inc/breadcrumbs.php'; ?>

    <div class="row">
        <div class="col-xl-12">
            <div class="panel">
                <div class="panel-hdr">
                    <h2>Row <?=ucfirst($mode)?></h2>
                </div>

                <div class="panel-container show">
                    <div class="panel-content">

                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <a href="#tab_profile" class="nav-link active" data-toggle="tab" role="tab" aria-selected="true">
                                    <i class="fal fa-user text-success"></i>
                                    <span class="hidden-sm-down ml-1" data-i18n="content.profile">Profile</span>
                                </a>
                            </li>
                        </ul>


                        <div class="tab-content border border-top-0 p-3">

                            <!-- START #tab_profile -->
                            <div class="tab-pane fade active show" id="tab_profile" role="tabpanel">

                                <form name='edit_form' id="edit_form" class="needs-validation" action="/<?=SHOP_INFO_ADMIN_DIR?>/people/partner_process" method="POST" novalidate autocomplete="off" >
                                <input type="hidden" name="mode" value="<?=$mode?>">
                                <input type="hidden" name="pt_id" value="<?=$row['pt_id']?>">


                                <div class="panel-content py-2 rounded-bottom border-faded border-top-0 border-left-0 border-right-0 text-muted d-flex mb-5">
                                    <?=genDetailButton('partners', $mode)?>                        
                                </div>

                                <div class="form-row form-group justify-content-md-center">


                                    <div id="area-supplier" class="col-12 col-lg-8 mb-3">
                                        <label class="form-label" for="pt_supplier_id" data-i18n="content.supplier">
                                            Supply Company 
                                        </label>
                                        <?=$select_supplier?>
                                    </div>


                                    <div class="col-12 col-lg-8 form-group">
                                        <label class="form-label" for="name-f" data-i18n="content.user_name">
                                            Name
                                        </label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text text-success">
                                                    <i class="ni ni-user fs-xl"></i>
                                                </span>
                                            </div>
                                            <input type="text" aria-label="Last name" class="form-control" name="pt_lastname" placeholder="Last name(성)" id="name-l" value="<?=$row['pt_lastname']?>">
                                            <input type="text" aria-label="First name" class="form-control" name="pt_firstname" placeholder="First name(이름)" id="name-f" value="<?=$row['pt_firstname']?>">
                                        </div>
                                    </div>


                                    <div class="col-12 col-lg-8 mb-3">
                                        <label class="form-label" for="pt_email" data-i18n="content.email">
                                            Email 
                                            <span class="text-danger">*</span>
                                        </label>
                                        <?=getInputMask('email', 'pt_email', $row['pt_email'], 'required')?>
                                        <span class="invalid-feedback">Please provide a Email.</span>
                                    </div>


                                    <div class="col-12 col-lg-8 mb-3">
                                        <label class="form-label" for="pt_tel" data-i18n="content.tel">
                                            Tel. 
                                        </label>
                                        <?=getInputMask('tel', 'pt_tel', $row['pt_tel'])?>
                                    </div>


                                    <div class="col-12 col-lg-8 mb-3">
                                        <label class="form-label" for="pt_mobile" data-i18n="content.mobile">
                                             Mobile 
                                        </label>
                                        <?=getInputMask('tel', 'pt_mobile', $row['pt_mobile'])?>
                                    </div>



                                    <div class="col-12 col-lg-8 mb-3">
                                        <label class="form-label" for="pt_title" data-i18n="content.user_title">
                                            Title 
                                        </label>
                                        <?=getInputMask('normal', 'pt_title', $row['pt_title'])?>
                                    </div>


                                    <div class="col-12 col-lg-8 mb-3">
                                        <label class="form-label" for="pt_dept" data-i18n="content.department">
                                            Department 
                                        </label>
                                        <?=getInputMask('normal', 'pt_dept', $row['pt_dept'])?>
                                    </div>


                                    <?php if($mode == 'update') : ?>
                                    <div class="col-12 col-lg-8 mb-3">
                                        <label class="form-label" data-i18n="updated_at">
                                            Updated At 
                                        </label>
                                        <input type="text" class="form-control" name="pt_updated_at" value="<?=$row['pt_updated_at']?>" disabled >
                                    </div>
                                    
                                    <div class="col-12 col-lg-8 mb-3">
                                        <label class="form-label" data-i18n="created_at">
                                            Created At 
                                        </label>
                                        <input type="text" class="form-control" name="pt_created_at" value="<?=$row['pt_created_at']?>" disabled >
                                    </div>
                                    <?php endif;?>

                                </div> <!-- END .form-row -->


                                <div class="panel-content py-2 rounded-bottom border-faded border-left-0 border-right-0 border-bottom-0 text-muted d-flex">
                                    <?=genDetailButton('partners', $mode)?>                        
                                </div>

                                </form>


                            </div> <!-- END #tab_profile -->

                        </div>
                    </div>
                <div>
            </div>
        </div>
    </div>
</main>



<form name='del_form' id="del_form" action="/<?=SHOP_INFO_ADMIN_DIR?>/people/partner_process" method="POST">
    <input type='hidden' name='mode' value='delete' />
    <input type="hidden" name="pt_id" value="<?=$row['pt_id']?>">
</form>




<script type="text/javascript">
$(document).ready(function() {
    // Tab 전환
    var _tab = location.hash;
    if(_tab.length > 0) {
        localStorage['lastTab'] = _tab;
    }
});
</script>
