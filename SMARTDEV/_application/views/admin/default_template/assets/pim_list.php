<div>
    <table id="data-table" class="table table-sm table-striped table-bordered table-hover" style="width:100%">
        <thead class="bg-warning-200">
            <tr>
                <th data-name="ip_address" data-type="text" data-op="eq">IP Address</th>
                <th data-name="pp_name" data-type="text" data-op="eq">User Name</th>
                <th data-name="pp_login_id" data-type="text" data-op="eq">Login ID</th>
                <th data-name="pp_email" data-type="text" data-op="eq">Email</th>
                <th data-name="ip_memo" data-type="text" data-op="cn">Memo</th>
                <th data-name="pim_id" >PIM ID.</th>
                <th style="width:48px;"></th>
            </tr>
        </thead>
        <tfoot class="bg-warning-200">
            <tr>
                <th>IP Address</th>
                <th>User Name</th>
                <th>Login ID</th>
                <th>Email</th>
                <th>Memo</th>
                <th>PIM ID.</th>
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
                "type": "pim",
            },
        },
        "columns": [
            {"data": "ip_address"},
            {"data": "pp_name"},
            {"data": "pp_login_id"},
            {"data": "pp_email"},
            {"data": "ip_memo"},
            {
                "data": "pim_id",
                'visible': false
            },
            {"data": null},
        ],
        // Define columns class 
        "columnDefs": [
            { "className": "fs-xs text-center", "targets": '_all' },
            {
                "targets": -1,
                "title": 'Controls',
                "orderable": false,
                "className": 'text-center fs-xs p-0',
                "render": function(data, type, full, meta) {
                    var btn_act = ''; 

                    // Edit/Detail 
                    var base_url = 'javascript:editRow(\''+data.ip_id+'\',\''+data.ip_address+'\', \''+ipc_id+'\');';
                    //var base_url = 'javascript:abc(\''+data.ip_id+'\');';
                    btn_act += dtEditButton(base_url);

                    // Delete 
                    var base_url = 'javascript:delRow(\''+data.ip_id+'\', \''+data.pim_id+'\');';
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


    // Edit Popup Save 
    $('#btn-save').click(function(e) {
        e.preventDefault();

        var _modal = $('#modal-edit');
        if( ! _modal.find('#pim_people_id').val() ) {
            Swal.fire("Confirm your values !", '<?=getAlertMsg('REQUIRED_VALUES')?>', "warning");
            return;
        }

        var params = _modal.find('form').serialize();
        params += '&request=ajax';
        $.post('/<?=SHOP_INFO_ADMIN_DIR?>/people/pim_process', params, function(res) {
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




function editRow(id, ip, ipc_id) {

    if( ! ip ) return;

    var params = {'ip_id':id, 'ip': ip, 'ipc_id': ipc_id, 'view_type':'pim'};
    $.post('/<?=SHOP_INFO_ADMIN_DIR?>/system/ajax_ip_template', params, function(res) {
        if(res.is_success) {
            $('#modal-edit').find('.modal-body').html(res.msg);
            $('#modal-edit').modal('show');
        } else {
             
        }
    },'json');
}


function delRow(id, pim_id) {

    if( ! id ) return;

    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!"
    }).then(function(result) {
        if (result.value) {
            var params = {'ip_id':id, 'pim_id':pim_id, 'mode':'delete'};
            $.post('/<?=SHOP_INFO_ADMIN_DIR?>/people/pim_process', params, function(res) {
                if(res.is_success) {
                    $('#data-table').DataTable().clear().draw();
                } else {
                     
                }
            },'json');
        }
    });
}

</script>
