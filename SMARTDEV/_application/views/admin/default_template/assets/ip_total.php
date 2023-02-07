<?php 
//$page_css[] = "";

//include left panel (navigation)
//follow the tree in inc/config.ui.php
require_once realpath(dirname(__FILE__).'/../').'/inc/config.ui.php';


$mode = 'list';
$active_key = array(
    "ips",                          // 2(Sub) Depth menu
    "iptotal"    // 3(Sub) Depth menu
);
$page_nav[$active_key[0]]["active"] = true;
$page_nav[$active_key[0]]["sub"][$active_key[1]]["active"] = true;

include realpath(dirname(__FILE__).'/../').'/inc/nav.php';

$title_symbol = 'fa-ethernet';



$sel_class = array(
    'ipc_name'      => '',
    'ipc_type'      => '',
    'ipc_category'  => '',
);

?>

<style type="text/css">
.select2-dropdown {z-index: 10060 !important;/*1051;*/}
#data-table.table-bordered.dataTable tbody th, table.table-bordered.dataTable tbody td {padding: 2px;}
.dataTables_wrapper tr.child td.child .dtr-details {font-size:12.5px;}
</style>


<main id="js-page-content" role="main" class="page-content">

    <?php include realpath(dirname(__FILE__).'/../').'/inc/breadcrumbs.php'; ?>

    <div class="row">
        <div class="col-xl-12">


            <div id="panel-4" class="panel">
                <div class="panel-hdr">
                    <h2>
                        Location<span class="fw-300"><i>and Class</i></span>
                    </h2>
                    <div class="panel-toolbar">
                        <?=genFullButton();?>
                    </div>
                </div>

                <div class="panel-container collapse show ">
                    <div class="panel-content">
                        <form action="/admin/assets/ip_total" method="POST" id="search_box">
                        <input type="hidden" name="is_search" value="YES">
                        <input type="hidden" name="search_type" value="server">

                            <div class="mb-1">
                                <span class="badge border border-danger text-danger">IP</span>
                                <span class="badge border border-danger text-danger">Name</span>
                                <span class="badge border border-danger text-danger">ServiceTag</span>
                                <span class="badge border border-danger text-danger">Memo</span>
                                <span class="fs-nano">등 통합 검색</span>
                            </div>

                            
                            <div class="input-group input-group-lg mb-1 shadow-1 rounded" id="total_search">
                                <input type="text" class="form-control shadow-inset-2" id="filter-icon" aria-label="type 2 or more letters" placeholder="Search anything..." value="<?=isset($req['search_value']) ? $req['search_value'] : ''?>" name="search_value" autocomplete="off">
                                <div class="input-group-append">
                                    <div class="btn btn-primary hidden-sm-down waves-effect waves-themed" id="search_submit">
                                        <i class="fal fa-search mr-lg-2"></i><span class="hidden-md-down">Search</span>
                                    </div>
                                </div>
                            </div>

                            <div class="fs-nano" style="color:#e7026e;">
                                <i class="fal fa-info-circle mr-1"></i>    
                                IP 검색시, 결과 나오지 않으면 자산 할당이 되어 있지 않은 IP
                            </div>
                        </form>
                        <br />


                        <?php foreach($rows as $l_id=>$row) : ?>
                        <div class="card m-auto border">
                            <div class="card-header py-2">
                                <div class="card-title"><i class="fas fa-map-marker-alt mr-2"></i><?=$location_map[$l_id]?></div>
                            </div>
                            <div class="card-body">
                            <?php 
                            $grows  = $this->common->getDataByDuplPK($row, 'ipc_name');
                            foreach($grows as $gname=>$group) {
                                echo '<span class="btn btn-xs btn-secondary mr-1 mb-1">'.$gname.'</span>';

                                foreach($group as $k=>$r) {
                                    //$icon = '<i class="fal fa-link mr-1"></i>';
                                    $icon = '';

                                    $class = 'btn-outline-info';
                                    if(isset($r['ipc_id']) && $ipc_id == $r['ipc_id']) {
                                        $class = 'btn-info';
                                        $sel_class = $r;
                                    }

                                    echo '<span class="btn btn-xs '.$class.' waves-effect waves-themed mr-1 mb-1 btn-cidr" data-id="'.$r['ipc_id'].'" data-type="'.$r['ipc_type'].'">';
                                    echo $icon.$r['ipc_cidr'];
                                    echo '</span>';
                                }
                            } 
                            ?>
                            </div>
                        </div>
                        <br />
                        <?php endforeach; ?>

                        
                        <div class="py-2 d-flex" id="iplist-head">
                            <span class="badge badge-primary mr-1" id="bdg_ipc_name"><?=$sel_class['ipc_name']?></span>
                            <span class="badge badge-success mr-1" id="bdg_ipc_type"><?=$sel_class['ipc_type']?></span>
                            <span class="badge badge-danger" id="bdg_ipc_category"><?=$sel_class['ipc_category']?></span>
                        </div>


                        <?php 
                        // 통합검색모드
                        if(isset($req['is_search']) && $req['is_search'] == 'YES') {

                            echo $this->load->view('admin/default_template/assets/ip_search_list.php', $req, true);

                        }else {

                            // 사내 IP
                            if($sel_class['ipc_type'] == 'LOCAL') {
                                echo $this->load->view('admin/default_template/assets/pim_list.php', array(), true);

                            // IDC IP
                            }else {

                                switch($sel_class['ipc_category']) {
                                    // assets_ip_map
                                    case 'VMWARE':
                                        echo $this->load->view('admin/default_template/assets/aim_list.php', array(), true);
                                        break;

                                   // assets_ip_map
                                    case 'IDRAC':
                                        echo $this->load->view('admin/default_template/assets/idrac_list.php', array(), true);
                                        break;

                                    // vmservice_ip_map
                                    default:
                                        echo $this->load->view('admin/default_template/assets/vim_list.php', array(), true);
                                        break;
                                }
                            }
                        }
                        ?>

                        
                    </div> <!-- .panel-content -->
                </div>
            </div>
        </div> <!-- .col -->
    </div> <!-- .row -->
    
</main>



<div class="modal" tabindex="-1" role="dialog" id="modal-edit">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fal fa-comment-alt-edit mr-1"></i>
                    Modal Edit or Add
                </h5>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <p>Modal body text goes here.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="btn-save">Save</button>
            </div>
        </div>
    </div>
</div>


<!-- this overlay is activated only when mobile menu is triggered -->
<div class="page-content-overlay" data-action="toggle" data-class="mobile-nav-on"></div> 
<!-- END Page Content -->

<script type="text/javascript">


$(document).ready(function() {

    // Init
    //$('#panel-4').addClass('panel-fullscreen');
    var ipc_id = '<?=$ipc_id?>';
    var ipc_type = '<?=$ipc_type?>';


    $('.btn-cidr').on('click', function(e) {
        if(ipc_id == $(this).data('id')) return;

        location.href = '/<?=SHOP_INFO_ADMIN_DIR?>/assets/ip_total/'+$(this).data('id');
    });


    $('#total_search').find('.dropdown-item').on('click', function(e) {
        $('#total_search').find('#search_text').html($(this).text());
        $('[name="search_type"]').val($(this).data('id'));
        $('#total_search').find('.dropdown-toggle').attr('aira-expanded', false);
        $('#total_search').find('.dropdown-menu').removeClass('show');
        $('[name="search_value"]').focus();
    });


    $('#total_search').find('#search_submit').on('click', function(e) {
        e.preventDefault();
        search_submit();
    });
    $('[name="search_value"]').on("keydown",function(e){
        if(e.keyCode == 13) {
            e.preventDefault();
            search_submit();
        }
    });
    function search_submit() {
        if( ! $('[name="search_value"]').val() || $('[name="search_value"]').val().length < 3 ) {
            Swal.fire({
                title: "Confirm your values !",
                icon: "warning",
                type: "warning",
                html: "검색어를 입력하세요!\n3단어이상의 검색어 입력 필수",
                onClose: function onClose() {
                    $('[name="search_value"]').focus();
                }
            });
            return;
        }
        $('#search_box').submit();
    }
});
</script>
