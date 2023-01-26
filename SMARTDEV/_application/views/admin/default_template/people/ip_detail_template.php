<div class="row">
    <form name='edit_form' id="edit_form" class="needs-validation w-100" method="POST" novalidate autocomplete="off" >
    <input type="hidden" name="mode" value="<?=$mode?>">
    <input type="hidden" name="ip_id" value="<?=$row['ip_id']?>">
    <input type="hidden" name="ip_class_id" value="<?=$row['ip_class_id']?>">
    <input type="hidden" name="ip_class_type" value="<?=$row['ip_class_type']?>">
    <input type="hidden" name="ip_class_category" value="<?=$row['ip_class_category']?>">

    <div class="col-xl-12">
        <div class="panel">

            <?php if($type == 'page') : ?>
            <div class="panel-hdr">
                <h2>Row <?=ucfirst($mode)?></h2>
               
                <div class="panel-toolbar">
                    <?=genDetailButton('ip', $mode)?>                        
                </div>
            </div>
            <?php endif; ?>

            <div class="panel-container show">
                <div class="panel-content">

                    <div class="form-row form-group justify-content-md-center">

                        <div class="form-group col-12 col-lg-8 mb-3">
                            <label class="form-label" for="ip_address">
                                IPv4 Address 
                                <span class="text-danger">*</span>
                                <?=genNewButton('ipclass')?>
                            </label>
                            <?=getInputMask('ipv4', 'ip_address', $row['ip_address'], $opt='required readonly')?>
                            <span class="invalid-feedback">Please provide a IP Address.</span>
                        </div>


                        <div class="col-12 col-lg-8 form-group area-type" id="area-people" style="display:none;">
                            <label class="form-label" for="ip_people_id">
                                Employee
                                <span class="text-danger">*</span>
                                <?=genLinkButton('employee', $row['ip_people_id'])?>
                                <?=genNewButton('employee')?>
                            </label>
                            <?=$select_people?> 
                        </div>


                        <div class="col-12 col-lg-8 form-group area-type" id="area-assets" style="display:none;">
                            <label class="form-label" for="ip_assets_model_id">
                                Assets 
                                <span class="text-danger">*</span>
                                <?=genNewButton('assets')?>
                            </label>
                            <select class="select2-ajax form-control" id="ip_assets_model_id" name="ip_assets_model_id" required></select>
                            <span class="help-block">제품명 or 모델명 or 시리얼 검색</span>
                            <div class="invalid-feedback"></div>
                        </div>



                        <div class="form-group col-12 col-lg-8 mb-3">
                            <label class="form-label" for="ip_memo">
                                Memo 
                            </label>
                            <?=getTextArea('ip_memo', $row['ip_memo'], $opt='')?>
                        </div>



                        <?php if($mode == 'update') : ?>
                        <div class="col-12 col-lg-8 mb-3">
                            <label class="form-label">
                                Updated At 
                            </label>
                            <input type="text" class="form-control" name="ip_updated_at" value="<?=$row['ip_updated_at']?>" disabled >
                        </div>
                        
                        <div class="col-12 col-lg-8 mb-3">
                            <label class="form-label">
                                Created At 
                            </label>
                            <input type="text" class="form-control" name="ip_created_at" value="<?=$row['ip_created_at']?>" disabled >
                        </div>
                        <?php endif;?>


                    </div>

                </div>


                <?php if($type == 'page') : ?>
                <div class="panel-content py-2 rounded-bottom border-faded border-left-0 border-right-0 border-bottom-0 text-muted d-flex">
                    <?=genDetailButton('ip', $mode)?>                        
                </div>
                <?php endif; ?>


            <div>
        </div>
    </div>

    </form>
</div>




<script type="text/javascript">


$(document).ready(function() {

    $('[name="ip_address"]').focusout(function(e) {
        setCIDR(); 
    });
    if($('[name="ip_address"]').val()) {
        setCIDR(); 
    }

    $(".select2").select2({
        placeholder: "@select searchable data",
    });


    <?php if($mode == 'update') : ?>
    

    <?php endif; ?>

    /*
        :: 주의 
        :: ajax return 값에 data.item  내에 '@id' 반드시 포함
        :: Because @id 값 기반 aria-select element 생성
    */
    $(".select2-ajax").select2({
        ajax: {
            url: '/<?=SHOP_INFO_ADMIN_DIR?>/assets/ajax_get_assets',
            type: 'post',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    q: params.term,         // search term
                    page: params.page,
                    class_type: '<?=$class_type?>' 
                };
            },
            processResults: function(res, params) {
                params.page = params.page || 1;
                var data = res.data;
                return {
                    results: data.items,
                    pagination: {
                        more: (params.page * 20) < data.total_count
                    }
                };
            },
            cache: true
        },
        placeholder: 'Search for a repository',
        escapeMarkup: function(markup) {
            return markup;
        }, 
        minimumInputLength: 1,
        templateResult: formatRepo,
        templateSelection: formatRepoSelection
    });
    function formatRepo(repo) {
        if (repo.loading) {
            return repo.text;
        }
        var markup = wrapperOption(repo);
        return markup;
    }
    function formatRepoSelection(repo) {
        return repo.am_name || repo.text;
    }
    function wrapperOption(data) {
        var markup = "<div class='select2-result-repository clearfix d-flex'>";
        markup += "<div class='select2-result-repository__meta'>";
        markup += "<div class='select2-result-repository__title fs-lg fw-500'>" + data.am_name+ "</div>";
        if (data.am_memo) {
            markup += "<div class='select2-result-repository__description fs-xs opacity-80 mb-1'>" + data.am_memo+ "</div>";
        }
        markup += "<div class='select2-result-repository__statistics d-flex fs-sm'>";
        markup += "<div class='select2-result-repository__forks mr-2'><i class='fal fa-hashtag'></i> " + data.am_models_name+ "</div>";
        markup += "<div class='select2-result-repository__stargazers mr-2'><i class='fal fa-map-marker-alt'></i> " + data.am_rack_code + "</div>";
        markup += "<div class='select2-result-repository__watchers mr-2'><i class='fal fa-tag'></i> " + data.am_serial_no + "</div>";
        markup += "</div></div></div>";
        return markup;
    }
});
</script>
