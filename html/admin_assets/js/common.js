//email validation check
function email_check(email){
    var email_ck=/^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/;
    if(email_ck.test(email) === false){
        return false;
    } else{
        return true;
    }
}

function ajax_params_set(list){
    var param = '';
    for(var key in list){
        var val = list[key];
        param += key+'='+val+'&';
    }

    return encodeURI(param);
}

function del_space(str){
    return str.replace(/(\s*)/g, "");
}

function password_validate(passwd){
    if(passwd.length < 10){
        return false;
    }
    var regexp_number = passwd.search(/[0-9]/g);
    var regexp_char = passwd.search(/[a-z]/ig);
    if(regexp_number < 0 || regexp_char < 0){
        return false;
    }
    return true;
}

function view_tracking(o_num, nation) {
	var width = 760;
	var height = 800;

	if(nation != 'KR') {
		width = 1000;
		height = 900;
	}


	var url = "/admin/sales/ajax_get_tracking";;
	var param = "ordernum="+o_num+"&nation="+nation;
	$.post(url, param, function(data){
		var res = $.parseJSON(data);
	   
		if(res.is_success == true) {
			window.open(res.msg, 'checkShipmentTracnking', 'width='+res.width+',height='+res.height+',scrollbars=yes');
		}else {
			alert(res.msg);
			return false;
		}
	});
}

function set_number_format(n) {
    if(n != null) {
        var reg = /(^[+-]?\d+)(\d{3})/;
        n += '';
        while (reg.test(n)) n = n.replace(reg, '$1' + ',' + '$2');
        return n;
    }
}

function unset_number_format(n) {
    return n.replace(/,/g, "");
}

function SuccessMsg(msg){
    $.smallBox({
        title : 'Success',
        content : "<i class='fa fa-check'></i> <i>"+msg+"</i>",
        color : "#119a6a",
        iconSmall : "fa fa-check fa-2x fadeInRight animated",
        timeout : 4000
    });
}

function reconn_dhl(o_id, o_ordernum) {
    if(confirm('Are you Sure?') == false) {
		return false;
	}

	var  win = window.open('', 'openLabel', 'width=470,height=650,scrollbars=yes');
	$.post('/admin/tool/ajax_conn_dhl', {'o_id':o_id}, function(data) {
		if(data.res == 'fail') {
			alert(data.msg);
			win.close();
			return false;
		}

		win.location.href = '/admin/tool/ajax_dhl_label/'+o_ordernum;
	}, 'json');
}

function malltail_label(o_id, o_ordernum, country_id) {
    switch(country_id) {
        case 'HK':
		    window.open('/admin/tool/ajax_dhl_label/'+o_ordernum, 'openLabel', 'width=470,height=650,scrollbars=yes');
            break;
        case 'SG':
		    window.open('/admin/tool/ajax_cj_label/'+o_id, 'openLabel', 'width=670,height=420,scrollbars=yes');
            break;
        default:
		    window.open('/admin/tool/ajax_malltail_label/'+o_id, 'openLabel', 'width=670,height=420,scrollbars=yes');
            break;
	}
}

function printPendingLabel(o_id) {
    window.open('/admin/sales/print_pending_label/'+o_id, 'openLabel', 'width=470,height=650,scrollbars=yes');
}

function setCookie(name,value,days) {
    if (days) {
        var date = new Date();
        date.setTime(date.getTime()+(days*24*60*60*1000));
        var expires = "; expires="+date.toGMTString();
    }
    else var expires = "";
    document.cookie = name+"="+value+expires+"; path=/";
}

function setCookie_sec(name,value,seconds) {
    if (seconds) {
        var date = new Date();
        date.setTime(date.getTime()+(seconds*1000));
        var expires = "; expires="+date.toGMTString();
    }
    else var expires = "";
    document.cookie = name+"="+value+expires+"; path=/";
}

function getCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
}

// 관리자 비밀번호 유효성체크
function admin_password_validate(pw) {
    if(pw.length < 10) {
        return false;
    }
    
    var alphas = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    var nums = '0123456789';
    var alpha_count = 0;
    var num_count = 0;
    var sp_count = 0;
    
    for(var i = 0 ; i < pw.length ; i++) {
        if(alphas.indexOf(pw.charAt(i)) >= 0) {
            alpha_count++;
            continue;
        }
        if(nums.indexOf(pw.charAt(i)) >= 0) {
            num_count++;
            continue;
        }
        sp_count++;
    }

    if(alpha_count * num_count * sp_count == 0) {
        return false;
    }

    return true;
}


function fillZero(width, str){
    return str.length >= width ? str:new Array(width-str.length+1).join('0')+str;
}


function toggleValid(id, mode, msg) {
    //var _id = $('#'+id);  
    //var _id = $('[name="'+id+'"]');  

    msg = '<i class="fas fa-info-circle mr-1"></i>' + msg;
    if(mode == 'valid') {
        id.removeClass('is-invalid').addClass('is-valid').parent().find('.invalid-feedback').html(msg);
    }else {
        id.removeClass('is-valid').addClass('is-invalid').parent().find('.invalid-feedback').html(msg);
    }
}


function setCIDR(area, name) {

    if(name == undefined || name.length < 1) {
        name = 'ip_address';
    }

    var ip_id = $(area+'[name="ip_id"]').val();
    var ip = $(area+'[name="'+name+'"]').val();
    var set_valid = $(area+'[name="set_valid"]').val();
    var am_id = $(area+'[name="am_id"]').val();

    var params = {"ip_id": ip_id, "ip": ip, "set_valid": set_valid, "assets_model_id": am_id};
    $.post('/admin/system/ajax_valid_ip', params, function(res) {
        if(res.is_success) {
            var msg = res.msg;
            toggleValid($(area+'[name="'+name+'"]'),'valid', '');
            $(area+'[name="ip_class_id"]').val(msg.ipc_id);
            $(area+'[name="ipc_location_id"]').val(msg.ipc_location_id);
            $(area+'[name="ipc_cidr"]').val(msg.ipc_cidr);
            $(area+'[name="ipc_name"]').val(msg.ipc_name);
            $(area+'[name="ip_class_type"]').val(msg.ipc_type);
            $(area+'[name="ip_class_category"]').val(msg.ipc_category);
        } else {
            toggleValid($(area+'[name="'+name+'"]'),'invalid', res.msg);
            $(area+'[name="ip_class_id"]').val('');
            $(area+'[name="ipc_location_id"]').val('');
            $(area+'[name="ipc_cidr"]').val('');
            $(area+'[name="ipc_name"]').val('');
            $(area+'[name="ip_class_type"]').val('');
            $(area+'[name="ip_class_category"]').val('');
        }
   },'json');
}


function calAssetLevel() {
        
    var num = [];
    num[0] = $('[name="sm_secure_conf"]').val() * 1;
    num[1] = $('[name="sm_secure_inte"]').val() * 1;
    num[2] = $('[name="sm_secure_avail"]').val() * 1;

    sum = 0;
    for (var i=0; i < num.length; i++) {
        if(num[i] < 1 || num[i] > 3) {
            alert('[자산중요도] 1~3 사이 값을 입력해 주세요!');
            return;
        }
        sum += num[i];
    }

    $('[name="sm_important_score"]').val(sum);

    var level = 0;
    if( sum >= 1 && sum <= 3 ) level = 3;
    else if( sum >= 4 && sum <= 6 ) level = 2;
    else level = 1;

    $('[name="sm_important_level"]').val(level);
}
