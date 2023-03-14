<link rel="stylesheet" media="screen, print" href="<?=$assets_dir?>/css/miscellaneous/nestable/nestable-rtl.css">
<link rel="stylesheet" media="screen, print" href="<?=$assets_dir?>/css/miscellaneous/nestable/nestable.css">
<link rel="stylesheet" media="screen, print" href="<?=$assets_dir?>/css/miscellaneous/nestable/nestable.css.map">
<?php 
//$page_css[] = "";

//include left panel (navigation)
//follow the tree in inc/config.ui.php
require_once realpath(dirname(__FILE__).'/../').'/inc/config.ui.php';


$mode = 'list';
$active_key = array(
    "assets",                          // 2(Sub) Depth menu
    "rackview"    // 3(Sub) Depth menu
);
$page_nav[$active_key[0]]["active"] = true;
$page_nav[$active_key[0]]["sub"][$active_key[1]]["active"] = true;

include realpath(dirname(__FILE__).'/../').'/inc/nav.php';

$title_symbol = 'fa-search-location';


$sel_class = array();

?>

<style type="text/css">
.dd {width:100%;}
.dd-handle {height:auto; font-size:11px; line-height:12px; padding:2px 8px;}
/*.dd-handle .badge {padding:3px 0px 3px 3px;}*/



.card-body .table tbody th {vertical-align:middle; text-align:center;}

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
                        Location<span class="fw-300"><i>and Rack Space</i></span>
                    </h2>
                    <div class="panel-toolbar">
                        <a href="/<?=SHOP_INFO_ADMIN_DIR?>/manage/rack" class="btn btn-sm btn-success waves-effect waves-themed">
                            <span class="fas fa-plus-square mr-1"></span> Add Rack Space 
                        </a>
                        <?=genFullButton();?>
                    </div>
                </div>

                <div class="panel-container collapse show ">
                    <div class="panel-content bg-primary-100 pattern-1">

                        <div class="alert border-danger alert-primary alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true"><i class="fal fa-times"></i></span>
                            </button>
                            <div class="d-flex align-items-center">
                                <div class="alert-icon">
                                    <i class="fal fa-exclamation-triangle"></i>
                                </div>
                                <div class="flex-1">
                                    <strong>Rack Unit 순서는 <code>Drag and Drop</code> 으로 순서 변경가능합니다. </strong>
                                </div>
                            </div>
                        </div>



                        <div class="alert border-success alert-primary fade show pattern-4" role="success">
                            <div class="card-columns">
                                <?php foreach($tt_rows as $r_location_id =>$tt_row) : ?>
                                <div class="card m-lg-0" style="margin-bottom:10px!important;">
                                    <div class="card-header bg-success-500">
                                        <i class="fas fa-map-marker-alt mr-2"></i>
                                        <?=$location_map[$r_location_id]?>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-group list-group-flush">
                                            <?php $floor_data = $this->common->getDataByDuplPK($tt_row, 'r_floor'); ?>
                                            <?php 
                                            foreach($floor_data as $floor=>$fdata): 
                                            ?>
                                            <li class="list-group-item">
                                                <div class="h5"><?=$floor?></div>
                                                <?php 
                                                $section_data = $this->common->getDataByPK($fdata, 'r_section');
                                                foreach($section_data as $section=>$sdata){

                                                    //echo print_r($sdata); exit;

                                                    $btn_theme = 'info';
                                                    if(
                                                        $r_location_id == $rack_data['r_location_id'] &&
                                                        $floor == $rack_data['r_floor'] &&
                                                        $section == $rack_data['r_section']
                                                    ) {
                                                        $btn_theme = 'danger';
                                                    }

                                                    $link = '/admin/assets/rackview/'.$sdata['r_id'];
                                                    echo '<a href="'.$link.'" class="btn btn-xs btn-'.$btn_theme.' waves-effect waves-themed mr-1 mt-1">';
                                                    echo '# '.$section; 
                                                    echo '<span class="badge bg-'.$btn_theme.'-700 ml-2">'.$sdata['cnt'].'</span>';
                                                    echo '</a>';
                                                }
                                                ?>
                                            </li>
                                            <?php 
                                            endforeach; 
                                            ?>
                                        </ul>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>



                        <?php foreach($rows as $l_id=>$row) : ?>
                        <div class="card m-auto border">
                            <div class="card-header py-2 bg-success text-white">
                                <div class="card-title">
                                    <i class="fas fa-map-marker-alt mr-2"></i><?=$location_map[$l_id]?>
                                </div>
                            </div>
                            <div class="card-body">

                                <div class="card border m-auto m-lg-0">
                                    <ul class="list-group list-group-flush">

                                        <?php 
                                        $floor_data = $this->common->getDataByDuplPK($row, 'r_floor');
                                        //echo print_r($floor_data); exit;
                                        foreach($floor_data as $floor=>$fdata):
                                        ?>
                                        <li class="list-group-item border-info">
                                            <div>
                                                <h4><span class="badge badge-danger">Floor - <?=$floor?></span></h4>
                                            </div>

                                            <div class="row">
                                                <?php 
                                                $section_data = $this->common->getDataByDuplPK($fdata, 'r_section');
                                                foreach($section_data as $section=>$sdata):
                                                ?>
                                                <div class="col-12">
                                                    <div class="card shadow-1 shadow-hover-5 mb-g">
                                                        <div class="card-header p-3">
                                                            <i class="fal fa-hashtag mr-1"></i>
                                                            <strong class="mr-auto">Setion <?=$section?></strong>
                                                        </div>

                                                        <div class="card-body p-1 bg-faded pattern-4">
                                                            <div class="row">
                                                                <?php
                                                                $frame_data = $this->common->getDataByPK($sdata, 'r_frame');
                                                                foreach($frame_data as $frame=>$frdata):
                                                                ?>

                                                                <div class="col-md-6 col-lg-4 col-xl-4">
                                                                    <div class="card shadow-1 shadow-hover-5 mb-g p-1">
                                                                        <div class="card-body p-0">
                                                                            <table class="table table-sm col-lg-12 mb-0 fs-nano">
                                                                                <thead class="bg-info-500">
                                                                                    <tr>
                                                                                        <th class="text-center">Rack</th>
                                                                                        <th class="">
                                                                                            <?=$frame?>
                                                                                        </th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody>

                                                                                <tr>
                                                                                <?php
                                                                                $as = isset($as_data[$frdata['r_id']]) ? $as_data[$frdata['r_id']] : array();
                                                                                if(sizeof($as) < 1) :

                                                                                    //echo 'Empty';

                                                                                else :
                                                                                ?>
                                                                                 
                                                                                <th><?=sizeof($as)?></th>
                                                                                <td class="p-0" rowspan="<?=sizeof($as)?>">



                                                                                    <div class="dd" id="nestable-<?=$frdata['r_id']?>">
                                                                                        <ol class="dd-list">


                                                                                <?php
                                                                                    foreach($as as $o=>$s) :


                                                                                        $link = '<a href="/admin/assets/detail/servers/'.$s['am_id'].'#tab_assets" class="text-info" target="_blank">';
                                                                                        $link .= $s['am_name'];
                                                                                        $link .= '<i class="fal fa-external-link-square ml-1"></i>';
                                                                                        $link .= '</a>';


                                                                                        $txt = $this->assets_type_tb_business->iconTypeName($type_data[$s['am_assets_type_id']]).' ';
                                                                                        $txt .= $this->status_tb_business->iconStatusName($status_data[$s['am_status_id']]['s_color_code'], $status_data[$s['am_status_id']]['s_name']);
                                                                                        $txt .= '&nbsp;'.$link;
                                                                                        //$txt .= $link.'<br />';

                                                                                        /*
                                                                                        if(strlen($s['am_vmware_name']) > 0) {
                                                                                            $txt .= $s['am_vmware_name'].'<br />';
                                                                                        }
                                                                                        */

                                                                                    ?>
                                                                                        <li class="dd-item" data-id="<?=$s['am_id']?>">
                                                                                            <div class="dd-handle d-flex flex-row">
                                                                                                <?//=$s['am_rack_order']?>
												<div class="flex-1 mt-1 "><?=$txt?></div>
												<div class="width-2">

												    <span class="h-100 w-100 d-flex align-items-center justify-content-center text-dark">
                                                                                                        <i class="fal fa-ellipsis-v fs-lg btn-m-s mb-2"></i>
                                                                                                        <i class="fal fa-ellipsis-v fs-lg btn-m-s mb-2"></i>
													<i class="fal fa-ellipsis-v fs-lg btn-m-s mb-2"></i>
                                                                                                    </span>

                                                						</div>

                                                <?php
                                                    $btn_detail_visible = TRUE;
						    if($s['am_assets_type_id'] == 1) {
							if( ! isset($popover_contents[$s['am_id']]) || strlen($popover_contents[$s['am_id']]['contents']) < 1) {
                                                    		$btn_detail_visible = FALSE;
							}
						    }else {
                                                	$btn_detail_visible = FALSE;
						    }

						?>
						<?php if($btn_detail_visible == TRUE) : ?>

<div>
						<button type="button" id="detail" class="btn btn-xs btn-outline-danger waves-effect waves-themed" data-toggle="popover" data-trigger="hover" data-placement="right"  data-html="true" title="" data-content="<?=$popover_contents[$s['am_id']]['contents']?>" data-original-title="" data-template="<?=$popover_template?>">Detail
							<span class="badge bg-danger-800 ml-2"><?=$popover_contents[$s['am_id']]['size']?></span>
						</button>
</div>

						<?php endif; ?>
                                                                                            </div>
                                                                                        </li>
                                                                                    <?php 
                                                                                    // END @as
                                                                                    endforeach;
                                                                                    ?>

                                                                                        </ol>
                                                                                    </div>

                                                                                    </td>
                                                                                </tr>

                                                                                <?php
                                                                                for($i = (sizeof($as)-1); $i > 0; $i--) {
                                                                                    echo '<tr><th>'.$i.'</th></tr>';
                                                                                }
                                                                                ?>

                                                                                <?php
                                                                                endif;
                                                                                ?>
                                                                                </tbody>
                                                                            </table>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <?php 
                                                                // END frame
                                                                endforeach; 
                                                                ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php 
                                                // END section
                                                endforeach; 
                                                ?>
                                            </div>

                                        </li>
                                        <?php 
                                        // END floor
                                        endforeach; 
                                        ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <br />
                        <?php 
                        // END location
                        endforeach; 
                        ?>

                        
                    </div> <!-- .panel-content -->
                </div>
            </div>
        </div> <!-- .col -->
    </div> <!-- .row -->
    
</main>


<script type="text/javascript">

$(document).ready(function() {


    <?php
    $rack_ids = array_keys($as_data);
    foreach($rack_ids as $id) :
    ?>
    $('#nestable-<?=$id?>').nestable({
        group : 1,
        maxDepth: 1,
    }).on('change', function(e) {
        var list = e.length ? e : $(e.target);
        if (window.JSON) {
            //console.log(list.nestable('serialize'));
            var data = JSON.stringify(list.nestable('serialize'));
            updateRackOrder(data);
        } else {
            console.log('JSON browser support required for this demo.');
        }
    });
    <?php endforeach; ?>


    $('.dd-handle a').on('mousedown', function(e) {
        e.preventDefault();
        return false;
    });

    /*var list_num = $('#detail_table >tbody tr').length */

    function updateRackOrder(data) {
        var params = 'data=' + data;
        var url = '/<?=SHOP_INFO_ADMIN_DIR?>/assets/update_rack_order';
        $.post(url, params, function(res) {
            if(res.is_success) {
                //alert('Success');
            } else {
                Swal.fire("Submit Error !", res.msg, "error");
            }
        },'json');
    }

    
});
</script>
