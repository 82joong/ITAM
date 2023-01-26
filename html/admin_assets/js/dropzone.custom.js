/* ===============================================


   @ VIEW :: DropZone 공통 옵션 정의


    1. Dropzone BOX HTML 생성
        <?=genDropzone()?>
    

    2. Custom Dropzone Obj 생성 
        var _dz = new Dropzone('div#div_dropzone', dzOptions);


    3. Update Mode 시, 업로드된 이미지 미리 Dropzone 에 추가
        <?php if($mode == 'update' && strlen($row['vd_origin_name']) > 0) : ?>
        var mockFile = { name:"<?=$row['vd_origin_name']?>", size:<?=$img_size?>, accepted:true};
        _dz.files.push(mockFile);
        _dz.emit("addedfile", mockFile);
        _dz.emit("thumbnail", mockFile, "<?=$img_url?>");
        _dz.emit("complete", mockFile)
        <?php endif; ?>

    4. Remove 시,

        removedfile: function(file) { ....
        img_origin[] 와 img_filename[]에 공백으로 처리
        @다중 파일 처리 구조 고려 안되어 있음.

================================================== */


// Button Remove Style
var dz_btn_remove = '';
dz_btn_remove += '<div class="btn btn-sm btn-danger waves-effect waves-themed" style="width:120px;cursor:pointer;">';
dz_btn_remove += '<span class="fal fa-trash-alt mr-1"></span>';
dz_btn_remove += 'Remove';
dz_btn_remove += '</div>';



var dzOptions = {
    paramName: 'file',
    //acceptedFiles: 'image/*',
    //autoProcessQueue: false,  // 자동업로드 여부 
    // (true일 경우, 바로 업로드 되어지며, false일 경우, 서버에는 올라가지 않은 상태임 processQueue() 호출시 올라간다.)
    clickable: true,                // 클릭가능여부 
    //previewContiner: '#dropzonePreview',                // 클릭가능여부 
    //thumbnailHeight: 90,          // Upload icon size 
    //thumbnailWidth: 90,           // Upload icon size 
    maxFiles: 1,                    // 업로드 파일수 
    maxFilesize: 10,                // 최대업로드용량 : 10MB 
    //parallelUploads: 100,         // 동시파일업로드 수(이걸 지정한 수 만큼 여러파일을 한번에 컨트롤러에 넘긴다.) 
    addRemoveLinks: true,           // 삭제버튼 표시 여부 
    dictRemoveFile: dz_btn_remove,  // 삭제버튼 표시 텍스트 
    uploadMultiple: true,           // 다중업로드 기능

    url: "/admin/manage/do_dropzone", 
    init: function () {
        this.on('maxfilesexceeded', function (file) {
            //console.log('init exceeded');
            //console.log(file);
            this.removeAllFiles();
            this.addFile(file);
        });
        this.on('thumbnail', function (file) {

            file.previewElement.addEventListener("click", function() {
                // file type : image 일때, Preview
                if(file.type == 'image') {
                    var img_src = file.previewElement.querySelector('img').src;
                    var _img_html = '<img src="'+img_src+'">';

                    $('#modal-imgpreview').find('.modal-body').html(_img_html);
                    $('#modal-imgpreview').modal('show');
                }else {

                    var downUrl = '/admin/system/file_download';
                    downUrl += '/'+file.uri+'/'+file.id;
                    //console.log(downUrl);
                    location.href = downUrl;
                }
            });
        });
    },
    error: function (file, response){
        if ($.type(response) === "string") {
            var message = response; //dropzone sends it's own error messages in string
        }else {
            var message = response.message;
        }

        file.previewElement.classList.add("dz-error");
        _ref = file.previewElement.querySelectorAll("[data-dz-errormessage]");
        _results = [];
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
            node = _ref[_i];
            _results.push(node.textContent = message);
        }
        return _results;
    },
    successmultiple: function (file, response) {
        //console.log('success');
        //console.log(file, response);

        var res = JSON.parse(response);
        //console.log(res);
        $.each(res, function (k, v) {
            if(v.is_success == false) {
                // TODO. Check is_success!!! 
                console.log('Error'); 
                console.log(v.msg); 
            }else {
                $('[name="img_origin[]"]').remove(); 
                $('[name="img_filename[]"]').remove(); 
                var img_origin = "<input type='hidden' name='img_origin[]' value='"+v.origin_name+"'>";
                var img_filename = "<input type='hidden' name='img_filename[]' value='"+v.file_name+"'>";
                $('#edit_form').append(img_origin);
                $('#edit_form').append(img_filename);
            }
        });
    },
    removedfile: function(file) {
        //console.log(file);
        var img_origin = "<input type='hidden' name='img_origin[]' value=''>";
        var img_filename = "<input type='hidden' name='img_filename[]' value=''>";
        $('#edit_form').append(img_origin);
        $('#edit_form').append(img_filename);

        file.previewElement.remove();
    }
    /*
    completemultiple: function (file) {},
    maxfilesexceeded: function (file) {},
    complete: function(file) {},
    addedfile: function(file) {},
    removedfile: function(file) {},
    reset: function () {}
    */
}
