// Show Per Page
var dtLengthMenu = [10,25,50,100,200];


/*  --- Layout Structure 
    --- Options
    l   -   length changing input control
    f   -   filtering input
    t   -   The table!
    i   -   Table information summary
    p   -   pagination control
    r   -   processing display element
    B   -   buttons
    R   -   ColReorder
    S   -   Select

    --- Markup
    < and >             - div element
    <"class" and >      - div with a class
    <"#id" and >        - div with an ID
    <"#id.class" and >  - div with an ID and a class

    --- Further reading
    https://datatables.net/reference/option/dom
    --------------------------------------
 */
var dtDoms = 
      "<'row mb-3'"
        + "<'col-sm-12 col-md-4 d-flex align-items-center justify-content-start'f>"
        + "<'col-sm-12 col-md-8 d-flex align-items-center justify-content-end'B<'mt-1 ml-1'l>>"
    + ">" 
    + "<'row'<'col-sm-12'tr>>"
    + "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>";


var dtButtons = [
    {
        "extend": 'colvis',
        "text": 'Column Visibility',
        "titleAttr": 'Col visibility',
        "className": 'btn-outline-default'
    }, 

    // MS-Excel에서 열면 한글 깨짐, 일반편집기에서 열기 
    {
        "extend": 'csvHtml5',
        "text": 'CSV',
        "titleAttr": 'Generate CSV',
        "charset": 'UTF-8',
        "className": 'btn-outline-primary',
        /*
        "action": function(e,dt,node,config) {
            console.log(config);

            Swal.fire({
                title: "다운로드",
                html: "다운로드 사유를 간단히 입력해 주세요!",
                input: "text",
                inputAttributes:{autocapitalize: "off"},
                type: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes"
            }).then(function(res) {
                //return res;
                console.log(res);
                
            });

            //$.fn.dataTable.ext.buttons.csvHtml5.action.call(this,e,dt,node,config);
        }
        */
    },
    {
        "extend": 'copyHtml5',
        "text": 'Copy',
        "titleAttr": 'Copy to clipboard',
        "className": 'btn-outline-info',
        /*
        "exportOptions": {
            "modifier" : {
                "order" : 'index', // 'current', 'applied','index', 'original'
                "page" : 'all', // 'all', 'current'
                "search" : 'none' // 'none', 'applied', 'removed'
            },
        }
        */
    },
    /*
    {
        "extend": 'pdfHtml5',
        "text": 'PDF',
        "titleAttr": 'Generate PDF',
        "className": 'btn-outline-danger'
    },
    */
    {
        "extend": 'excelHtml5',
        "text": 'Excel',
        "titleAttr": 'Generate Excel',
        "className": 'btn-outline-success'
    },
    {
        "extend": 'print',
        "text": 'Print',
        "titleAttr": 'Print Table',
        "className": 'btn-outline-default'
    }
]



function dtViewButton(url, target='') {

    var btn_class = 'btn btn-sm btn-icon';
    var btn_elem = '';

    btn_elem += '<a href="'+url+'" class="'+btn_class+' btn-outline-success mr-1" title="View" target="'+target+'">';
    btn_elem += '<i class="fal fa-window-alt"></i>';
    btn_elem += '</a>';

    return btn_elem;
}


function dtCloneButton(url, target='') {

    var btn_class = 'btn btn-sm btn-icon';
    var btn_elem = '';

    btn_elem += '<a href="'+url+'" class="'+btn_class+' btn-outline-warning mr-1" title="Copy" target="'+target+'">';
    btn_elem += '<i class="fal fa-copy"></i>';
    btn_elem += '</a>';

    return btn_elem;
}

function dtEditButton(url, target='') {

    var btn_class = 'btn btn-sm btn-icon';
    var btn_elem = '';

    btn_elem += '<a href="'+url+'" class="'+btn_class+' btn-outline-info" title="Edit Record" target="'+target+'">';
    btn_elem += '<i class="fal fa-edit"></i>';
    btn_elem += '</a>';

    return btn_elem;
}
function dtDeleteButton(url) {

    var btn_class = 'btn btn-sm btn-icon ml-1';
    var btn_elem = '';

    btn_elem += '<a href="'+url+'" class="'+btn_class+' btn-outline-danger" title="Delete Record">';
    btn_elem += '<i class="fal fa-trash-alt"></i>';
    btn_elem += '</a>';

    return btn_elem;
}
