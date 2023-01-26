<?php 
//include left panel (navigation)
//follow the tree in inc/config.ui.php
require_once realpath(dirname(__FILE__).'/../').'/inc/config.ui.php';


$active_key = array(
    "assets",               // 1 Depth menu
    "ips",          // 2(Sub) Depth menu
    "iplist"          // 2(Sub) Depth menu
);
$page_nav[$active_key[0]]["active"] = true;
$page_nav[$active_key[0]]["sub"][$active_key[1]]["active"] = true;
$page_nav[$active_key[0]]["sub"][$active_key[1]]["sub"][$active_key[2]]["active"] = true;
include realpath(dirname(__FILE__).'/../').'/inc/nav.php';



$title_symbol = 'fa-enthernet';
?>



<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL -->
<style type="text/css">
</style>


<main id="js-page-content" role="main" class="page-content">

    <?php include realpath(dirname(__FILE__).'/../').'/inc/breadcrumbs.php'; ?>


    <?php 
    $assign_data = array(
        'select_people'     => $select_people, 
        'select_allocation' => $select_allocation, 
        'row'               => $row,
        'mode'              => $mode,
        'type'              => 'page',
        'class_type'        => $row['ip_class_type']
    );
    echo $this->load->view('admin/default_template/people/ip_detail_template.php', $assign_data, true);
    ?>

</main>



<form name='del_form' id="del_form" action="/<?=SHOP_INFO_ADMIN_DIR?>/people/ip_process" method="POST">
    <input type='hidden' name='mode' value='delete' />
    <input type="hidden" name="ip_id" value="<?=$row['ip_id']?>">
</form>


<script type="text/javascript">
$(document).ready(function() {

        
    /*
    $(".select2").select2({
        placeholder: "@select searchable data",
    });
    */

});
</script>
