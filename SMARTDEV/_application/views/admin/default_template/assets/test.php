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

.dd-handle {height:auto;}
</style>


<main id="js-page-content" role="main" class="page-content">


    <div class="row">
        <div class="col-xl-12">


            <div id="panel-4" class="panel">
                <div class="panel-hdr">
                    TEST
                </div>




                <div class="panel-container collapse show ">

                    <div class="dd" id="nestable">
                        <ol class="dd-list">
                            <li class="dd-item" data-id="1">
                                <div class="dd-handle">
                                    Item 1 <span>- Description <br /> Field</span>
                                </div>
                            </li>
                            <li class="dd-item" data-id="2">
                                <div class="dd-handle">
                                    Item 2 <span>- Description Field</span>
                                </div>
                            </li>
                            <li class="dd-item" data-id="3">
                                <div class="dd-handle">
                                    Item 3 <span>- Description Field</span>
                                </div>
                            </li>
                        </ol>
                    </div>
                    <textarea id="nestable-output"></textarea>


                    <div class="dd" id="nestable-2">
                        <ol class="dd-list">
                            <li class="dd-item" data-id="11">
                                <div class="dd-handle">
                                    Item 11 <span>- Description Field</span>
                                </div>
                            </li>
                            <li class="dd-item" data-id="12">
                                <div class="dd-handle">
                                    Item 12 <span>- Description Field</span>
                                </div>
                            </li>
                            <li class="dd-item" data-id="13">
                                <div class="dd-handle">
                                    Item 13 <span>- Description Field</span>
                                </div>
                            </li>
                        </ol>
                    </div>
                    <textarea id="nestable-output-2"></textarea>
                <div>

            </div>
        </div>

    </div>

</main>



<script type="text/javascript">

$(document).ready(function() {


    $('#nestable').nestable({
        group : 1
    }).on('change', function(e) {
        var list = e.length ? e : $(e.target);
        if (window.JSON) {
            console.log(list.nestable('serialize'));
        } else {
            output.val('JSON browser support required for this demo.');
        }
    });


        /*
    // PAGE RELATED SCRIPTS
    var updateOutput = function(e) {

        var list = e.length ? e : $(e.target), output = list.data('output');
        if (window.JSON) {

            console.log(list);
            console.log(output);

            output.val(window.JSON.stringify(list.nestable('serialize')));
            console.log(list.nestable('serialize'));
        } else {
            output.val('JSON browser support required for this demo.');
            console.log('TEST2');
        }
    };

    $('#nestable').nestable({
        group : 1
    }).on('change', updateOutput);
    $('#nestable-2').nestable({
        group : 1
    }).on('change', updateOutput);

    // output initial serialised data
    updateOutput($('#nestable').data('output', $('#nestable-output')));
    updateOutput($('#nestable-2').data('output', $('#nestable-output-2')));
    */

});
</script>
