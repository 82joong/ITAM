<div>
    <table id="data-table" class="table table-sm table-striped table-bordered table-hover" style="width:100%">
        <thead class="bg-warning-200">
            <tr>
                <th data-name="lab_id" >ID</th>
                <th data-name="lab_ip">IP</th>
                <th data-name="lab_remoteip">Remote IP</th>
                <th data-name="lab_rack">Rack</th>
                <th data-name="lab_owner">Owner</th>
                <th data-name="lab_sw">SW</th>
                <th data-name="lab_backup">Backup</th>
                <th data-name="lab_os">OS</th>
                <th data-name="lab_server">Server<th>
                <th data-name="lab_servicetag">ServiceTag</th>
                <th data-name="lab_cpu">CPU</th>
                <th data-name="lab_ram">RAM</th>
                <th data-name="lab_hdd">HDD</th>
                <th data-name="lab_hddtype">HDD Type</th>
                <th data-name="lab_regdate">Regdate</th>
                <th data-name="lab_memo">Memo</th>
            </tr>
        </thead>
        <tfoot class="bg-warning-200">
            <tr>
                <th>ID</th>
                <th>IP</th>
                <th>Remote IP</th>
                <th>Rack</th>
                <th>Owner</th>
                <th>SW</th>
                <th>Backup</th>
                <th>OS</th>
                <th>Server<th>
                <th>ServiceTag</th>
                <th>CPU</th>
                <th>RAM</th>
                <th>HDD</th>
                <th>HDD Type</th>
                <th>Regdate</th>
                <th>Memo</th>
            </tr>
        </tfoot>
    </table>
</div>



<script type="text/javascript">


$(document).ready(function() {


    // Init
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
            "url": "/<?=SHOP_INFO_ADMIN_DIR?>/system/lablist/"+ipc_id,
            "type": "POST",
            "dataType": "JSON",
            "data": {
                "mode": "list",
                "type": "lab",
            },
        },
        "columns": [
            {"data": "lab_id"},
            {"data": "lab_ip"},
            {"data": "lab_remoteip"},
            {"data": "lab_rack"},
            {"data": "lab_owner"},
            {"data": "lab_sw"},
            {"data": "lab_backup"},
            {"data": "lab_os"},
            {"data": "lab_server"},
            {"data": "lab_servicetag"},
            {"data": "lab_cpu"},
            {"data": "lab_ram"},
            {"data": "lab_hdd"},
            {"data": "lab_hddtype"},
            {"data": "lab_regdate"},
            {"data": "lab_memo"},
        ],
        // Define columns class 
        "columnDefs": [
            { "className": "fs-nano text-center", "targets": "_all" },
        ],
        "dom": dtDoms,
        "buttons": dtButtons
    });

