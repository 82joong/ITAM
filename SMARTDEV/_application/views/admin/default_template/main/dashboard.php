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

<?php 
foreach($model_assets as $m_id=>$rows) { 
	$name = $assets_map[$m_id]['at_name'];
	$icon = $assets_map[$m_id]['at_icon'];
?>
        <div class="col-xl-4 col-md-6 col-sm-12 col-xs-12">
	    <div id="panel-4" class="panel">
                <div class="panel-hdr">
                    <h2 class="font-weight-bold">
		    	<i class="fal <?=$icon?> mr-2"></i>
			<?=$name?> 모델별 수량 현황	
                    </h2>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">
                        <div aria-live="polite" aria-atomic="true" class="d-flex justify-content-center align-items-center">
				<canvas id="bar_chart_<?=strtolower($name)?>" ></canvas>
                        </div>
                    </div>
                </div>
            </div> 
	</div>
<?php
}
?>

    </div>


    <div class="row">

        <div class="col-xl-12">
            <div id="panel-4" class="panel">
                <div class="panel-hdr">
                    <h2 class="font-weight-bold">
                        <i class="fal fa-chart-bar mr-2"></i>
                        자산 유형별 최근 <?=$previous_month?> 개월 간 등록된 자원의 수 
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


        <div class="col-12">
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
                        <i class="fal fa-chart-bar mr-2"></i>
                        자산 상태별 현황 
                    </h2>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">
                        <canvas id="status_bar" class="col-12" style="height:200px;"></canvas>
                    </div>
                </div>
            </div> 
	</div>


    </div> <!-- END .row -->




    <div class="row">
        <?php foreach($company_assets as $c_id=>$ca) : ?>
	<?php if($ca['c_count'] < 1) { continue; } ?>
        <div class="col-sm-4 col-xl-2">
            <div class="p-2 rounded position-relative text-danger mb-g shadow border border-danger" style="background-color:#fff;">
                <div class="">
                    <h4 class="display-4 d-block l-h-n m-0 fw-500">
                        <?=number_format($ca['c_count'])?>
                        <small class="m-0 l-h-n"><?=$ca['c_name']?></small>
                    </h4>
                </div>
		<img src="<?=$ca['c_img_path']?>" class="position-absolute pos-right pos-top opacity-50 mt-2 mr-2" style="width:60%;height:auto;">
            </div>
        </div>
        <?php endforeach; ?>
    </div>



    <div class="row">
        <div class="col-xl-12">
            <div id="panel-4" class="panel">
                <div class="panel-hdr">
                    <h2 class="font-weight-bold">
                        <i class="fal fa-chart-line mr-2"></i>
                       	누적 인력 현황 
                    </h2>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">
                        <div aria-live="polite" aria-atomic="true" class="d-flex justify-content-center align-items-center">
                            <canvas id="people_total_chart" class="col-12" style="height:130px;"></canvas>
                        </div>
                    </div>
                </div>
            </div> 
        </div>
    </div>


    <div class="row">
        <div class="col-xl-12">
            <div id="panel-4" class="panel">
                <div class="panel-hdr">
                    <h2 class="font-weight-bold">
                        <i class="fal fa-chart-bar mr-2"></i>
                        최근 입퇴사자 현황 
                    </h2>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">
                        <div aria-live="polite" aria-atomic="true" class="d-flex justify-content-center align-items-center">
                            <canvas id="people_stacked" class="col-12" style="height:180px;"></canvas>
                        </div>
                    </div>
                </div>
            </div> 
        </div>
    </div>



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




/* Status Bar */

var statusBar = function()
{
    var config = {
        type: 'bar',
        data: {
            datasets: [{
                data: [<?=implode(',', $pie_data['datasets'])?>],
		backgroundColor: ['<?=implode("','", $pie_data['colors'])?>'],
                label: '' 
            }],
            labels: ['<?=implode("','", $pie_data['labels'])?>'],
        },
        options: {
            responsive: true,
            legend: {
                display: false,
            }
        }
    };
    new Chart($("#status_bar").get(0).getContext("2d"), config);
}
/* Status Bar -- end */



/* Bar chart */
<?php 
foreach($model_assets as $m_id=>$rows) { 

	$bgcolor = $bar_data['colors'][$m_id]['background'];
	$border = $bar_data['colors'][$m_id]['border'];

	$name = $assets_map[$m_id]['at_name'];
	$labels = implode("','", $rows['labels']);
	$values = implode(",", $rows['data']);
?>

var barChart<?=$name?> = function()
{
    var config = {
        type: 'bar',
        data: {
		labels: ['<?=$labels?>'],
	      	datasets: [{
			label: '',
			data: [<?=$values?>],
			borderWidth: 1,
			backgroundColor: '<?=$bgcolor?>',
			borderColor: '<?=$border?>',
	      	}] 
        },
        options: {
            responsive: true,
	    legend: {
		display: false
	    },
	    scales: {
	    	yAxes : [{
		    ticks: {
			beginAtZero: true,
			min: 0,	
		    }	
		}],
		y: { beginAtZero: true }
	    }
        }
    };
    new Chart($("#bar_chart_<?=strtolower($name)?>").get(0).getContext("2d"), config);
}
<?php 
}
?>
/* pie chart -- end */



/* people stacked */
var peopleStacked = function()
{
    var barStackedData = {
        labels: ['<?=implode('\', \'', $people_data['labels'])?>'],
        datasets: [
        <?php foreach($people_data['datasets'] as $k=>$v) :?>
            {
                label: "<?=$k?>",
                backgroundColor: "<?=$people_data['colors'][$k]['background']?>",
                borderColor: "<?=$people_data['colors'][$k]['background']?>",
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
                    ticks: {beginAtZero: true, fontSize: 9}
                }]
            }
        }
    }
    new Chart($("#people_stacked").get(0).getContext("2d"), config);
}
/* people stacked -- end */



/* People Total chart */
var peopleTotalChart = function()
{
    var config = {
        type: 'line',
        data:
        {
            labels: ['<?=implode('\', \'', $people_data['labels'])?>'],
            datasets: [
            {
                label: "",
		lineTension: 0,
		backgroundColor: 'rgba(29,201,183, 0.2)',
                borderColor: color.success._500,
                pointBackgroundColor: color.success._700,
                pointBorderColor: 'rgba(0, 0, 0, 0)',
                pointBorderWidth: 1,
                borderWidth: 1,
                pointRadius: 3,
                pointHoverRadius: 4,
                data: ['<?=implode('\', \'', $people_data['daily_total'])?>'],
                fill: true 
            }]
        },
        options:
        {
            responsive: true,
	    legend:{display: false, labels: {display: false}},
            title:{display: false, text: 'Line Chart'},
            tooltips:{mode: 'index', intersect: false,},
            hover:{mode: 'nearest', intersect: true},
            scales:
            {
                xAxes: [
                {
                    display: true,
                    gridLines: {display: true,color: "#f2f2f2"},
                    ticks: {beginAtZero: true,fontSize: 11}
                }],
                yAxes: [
                {
                    display: true,
                    gridLines: {display: true, color: "#f2f2f2"},
                    ticks: {suggestedMin: 800, stepValue:50, fontSize: 11}
                }]
            }
        }
    };
    new Chart($("#people_total_chart").get(0).getContext("2d"), config);
}
/* People Total chart -- end */



/* initialize all charts */
$(document).ready(function() {
    barStacked();
    peopleStacked();
    peopleTotalChart();

<?php 
foreach($model_assets as $m_id=>$rows) { 
	$name = $assets_map[$m_id]['at_name'];
?>
    barChart<?=$name?>();

<?php
}
?>
    statusBar();
});
</script>
