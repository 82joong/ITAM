<div class="col-12 col-lg-12 mb-3">

    <table id="data-table-mtn" class="table table-sm table-striped table-bordered table-hover" style="width:100%">
        <thead class="bg-info-500">
            <tr>
                <th data-name="mtn_id">ID</th>
                <th data-name="am_models_name" data-i18n="content.model">Assets</th>
                <th data-name="am_name" data-i18n="content.assets_name">Assets Name</th>
                <th data-name="mtn_type" data-i18n="content.maintenance_type">Type</th>
                <th data-name="mtn_title" data-i18n="content.maintenance_title">Title</th>
                <th data-name="mtn_price" data-i18n="content.maintenance_price">Price</th>
                <th data-name="mtn_memo" data-i18n="content.memo">Memo</th>
                <th data-name="mtn_start_at" data-i18n="content.start_at">Start At</th>
                <th data-name="mtn_end_at" data-i18n="content.end_at">End At</th>
                <th data-name="mtn_created_at" data-i18n="content.created_at">Created At</th>
                <th data-name="mtn_updated_at" data-i18n="content.updated_at">Updated At</th>
            </tr>
        </thead>
    </table>

</div>


<script type="text/javascript">    
$(document).ready(function() {


    var table = $('#data-table-mtn').DataTable({
        "bPaginate"     : false,
        "orderCellsTop" : false, 
        "ordering"      : false,
        "fixedHeader"   : true, 
        "processing"    : true,
        "serverSide"    : true,
        "searching"     : false,
        "responsive"    : false,
        "select"        : "single",
        "altEditor"     : true,
        "order"         : [[0, "desc"]],    // Default Sorting
        "filter"        : false,
        "lengthChange"  : false,
        "rowReorder": {
            "selector": "tr td:first-child"
        },

        "ajax": {
            "url": "/<?=SHOP_INFO_ADMIN_DIR?>/assets/maintenance",
            "type": "POST",
            "dataType": "JSON",
            "data": {
                "mode": "list",
                "assets_model_id": "<?=$assets_model_id?>",
            }
        },

        "columns": [
            {"data": "mtn_id"},
            {"data": "am_models_name"},
            {"data": "am_name",},
            {"data": "mtn_type"},
            {"data": "mtn_title"},
            {
                "data": "mtn_price",
                "render": $.fn.dataTable.render.number(',')
            },
            {
                "data": "mtn_memo",
                "className": "text-left"
            },
            {"data": "mtn_start_at"},
            {"data": "mtn_end_at"},
            {"data": "mtn_created_at"},
            {"data": "mtn_updated_at"},
        ],
        // Define columns class 
        "columnDefs": [
            { "className": "text-center fs-nano", "targets": "_all" },
            { 
                "className": "text-center fs-nano",
                "responsivePriority": 1,
                "targets": 6 
            }
        ],

        "dom":
              "<'row mb-1 mt-3'"
                + "<'col-sm-12 col-md-4 d-flex align-items-center justify-content-start'f>"
                + "<'col-sm-12 col-md-8 d-flex align-items-center justify-content-end'B>"
            + ">" 
            + "<'row'<'col-sm-12'tr>>"
            + "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",

        "buttons": [
            {
                text: '<i class="fal fa-plus-square mr-1"></i> New',
                name: 'new_custom',
                className: 'btn-success btn-sm mr-1',
                action: function(e, dt, button, config) {
                    window.open('/admin/assets/maintenance_detail?am_id=<?=$assets_model_id?>', '_blank');
                }
            },
            {
                extend: 'selected',
                text: '<i class="fal fa-edit"></i> Edit',
                name: 'edit_custom',
                className: 'btn-primary btn-sm',
                action: function(e, dt, button, config) {
                    var mtn_id = table.rows({selected:true}).data()[0].mtn_id;
                    window.open('/admin/assets/maintenance_detail/' + mtn_id, '_blank');
                }
            }
        ],
        "initComplete": function(settings, json) {
            $('pre:not(:has(code))').each(function(i, block) {
                hljs.highlightBlock(block);
            });
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


       
});
</script>
