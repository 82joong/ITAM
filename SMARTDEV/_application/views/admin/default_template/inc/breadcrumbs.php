<ol class="breadcrumb page-breadcrumb">
    <li class="breadcrumb-item"><a href="/<?=SHOP_INFO_ADMIN_DIR?>" data-i18b="nav.home">Home</a></li>
    <?php
    $page_point = $page_nav; 
    $bd_tit = '';
    $pg_ico = $title_symbol;
    $bd_dst = '';
    $i18n = 'nav';

    foreach($active_key as $k=>$v) {

        if($k == 0) {
            $i18n .= '.'.$v;
        }else {
            $i18n .= '_'.$v;
        }

        if($k == (sizeof($active_key)-1)) {
            $page_point = $page_point[$v]; 
        }else {
            $page_point = $page_point[$v]['sub']; 
        }

        echo '<li class="breadcrumb-item">';

        // Breadcrumb Last Tail Element
        if($k == (sizeof($active_key)-1)) {
            $pg_tit = $page_point['title'];
            $pg_ico = isset($page_point['icon']) ? $page_point['icon'] : $title_symbol;
            $pg_dst = isset($page_point['description']) ? $page_point['description'] : '';
            $pg_url = isset($page_point['url']) ? $page_point['url'] : '/';
            $pg_i18n = $i18n;

            echo '<a href="'.$pg_url.'" data-i18n="'.$i18n.'">'.$pg_tit.'</a>';
        }else {
            echo '<span data-i18n="'.$i18n.'">'.ucfirst($v).'</span>';
        }
        echo '</li>'.PHP_EOL;
    }
    ?>
    <li class="position-absolute pos-top pos-right d-none d-sm-block"><span class="js-get-date"></span></li>
</ol>



<div class="subheader">
    <h1 class="subheader-title">
        <i class='subheader-icon fal <?=$pg_ico?>'></i> 
        <span data-i18n="<?=$pg_i18n?>"><?=$pg_tit?></span>

        <?php if(isset($mode) && strlen($mode) > 0) :?>
        : <span class='fw-300'><?=ucfirst($mode)?></span>
        <?php endif;?>

        <?php if(strlen($pg_dst) > 0) : ?>
        <small><?=$pg_dst?></small>
        <?php endif; ?>
    </h1>


    <?php 
    if(isset($subheader_contents) && strlen($subheader_contents) > 0) { 
        echo $subheader_contents;
    }
    ?>
</div>
