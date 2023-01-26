<div>
    <table id="data-table" class="table table-sm table-striped table-bordered table-hover" style="width:100%">
        <thead class="bg-warning-200">
            <tr>
                <th data-name="ip_address" data-type="text" data-op="eq">iDrac IP Address</th>
                <th data-name="am_name" data-type="text" data-op="cn">Assets Name</th>
                <th data-name="am_models_name" data-type="text" data-op="cn">Model Name</th>
                <th data-name="am_serial_no" data-type="text" data-op="cn">Serial No.</th>
                <th data-name="am_rack_code" data-type="text" data-op="cn">Rack code</th>
                <th data-name="aim_id" data-type="none">AIM ID.</th>
                <th data-name="ip_id" data-type="none">IP ID.</th>
                <th style="width:48px;"></th>
            </tr>
        </thead>
        <tfoot class="bg-warning-200">
            <tr>
                <th>iDrac IP Address</th>
                <th>Assets Name</th>
                <th>Model Name</th>
                <th>Serial No.</th>
                <th>Rack Code</th>
                <th>AIM ID.</th>
                <th>IP ID.</th>
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
                "type": "idrac",
            },
        },
        "columns": [
            {"data": "ip_address"},
            {"data": "am_name"},
            {"data": "am_models_name"},
            {"data": "am_serial_no"},
            {"data": "am_rack_code"},
            {
                "data": "aim_id",
                'visible': false,
                'searchable': false,
            },
            {
                "data": "ip_id",
                'visible': false,
                'searchable': false,
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
                    var base_url = 'javascript:editRow(\''+data.ip_id+'\',\''+data.ip_address+'\');';
                    btn_act += dtEditButton(base_url);

                    // Delete 
                    var base_url = 'javascript:delRow(\''+data.ip_id+'\', \''+data.aim_id+'\');';
                    btn_act += dtDeleteButton(base_url);
                    return btn_act;
                }
            }
        ],
        "dom": dtDoms,
        "buttons": dtButtons
    });


    // Search Box Event
    $('input[type="search"]').unbind();
    $('input[type="search"]').on('keypress', function(e) {
        if(e.keyCode == 13) {
            table.search(this.value).draw();
        }
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




    // Generate Search Filter 
    generatorColFilter('data-table', table);


    // Edit Popup Save 
    $('#btn-save').click(function(e) {
        e.preventDefault();

        if( ! $('#modal-edit').find('#am_id').val() ) {
            Swal.fire("Confirm your values !", '<?=getAlertMsg('REQUIRED_VALUES')?>', "warning");
            return;
        }

        var params = $('#modal-edit').find('form').serialize();
        params += '&request=ajax';
        $.post('/<?=SHOP_INFO_ADMIN_DIR?>/assets/aim_process', params, function(res) {
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




function editRow(id, ip) {

    if( ! ip ) return;
    var params = {'ip_id':id, 'ip':ip, 'view_type':'idrac'};
    $.post('/<?=SHOP_INFO_ADMIN_DIR?>/system/ajax_ip_template', params, function(res) {
        if(res.is_success) {
            $('#modal-edit').find('.modal-body').html(res.msg);
            $('#modal-edit').modal('show');
        } else {
             Swal.fire("", res.msg, "error");
        }
    },'json');
}


function delRow(id, aim_id) {

    if( ! id ) return;

    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!"
    }).then(function(result) {
        if (result.value) {
            var params = {'aim_ip_id':id, 'aim_id':aim_id, 'mode':'delete'};
            $.post('/<?=SHOP_INFO_ADMIN_DIR?>/assets/aim_process', params, function(res) {
                if(res.is_success) {
                    $('#data-table').DataTable().clear().draw();
                } else {
                     
                }
            },'json');
        }
    });
}

</script>
