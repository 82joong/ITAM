<?php 
//$page_css[] = "";

//include left panel (navigation)
//follow the tree in inc/config.ui.php
require_once realpath(dirname(__FILE__).'/../').'/inc/config.ui.php';


$mode = 'detail';
$active_key = array(
    "service",               // 1 Depth menu
    "vmware"          // 2(Sub) Depth menu
);
$page_nav[$active_key[0]]["active"] = true;
$page_nav[$active_key[0]]["sub"][$active_key[1]]["active"] = true;
include realpath(dirname(__FILE__).'/../').'/inc/nav.php';

$title_symbol = 'fa-warehouse';
?>

<style type="text/css">
</style>



<main id="js-page-content" role="main" class="page-content">
    <?php include realpath(dirname(__FILE__).'/../').'/inc/breadcrumbs.php'; ?>


    <div class="row">
        <div class="col-xl-12">
            <div class="panel">
                <div class="panel-hdr bg-fusion-400 fg-fusion-gradient">
                    <h2>Information</h2>


                    <div class="panel-toolbar ml-2">
                        <h5 class="m-0">
                            <span class="badge badge-danger fw-400 l-h-n">
                                <?=$row['vms_ip_address']?> 
                            </span>
                        </h5>
                    </div>
                </div>


                <div class="panel-container show">

                    <div class="panel-content p-0">
                        <div class="row row-grid no-gutters">
                            
                        </div>
                    </div>

                <div>
            </div>
        </div>
    </div>




    <?php
    $top = array_shift($sys);

    $vms_memo = strlen($row['vms_memo']) > 0 ? $row['vms_memo'] : '&nbsp;';
    $info = array(
        array('tit' => $row['vms_name'], 'dsc' =>  $vms_memo, 'ico' => 'tag'),
        array('tit' => 'Processor', 'dsc' => $top['cpu_model'], 'ico' => 'microship'),
        array('tit' => 'MySQL', 'dsc' => $top['mysql_version'], 'ico' => 'database'),
        array('tit' => 'up '.$top['live'].' days', 'dsc' => 'Last Time : '.$top['regdate'], 'ico' => 'clock'),
    );
    ?>
    <div class="row">
        <?php foreach($info as $k=>$in) : ?>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-fusion-500 rounded overflow-hidden position-relative text-white mb-g">
                <div class="">
                    <h3 class="display-4 d-block l-h-n m-0 fw-500 text-warning">
                        <?=$in['tit']?> 
                        <small class="m-0 l-h-n text-white"><?=$in['dsc']?></small>
                    </h3>
                </div>
                <i class="fal fa-<?=$in['ico']?> position-absolute pos-right pos-bottom opacity-15 mb-n1 mr-n1" style="font-size:6rem"></i>
            </div>
        </div>
        <?php endforeach; ?>
    </div>


    <div class="row">

        <div class="col-lg-12 sortable-grid ui-sortable">
            <div id="panel-1" class="panel panel-sortable" 
                data-panel-fullscreen="false" 
                data-panel-collapsed="false" 
                data-panel-color="false" 
                data-panel-locked="false" 
                data-panel-refresh="false" 
                data-panel-reset="false" 
                role="widget">
                <div class="panel-hdr" role="heading">
                    <h2 class="ui-sortable-handle">Load Averages</h2>
                </div>


                <div class="panel-container show" role="content">


                    <?php /*?>
                    <div class="panel-content border-faded border-left-0 border-right-0 border-top-0">
                        <div class="row no-gutters">


                            <div class="col-lg-7 col-xl-8">
                                <div class="position-relative">
                                    <div class="custom-control custom-switch position-absolute pos-top pos-left ml-5 mt-3 z-index-cloud">
                                        <input type="checkbox" class="custom-control-input" id="start_interval">
                                        <label class="custom-control-label" for="start_interval">Live Update</label>
                                    </div>
                                    <div id="updating-chart" style="height: 242px; padding: 0px; position: relative;">
                                        <canvas class="flot-base" width="576" height="242" style="direction: ltr; position: absolute; left: 0px; top: 0px; width: 576.328px; height: 242px;"></canvas>
                                        <canvas class="flot-overlay" width="576" height="242" style="direction: ltr; position: absolute; left: 0px; top: 0px; width: 576.328px; height: 242px;"></canvas>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <?php */?>


                    <div class="panel-content border-faded border-left-0 border-right-0 border-top-0">
                        <div class="row no-gutters">
                            <div class="col-lg-12 col-xl-12 pl-lg-12">
                                <div id="flot-area" style="width:100%;height:300px;"></div>
                            </div>
                        </div>
                    </div>


                    <?php 
                    $pie_line = array();
                    $load = array();
                    $live_days = 0;
                    $cnt = 0;
                    $load_yaxis_limit = 1;

                    foreach(array_reverse($sys) as $_id=>$val) {
                        $pie_line['cpu']['data'][] = number_format($val['cpu'], 2);
                        $pie_line['mem']['data'][]  = number_format($val['mem'], 2);
                        $pie_line['swap']['data'][] = number_format($val['swap'], 2);

                        $load['load1'][] = '['.($val['ux_st']*1000).', '.number_format($val['load1'], 2).']';
                        $load['load2'][] = '['.($val['ux_st']*1000).', '.number_format($val['load2'], 2).']';
                        $load['load3'][] = '['.($val['ux_st']*1000).', '.number_format($val['load3'], 2).']';

                        $load_max = array($val['load1'], $val['load2'], $val['load3']);

                        if($load_yaxis_limit < max($load_max)) {
                            $load_yaxis_limit = max($load_max);
                        }

                        if($cnt == 0) {
                            $live_days = $val['live'];
                        }
                        $cnt = $cnt + 1;
                    }

                    $pie_line['cpu']['color'] = $color_set['primary'];
                    $pie_line['mem']['color'] = $color_set['success'];
                    $pie_line['swap']['color'] = $color_set['info'];

                    $load_yasxis_limit = (( $load_yasxis_limit / 10 ) * 10 ) + 1;

                    //echo print_r($load); exit;
                    ?>


                    <? /* START :::::: Easy - Pie Chart */ ?>
                    <div class="panel-content p-0">
                        <div class="row row-grid no-gutters">

                            <?php 
                            foreach($pie_line as $k=>$v) : 
                                $_key = strtoupper($k);
                            ?>

                            <div class="col-sm-12 col-md-6 col-lg-6 col-xl-3">
                                <div class="px-3 py-2 d-flex align-items-center">
                                    <?php 
                                    // https://github.com/rendro/easy-pie-chart
                                    $config = array(
                                        '_id'           => 'pie-'.$_key,
                                        'width'         => 95,
                                        'height'        => 95,
                                        'percent'       => $v['data'][0],
                                        'piesize'       => 95,
                                        'linewidth'     => 10,
                                        'linecap'       => 'butt',
                                        'scalelength'   => 3,
                                        'title'         => $v['data'][0],
                                        'sub_title'     => 'Percent (%)',
                                        'color'         => $v['color'] 
                                    );
                                    echo $this->load->view('/admin/default_template/template/easy_pie.php', $config, true); 
                                    ?>


                                    <span class="d-inline-block ml-2 text-muted">
                                        <?=$_key?> 
                                        <i class="fal fa-caret-down color-success-500 ml-1"></i>
                                    </span>

                                    <div class="ml-auto d-inline-flex align-items-center">
                                        <?php 
                                        $max = max(array_values($v['data']));
                                        $min = min(array_values($v['data']));

                                        // https://omnipotent.net/jquery.sparkline/#s-docs
                                        $config = array(
                                            '_id'           => 'line-'.$_key,
                                            'width'         => 100,
                                            'height'        => 70,
                                            'type'          => 'line', 
                                            'linewidth'     => 1,
                                            'color'         => $v['color'], 
                                            'value'         => implode(',', array_values($v['data'])),
                                            'min'           => ((ceil($min/10) * 10) - 10) >= 0 ? ((ceil($min/10) * 10) - 10) : 0,
                                            'max'           => ((floor($max/10) * 10) + 10) >= 100 ? 100 :  ((floor($max/10) * 10) + 10)
                                        );
                                        //echo $config['min'].'<br />';
                                        //echo $config['max'].'<br />';
                                        echo $this->load->view('/admin/default_template/template/spark_line.php', $config, true); 
                                        ?>


                                        <?php
                                        $class = 'badge-success';
                                        if($max > constant("{$_key}_MAX")) {
                                            $class = 'badge-danger';
                                        }
                                        ?>
                                        <div class="d-inline-flex flex-column small ml-2">
                                            <span class="d-inline-block badge <?=$class?> opacity-50 text-center p-1 width-6">
                                                <?=$max?> %
                                            </span>
                                            <span class="d-inline-block badge bg-fusion-300 opacity-50 text-center p-1 width-6 mt-1">
                                                <?=$min?> %
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>


                            <div class="col-sm-12 col-md-6 col-lg-6 col-xl-3">
                                <div class="px-3 py-2 d-flex flex-column align-items-center">

                                    <div class="mt-3">
                                        <span class="d-inline-block ml-2 text-muted">
                                            Listen Ports 
                                        </span>
                                    </div>

                                    <div class="">
                                        <ul class="pagination mb-0 mt-3 align-items-end">
                                            <?php
                                            $port = explode(',', $top['listen_port']); 
                                            $alert_port = array_diff($port, PORT_MAX);

                                            foreach($port as $p) :
                                                $class = "border border-info text-info";
                                                if(in_array($p, $alert_port)) $class = "badge-danger fs-xl";
                                            ?>
                                            <li class="page-item">
                                                <span class="badge pl-2 pr-2 <?=$class?>"><?=$p?></span>
                                            </li>
                                            <?php
                                            endforeach;
                                            ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>


                        </div>
                    </div>
                    <? /* END :::::: Easy - Pie Chart */ ?>



                </div>
            </div>
        </div>
    </div>




    <div class="row mb-4">
        
        <div class="col-lg-6 sortable-grid ui-sortable" role="widget">
            <div class="panel-hdr bg-fusion-500" role="heading">
                <h2 class="ui-sortable-handle text-muted">Top</h2>
            </div>

            <div class="panel-container show" role="content">
                <div class="panel-content ">
                    <table class="table table-bordered table-sm table-hover table-dark m-0">
                        <thead class="thead-themed">
                            <tr class="text-warning">
                                <th>#</th>
                                <th>COMMAND</th>
                                <th>WCPU</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for($i=1; $i<=5; $i++) : ?>
                            <tr>
                                <th scope="row"><?=$i?></th>
                                <td><?=$top['top'.$i.'_name']?></td>
                                <td><?=$top['top'.$i.'_cpu']?></td>
                            </tr>
                            <?php endfor; ?>
                            
                        </tbody>
                    </table>
                </div>
            </div>
        </div>


        <?php
        $disk = explode('&', $top['disk']);
        $disk = array_filter($disk);
        ?>
        <div class="col-lg-6 sortable-grid ui-sortable" role="widget">
            <div class="panel-hdr bg-fusion-500" role="heading">
                <h2 class="ui-sortable-handle text-muted">Disk Space</h2>
            </div>

            <div class="panel-container show" role="content">
                <div class="panel-content ">
                    <table class="table table-bordered table-sm table-hover table-dark m-0">
                        <thead class="thead-themed">
                            <tr class="text-warning">
                                <th>#</th>
                                <th>FileSystem</th>
                                <th>Capacity</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($disk as $k=>$d) : ?>
                            <?php 
                            $df = explode('=', $d); 
                            $per = str_replace('%', '', trim($df[1]));

                            $class = '';
                            if( intVal($per) > (DISK_MAX*1) ) $class = 'bg-danger';
                            ?>
                            <tr class="<?=$class?>">
                                <th scope="row"><?=$k+1?></th>
                                <td><?=$df[0]?></td>
                                <td><?=$df[1]?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>



</main>

<script>



$(document).ready(function() {

    <?php
    //https://github.com/10bestdesign/jqvmap
    ?>
    $('#vector-map').vectorMap({
        map: 'world_en',
        backgroundColor: '#a5bfdd',
        borderColor: '#818181',
        borderOpacity: 0.25,
        borderWidth: 1,
        color: '#f4f3f0',
        enableZoom: true,
        hoverColor: '#c9dfaf',
        hoverOpacity: null,
        normalizeFunction: 'linear',
        scaleColors: ['#b6d6ff', '#005ace'],
        selectedColor: '#c9dfaf',
        selectedRegions: null,
        showTooltip: true,
        onRegionClick: function(element, code, region)
        {
            var message = 'You clicked "'
            + region
            + '" which has the code: '
            + code.toUpperCase();

            alert(message);
        }

    });


    var dataSetPie = [
        {
            label: "Asia",
            data: 4119630000,
            color: color.primary._500
        },
        {
            label: "Latin America",
            data: 590950000,
            color: color.info._500
        },
        {
            label: "Africa",
            data: 1012960000,
            color: color.warning._500
        },
        {
            label: "Oceania",
            data: 95100000,
            color: color.danger._500
        },
        {
            label: "Europe",
            data: 727080000,
            color: color.success._500
        },
        {
            label: "North America",
            data: 344120000,
            color: color.fusion._400
        }
    ];


    $.plot($("#flotPie"), dataSetPie,
    {
        series:
        {
            pie:
            {
                innerradius: 0.5,
                show: true,
                radius: 1,
                label:
                {
                    show: true,
                    radius: 2 / 3,
                    threshold: 0.1
                }
            }
        },
        legend:
        {
            show: false
        }
    });


    /* TAB 1: UPDATING CHART */
    var data = [],
    totalPoints = 200;
    var getRandomData = function() {
        if (data.length > 0) data = data.slice(1);

        // do a random walk
        while (data.length < totalPoints)
        {
            var prev = data.length > 0 ? data[data.length - 1] : 50;
            var y = prev + Math.random() * 10 - 5;
            if (y < 0)
                y = 0;
            if (y > 100)
                y = 100;
            data.push(y);
        }

        // zip the generated y values with the x values
        var res = [];
        for (var i = 0; i < data.length; ++i)
            res.push([i, data[i]])
        return res;
    }



    // setup plot
    var options = {
        colors: [color.primary._700],
        series:
        {
            lines:
            {
                show: true,
                lineWidth: 0.5,
                fill: 0.9,
                fillColor:
                {
                    colors: [
                    {
                        opacity: 0.6
                    },
                    {
                        opacity: 0
                    }]
                },
            },
            shadowSize: 0 // Drawing is faster without shadows
        },
        grid:
        {
            borderColor: '#F0F0F0',
            borderWidth: 1,
            labelMargin: 5
        },
        xaxis:
        {
            color: '#F0F0F0',
            font:
            {
                size: 10,
                color: '#999'
            }
        },
        yaxis:
        {
            min: 0,
            max: 100,
            color: '#F0F0F0',
            font:
            {
                size: 10,
                color: '#999'
            }
        }
    };

    //var plot = $.plot($("#updating-chart"), [getRandomData()], options);

    /* live switch */
    $('input[type="checkbox"]#start_interval').click(function() {
        if ($(this).prop('checked')) {
            $on = true;
            updateInterval = 1500;
            update();
        } else {
            clearInterval(updateInterval);
            $on = false;
        }
    });
    var update = function() {
        if($on == true) {
            plot.setData([getRandomData()]);
            plot.draw();
            setTimeout(update, updateInterval);
        }else {
            clearInterval(updateInterval)
        }
    }




    /* flot area */
    var dataSet1 = [
       <?php echo implode(', ', $load['load1']); ?> 
    ];
    var dataSet2 = [
       <?php echo implode(', ', $load['load2']); ?> 
    ];
    var dataSet3 = [
       <?php echo implode(', ', $load['load3']); ?> 
    ];

    var flotArea = $.plot($('#flot-area'), [
        {
            data: dataSet1,
            label: '1 Min',
            color: color.success._500, 
        },
        {
            data: dataSet2,
            label: '5 Min',
            color: color.info._500
        },
        {
            data: dataSet3,
            label: '15 Min',
            color: color.primary._700
        }
    ],
    {
        series: {
            lines: {
                show: true,
                lineWidth: 1,
                fill: 0.4
            },
            shadowSize: 0
        },
        points: {
            show: true,
        },
        legend: {
            noColumns: 1,
            position: 'nw'
        },
        grid: {
            hoverable: true,
            clickable: true,
            borderColor: '#ddd',
            borderWidth: 0,
            labelMargin: 5,
            backgroundColor: '#fff'
        },
        yaxis: {
            min: 0,
            max: <?=$load_yaxis_limit?>,
            color: '#eee',
            font: {
                size: 10,
                color: '#999'
            }
        },
        xaxis: {
            mode: "time",
            timezone: "browser",
            color: '#eee',
            font: {
                size: 10,
                color: '#999'
            },
        },
        tooltip: true,
        tooltipOpts: {
            cssClass: 'tooltip-inner',
            content: "<span class='text-warning fw-500'>%x</span> was <span class='text-success fw-500'>%y</span>",
            dateFormat: "%Y-%M-%D",
            defaultTheme: false
         }
    });
    /* flot area -- end */




});
</script>
