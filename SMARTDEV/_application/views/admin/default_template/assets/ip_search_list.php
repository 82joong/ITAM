<div>
    <table id="data-table" class="table table-sm table-striped table-bordered table-hover" style="width:100%">
        <thead class="bg-warning-200">
            <tr>
                <th data-name="ip_class_id">IP Class</th>
                <th data-name="ip_address">IP Address</th>
                <th data-name="ip_class_type">IP Type</th>
                <th data-name="ip_class_category">IP Category</th>
                <th data-name="ip_memo">IP Memo</th>
                <th data-name="am_name">Assets Name</th>
                <th data-name="am_models_name">Model Name</th>
                <th data-name="am_vmware_name">VMWare Name</th>
                <th data-name="am_serial_no">Service Tag</th>
                <th data-name="am_tags">Tags</th>
                <th data-name="am_rack_code">Rack Code</th>
                <th data-name="am_memo">Assets Memo</th>
                <th data-name="vms_name">VMService Name</th>
                <th data-name="vms_memo">VMService Memo</th>
                <th data-name="pp_name">Employee Name</th>
                <th data-name="pp_email">Email</th>
                <th data-name="pp_memo">Employee Memo</th>
            </tr>
        </thead>

        <?php /*?>
        <tfoot class="bg-warning-200">
            <tr>
                <th>IP Class</th>
                <th>IP Address</th>
                <th>IP Type</th>
                <th>IP Category</th>
                <th>IP Memo</th>
                <th>Assets Name</th>
                <th>Model Name</th>
                <th>VMWare Name</th>
                <th>Service Tag</th>
                <th>Tags</th>
                <th>Rack Code</th>
                <th>Assets Memo</th>
                <th>VMService Name</th>
                <th>VMService Memo</th>
                <th>Employee Name</th>
                <th>Email</th>
                <th>Employee Memo</th>
            </tr>
        </tfoot>
        <?php */?>
    </table>
</div>





<script type="text/javascript">

$(document).ready(function() {

    // Init
    //$('#panel-4').addClass('panel-fullscreen');
    var ipc_id = '<?=$ipc_id?>';

    // Setup - add a text input to each footer cell
    //$('#data-table thead tr').clone(true).appendTo('#data-table thead');



    var dtDoms = 
      "<'row mb-3'"
        + "<'col-sm-12 col-md-4 d-flex align-items-center justify-content-start'>"
        + "<'col-sm-12 col-md-8 d-flex align-items-center justify-content-end'B<'mt-1 ml-1'l>>"
    + ">" 
    + "<'row'<'col-sm-12'tr>>"
    + "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>";



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
        "rowGroup"      : { 
            "dataSrc"   : "ip_class_id",
        },
        "ajax": {
            "url": "/<?=SHOP_INFO_ADMIN_DIR?>/system/ipsearch",
            "type": "POST",
            "dataType": "JSON",
            "data": {
                "search_type": "<?=$search_type?>",
                "search_value": "<?=$search_value?>",
            },
        },
        "columns": [
            {"data": "ip_class_id"},
            {"data": "ip_address"},
            {"data": "ip_class_type"},
            {"data": "ip_class_category"},
            {"data": "ip_memo"},
            {"data": "am_name"},
            {"data": "am_models_name"},
            {"data": "am_vmware_name"},
            {"data": "am_serial_no"},
            {"data": "am_tags"},
            {"data": "am_rack_code"},
            {"data": "am_memo"},
            {"data": "vms_name"},
            {"data": "vms_memo"},
            {"data": "pp_name"},
            {"data": "pp_email"},
            {"data": "pp_memo"},
        ],
        // Define columns class 
        "columnDefs": [
            { "className": "fs-nano text-center", "targets": '_all' },
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
    //generatorColFilter('data-table', table);

});

</script>
