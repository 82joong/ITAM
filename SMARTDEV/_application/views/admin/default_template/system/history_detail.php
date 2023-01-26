<?php 
//$page_css[] = "";

//include left panel (navigation)
//follow the tree in inc/config.ui.php
require_once realpath(dirname(__FILE__).'/../').'/inc/config.ui.php';


$active_key = array(
    "system",          // 1 Depth menu
    "history"               // 2(Sub) Depth menu
);
$page_nav[$active_key[0]]["active"] = true;
$page_nav[$active_key[0]]["sub"][$active_key[1]]["active"] = true;
include realpath(dirname(__FILE__).'/../').'/inc/nav.php';


$title_symbol = 'fa-cog';
?>



<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL -->
<style type="text/css">
</style>


<main id="js-page-content" role="main" class="page-content">
 

    <?php include realpath(dirname(__FILE__).'/../').'/inc/breadcrumbs.php'; ?>


    <div class="row">
        <form name='edit_form' id="edit_form" class="needs-validation" action="/<?=SHOP_INFO_ADMIN_DIR?>/system/history_process" method="POST" novalidate autocomplete="off" >
        <input type="hidden" name="mode" value="<?=$mode?>">
        <input type="hidden" name="<?=$pk?>" value="<?=$values[$pk]?>">

        <div class="col-xl-12">
            <div class="panel">
                <div class="panel-hdr">
                    <h2>Row <?=ucfirst($mode)?></h2>
                   
                    <div class="panel-toolbar">
                        <button type="submit" class="btn-save mr-1 btn btn-sm btn-success waves-effect waves-themed">
                            <span class="fal fa-save mr-1"></span> Save 
                        </button>

                        <a href="/<?=SHOP_INFO_ADMIN_DIR?>/system/history" class="btn btn-sm btn-danger waves-effect waves-themed">
                            <span class="fal fa-times mr-1"></span> Cancle/List
                        </a>
                    </div>
                </div>

                <div class="panel-container show">
                    <div class="panel-content">

                        <div class="form-row form-group justify-content-md-center">
						
							
                        </div>
                    </div>



                    <div class="panel-content py-2 rounded-bottom border-faded border-left-0 border-right-0 border-bottom-0 text-muted d-flex">

                        <button type="submit" class="btn-save mr-1 btn btn-sm btn-success waves-effect waves-themed ml-auto">
                            <span class="fal fa-save mr-1"></span> Save 
                        </button>


                        <a href="/<?=SHOP_INFO_ADMIN_DIR?>/system/history?keep=yes" class="btn btn-sm btn-danger waves-effect waves-themed">
                            <span class="fal fa-times mr-1"></span> Cancle/List 
                        </a>
                    </div>


                <div>
            </div>
        </div>

        </form>
    </div>
</main>



<form name='del_form' id="del_form" action="/<?=SHOP_INFO_ADMIN_DIR?>/system/history_process" method="POST">
    <input type='hidden' name='mode' value='delete' />
	<input type='hidden' name='<?=$pk?>' value='<?=$values[$pk]?>' />
</form>




<script type="text/javascript">
$(document).ready(function() {

});

</script>
