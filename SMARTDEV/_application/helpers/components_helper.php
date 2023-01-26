<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function getCountries() {
    $countries = array(
        "Afganistan" => "Afghanistan",
        "Albania" => "Albania",
        "Algeria" => "Algeria",
        "American Samoa" => "American Samoa",
        "Andorra" => "Andorra",
        "Angola" => "Angola",
        "Anguilla" => "Anguilla",
        "Antigua & Barbuda" => "Antigua & Barbuda",
        "Argentina" => "Argentina",
        "Armenia" => "Armenia",
        "Aruba" => "Aruba",
        "Australia" => "Australia",
        "Austria" => "Austria",
        "Azerbaijan" => "Azerbaijan",
        "Bahamas" => "Bahamas",
        "Bahrain" => "Bahrain",
        "Bangladesh" => "Bangladesh",
        "Barbados" => "Barbados",
        "Belarus" => "Belarus",
        "Belgium" => "Belgium",
        "Belize" => "Belize",
        "Benin" => "Benin",
        "Bermuda" => "Bermuda",
        "Bhutan" => "Bhutan",
        "Bolivia" => "Bolivia",
        "Bonaire" => "Bonaire",
        "Bosnia & Herzegovina" => "Bosnia & Herzegovina",
        "Botswana" => "Botswana",
        "Brazil" => "Brazil",
        "British Indian Ocean Ter" => "British Indian Ocean Ter",
        "Brunei" => "Brunei",
        "Bulgaria" => "Bulgaria",
        "Burkina Faso" => "Burkina Faso",
        "Burundi" => "Burundi",
        "Cambodia" => "Cambodia",
        "Cameroon" => "Cameroon",
        "Canada" => "Canada",
        "Canary Islands" => "Canary Islands",
        "Cape Verde" => "Cape Verde",
        "Cayman Islands" => "Cayman Islands",
        "Central African Republic" => "Central African Republic",
        "Chad" => "Chad",
        "Channel Islands" => "Channel Islands",
        "Chile" => "Chile",
        "China" => "China",
        "Christmas Island" => "Christmas Island",
        "Cocos Island" => "Cocos Island",
        "Colombia" => "Colombia",
        "Comoros" => "Comoros",
        "Congo" => "Congo",
        "Cook Islands" => "Cook Islands",
        "Costa Rica" => "Costa Rica",
        "Cote DIvoire" => "Cote DIvoire",
        "Croatia" => "Croatia",
        "Cuba" => "Cuba",
        "Curaco" => "Curacao",
        "Cyprus" => "Cyprus",
        "Czech Republic" => "Czech Republic",
        "Denmark" => "Denmark",
        "Djibouti" => "Djibouti",
        "Dominica" => "Dominica",
        "Dominican Republic" => "Dominican Republic",
        "East Timor" => "East Timor",
        "Ecuador" => "Ecuador",
        "Egypt" => "Egypt",
        "El Salvador" => "El Salvador",
        "Equatorial Guinea" => "Equatorial Guinea",
        "Eritrea" => "Eritrea",
        "Estonia" => "Estonia",
        "Ethiopia" => "Ethiopia",
        "Falkland Islands" => "Falkland Islands",
        "Faroe Islands" => "Faroe Islands",
        "Fiji" => "Fiji",
        "Finland" => "Finland",
        "France" => "France",
        "French Guiana" => "French Guiana",
        "French Polynesia" => "French Polynesia",
        "French Southern Ter" => "French Southern Ter",
        "Gabon" => "Gabon",
        "Gambia" => "Gambia",
        "Georgia" => "Georgia",
        "Germany" => "Germany",
        "Ghana" => "Ghana",
        "Gibraltar" => "Gibraltar",
        "Great Britain" => "Great Britain",
        "Greece" => "Greece",
        "Greenland" => "Greenland",
        "Grenada" => "Grenada",
        "Guadeloupe" => "Guadeloupe",
        "Guam" => "Guam",
        "Guatemala" => "Guatemala",
        "Guinea" => "Guinea",
        "Guyana" => "Guyana",
        "Haiti" => "Haiti",
        "Hawaii" => "Hawaii",
        "Honduras" => "Honduras",
        "Hong Kong" => "Hong Kong",
        "Hungary" => "Hungary",
        "Iceland" => "Iceland",
        "Indonesia" => "Indonesia",
        "India" => "India",
        "Iran" => "Iran",
        "Iraq" => "Iraq",
        "Ireland" => "Ireland",
        "Isle of Man" => "Isle of Man",
        "Israel" => "Israel",
        "Italy" => "Italy",
        "Jamaica" => "Jamaica",
        "Japan" => "Japan",
        "Jordan" => "Jordan",
        "Kazakhstan" => "Kazakhstan",
        "Kenya" => "Kenya",
        "Kiribati" => "Kiribati",
        "Korea North" => "Korea North",
        "Korea Sout" => "Korea South",
        "Kuwait" => "Kuwait",
        "Kyrgyzstan" => "Kyrgyzstan",
        "Laos" => "Laos",
        "Latvia" => "Latvia",
        "Lebanon" => "Lebanon",
        "Lesotho" => "Lesotho",
        "Liberia" => "Liberia",
        "Libya" => "Libya",
        "Liechtenstein" => "Liechtenstein",
        "Lithuania" => "Lithuania",
        "Luxembourg" => "Luxembourg",
        "Macau" => "Macau",
        "Macedonia" => "Macedonia",
        "Madagascar" => "Madagascar",
        "Malaysia" => "Malaysia",
        "Malawi" => "Malawi",
        "Maldives" => "Maldives",
        "Mali" => "Mali",
        "Malta" => "Malta",
        "Marshall Islands" => "Marshall Islands",
        "Martinique" => "Martinique",
        "Mauritania" => "Mauritania",
        "Mauritius" => "Mauritius",
        "Mayotte" => "Mayotte",
        "Mexico" => "Mexico",
        "Midway Islands" => "Midway Islands",
        "Moldova" => "Moldova",
        "Monaco" => "Monaco",
        "Mongolia" => "Mongolia",
        "Montserrat" => "Montserrat",
        "Morocco" => "Morocco",
        "Mozambique" => "Mozambique",
        "Myanmar" => "Myanmar",
        "Nambia" => "Nambia",
        "Nauru" => "Nauru",
        "Nepal" => "Nepal",
        "Netherland Antilles" => "Netherland Antilles",
        "Netherlands" => "Netherlands (Holland, Europe)",
        "Nevis" => "Nevis",
        "New Caledonia" => "New Caledonia",
        "New Zealand" => "New Zealand",
        "Nicaragua" => "Nicaragua",
        "Niger" => "Niger",
        "Nigeria" => "Nigeria",
        "Niue" => "Niue",
        "Norfolk Island" => "Norfolk Island",
        "Norway" => "Norway",
        "Oman" => "Oman",
        "Pakistan" => "Pakistan",
        "Palau Island" => "Palau Island",
        "Palestine" => "Palestine",
        "Panama" => "Panama",
        "Papua New Guinea" => "Papua New Guinea",
        "Paraguay" => "Paraguay",
        "Peru" => "Peru",
        "Phillipines" => "Philippines",
        "Pitcairn Island" => "Pitcairn Island",
        "Poland" => "Poland",
        "Portugal" => "Portugal",
        "Puerto Rico" => "Puerto Rico",
        "Qatar" => "Qatar",
        "Republic of Montenegro" => "Republic of Montenegro",
        "Republic of Serbia" => "Republic of Serbia",
        "Reunion" => "Reunion",
        "Romania" => "Romania",
        "Russia" => "Russia",
        "Rwanda" => "Rwanda",
        "St Barthelemy" => "St Barthelemy",
        "St Eustatius" => "St Eustatius",
        "St Helena" => "St Helena",
        "St Kitts-Nevis" => "St Kitts-Nevis",
        "St Lucia" => "St Lucia",
        "St Maarten" => "St Maarten",
        "St Pierre & Miquelon" => "St Pierre & Miquelon",
        "St Vincent & Grenadines" => "St Vincent & Grenadines",
        "Saipan" => "Saipan",
        "Samoa" => "Samoa",
        "Samoa American" => "Samoa American",
        "San Marino" => "San Marino",
        "Sao Tome & Principe" => "Sao Tome & Principe",
        "Saudi Arabia" => "Saudi Arabia",
        "Senegal" => "Senegal",
        "Seychelles" => "Seychelles",
        "Sierra Leone" => "Sierra Leone",
        "Singapore" => "Singapore",
        "Slovakia" => "Slovakia",
        "Slovenia" => "Slovenia",
        "Solomon Islands" => "Solomon Islands",
        "Somalia" => "Somalia",
        "South Africa" => "South Africa",
        "Spain" => "Spain",
        "Sri Lanka" => "Sri Lanka",
        "Sudan" => "Sudan",
        "Suriname" => "Suriname",
        "Swaziland" => "Swaziland",
        "Sweden" => "Sweden",
        "Switzerland" => "Switzerland",
        "Syria" => "Syria",
        "Tahiti" => "Tahiti",
        "Taiwan" => "Taiwan",
        "Tajikistan" => "Tajikistan",
        "Tanzania" => "Tanzania",
        "Thailand" => "Thailand",
        "Togo" => "Togo",
        "Tokelau" => "Tokelau",
        "Tonga" => "Tonga",
        "Trinidad & Tobago" => "Trinidad & Tobago",
        "Tunisia" => "Tunisia",
        "Turkey" => "Turkey",
        "Turkmenistan" => "Turkmenistan",
        "Turks & Caicos Is" => "Turks & Caicos Is",
        "Tuvalu" => "Tuvalu",
        "Uganda" => "Uganda",
        "United Kingdom" => "United Kingdom",
        "Ukraine" => "Ukraine",
        "United Arab Erimates" => "United Arab Emirates",
        "United States of America" => "United States of America",
        "Uraguay" => "Uruguay",
        "Uzbekistan" => "Uzbekistan",
        "Vanuatu" => "Vanuatu",
        "Vatican City State" => "Vatican City State",
        "Venezuela" => "Venezuela",
        "Vietnam" => "Vietnam",
        "Virgin Islands (Brit)" => "Virgin Islands (Brit)",
        "Virgin Islands (USA)" => "Virgin Islands (USA)",
        "Wake Island" => "Wake Island",
        "Wallis & Futana Is" => "Wallis & Futana Is",
        "Yemen" => "Yemen",
        "Zaire" => "Zaire",
        "Zambia" => "Zambia",
        "Zimbabwe" => "Zimbabwe",
    );

    return $countries;
}
    


// $('.select2').select2();
function getCountriesSearchSelect($name='', $set_value='', $opt='') {

    $countries = getCountries(); 

    $_html = '<select class="select2 form-control w-100" name="'.$name.'">';
    $_html .= '<option></option>';
    foreach($countries as $k=>$v) {

        $selected = '';
        if($k == $set_value) $selected = 'selected';

        $_html .= '<option value="'.$k.'" '.$selected.'>'.$v.'</option>';
       }
    $_html .= '</select>';


    if(strpos($opt, 'required') !== false) { 
        $_html .= '<div class="invalid-feedback"><i class="fas fa-info-circle mr-1"></i>This is required.</div>';
    }

    return $_html;
}






// $('.select2').select2();
function getSearchSelect($data=array(), $name='', $set_value='', $opt='') {

    $_html = '<select class="select2 form-control w-100" name="'.$name.'" id="'.$name.'" '.$opt.'>';
    $_html .= '<option value=""></option>';
    foreach($data as $k=>$v) {
        $selected = '';
        if($k == $set_value) $selected = 'selected';

        $_html .= '<option value="'.$k.'" '.$selected.'>'.$v.'</option>';
    }
    $_html .= '</select>';


    if(strpos($opt, 'required') !== false) { 
        $_html .= '<div class="invalid-feedback"><i class="fas fa-info-circle mr-1"></i>This is required.</div>';
    }

    return $_html;
}



/*

Array(
    [1] => Array(
        [0] => Array(
            [grp_name]  => COCEN_11111
            [grp_id]    => 1
            [opt_name]  => Dell XPS 54
            [opt_id]    => 3 
        )
    )

    [2] => Array(
        [0] => Array(
            [grp_name]  => COCEN_123452
            [grp_id]    => 2
            [opt_name]  => Dell Inspiron 3452
            [opt_id]    => 6 
        )

        [1] => Array(
            [grp_name]  => COCEN_123452
            [grp_id]    => 2
            [opt_name]  => Dell Inspiron 3433
            [opt_id]    => 5 
        )
        ....

*/
function getGroupSearchSelect($data=array(), $name='', $set_value='', $opt='') {

    $_opt_html = '<option value=""></option>';

    foreach($data as $grp_data) {
        if(sizeof($grp_data) < 1) continue;

        $_opt_html .= '<optgroup label="'.$grp_data[0]['grp_name'].'">';
        foreach($grp_data as $opt_data) {
            $selected = '';
            if($opt_data['opt_id'] == $set_value) $selected = 'selected';

            $icon_attr = '';
            if(isset($opt_data['opt_icon']) && strlen($opt_data['opt_icon'])) {
                $icon_attr = 'data-icon="'.$opt_data['opt_icon'].'" data-color="'.$opt_data['opt_color'].'"';
            }

            $_opt_html .= '<option value="'.$opt_data['opt_id'].'" '.$selected.' '.$icon_attr.'>'.$opt_data['opt_name'].'</option>';
        }
        $_opt_html .= '</optgroup>';
    }
    $sel_class = 'select2';
    if(isset($icon_attr) && strlen($icon_attr) > 0) {
        $sel_class = 'select2-icon';
    }

    $_html = '<select class="'.$sel_class.' form-control w-100" name="'.$name.'" id="'.$name.'" '.$opt.'>';
    $_html .= $_opt_html;
    $_html .= '</select>';

    if(strpos($opt, 'required') !== false) { 
        $_html .= '<div class="invalid-feedback"><i class="fas fa-info-circle mr-1"></i>This is required.</div>';
    }

    return $_html;
}

function getSearchWithIconSelect($data=array(), $name='', $set_value='', $opt='') {

    $_html = '<select class="select2-icon form-control w-100" name="'.$name.'" id="'.$name.'" '.$opt.'>';

    $_html .= '<option value=""></option>';
    foreach($data as $k=>$v) {
        $selected = '';
        if($k == $set_value) $selected = 'selected';
        $_html .= '<option value="'.$k.'" '.$selected.' data-icon="'.$v['opt_icon'].'"  data-color="'.$v['opt_color'].'">'.$v['opt_name'].'</option>';
    }
    $_html .= '</select>';

    if(strpos($opt, 'required') !== false) { 
        $_html .= '<div class="invalid-feedback"><i class="fas fa-info-circle mr-1"></i>This is required.</div>';
    }

    return $_html;
}





function getSelect($data=array(), $name='', $set_value='', $opt='') {

    $_html = '<select class="custom-select" name="'.$name.'" id="'.$name.'" '.$opt.'>';
    $_html .= '<option></option>';

    foreach($data as $k=>$v) {

        $selected = '';
        if($k == $set_value) $selected = 'selected';

        $_html .= '<option value="'.$k.'" '.$selected.'>'.$v.'</option>';
    }
    $_html .= '</select>';

    if(strpos($opt, 'required') !== false) { 
        $_html .= '<div class="invalid-feedback"><i class="fas fa-info-circle mr-1"></i>This is required.</div>';
    }

    return $_html;
}


function getInputMaskMap($type) {


    $input_mask = array(

            'normal' => array(
                'fal_icon'  => "fa-terminal",
                'str_icon'  => "",
                'pattern'   => "",
                'help'      => "",
            ),
            'boolean' => array(
                'fal_icon'  => "fa-light-swich",
                'str_icon'  => "",
                'pattern'   => "",
                'help'      => "YES or NO",
            ),

            'tel' => array(
                'fal_icon'  => "fa-phone-alt",
                'str_icon'  => "",
                'pattern'   => "'mask': '99[9]-999[9]-9999', 'removeMaskOnSubmit':true",
                'help'      => "999-9999-9999",
            ),

            'fax' => array(
                'fal_icon'  => "fa-fax",
                'str_icon'  => "",
                'pattern'   => "'mask': '99[9]-999[9]-9999', 'removeMaskOnSubmit':true",
                'help'      => "999-9999-9999",
            ),

            'zip' => array(
                'fal_icon'  => "fa-envelope-square",
                'str_icon'  => "",
                'pattern'   => "'mask': '99999', 'removeMaskOnSubmit':true",
                'help'      => "99999",
            ),
            'email' => array(
                'fal_icon'  => "fa-mail-bulk",
                'str_icon'  => "",
                'pattern'   => "'alias': 'email', 'removeMaskOnSubmit':true",
                'help'      => "xxx@xxx.xxx 영문,숫자가능  (!) 미입력시 키보드 한글 여부 확인",
            ),

            'url' => array(
                'fal_icon'  => "fa-window",
                'str_icon'  => "",
                'pattern'   => "'alias': 'url', 'removeMaskOnSubmit':true, 'regex': '(http|https)://.*'",
                'help'      => "http:// or https://",
            ),

            'ko_currency' => array(
                'fal_icon'  => "",
                'str_icon'  => "&#8361;",
                'pattern'   => "'alias':'decimal', 'removeMaskOnSubmit':true, 'autoGroup':true, 'groupSeparator':',', 'digits':3",
                'help'      => "99,999,999,999",
            ),
            'biz_number' => array(
                'fal_icon'  => "fa-building",
                'str_icon'  => "",
                'pattern'   => "'mask':'999-99-99999', 'removeMaskOnSubmit':true",
                'help'      => "999-99-99999",
            ),
            'number' => array(
                'fal_icon'  => "",
                'str_icon'  => "XX",
                'pattern'   => "'mask':'9999999999', 'removeMaskOnSubmit':true, 'numericInput':true",
                'help'      => "9999999999",
            ),
            'decimal_number' => array(
                'fal_icon'  => "",
                'str_icon'  => "XX",
                'pattern'   => "'mask':'[9]9', 'removeMaskOnSubmit':true, 'numericInput':true",
                'help'      => "99",
            ),
            'unit_number' => array(
                'fal_icon'  => "",
                'str_icon'  => "X",
                'pattern'   => "'mask':'9', 'removeMaskOnSubmit':true, 'numericInput':true",
                'help'      => "1~3",
            ),
            'date' => array(
                'fal_icon'  => "fa-calendar",
                'str_icon'  => "",
                'pattern'   => "'mask':'9999-99-99', 'removeMaskOnSubmit':true, 'numericInput':true",
                'help'      => "yyyy-mm-dd",
            ),
            'datetime' => array(
                'fal_icon'  => "fa-calendar",
                'str_icon'  => "",
                'pattern'   => "'mask':'9999-99-99 99:99:99', 'removeMaskOnSubmit':true, 'numericinput':true",
                'help'      => "yyyy-mm-dd hh:mm:ss",
            ),
            'mac' => array(
                'fal_icon'  => "fa-desktop-alt",
                'str_icon'  => "",
                'pattern'   => "'alias':'mac', 'removeMaskOnSubmit':true",
                'help'      => "99:99:99:99:99:99",
            ),
            'ipv4' => array(
                'fal_icon'  => "fa-network-wired",
                'str_icon'  => "",
                'pattern'   => "'alias':'ip'",
                'help'      => "192.168.119.310",
            ),
            'cidr' => array(
                'fal_icon'  => "fa-network-wired",
                'str_icon'  => "",
                'pattern'   => "'mask':'9[9[9]].9[9[9]].9[9[9]].9[9[9]]/9[9]'",
                'help'      => "127.0.0.0/24",
            ),
            'ipv6' => array(
                'fal_icon'  => "fa-network-wired",
                'str_icon'  => "",
                'pattern'   => "'alias':'9999:9999:9999:9999:9999:9999:9999:9999:', 'removeMaskOnSubmit':true",
                'help'      => "2001:00A9:1000:0000:0000:0000:1234:5678",
            ),
            'upper' => array(
                'fal_icon'  => "",
                'str_icon'  => "xX",
                'pattern'   => "'mask':'&{1,20}'",
                'help'      => "Upper(a->A) & Under character 20",
            ),
            
    );

    return $input_mask[$type];
}


// $('.input-mask').inputmask();
function getInputMask($type='', $name='', $set_value='', $opt='') {

    $mask = getInputMaskMap($type);

    $_html ='';
    $_html .= '<div class="input-group">';
    $_html .= '<div class="input-group-prepend">';
    $_html .= '<span class="input-group-text">';
    if(strlen($mask['fal_icon']) > 0) {
        $_html .= '<i class="fal '.$mask['fal_icon'].' width-1 text-align-center"></i>';
    }else {
        $_html .= $mask['str_icon'];
    }
    $_html .= '</span>';
    $_html .= '</div>';
    $_html .= '<input type="text" name="'.$name.'" value="'.$set_value.'" data-inputmask="'.$mask['pattern'].'" '.$opt.' class="form-control input-mask">';

    if(strpos($opt, 'required') !== false) { 
        $_html .= '<div class="invalid-feedback"><i class="fas fa-info-circle mr-1"></i>This is required.</div>';
    }

    $_html .= '</div>';
    $_html .= '<span class="help-block">'.$mask['help'].'</span>';

    return $_html;
}


function getTextArea($name='', $set_value='', $opt='', $title='Comment') {

    $_html = '';
    $_html .= '<div class="input-group">';
    $_html .= '<div class="input-group-prepend">';  
    $_html .= '<span class="input-group-text">'.$title.'</span>';
    $_html .= '</div>';
    $_html .= '<textarea class="form-control" style="height:80px;" aria-label="With textarea" name="'.$name.'" '.$opt.'>'.$set_value.'</textarea>';
    $_html .= '</div>';

    return $_html;
}


/* 

Array (
 [text] => Text
 [textarea] => Textarea
 [checkbox] => Checkbox
 [radio] => Radio
)

@return "text","textarea","checkbox","radio"
*/

function strColumnOptsByKey($data) {
    $str = '';
    foreach($data as $k=>$v) {
        $str .= '"'.$k.'",';
    }
    $str = substr($str, 0, -1);
    return $str;
}




// DataTable 상단 [New] Button
function genNewButton($link) {

    $url = '';

    switch($link) {
        case 'assets':
			$url = '/'.SHOP_INFO_ADMIN_DIR.'/assets/detail/servers';
            break;
        case 'orders':
			$url = '/'.SHOP_INFO_ADMIN_DIR.'/purchase/'.$link.'_detail';
            break;
        case 'employee':
        case 'ip':
			$url = '/'.SHOP_INFO_ADMIN_DIR.'/people/'.$link.'_detail';
            break;
        case 'type':
        case 'ipclass':
			$url = '/'.SHOP_INFO_ADMIN_DIR.'/system/'.$link.'_detail';
            break;
        default:
        	$url = '/'.SHOP_INFO_ADMIN_DIR.'/manage/'.$link.'_detail';
            break;
    }

    $html = '<a href="'.$url.'" target="_blank" class="btn btn-xs btn-outline-info waves-effect waves-themed mr-1 position-absolute pos-right">';
    $html .= '<i class="fal fa-plus-circle mr-1"></i> ';
    $html .= 'New'; 
    $html .= '</a>';
    
    return $html;
}


function genLinkButton($link, $id=0) {

    $html = '';
    $url = '';

    if($id < 1) {
        return $html;
    }

    switch($link) {
        case 'assets':
			$url = '/'.SHOP_INFO_ADMIN_DIR.'/assets/status_detail';
            break;
        case 'orders':
			$url = '/'.SHOP_INFO_ADMIN_DIR.'/purchase/'.$link.'_detail';
            break;
        case 'employee':
        case 'ip':
			$url = '/'.SHOP_INFO_ADMIN_DIR.'/people/'.$link.'_detail';
            break;
        case 'type':
        case 'ipclass':
        case 'hostmap':
			$url = '/'.SHOP_INFO_ADMIN_DIR.'/system/'.$link.'_detail';
            break;
        default:
        	$url = '/'.SHOP_INFO_ADMIN_DIR.'/manage/'.$link.'_detail';
            break;
    }
    $url .= '/'.$id;

    $html .= '<a href="'.$url.'" target="_blank" class="btn btn-xs btn-outline-primary waves-effect waves-themed position-absolute pos-right" style="margin-right:78px;">';
    $html .= '<i class="fal fa-location-circle mr-1"></i> ';
    $html .= 'Detail'; 
    $html .= '</a>';
    
    return $html;
}


function genIconFindButton() {

    $url = 'http://ui2.phoenixq.hamt.co.kr/icons_fontawesome_light.html';

    $html = '';
    $html .= '<a href="'.$url.'" target="_blank" class="btn btn-xs btn-outline-info waves-effect waves-themed mr-1 position-absolute pos-right">';
    $html .= '<i class="fal fa-search mr-1"></i> Find Icon';
    $html .= '</a>';

    return $html;
}


function genDetailButton($link, $mode, $type='') {

    $url = '';
    switch($link) {
        case 'assets_type':
			$url = '/'.SHOP_INFO_ADMIN_DIR.'/assets/type/'.$type;
            break;
        case 'maintenance':
			$url = '/'.SHOP_INFO_ADMIN_DIR.'/assets/'.$link;
            break;
        case 'orders':
			$url = '/'.SHOP_INFO_ADMIN_DIR.'/purchase/'.$link;
            break;
        case 'employee':
        case 'partners':
			$url = '/'.SHOP_INFO_ADMIN_DIR.'/people/'.$link;
            break;
        case 'ip':
			$url = '/'.SHOP_INFO_ADMIN_DIR.'/people/ip_list';
            break;
        case 'local':
        case 'idc':
			$url = '/'.SHOP_INFO_ADMIN_DIR.'/system/ipclass/'.$link;
            break;
        case 'type':
        case 'status':
        case 'manager':
        case 'ipclass':
        case 'hostmap':
			$url = '/'.SHOP_INFO_ADMIN_DIR.'/system/'.$link;
            break;
        default: 
    		$url = '/'.SHOP_INFO_ADMIN_DIR.'/manage/'.$link;
            break;
    }

                        
    $text = 'Cancel';
    $icon = 'fa-times';
    if($mode == 'update') {
        $text = 'Lists';
        $icon = 'fa-list-alt';
        $url .= '?keep=yes';
    }

    $html = '<button type="submit" class="btn-save mr-1 btn btn-sm btn-success waves-effect waves-themed ml-auto">';
    $html .= '<span class="fal fa-save mr-1"></span> Save ';
    $html .= '</button>';

    $html .= '<a href="'.$url.'" class="btn btn-sm btn-danger waves-effect waves-themed">';
    $html .= '<span class="fal '.$icon.' mr-1"></span> '; 
    $html .= $text;
    $html .= '</a>';

    return $html;
}



function genDeleteButton() {

    $text = 'Delete';
    $icon = 'fa-trash-alt';

    $url = 'javascript:delAssets();';

    $html = '';
    $html .= '<a href="'.$url.'" class="btn btn-sm btn-dark waves-effect ml-1 waves-themed">';
    $html .= '<span class="fal '.$icon.' mr-1"></span> '; 
    $html .= $text;
    $html .= '</a>';

    return $html;
}


function genFullButton() {

    $html = '';
    $html .= '<div class="btn btn-sm btn-warning waves-effect waves-themed ml-1 text-white" data-action="panel-fullscreen" data-toggle="tooltip" data-offset="0,10" data-original-title="Fullscreen">';
    $html .= '<i class="fas fa-expand-wide mr-1"></i>Full Screen';
    $html .= '</div>';

    return $html;
}

function genDropzone() {

    $html = '<div id="div_dropzone" class="dropzone needsclick" style="min-height: 7rem;">';
    $html .= '<div class="dz-message needsclick">';
    $html .= '<i class="fal fa-cloud-upload text-muted mb-3"></i> <br>';
    $html .= '<span class="text-uppercase">Drop files here or click to upload.</span>';
    $html .= '<br>';
    $html .= '<span class="fs-sm text-muted">This is just a demo dropzone. Selected files are <strong>not</strong> actually uploaded.</span>';
    $html .= '</div>';
    $html .= '</div>';
    
    return $html;
} 


function tagsToHtml($tags) {

    if(strlen($tags) < 1) return;

    $html_tag = '';
    $tags = explode(',', $tags);
    foreach($tags as $tag) {
        $html_tag .= '<span class="badge border border-info text-info mr-1">#'.$tag.'</span>';
    }
    return $html_tag; 
}

function nameToLinkHtml($link, $name, $target='_self', $ico='fa-external-link-square') {

    if(strlen($name) < 1) return;

    $opt_target = 'target="'.$target.'"';
   
    $html = '<a href="'.$link.'" class="text-info" '.$opt_target.'>';
    $html.= $name;
    $html.= '<i class="fal '.$ico.' ml-1"></i>';
    $html.= '</a>';

    return $html;
}
?>
