

<div class="badge badge-danger">
    <i class="fa fa-chevron-circle-down mr-1"></i>Detail Info
</div>
<table class="table table-bordered table-sm text-center fs-nano">
    <thead class="bg-primary-500">
        <tr>
            <th>Access</th>
            <th>Act</th>
            <th>Access Table</th>
            <th>Access Key</th>
            <th>IP</th>
            <th>Created At</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><?=$h_name?>(<?=$h_loginid?>)</td>
            <td>
                <div class="badge badge-dark"><?=$h_act_mode?></div>
            </td>
            <td><?=$h_act_table?></td>
            <td><?=$h_act_key?></td>
            <td><?=$h_ip?></td>
            <td><?=$h_created_at?></td>
        </tr>
    </tbody>
</table>

<?php 
$log = array();
if( $this->common->is_serialized($h_serialize) ) {
    $log = unserialize($h_serialize);
}
?>



<?php if( isset($log['del_msg']) && strlen($log['del_msg']) > 0 ) : ?>
<div class="badge badge-danger mb-1">
    <i class="fa fa-chevron-circle-down mr-1"></i>Delete Message
</div>
<table class="table table-striped table-bordered table-sm tbl-log fs-nano">
    <tr>
        <td><?=$log['del_msg']?></td>
    </tr>
</table>
<?php endif;?>


<?php if( isset($log['params']) && sizeof($log['params']) > 0 ) : ?>
<div class="badge badge-success mb-1">
    <i class="fa fa-chevron-circle-down mr-1"></i>Params
</div>
<table class="table table-striped table-bordered table-sm tbl-log fs-nano">
    <?php foreach($log['params'] as $p_key=>$p_val) : ?>
    <tr>
        <th style="width: 20%"><?=$p_key?></th>
        <td>

            <?php 
            if( $this->common->is_serialized($p_val) ) {
                $p_val = unserialize($p_val);
            }
            ?>

            <?php if( is_array($p_val) ) : ?>
                <?php _printArrayVal($p_val); ?> 
            <?php else: ?>
                <?=$p_val?>
            <?php endif; ?>

        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>


<?php if( isset($log['prev_data']) && sizeof($log['prev_data']) > 0 ) : ?>
<div class="badge badge-success mb-1">
    <i class="fa fa-chevron-circle-down mr-1"></i>Previous Data
</div>
<table class="table table-striped table-bordered table-sm tbl-log fs-nano">
    <?php foreach($log['prev_data'] as $p_key=>$p_val) : ?>
    <tr>
        <th style="width: 20%"><?=$p_key?></th>
        <td>

            <?php 
            if( $this->common->is_serialized($p_val) ) {
                $p_val = unserialize($p_val);
            }
            ?>

            <?php if( is_array($p_val) ) : ?>
                <?php _printArrayVal($p_val); ?> 
            <?php else: ?>
                <?=$p_val?>
            <?php endif; ?>



        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>


<?php 
function _printArrayVal($data) {
    if( is_array($data) ) {
        foreach($data as $key=>$val) {
            echo '<div class="row m-1" style="border-bottom: 1px dashed #ccc;">';
            echo '<div class="p-1"><code>@'.$key.'</code></div>';
            echo '<div class="p-1">';
            _printArrayVal($val);
            echo '</div>';
            echo '</div>';
        }
    } else { 
        echo $data;
    }   
}
?>
