<?php
$attr  = 'data-percent="'.$percent.'"';
$attr .= 'data-piesize="'.$piesize.'"';
$attr .= 'data-linewidth="'.$linewidth.'"';
$attr .= 'data-linecap="'.$linecap.'"';
$attr .= 'data-scalelength="'.$scalelength.'"';
?>


<div id="<?=$_id?>" class="js-easy-pie-chart <?=$color['easy_pie_color']['fill_color']?> position-relative d-inline-flex align-items-center justify-content-center" <?=$attr?>>
    <div class="d-flex flex-column align-items-center justify-content-center position-absolute pos-left pos-right pos-top pos-bottom fw-300 fs-lg">
        <span class="js-percent d-block text-dark"><?=$title?></span>
        <div class="d-block fs-xs text-dark opacity-70">
            <small><?=$sub_title?></small>
        </div>
    </div>
    <canvas height="<?=$height?>" width="<?=$width?>" style="position:absolute"></canvas>
</div>


