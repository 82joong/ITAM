<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Display Debug backtrace
|--------------------------------------------------------------------------
|
| If set to TRUE, a backtrace will be displayed along with php errors. If
| error_reporting is disabled, the backtrace will not display, regardless
| of this setting
|
*/
defined('SHOW_DEBUG_BACKTRACE') OR define('SHOW_DEBUG_BACKTRACE', TRUE);

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
defined('FILE_READ_MODE')  OR define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') OR define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE')   OR define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE')  OR define('DIR_WRITE_MODE', 0755);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/
defined('FOPEN_READ')                           OR define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE')                     OR define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE')       OR define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE')  OR define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE')                   OR define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE')              OR define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT')            OR define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT')       OR define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
|--------------------------------------------------------------------------
| Exit Status Codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
*/
defined('EXIT_SUCCESS')        OR define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR')          OR define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG')         OR define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE')   OR define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS')  OR define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') OR define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     OR define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE')       OR define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN')      OR define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      OR define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code


// Admin Site Name
define('ADMIN_SITE_NAME', 'IT Assets Management');

// Admin Logo Img
//define('LOGO', 'logo.png');
define('LOGO', 'smartdev_logo.png');

// Database Name
define('_SHOP_INFO_DATABASE_', 'itam');

// admin 디렉토리
define('SHOP_INFO_ADMIN_DIR', 'admin');
define('ADMIN_ASSETS_DIR', '/admin_assets');

// todo define IS_MOBILE_SHOP
define('IS_MOBILE_SHOP', false);


define('CURRENCY_KEY', 'CNY_KRW');


// @APPPATH => /home/team/joong/html/ci3_default/SMARTDEV/_application/

define('WEB_DIR', realpath(FCPATH));
define('DISPLAY_PATH', FCPATH.'webdata/display');
define('THUMBNAIL_PATH', FCPATH.'img_assets/thumbnail');
define('IMG_TEMP_PATH', DISPLAY_PATH.'/temp_upload');
define('IMG_PATH', DISPLAY_PATH.'/images');
define('APPDATA_PATH', realpath(FCPATH.'../SMARTDEV/appdata'));


define('THUMBNAIL_URL', '/img_assets/thumbnail');



define('ELASTIC_NGINX_INDEX', 'itam-nginx-access-2021.10');
define('ELASTIC_SYSLOG_INDEX', 'syslog-*');


define('USE_ELASTIC_SECURITY', TRUE);
define('ELASTIC_USER', 'elastic');
define('ELASTIC_USER_PW', 'smartdev123!@#');


define('TAX', '10');    // TAX 10%

// Thresholds
define('CPU_MAX', 99.99);
define('MEM_MAX', 99.99);
define('SWAP_MAX', 0);
define('DISK_MAX', 69.99);
define('TOP_MAX', 99.99);
define('PORT_MAX', array(22, 25, 80, 443, 3306, 9000));

// 반출, 폐기, 수리불가 (고장) : 운용불가
define('OUT_STATUS', array(6,7,8,9,10));

// Info Serialize 
define('USE_AUTH_INFO', FALSE);
define('HTTPS_DASHBOARD_URL', 'https://syslog-api.makeshop.co.kr');

// REAL SERVER
if(APPPATH == '/usr/home/httpd/html/SMARTDEV/_application/') {

    define('LOG_FILE_PATH', '/usr/home/httpd/html/SMARTDEV/appdata/logdata');
    define('SYNC_FILE_PATH', '/usr/home/httpd/html/SMARTDEV/appdata/sync_data');

    define('IS_REAL_SERVER', true);
    define('ADMIN_DOMAIN', 'lab.makeshop.co.kr');
    define('HTTPS_SHOP_URL', 'https://lab.makeshop.co.kr');

    // OTP
    define('USE_OTP', TRUE);


    define('ELASTIC_HOST', '');
    define('ELASTIC_VERSION', '');


    define('ELASTIC_SYSLOG_HOST', 'https://syslog-api.makeshop.co.kr:9200');
    define('ELASTIC_SYSLOG_VERSION', '7.15');

} else {

    define('IS_REAL_SERVER', false);
    define('ADMIN_DOMAIN', 'itam.82joong.joong.co.kr');

    // OTP
    define('USE_OTP', FALSE);

    define('LOG_FILE_PATH', '/home/team/82joong/html/itam/SMARTDEV/appdata/logdata');
    define('SYNC_FILE_PATH', '/home/team/82joong/html/itam/SMARTDEV/appdata/sync_data');


    define('ELASTIC_HOST', 'http://itam.82joong.joong.co.kr:9200');
    define('ELASTIC_VERSION', '7.13');

    define('ELASTIC_SYSLOG_HOST', 'https://syslog-api.makeshop.co.kr:9200');
    define('ELASTIC_SYSLOG_VERSION', '7.15');

}
