<div>
    <table id="data-table" class="table table-sm table-striped table-bordered table-hover" style="width:100%">
        <thead class="bg-warning-200">
            <tr>
                <th data-name="ip_address" data-type="text" data-op="eq">IP Address</th>
                <th data-name="vmware_ip" data-type="text" data-op="eq">VMWare IP</th>
                <th data-name="am_name" data-type="text" data-op="cn">Assets Name</th>
                <th data-name="am_vmware_name" data-type="text" data-op="cn">VMWare</th>
                <th data-name="vms_name" data-type="text" data-op="cn">VMService</th>
                <th data-name="vms_memo" data-type="text" data-op="cn">VMService Memo</th>
                <th data-name="ip_memo" data-type="text" data-op="cn">IP Memo</th>
                <th data-name="ip_id" >IP ID.</th>
                <th data-name="vms_id" >VMS ID.</th>
                <th data-name="vms_alias_id" >ALIAS ID.</th>
                <th style="width:48px;"></th>
            </tr>
        </thead>
        <tfoot class="bg-warning-200">
            <tr>
                <th>IP Address</th>
                <th>VMWare IP</th>
                <th>Assets Name</th>
                <th>VMWare</th>
                <th>VMService</th>
                <th>VMService Memo</th>
                <th>IP Memo</th>
                <th>IP ID.</th>
                <th>VMS ID.</th>
                <th>ALIAS ID.</th>
                <th></th>
            </tr>
        </tfoot>
    </table>
</div>




<script type="text/javascript">


$(document).ready(function() {


    // Init
    //$('#panel-4').addClass('panel-fullscreen');
    var ipc_id = '<?=$ipc_id?>';


    if( $('#edit_form [name="ip_address"]').val() ) {
        setCIDR('#edit_form '); 
    }


    // Setup - add a text input to each footer cell
    $('#data-table thead tr').clone(true).appendTo('#data-table thead');

    var table = $('#data-table').DataTable({
        "paging"        : false,
        "orderCellsTop" : true, 
        "fixedHeader"   : true, 
        "processing"    : true,
        "serverSide"    : true,
        "searching"     : true,
        "responsive"    : true,
        "select"        : "single",
        "order"         : [[0, "desc"]],    // Default Sorting
        "ajax": {
            "url": "/<?=SHOP_INFO_ADMIN_DIR?>/system/iplist/"+ipc_id,
            "type": "POST",
            "dataType": "JSON",
            "data": {
                "mode": "list",
                "type": "vim",
            },
        },
        "columns": [
            {"data": "ip_address"},
            {"data": "vmware_ip"},
            {"data": "am_name"},
            {"data": "am_vmware_name"},
            {"data": "vms_name"},
            {"data": "vms_memo"},
            {"data": "ip_memo"},
            {"data": "ip_id", "visible": false},
            {"data": "vms_id", "visible": false},
            {"data": "vms_alias_id", "visible": false},
            {"data": null},
        ],
        // Define columns class 
        "columnDefs": [
            { "className": "fs-nano text-center", "targets": "_all" },
            {
                "targets": -1,
                "title": 'Controls',
                "orderable": false,
                "className": 'text-center fs-xs p-0',
                "render": function(data, type, full, meta) {

                    var btn_act = ''; 

                    // Edit/Detail 
                    var base_url = 'javascript:editRow(\''+data.ip_id+'\',\''+data.ip_address+'\',\''+data.vms_id+'\', \''+data.vms_alias_id+'\');';
                    btn_act += dtEditButton(base_url);

                    // Delete 
                    var base_url = 'javascript:delRow(\''+data.ip_id+'\', \''+data.vms_id+'\', \''+data.vms_alias_id+'\',\''+data.dim_id+'\');';
                    btn_act += dtDeleteButton(base_url);


                    return btn_act;
                }
            }
        ],
        "dom": dtDoms,
        "buttons": dtButtons
    });

    table.on( 'responsive-resize', function ( e, datatable, columns ) {
        var count = columns.reduce( function (a,b) {
            return b === false ? a+1 : a;
        }, 0 );

        $('.dataTable thead tr:last-child th').show();
        if(count > 0) {
            $.each(columns, function(k, v) {
                if(v === false) {
                    $('.dataTable thead tr:last-child th:nth-child('+(k+1)+')').css('display', 'none');
                }
            });
        }
        //console.log( count +' column(s) are hidden' );
    });




    // Search Box Event
    $('input[type="search"]').unbind();
    $('input[type="search"]').on('keypress', function(e) {
        if(e.keyCode == 13) {
            table.search(this.value).draw();
        }
    });


    // Generate Search Filter 
    generatorColFilter('data-table', table);



    function checkValidation(nameKey) {
        var _modal = $('#modal-edit');
        if( ! _modal.find('[name="'+nameKey+'"]').val() ) {
            toggleValid($('#modal-edit [name="'+nameKey+'"]'), 'invalid', '<?=getAlertMsg('REQUIRED_VALUES')?>');
            return false;
        }
        return true;
    }

    // Edit Popup Save 
    $('#btn-save').click(function(e) {
        e.preventDefault();

        var _modal = $('#modal-edit');
        var type = _modal.find('[name="type"]').val();

        switch(type) {
            case 'dim':
                if( ! checkValidation('am_id') ) return;
                var url = '/<?=SHOP_INFO_ADMIN_DIR?>/assets/dim_process';
                break;
            case 'vim':
                if( ! checkValidation('vms_assets_model_id') ) return;
                if( ! checkValidation('vms_name') ) return;
                var url = '/<?=SHOP_INFO_ADMIN_DIR?>/assets/vmservice_process';
                break;
            case 'alias':
                if( ! checkValidation('vms_alias_id') ) return;
                if( ! checkValidation('vms_name') ) return;
                var url = '/<?=SHOP_INFO_ADMIN_DIR?>/assets/alias_process';
                break;
        }

        var params = $('#modal-edit').find('form').serialize();
        params += '&request=ajax';
        $.post(url, params, function(res) {
            if(res.is_success) {
                // AND CLOSE
                $('#modal-edit').modal('hide');
                table.clear().draw();
            } else {
                Swal.fire("Submit Error !", res.msg, "error");
            }
        },'json');
    });
});

function editRow(id, ip, vms_id, alias_id) {
    if( ! ip ) return;

    if(id > 0) {
        if(vms_id > 0) {
            if(alias_id > 0) {
                editPopup(id, ip, 'alias');
            }else {
                editPopup(id, ip, 'vim');
            }
        }else {
            editPopup(id, ip, 'dim');
        }
        return;
    }


    var btn_html = '';
    btn_html += '<div><span class="badge badge-success">VMService IP</span> VMWare 의해 가상화하위 서비스 IP 할당</div>';
    btn_html += '<div><span class="badge badge-info">Direct IP</span> VMWare 가상화 없이 단독 서비스 IP 할당</div>';
    btn_html += '<div class="mb-3"><span class="badge badge-warning">Alias IP</span> VMService Alias IP 할당</div>';
    btn_html += '<a href="javascript:editPopup(\''+id+'\',\''+ip+'\', \'vim\');" class="btn btn-success mr-1"> VMService IP </a>';
    btn_html += '<a href="javascript:editPopup(\''+id+'\',\''+ip+'\', \'dim\');" class="btn btn-info mr-1"> Direct IP </a>';
    btn_html += '<a href="javascript:editPopup(\''+id+'\',\''+ip+'\', \'alias\');" class="btn btn-warning"> Alias IP </a>';

    Swal.fire({
        title: "<strong></strong>",
        icon: "info",
        type: "info",
        html: btn_html,
        showCloseButton: true,
        closeButtonHtml: '&times;',
        showCancelButton: true,
        showConfirmButton: false,
    });
}



function editPopup(id, ip, type) {
    
    if( ! ip ) return;

    var params = {'ip_id':id, 'ip':ip, 'view_type':type};
    $.post('/<?=SHOP_INFO_ADMIN_DIR?>/system/ajax_ip_template', params, function(res) {
        if(res.is_success) {
            Swal.close();
            $('#modal-edit').find('.modal-body').html(res.msg);
            $('#modal-edit').modal('show');
        } else {
             
        }
    },'json');
}



function delRow(id, vms_id, alias_id, dim_id) {

    if( ! id ) return;

    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!"
    }).then(function(result) {
        if (result.value) {

            var url = '/<?=SHOP_INFO_ADMIN_DIR?>/assets/vmservice_process';
            var params = {'vms_ip_id':id, 'vms_id':vms_id, 'mode':'delete'};
            if(dim_id > 0)  {
                var url = '/<?=SHOP_INFO_ADMIN_DIR?>/assets/dim_process';
                var params = {'dim_ip_id':id, 'dim_id':dim_id, 'mode':'delete'};
            }else {
                if(alias_id > 0) {
                    var url = '/<?=SHOP_INFO_ADMIN_DIR?>/assets/alias_process';
                }
            }
            $.post(url, params, function(res) {
                if(res.is_success) {
                    $('#data-table').DataTable().clear().draw();
                } else {
                     
                }
            },'json');

        }
    });
}

</script>
