var _OP_SPLITER = '_%OP%_';
var _VALUE_SPLITER = '_%AND%_';

// 로컬스토리지 검색필터
function getLatestColumns() {
    var columns = {};
    columns = localStorage.getItem('columns');
    return columns ? JSON.parse(columns) : {};
}

function getPageLength() {
    var page_len = localStorage.getItem('page_len');
    return page_len ? page_len : 25;
}
function getPage() {
    var page = localStorage.getItem('page');
    return page ? page : 0;
}

function getSearch() {
    var search = localStorage.getItem('search', search);
    return search ? search : '';
}
// 페이지 길이 
function setPageLength(page_len) {
    localStorage.setItem('page_len', page_len);
}
// 페이지번호
function setPage(page) {
    localStorage.setItem('page', page);
}
// 통합검색
function setSearch(search) {
    localStorage.setItem('search', search);
}
// 컬럼검색
function setLatestColumn(i, value) {
    columns = getLatestColumns();
    columns[i] = {'value': value}
    localStorage.setItem('columns', JSON.stringify(columns));
}

function initLatestColumn(i) {
    columns = getLatestColumns();
    columns[i] = {};
    localStorage.setItem('columns', JSON.stringify(columns));
}

function clearHistory() {    
    localStorage.removeItem('page');
    localStorage.removeItem('page_len');
    localStorage.removeItem('search');
    localStorage.removeItem('columns');
}


// 검색필터에 값 찾아오기
function findingColFilter(table_id, table) {

    var page = getPage();
    var columns = getLatestColumns();

    if (Object.keys(columns).length > 0) {
        $('#data-table thead tr:eq(1) th').each(function(i) {

            if ( ! columns[i]) {
                return true;
            }
    
            var value = columns[i]['value'];
            
            if ( ! value) {
                clearHistory();
                return true;
            }
            
            var operator = $(this).data('op');  
            var type     = $(this).data('type');
    
            switch(type) {
                case 'text': 
                    $('input', this).val(value); 
                    table.columns(i).search(operator + _OP_SPLITER + value);
                    break; 
                case 'textarea': 
                    $('textarea', this).val(value); 
                    table.columns(i).search(operator + _OP_SPLITER + value);        
                    break;
                case 'range':
                    var _value = [];
                    // input 필드가 두개이상
                    $(this).find('input').each(function(i, item) {
                        $(item).data('role') == 'start' ? _value.push(operator + _OP_SPLITER + value[i]) : _value.push(value[i]);
                        item.value = value[i];
                    });
                    table.columns(i).search(_value.join(_VALUE_SPLITER));  
                    break;
                case 'select': 
                    $('select', this).val(value);
                    table.columns(i).search(operator + _OP_SPLITER + value);  
                    break;
                default: return true;
            }
            setLatestColumn(i, value);
        });
    }
 
    setTimeout(function() {
        if (page) {
            $('#' + table_id).dataTable().fnPageChange(parseInt(page));
        } else {
            table.draw();
        }
        // initPageInfo();
        //$('[name="data-table_length"]').val(getPageLength());
    });
}

function generatorColFilter(table_id, table) {

    $('#'+table_id+' thead tr:eq(1) th').each(function(i) {
        var title = $(this).text();
        var type = $(this).data('type');
        var operator = $(this).data('op');         // operator type은 Common > transSearchFiltersToParams() 참조
        var element = '';
        var element_value = '';
        var _this = $(this);
        
        
        switch(type) {
            case 'text':
                /*
                element += '<div class="input-group">';
                element += '<input type="text" class="form-control form-control-xs border-info"  aria-label="Search ' + title + '" aria-describedby="button-addon5">';
                element += '<div class="btn-clear input-group-append">';
                element += '<button class="btn btn-xs btn-info waves-effect waves-themed" type="button" id="button-addon5">';
                element += '<i class="fal fa-times"></i>';
                element += '</button>';
                element += '</div>';
                element += '</div>';
                */

                element += '<input type="text" class="form-control form-control-xs border-info" aria-label="Search ' + title + '" aria-describedby="button-addon5">';
                element += '<div class="btn-clear input-group-append">';
                element += '</div>';
                $(this).html(element);


                // Btn Clear Action
                $('.btn-clear', this).on('click', function() {
                    initLatestColumn(i);
                    $(this).siblings('input').val('');
                });

                // Enter Action => Search
                $('input', this).on('keypress', function(e) {
                    if(e.keyCode == 13) {
                        //if(this.value.length < 1) return;

                        var col_search = operator+_OP_SPLITER+this.value;
                        if (table.column(i).search() !== col_search) {
                            table
                            .column(i)
                            .search(col_search)
                            .draw();
                            setLatestColumn(i, this.value);
                        }
                    }
                });
                break;

            case 'textarea':

                var op_data = operator.split(' ');

                element += '<div class="input-group">';
                element += '<div class="input-group-prepend">';
                element += '<button type="button" class="btn btn-info border-info">'+op_data[0]+'</button>';
                element += '<button type="button" class="btn btn-info dropdown-toggle dropdown-toggle-split waves-effect waves-themed border-info" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
                element += '<span class="sr-only">Toggle Dropdown</span>';
                element += '</button>';
                element += '<div class="dropdown-menu">';

                $(op_data).each(function(index, item) {
                    element += '<a class="dropdown-item" href="#">'+item+'</a>';
                });

                element += '</div>';
                element += '<textarea class="form-control border-info" style="padding:2px; min-width:75px; border-radius:0px;"></textarea>';

                element += '<div class="">';
                element += '<button class="btn-clear btn btn-sm btn-info waves-effect waves-themed ml-1 mb-1" type="button" style="height:33x;">';
                element += '<i class="fal fa-times"></i>';
                element += '<button class="btn-search btn btn-sm btn-primary waves-effect waves-themed ml-1" type="button" style="height:33px; padding: 7px 12px 8px 10px;">';
                element += '<i class="fal fa-search"></i>';
                element += '</div>';
                element += '</div>';
                $(this).html(element);

                // Select operaor Action
                $('.dropdown-item', this).on('click', function() {
                    var sel_op = $(this).text();
                    $(this).parent('div').removeClass('show');
                    $(this).parent('div').parent('div').find('button').first().html(sel_op);
                });

                // Btn Clear Action
                $('.btn-clear', this).on('click', function(e) {
                    $(this).parent().siblings('textarea').val('');
                    initLatestColumn(i);
                });

                // Btn Search Action
                $('.btn-search', this).on('click', function(e) {
                    operator = $(this).parent().parent().find('button').first().text();
                    value = $(this).parent().siblings('textarea').val();

                    //if(value.length < 1) return;

                    value = value.replace(/\n/gi, _VALUE_SPLITER);
                    var col_search = operator+_OP_SPLITER+value;
                    if (table.column(i).search() !== col_search) {
                        table
                        .column(i)
                        .search(col_search)
                        .draw();
                        setLatestColumn(i, this.value);
                    }
                });
                break;


            case 'range':

                var datepicker = $(this).data('datepicker');

                element += '<div class="input-daterange input-group" id="datepicker-'+i+'">';
                element += '<input type="text" class="form-control border-info form-control-xs p-1" data-role="start">';
                element += '<div class="input-group-append input-group-prepend">';
                element += '<span class="input-group-text border-info fs-xl" style="height:20px;padding:1px;"><i class="fal fa-ellipsis-h"></i></span>';
                element += '</div>';
                element += '<input type="text" class="form-control border-info form-control-xs p-1" data-role="end">';
                element += '</div>';
                $(this).html(element);

                // IF Date Range type
                if(datepicker == 'use') {
                    $('#datepicker-'+i).datepicker({
                        format: "yyyy-mm-dd",
                        todayHighlight: true,
                    });
                }
                
                
                $('input', this).on('keyup change', function() {
                    var range_date = [];
                    var items = [];
                    _this.find('input').each(function(index, item) {
                        if($(this).data('role') == 'start') {
                            range_date.push(operator+_OP_SPLITER+item.value);
                        }else {
                            range_date.push(item.value);
                        }
                        items.push(item.value);
                    });
                    var col_search = range_date.join(_VALUE_SPLITER);
                    if (table.column(i).search() !== col_search) {
                        table
                        .column(i)
                        .search(col_search)
                        .draw();
                    }
                    setLatestColumn(i, items);
                });
                break;


            case 'select':
                element_value = $(this).data('value');
                var opt_data = element_value.split(';');
                
                element += '<select class="form-control border-info form-control-xs p-1" >';
                for( var k in opt_data) {
                    var tmp = opt_data[k].split(':');
                    element += '<option value="'+tmp[0]+'">'+tmp[1]+'</option>';
                } 
                element += '</select>';

                $(this).html(element);
                $('select', this).on('change', function() {
                    var col_search = operator+_OP_SPLITER+this.value;
                    if (table.column(i).search() !== col_search) {
                        table
                        .column(i)
                        .search(col_search)
                        .draw();
                        setLatestColumn(i, this.value);
                    }
                });
                break;


            case 'treeselect':
                
                // [Reference] http://vue-treeselect.js.org

                element += '<div id="tree_select">';
                element += '<treeselect v-model="value" :show-count="true" :multiple="false" :options="options" v-on:input="optChange" />';
                element += '</div>';
 
                $(this).html(element);
                break;

            default:
                $(this).html('');
                break;
        }
    });
}
