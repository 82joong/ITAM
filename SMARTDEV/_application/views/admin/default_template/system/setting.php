<?php 
//$page_css[] = "";

//include left panel (navigation)
//follow the tree in inc/config.ui.php
require_once realpath(dirname(__FILE__).'/../').'/inc/config.ui.php';


$active_key = array(
    "system",               // 1 Depth menu
    "setting"          // 2(Sub) Depth menu
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
        <div class="col-xl-12">
            <div class="panel">

                <div class="panel-hdr">
                   
                    <div class="panel-toolbar position-absolute pos-right">
                        <div v-on:click="onSubmit" class="btn-save btn btn-sm btn-success waves-effect waves-themed">
                            <span class="fal fa-save mr-1"></span> Save 
                        </div>
                    </div>
                </div>


                <div class="panel-container show">
                    <div class="panel-content">
                      

                        <form name='form_setting' id="form_setting" class="needs-validation" action="/<?=SHOP_INFO_ADMIN_DIR?>/system/manage_setting" method="POST" novalidate autocomplete="off" >
                        <input type="hidden" name="key" v-model="thisPanel">
                        <input type="hidden" name="tab" v-model="thisTab">


                        <div class="row">
                            <div class="col-auto">
                                <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">

                                    <?php foreach($tabs as $id=>$tab) : ?>
                                    <?php
                                            $active = '';
                                            $selected = false;
                                            if($id=='ips') {
                                                $selected = true;
                                                $active = 'active';
                                            }
                                    ?>
                                    <a class="nav-link <?=$active?>" id="v-pills-<?=$id?>-tab" data-toggle="pill" 
                                        href="#v-pills-<?=$id?>" role="tab" aria-controls="v-pills-<?=$id?>" aria-selected="<?=$selected?>">
                                        <i class="fal <?=$tab['ico']?>"></i>
                                        <span class="hidden-sm-down ml-1"><?=$tab['title']?></span>
                                    </a>
                                    <?php endforeach; ?>

                                </div>
                            </div> <!-- END col-auto -->


                            <div class="col">
                                <div class="tab-content" id="v-pills-tabContent">


                                    <div class="tab-pane fade show active" id="v-pills-ips" role="tabpanel" aria-labelledby="v-pills-ips-tab">
                                        <h3>Allow IPs</h3>
                                        <div class="form-row form-group justify-content-md-center mt-3">
                                            <div class="col-12 col-lg-12 mb-4 input-group">
                                                <textarea class="form-control" name="as_allow_ips" aria-label="With textarea" v-model="allowIPs"></textarea>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="tab-pane fade" id="v-pills-threshold" role="tabpanel" aria-labelledby="v-pills-threshold-tab">
                                        <h3 class="border-bottom pb-3 fw-500">Threshold</h3>
                                        <div class="form-row form-group justify-content-md-center mt-5">
                                            <div class="alert alert-warning text-left fs-small w-100" role="alert">
                                                <code>Menu > Syslog > Top Server</code> 데이터에 대한 설정되는 숫자의 <code>초과값</code> 탐지
                                            </div>

                                            <?php
                                            $max_data = array(
                                                'cpu'   =>  array(
                                                    'title'     => 'CPU',
                                                    'field'     => 'as_cpu_max',
                                                    'default'   => CPU_MAX,
                                                    'under'     => 0,
                                                    'upper'     => 99.99,
                                                    'ico'       => 'fa-microchip',
                                                ),
                                                'mem'   =>  array(
                                                    'title'     => 'Memory',
                                                    'field'     => 'as_mem_max',
                                                    'default'   => MEM_MAX,
                                                    'under'     => 1,
                                                    'upper'     => 99.99,
                                                    'ico'       => 'fa-memory',
                                                ),
                                                'disk'   =>  array(
                                                    'title'     => 'DISK',
                                                    'field'     => 'as_disk_max',
                                                    'default'   => DISK_MAX,
                                                    'under'     => 1,
                                                    'upper'     => 99.99,
                                                    'ico'       => 'fa-server',
                                                ),
                                                'top'   =>  array(
                                                    'title'     => 'TOP',
                                                    'field'     => 'as_top_max',
                                                    'default'   => TOP_MAX,
                                                    'under'     => 1,
                                                    'upper'     => 99.99,
                                                    'ico'       => 'fa-list-ol',
                                                ),
                                                'swap'   =>  array(
                                                    'title'     => 'SWAP',
                                                    'field'     => 'as_swap_max',
                                                    'default'   => SWAP_MAX,
                                                    'under'     => 0,
                                                    'upper'     => 10,
                                                    'ico'       => 'fa-album-collection',
                                                )
                                            );
                                            ?>

                                            <?php foreach($max_data as $k=>$max) : ?>
                                            <div class="col-12 col-lg-12 row mb-3">
                                                <div class="col-12">
                                                    <label class="form-label">
                                                        <i class="fal <?=$max['ico']?> mr-1"></i>        
                                                        MAX <?=$max['title']?> (%)
                                                    </label>
                                                </div>

                                                <div class="col-lg-10 col-sm-8">
                                                    <input class="form-control grid-range" data-id="<?=$max['field']?>" id="gridrange" type="range" name="gridrange" min="<?=$max['under']?>" max="<?=$max['upper']?>" step="0.01" value="<?=$max['default']?>">
                                                </div>
                                                <div class="col-lg-2 col-sm-4">
                                                    <input type="text" class="form-control form-control-sm text-right" id="<?=$max['field']?>" name="<?=$max['field']?>" placeholder="" value="<?=$max['default']?>">
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                            

                                           <div class="col-12 col-lg-12 row mb-3">
                                                <div class="col-12">
                                                    <label class="form-label">
                                                        <i class="fal fa-ethernet mr-1"></i>        
                                                        Allow Ports 
                                                    </label>
                                                </div>

                                                <div class="col-12">
                                                    <input class="form-control" type="text" data-role="tagsinput" name="as_allow_ports" value="" v-model="allowPorts">
                                                </div>
                                            </div>

                                        </div>
                                    </div>


                                </div>
                            </div>  <!-- END .col -->
                        </div>  <!-- END .row -->
                        </form>

                    </div>


                    <div class="panel-content py-2 rounded-bottom border-faded border-left-0 border-right-0 border-bottom-0 text-muted d-flex">
                        <div v-on:click="onSubmit" class="btn-save btn btn-sm btn-success waves-effect waves-themed ml-auto">
                            <span class="fal fa-save mr-1"></span> Save 
                        </div>
                    </div>

                </div>  <!-- END panel-container -->
            </div>
        </div>
    </div>
</main>


<link rel="stylesheet" media="screen, print" href="<?=$assets_dir?>/css/formplugins/bootstrap-tagsinput/bootstrap-tagsinput.css">

<script>
$('.nav-link').on('click', function() {
    $(this).siblings().removeClass('active');
    $(this).addClass('active');

    var _id = $(this).attr('aria-controls');
    $('#'+_id).siblings().removeClass('show').removeClass('active');
    $('#'+_id).addClass('show').addClass('active');

    console.log(id);
});



var vm = new Vue({
    el: '.page-content',
    data: {
        allowIPs            : '<?=isset($as_data['as_allow_ips']) ? $as_data['as_allow_ips'] : '';?>',
        allowPorts          : '<?=isset($as_data['as_aloow_ports']) ? $as_data['as_allow_ports'] : implode(',',PORT_MAX);?>',
        thisPanel           : 'as_allow_ips',
        thisTab             : '',
    },
    created: function() {
        this.initTab();
    },
    mounted: function() {
        //$('[name="as_hash_words"]').val(this.hashTags);
    },
    methods: {

        onSubmit: function() {
            console.log(this.thisPanel);

            // validation
            var obj_frm = $('#form_setting').get()[0];
            if (obj_frm.checkValidity() === false) {
                obj_frm.classList.add('was-validated');
                return;
            }


            var msg = '';
            switch(this.thisPanel) {
                case 'as_assets_type':
                    msg += '해당 자산 분류로 ';
                    break;
                case 'as_allow_ips':
                    msg += '해당 IP 허용으로 ';
                    break;
                case 'as_currency':
                    msg += '해당 금액으로 ';
                    break;
            } // END_switch

            msg += '<?=getConfirmMsg('SETTING')?>';
            if( ! confirm(msg) ) {
                return;
            }
           // form_setting.submit();
        },

        initTab: function() {

             var hash = window.location.hash;
             if ( ! hash) return;

             hash = hash.replace('#', '');

             var tab = document.getElementById(hash + '-tab');
             if ( ! tab) return;

             $(tab).siblings().removeClass('active');
             $(tab).addClass('active');

             var _id = $(tab).attr('aria-controls');

             $('#'+_id).siblings().removeClass('show').removeClass('active');
             $('#'+_id).addClass('show').addClass('active');

             this.thisPanel = $(tab).data('id');
             this.thisTab = _id;
        },  

    },
    watch: {
       
    }
});




$(".grid-range").on("input change", function() {
    var _id = $(this).data('id');
    $('#'+_id).val($(this).val());
});


</script>
