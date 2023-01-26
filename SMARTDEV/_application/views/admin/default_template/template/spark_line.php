<?php
$attr   = 'sparkType="'.$type.'"'; 
$attr  .= 'sparkWidth="'.$width.'"'; 
$attr  .= 'sparkHeight="'.$height.'"'; 
$attr  .= 'sparkLineColor="'.$color['spark_line_color']['line_color'].'"'; 
$attr  .= 'sparkFillColor="'.$color['spark_line_color']['fill_color'].'"'; 
$attr  .= 'sparkLineWidth="'.$linewidth.'"';
$attr  .= 'sparkNormalRangeMin="'.$min.'"';
$attr  .= 'sparkNormalRangeMax="'.$max.'"';
?>

<div id="<?=$_id?>" class="sparklines d-inline-flex" <?=$attr?> values="<?=$value?>">
    <canvas width="<?=$width?>" height="<?=$height?>" style="display:inline-block; width:<?=$width?>px; height:<?=$height?>px; vertical-align:top;"></canvas>
</div>
