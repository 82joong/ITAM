<?php 
//$page_css[] = "";

//include left panel (navigation)
//follow the tree in inc/config.ui.php
require_once realpath(dirname(__FILE__).'/../').'/inc/config.ui.php';


$mode = '';
$active_key = array(
    "dashboard",               // 1 Depth menu
);
$page_nav[$active_key[0]]["active"] = true;
include realpath(dirname(__FILE__).'/../').'/inc/nav.php';


?>
<link rel="stylesheet" media="screen, print" href="/admin_assets/css/statistics/chartjs/chartjs.css">

<style>
.panel-tag {font-size:12px;}
/* canvas {height:350px!important;} */

</style>


<main id="js-page-content" role="main" class="page-content">


    <ol class="breadcrumb page-breadcrumb">
        <li class="breadcrumb-item"><a href="/<?=SHOP_INFO_ADMIN_DIR?>">Home</a></li>
        <li class="breadcrumb-item active"><?=$page_nav[$active_key[0]]['title']?></li>
        <li class="position-absolute pos-top pos-right d-none d-sm-block"><span class="js-get-date"></span></li>
    </ol>
    <div class="subheader">
        <h1 class="subheader-title">
            <i class='subheader-icon fal fa-chart-area'></i> 
            <?=$page_nav[$active_key[0]]['title']?> 

            <?php if(isset($mode) && strlen($mode) > 0) :?>
            : <span class='fw-300'><?=ucfirst($mode)?></span>
            <?php endif;?>

            <?php if(isset($page_nav[$active_key[0]]['description'])) : ?>
            <small><?=$page_nav[$active_key[0]]['description']?></small>
            <?php endif; ?>
        </h1>
    </div>





    <div class="row">
        <?php foreach($assets_map as $at_id=>$at) : ?>
        <div class="col-sm-4 col-xl-2">
            <div class="p-2 rounded position-relative text-white mb-g shadow border border-light pattern-1" style="background-color:<?=$at['at_color']?>;opacity:0.7;">
                <div class="">
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">
                        <?=number_format($at['count'])?>
                        <small class="m-0 l-h-n"><?=$at['at_name']?></small>
                    </h3>
                </div>
                <i class="fas <?=$at['at_icon']?>  position-absolute pos-right pos-bottom opacity-15 mb-n1 mr-n1" style="font-size:5rem"></i>
            </div>
        </div>
        <?php endforeach; ?>
    </div>




    <div class="row">

        <div class="col-xl-12">
            <div id="panel-4" class="panel">
                <div class="panel-hdr">
                    <h2 class="font-weight-bold">
                        <i class="fal fa-chart-bar mr-2"></i>
                        자산 유형별 최근 <?=$previous_date?>일간 배치(등록)된 자원의 수 
                    </h2>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">
                        <div aria-live="polite" aria-atomic="true" class="d-flex justify-content-center align-items-center">
                            <div id="bar_stacked" class="col-12">
                                <canvas height="60"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div> 
        </div>


        <div class="col-6">
            <div id="panel-4" class="panel">
                <div class="panel-hdr">
                    <h2 class="font-weight-bold">
                        <i class="fal fa-table mr-2"></i>
                        위치별 자산 현황
                    </h2>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">
                        <div id="rack_table" class="col-12">
                            <table class="table table-bordered table-hover table-sm table-dark fs-nano text-center m-0">
                                <thead class="thead-themed">
                                    <tr>
                                        <th class="bg-primary-500">Assets Category</th>
                                        <?php foreach($location_map as $l_id=>$l_name): ?>
                                        <th><?=$l_name?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $i = 1;
                                    foreach($category_map as $c_id=>$c_name): 
                                        $rows = isset($tbl_data[$c_id]) ? $tbl_data[$c_id] : array();
                                        if(sizeof($rows) < 1) continue;
                                        $rows = $this->common->getDataByPK($rows, 'am_location_id');
                                    ?>
                                    <tr>
                                        <td class="bg-primary-500"><?=$c_name?></td>
                                        <?php 
                                        foreach($location_map as $l_id=>$l_name): 
                                        $cnt = isset($rows[$l_id]) ? $rows[$l_id]['cnt'] : 0;

                                        $class = '';
                                        if($cnt > 0) $class = 'color-warning-500';
                                        ?>
                                        <th class="<?=$class?>"><?=number_format($cnt)?></th>
                                        <?php 
                                        endforeach; 
                                        ?>
                                    </tr>
                                    <?php 
                                        $i = $i + 1;
                                    endforeach; 
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div> 
        </div>



        <div class="col-6">
            <div id="panel-4" class="panel">
                <div class="panel-hdr">
                    <h2 class="font-weight-bold">
                        <i class="fal fa-chart-pie mr-2"></i>
                        자산 상태별 현황 
                    </h2>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">
                        <div id="pie_chart" class="col-12">
                            <canvas height="120"></canvas>
                        </div>
                    </div>
                </div>
            </div> 
        </div>

    </div> <!-- END .row -->

</main>




<script src="/admin_assets/js/statistics/chartjs/chartjs.bundle.js"></script>
<script>




/* bar stacked */
var barStacked = function()
{
    var barStackedData = {
        labels: ['<?=implode('\', \'', $bar_data['labels'])?>'],
        datasets: [
        <?php foreach($bar_data['datasets'] as $k=>$v) :?>
            {
                label: "<?=$assets_map[$k]['at_name']?>",
                backgroundColor: "<?=$bar_data['colors'][$k]['background']?>",
                borderColor: "<?=$bar_data['colors'][$k]['background']?>",
                borderWidth: 1,
                data: [<?=implode(', ', $v)?>]
            },
        <?php endforeach; ?>
        ]
    };
    var config = {
        type: 'bar',
        data: barStackedData,
        options: {
            responsive: true,
            legend:
            {
                display: true,
                labels: {display: false}
            },
            scales: {
                yAxes: [{
                    stacked: true,
                    gridLines: {display: true, color: "#f2f2f2"},
                    ticks: {beginAtZero: true, fontSize: 11}
                }],
                xAxes: [{
                    stacked: true,
                    gridLines: {display: true, color: "#f2f2f2"},
                    ticks: {beginAtZero: true, fontSize: 10}
                }]
            }
        }
    }
    new Chart($("#bar_stacked > canvas").get(0).getContext("2d"), config);
}
/* bar stacked -- end */


/* pie chart */
var pieChart = function()
{
    var config = {
        type: 'pie',
        data: {
            datasets: [{
                data: [<?=implode(', ', $pie_data['datasets'])?>],
                backgroundColor: [
                    <?php foreach($pie_data['colors'] as $v) : ?>
                    '<?=$v?>',
                    <?php endforeach; ?>
                ],
                label: 'My dataset' // for legend
            }],
            labels: ['<?=implode('\', \'', $pie_data['labels'])?>'],
        },
        options: {
            responsive: true,
            legend: {
                display: true,
                position: 'bottom',
            }
        }
    };
    new Chart($("#pie_chart > canvas").get(0).getContext("2d"), config);
}
/* pie chart -- end */


/* initialize all charts */
$(document).ready(function() {
    barStacked();
    pieChart();
});
</script>
