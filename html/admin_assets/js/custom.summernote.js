var autoSave = $('#autoSave');
var interval;
var timer = function() {
    interval = setInterval(function() {
    //start slide...
    if (autoSave.prop('checked'))
        saveToLocal();
        clearInterval(interval);
    }, 3000);
};

//save
var saveToLocal = function() {
    localStorage.setItem('summernoteData', $('#box_summernote').summernote("code"));
    //console.log("saved");
}

//delete 
var removeFromLocal = function() {
    localStorage.removeItem("summernoteData");
    $('#box_summernote').summernote('reset');
}


//toolbar
var toolBar = [
    ['style', ['style']],
    // ['highlight', ['highlight']],   // Syntax HighLight
    ['font', ['strikethrough', 'superscript', 'subscript']],
    ['font', ['bold', 'italic', 'underline', 'clear']],
    ['fontsize', ['fontsize']],
    ['fontname', ['fontname']],
    ['color', ['color']],
    ['para', ['ul', 'ol', 'paragraph']],
    ['height', ['height']]
    ['table', ['table']],
    //['insert', ['link', 'picture', 'video']],
    ['insert', ['link']],
    ['view', ['fullscreen', 'codeview', 'help']]
];

