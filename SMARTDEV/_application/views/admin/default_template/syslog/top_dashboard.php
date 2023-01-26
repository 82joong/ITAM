<?php 
//$page_css[] = "";

//include left panel (navigation)
//follow the tree in inc/config.ui.php
require_once realpath(dirname(__FILE__).'/../').'/inc/config.ui.php';


$active_key = array(
    "syslog",                   // 1 Depth menu
    "monitor",                  // 2(Sub) Depth menu
    "dashboard",            // 3(Sub) Depth menu
);
$page_nav[$active_key[0]]["active"] = true;
$page_nav[$active_key[0]]["sub"][$active_key[1]]['active'] = true;
$page_nav[$active_key[0]]["sub"][$active_key[1]]['sub'] [$active_key[2]]['active']= true;


include realpath(dirname(__FILE__).'/../').'/inc/nav.php';


$title_symbol = 'fa-chart-area';


$_ES_DASH_LINK = HTTPS_DASHBOARD_URL."/app/dashboards#/view/9f3d2770-9855-11ec-b22c-db079a821bb2?embed=true";
$_ES_DASH_LINK .= "&_g=(filters%3A!()%2CrefreshInterval%3A(pause%3A!t%2Cvalue%3A0)%2Ctime%3A(from%3Anow-1w%2Cto%3Anow))";
//$_ES_DASH_LINK .= "&_a=(columns:!(),filters:!(),interval:auto,query:(language:kuery,query:'host:%22special330%22%20'),sort:!(!('@timestamp',desc)))";
$_ES_DASH_LINK .= "&show-query-input=true&show-time-filter=true";

?>



<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL -->
<style type="text/css">

</style>


<main id="js-page-content" role="main" class="page-content">
 

    <?php include realpath(dirname(__FILE__).'/../').'/inc/breadcrumbs.php'; ?>


    <div class="row">

        <div class="col-xl-12">
            <div class="panel" style="background-color:#101010;">
                <div class="panel-hdr" style="background-color:#101010;">
                    <h2 class="text-success fw-900" >호스트별 서버모니터링 [es.syslog-*] Kibana Dashboard</h2>

                    <div class="panel-toolbar">
                        <?=genFullButton();?>
                    </div>

                </div>



                <div class="panel-container show">
                    <div class="panel-content">

                        <div class="text-danger font-italic">
                            <i class="fas fa-info-square ml-1"></i>
                            검색 방법 : host가 campus2 인 데이터 조회<br /> 
                            <span class="ml-3">- [KQL] > host:campus2 입력 또는</span><br />
                            <span class="ml-3">- [+Add filter] > field: host, operator: is, Value: campus2 입력 또는</span><br />
                            <span class="ml-3">- HostName 선택 > campus2 입력</span><br />
                        </div>

                        <div class="form-row form-group justify-content-md-center">
                            <iframe src="<?=$_ES_DASH_LINK?>" height="2050px" width="100%" frameborder=0 framespacing=0 marginheight=0 marginwidth=0 scrolling=no vspace=0></iframe>

                        </div>
                    </div>

                <div>
            </div>
        </div>
    </div>
</main>

<script type="text/javascript">
$(document).ready(function() {


});
</script>
