<?php 
//$page_css[] = "";

//include left panel (navigation)
//follow the tree in inc/config.ui.php
require_once realpath(dirname(__FILE__).'/../').'/inc/config.ui.php';


$mode = 'detail';
$active_key = array(
    "assets",                   // 1 Depth menu
    "status",                   // 2(Sub) Depth menu
    "all" 
);
$page_nav[$active_key[0]]["active"] = true;
$page_nav[$active_key[0]]["sub"]['status']['active'] = true;
$page_nav[$active_key[0]]["sub"]['status']['sub'][$active_key[2]]["active"] = true;


include realpath(dirname(__FILE__).'/../').'/inc/nav.php';
$title_symbol = 'fa-server';
?>

<style type="text/css">
</style>


<main id="js-page-content" role="main" class="page-content">
    <?php include realpath(dirname(__FILE__).'/../').'/inc/breadcrumbs.php'; ?>


    <div class="row">
        <div class="col-xl-12">
            <div class="panel">
                <div class="panel-container show">

                    <div class="panel-content">
                        <ul class="nav nav-tabs nav-fill" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#tab_justified-1" role="tab" aria-selected="true">
                                    <i class="fas fa-ballot text-primary mr-1"></i>
                                    <span data-1i8n="content.details">Details</span>
                                </a>
                            </li>

                            <?php /*?>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#tab_justified-2" role="tab" aria-selected="false">Orders</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#tab_justified-3" role="tab" aria-selected="false">History</a>
                            </li>
                            <?php */?>

                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#tab_justified-4" role="tab" aria-selected="false">
                                    <i class="fas fa-tools text-danger mr-1"></i>
                                    <span data-1i8n="content.Maintenance">Maintenance</span>
                                    <span class="badge badge-icon position-relative"><?=$works_cnt?></span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link disabled" href="#" tabindex="-1"></a>
                            </li>
                        </ul>
                        <div class="tab-content p-3">
                            <div class="tab-pane fade active show" id="tab_justified-1" role="tabpanel">
                                <div class="row">

                                    <div class="col-8">
                                        <div class="frame-wrap">
                                            <table class="table table-sm table-striped table-bordered table-hover m-0">
                                                <tr>
                                                    <th scope="row" data-i18n="content.assets_name">Assets Name</th>
                                                    <td><?=$row['am_name']?></td>
                                                </tr>
                                                <tr>
                                                    <th scope="row" data-i18n="content.tags">Tags</th>
                                                    <td><?=tagsToHtml($row['am_tags'])?></td>
                                                </tr>
                                                <tr>
                                                    <th scope="row" class="w-25" data-i18n="content.status">Staus</th>
                                                    <td><?=$row['status']?></td>
                                                </tr>
                                                <tr>
                                                    <th scope="row" data-i18n="content.serial_no">Serial No.</th>
                                                    <td><?=$row['am_serial_no']?></td>
                                                </tr>

                                                <tr>
                                                    <th scope="row" data-i18n="content.assets_type">Assets Type</th>
                                                    <td><?=$row['type']?></td>
                                                </tr>

                                                <tr>
                                                    <th scope="row" data-i18n="content.category">Category</th>
                                                    <td><?=$row['category']?></td>
                                                </tr>

                                                <tr>
                                                    <th scope="row" data-i18n="content.quantity">Quantity</th>
                                                    <td><?=$row['am_quantity']?></td>
                                                </tr>

                                                <tr>
                                                    <th scope="row" data-i18n="content.estimatenum">Estimatenum</th>
                                                    <td>
                                                        <a href="/admin/purchase/order_detail/<?=$row['am_order_id']?>" target="_blank">
                                                            <?=$row['am_estimatenum']?>
                                                        </a>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <th scope="row" data-i18n="content.memo">Memo</th>
                                                    <td>
                                                        <div class="help-block"><?=$row['am_memo']?></div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th scope="row" data-i18n="content.eos">EOS Rate</th>
                                                    <td><?=$row['am_eos_rate']?></td>
                                                </tr>
                                                <tr>
                                                    <th scope="row" data-i18n="content.eos_expired_at">EOS Expired At</th>
                                                    <td><?=date('Y-m-d', strtotime($row['am_eos_expired_at']))?></td>
                                                </tr>

                                                
                                                <tr>
                                                    <th scope="row" data-i18n="content.location">Location</th>
                                                    <td>
                                                        <div>
                                                            <i class="fas fa-mouse-pointer mr-1"></i>
                                                            <span><?=$location['l_name']?></span>
                                                        </div>
                                                        <div>
                                                            <i class="fas fa-phone-alt mr-1"></i>
                                                            <a href="tel:<?=$location['l_tel']?>"><?=$location['l_tel']?></a>
                                                        </div>
                                                        <div>
                                                            <i class="fas fa-map-marker-alt mr-1"></i>
                                                            <span><?=$location['l_address']?> #<?=$location['l_zip']?></span>
                                                        </div>
                                                        <div>
                                                            <i class="fas fa-globe-asia mr-1"></i>
                                                            <span><?=$location['l_country']?>, <?=$location['l_city']?></span>
                                                        </div>
                                                        <div class="help-block"><?=$location['l_memo']?></div>
                                                    </td>
                                                </tr>
                                               

                                                <tr>
                                                    <th scope="row" data-i18n="content.rack_space">Rack Space</th>
                                                    <td><?=$row['am_rack_code']?></td>
                                                </tr>

                                                <tr>
                                                    <th scope="row" data-i18n="content.company">Company</th>
                                                    <td>
                                                        <div>
                                                            <i class="fas fa-building mr-1"></i>
                                                            <span><?=$company['c_name']?></span>
                                                            <span class="ml-2 badge badge-warning"><?=$company['c_code']?></span>
                                                        </div>
                                                        <div>
                                                            <i class="fas fa-user-circle mr-1"></i>
                                                            <span><?=$company['c_biz_owner']?></span>
                                                        </div> 
                                                        <div>
                                                            <i class="fas fa-tags mr-1"></i>
                                                            <span><?=$company['c_biz_number']?></span>
                                                        </div>
                                                        <div>
                                                            <i class="fas fa-phone-alt mr-1"></i>
                                                            <a href="tal:<?=$company['c_tel']?>"><?=$company['c_tel']?></a>
                                                        </div>
                                                    </td>
                                                </tr>
                                               



                                                <tr>
                                                    <th scope="row" data-i18n="content.supplier">Supplier</th>
                                                    <td>
                                                        <div>
                                                            <i class="fas fa-building mr-1"></i>
                                                            <span><?=$supplier['sp_name']?></span>
                                                        </div>
                                                        <div>
                                                            <i class="fas fa-window mr-1"></i>
                                                            <a href="<?=$supplier['sp_url']?>" target="_blank"><?=$supplier['sp_url']?></a>
                                                        </div>
                                                        <div>
                                                            <i class="fas fa-user-circle mr-1"></i>
                                                            <span><?=$supplier['sp_contact_name']?></span>
                                                        </div> 
                                                        <div>
                                                            <i class="fas fa-mail-bulk mr-1"></i>
                                                            <a href="mailto:<?=$supplier['sp_email']?>"><?=$supplier['sp_email']?></a>
                                                        </div> 
                                                        <div>
                                                            <i class="fas fa-phone-alt mr-1"></i>
                                                            <a href="tel:<?=$supplier['sp_tel']?>"><?=$supplier['sp_tel']?></a>
                                                        </div>
                                                        <div>
                                                            <i class="fas fa-fax mr-1"></i>
                                                            <span><?=$supplier['sp_fax']?></span>
                                                        </div>
                                                        <div>
                                                            <i class="fas fa-map-marker-alt mr-1"></i>
                                                            <span><?=$supplier['sp_address']?> #<?=$supplier['sp_zip']?></span>
                                                        </div>
                                                        <div>
                                                            <i class="fas fa-globe-asia mr-1"></i>
                                                            <span><?=$supplier['sp_country']?>, <?=$supplier['sp_city']?></span>
                                                        </div>
                                                        <div class="help-block"><?=strip_tags($supplier['sp_memo'])?></div>
                                                    </td>
                                                </tr>
 


                                                <tr>
                                                    <th scope="row" data-i18n="content.created_at">Created At</th>
                                                    <td><?=$row['am_created_at']?></td>
                                                </tr>
                                                <tr>
                                                    <th scope="row" data-i18n="content.updated_at">Updated At</th>
                                                    <td><?=$row['am_updated_at']?></td>
                                                </tr>
                                            </table>
                                        </div>

                                    </div>

                                    <div class="col-4" id="area-model">
                                        <div class="card border m-auto m-lg-0">
                                            <?php if(strlen($model['img_url']) > 0) : ?>
                                            <img src="<?=$model['img_url']?>" class="card-img-top" alt="<?=$model['m_model_name']?>">
                                            <?php endif; ?>
                                            <div class="card-body bg-faded">
                                                <h5 class="card-title font-weight-bold"><?=$model['m_model_name']?></h5>
                                                <p class="card-text"><?=$model['m_description']?></p>
                                            </div>

                                            <div class="card-body m-lg-0">
                                                <ul class="list-group list-group-flush">
                                                    <?php foreach($custom as $v): ?> 
                                                    <li class="list-group-item p-2">
                                                        <!--span class="fw-500 fs-xs"><?=$v['cv_name']?></span-->
                                                        <span class="badge border border-primary text-primary"><?=$v['cv_name']?></span>
                                                        <span><?=$v['cv_value']?></span>
                                                    </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        </div>


                                        <?php // START Vendor?>
                                        <br />
                                        <div class="card border m-auto m-lg-0 mt-8">
                                            <div class="card-header py-2">
                                                <span class="mr-2"><?=$vendor['icon']?></span>
                                                <span class="h3 position-absolute mt-1"><?=$vendor['vd_name']?></span>
                                            </div>
                                            <ul class="list-group list-group-flush">
                                                <li class="list-group-item">
                                                    <div>
                                                        <i class="fas fa-browser mr-1"></i>
                                                        <a href="<?=$vendor['vd_url']?>" target="_blank" class="text-info">
                                                            <?=$vendor['vd_url']?>
                                                        </a>
                                                    </div>
                                                    <div>
                                                        <i class="fas fa-mouse-pointer mr-1"></i>
                                                        <a href="<?=$vendor['vd_support_url']?>" target="_blank" class="text-info" style="word-break:break-all;">
                                                            <?=$vendor['vd_support_url']?>
                                                        </a>
                                                    </div>
                                                    <div>
                                                        <i class="fas fa-user-headset mr-1"></i>
                                                        <a href="tel:<?=$vendor['vd_support_tel']?>" class="text-info">
                                                            <?=$vendor['vd_support_tel']?>
                                                        </a>
                                                    </div>
                                                    <div>
                                                        <i class="fas fa-mail-bulk mr-1"></i>
                                                        <a href="mailto:<?=$vendor['vd_support_email']?>" class="text-info">
                                                            <?=$vendor['vd_support_email']?>
                                                        </a>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                        <?php // END Vendor?>

                                    </div> <!-- END #area-model -->

                                </div> <!-- END .row -->
                            </div>


                            <?php /*?>
                            <div class="tab-pane fade" id="tab_justified-2" role="tabpanel">
                                TAB 2
                            </div>
                            <div class="tab-pane fade" id="tab_justified-3" role="tabpanel">
                                TAB 3
                            </div>
                            <?php */?>


                            <!-- START TAB4 -->
                            <div class="tab-pane fade" id="tab_justified-4" role="tabpanel">
                                <?php
                                $params = array(
                                    'assets_model_id' => $row['am_id'] 
                                );
                                echo $this->load->view('/admin/default_template/assets/maintenance_tab.php', $params, true);
                                ?>
                            </div>
                            <!-- END TAB4 -->

                        </div>
                    </div>
            
                </div> <!-- END .panel-container -->
            </div> <!-- END .panel -->
        </div> <!-- END .col-xl-12-->
    </div> <!-- END .row -->
</main>

<script type="javascript">

$(document).ready(function() {
    // Tab 전환
    var _tab = location.hash;
    if(_tab.length > 0) {
        localStorage['lastTab'] = _tab;
    }
});
</script>
