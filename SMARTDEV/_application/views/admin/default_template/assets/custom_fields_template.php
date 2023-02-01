
<div class="row">
    <input type="hidden" name="fieldset_id" value="<?=$fieldset_data['fs_id']?>">
    <span class="badge border border-danger text-danger mr-2">Custom Fields</span>

    <?php /* if($mode == 'insert') : ?>
    <b><?=$fieldset_data['fs_name']?></b>
    <a href="/admin/manage/fieldset_detail/<?=$fieldset_data['fs_id']?>" target="_blank" class="btn btn-primary btn-xs waves-effect waves-themed ml-3 ">
        <i class="fal fa-external-link"></i> Detail
    </a>
    <?php endif; */ ?>
</div>
<hr />

<?php
foreach($custom_data as $custom) {

    $value_data = array();
    $value = $custom[$prefix.'value'];
    $required = $custom[$prefix.'required'];

    $type = $format_map[$custom[$prefix.'format']]['inputmask'];
    $name = strtolower($custom[$prefix.'name']);
    $id = $custom[$prefix.'id'];

    echo '<div class="col-12 mb-3">'.PHP_EOL;
    echo '<label class="form-label" for="'.$name.'">'.PHP_EOL;
    echo $custom[$prefix.'name'].PHP_EOL;
    echo '</label>'.PHP_EOL;

    $opt = '';
    if($required == 'YES') {
        $opt .= 'required';
        echo '<span class="text-danger">*</span>';
    }

    switch($custom[$prefix.'format_element']) {
        case 'text':
            echo getInputMask($type, 'fields['.$id.']', $value, $opt).PHP_EOL;
            echo '<span class="help-block">'.$custom[$prefix.'help_text'].'</span>';
            break;
        
        case 'textarea':

            echo '<div class="input-group">';
            echo '<div class="input-group-prepend">';  
            echo '<span class="input-group-text">Comment</span>';
            echo '</div>';
            echo '<textarea class="form-control" aria-label="With textarea" name="fields['.$id.']" '.$opt.'>'.$value.'</textarea>';
            echo '</div>';
            echo '<span class="help-block">'.$custom[$prefix.'help_text'].'</span>';

            break;

        case 'checkbox':

            if(strlen($value) > 0) {
                $value_data = explode(',', $value);
                $value_data = array_values($value_data);
            }

            echo '<div class="frame-wrap" style="background-color:#fffaee;">';
            $fields = explode(',', $custom[$prefix.'element_value']);
            foreach($fields as $k=>$f) {

                $chk = '';
                if(sizeof($value_data) && in_array($f, $value_data)) {
                    $chk = ' checked';
                } 

                echo '<div class="custom-control custom-checkbox custom-control-inline">';

                // $opt 미추가

                echo '<input type="checkbox" class="custom-control-input" id="'.$f.'" name="fields['.$id.']['.$k.']" value="'.$f.'" '.$chk.'>';
                echo '<label class="custom-control-label" for="'.$f.'">'.$f.'</label>';
                echo '</div>';
            }
            echo '<div class="help-block">'.$custom[$prefix.'help_text'].'</div>';
            echo '</div>';

            break;


        case 'radio':

            echo '<div class="frame-wrap" style="background-color:#fffaee;">';
            $fields = explode(',', $custom[$prefix.'element_value']);
            foreach($fields as $k=>$f) {

                $chk = '';
                if(strlen($value) > 0 && $value == $f) {
                    $chk = ' checked';
                }

                echo '<div class="custom-control custom-radio custom-control-inline">';
                echo '<input type="radio" class="custom-control-input" id="'.$f.'" name="fields['.$id.']" value="'.$f.'" '.$opt.' '.$chk.'>';
                echo '<label class="custom-control-label" for="'.$f.'">'.$f.'</label>';
                echo '</div>';
            }
            echo '<div class="help-block">'.$custom[$prefix.'help_text'].'</div>';
            echo '</div>';

            break;

    } // END_SWITCH


    /*
    if($required == 'YES') {
        echo '<span class="invalid-feedback">Please provide a '.$name.'.</span>';
    }
    */

    echo '</div>'.PHP_EOL;
}

?>
