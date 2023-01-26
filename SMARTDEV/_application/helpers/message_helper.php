<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function getAlertMsg($code, $lang='kr') {

    if(strlen($code) < 1) return;

    $msg = array(
        'INVALID_ACCESS' => array(
            'kr'    => '잘못된 접근입니다.',
            'en'    => 'Invalid access!'
        ),
        'INVALID_SUBMIT' => array(
            'kr'    => '잘못된 데이터 값으로 접근되었습니다.',
            'en'    => 'Invalid submit data!'
        ),
        'REQUIRED_VALUES' => array(
            'kr'    => '필수 항목이 누락되었습니다.',
            'en'    => 'Need to requied value.'
        ),
        'DUPLICATE_VALUES' => array(
            'kr'    => '해당 값은 중복입력이 불가합니다.',
            'en'    => 'This is dulplicate value.'
        ),
        'EMPTY_PARAMS' => array(
            'kr'    => '입력값이 누락되었습니다. ',
            'en'    => 'This is empty value.'
        ),
        'SHORT_LENGTH' => array(
            'kr'    => '입력값이 길이가 충족되지 않습니다. ',
            'en'    => 'This is value too short.'
        ),
        'EXISTS_BLANK' => array(
            'kr'    => '입력값 공백이 포함되어 있습니다. ',
            'en'    => 'The input contains spaces.'
        ),
        'NOT_MIXED' => array(
            'kr'    => '영문, 숫자, 특수문자 혼합하여 입력해주세요',
            'en'    => 'Enter a mixture of letters, numbers, and special characters.'
        ),
        'FAILED_INSERT' => array(
            'kr'    => '입력에 실패 했습니다. 데이터를 확인해 주세요.',
            'en'    => 'Failed Insert!. Confirm your params (data).'
        ),
        'FAILED_UPDATE' => array(
            'kr'    => '수정에 실패 했습니다. 데이터를 확인해 주세요.',
            'en'    => 'Failed Update!. Confirm your params (data).'
        ),
        'FAILED_DELETE' => array(
            'kr'    => '삭제에 실패 했습니다. 데이터를 확인해 주세요.',
            'en'    => 'Failed Delete!. Confirm your params (data).'
        ),
        'DUPLICATE_IP' => array(
            'kr'    => '이미 등록된 IP 입니다. 다른 IP를 입력해 주세요.',
            'en'    => 'Duplicated IP. Plz Insert Another IP.'
        ),
        'FAILED_DELETE_BINDED' => array(
            'kr'    => '데이터 정합성에 의해 삭제에 실패했습니다. 연결된 데이터를 확인해주세요.',
            'en'    => 'Failed Delete!. Confirm another data.'
        ),
        'EMPTY_HOST_DATA' => array(
            'kr'    => 'Host Syslog 데이터가 매칭되지 않았습니다. 매핑을 확인해주세요.',
            'en'    => 'Failed get data!. Confirm host mapping.'
        ),
        'FAILED_SEND_EMAIL' => array(
            'kr'    => '이메일 발송에 실패 했습니다.',
            'en'    => 'Failed send mail!. Confirm your mail data.'
        ),
    );
    return $msg[$code][$lang];
}


function getConfirmMsg($code, $lang='kr') {

    $msg = array(

        'SETTING' => array(
            'kr'    => '설정하시겠습니까?',
            'en'    => 'Confirm?'
        ),
        'REMOVE' => array(
            'kr'    => '삭제 하시겠습니까?',
            'en'    => 'Do you want to delete?'
        ),

    );
    return $msg[$code][$lang];
}

function getInvalidMsg($code, $lang='kr') {

    $msg = array(

        'BLANK' => array(
            'kr'    => '입력값은 필수입니다.',
            'en'    => 'Please provide a input value.'
        ),

    );
    return $msg[$code][$lang];
}
?>
