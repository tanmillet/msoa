<?php
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
header("Content-type: text/html; charset=utf-8");
define('ROOT_PATH', dirname(__FILE__));
define("KEY_PREFIX", 'dmadmin:');
define("PRIVDATA_DIR", '/data/private');
define("TEMP_DIR", PRIVDATA_DIR . '/tmp');
define('IMAGE_DOMAIN', 'http://ps.stosz.com');
define('DS', DIRECTORY_SEPARATOR);
define('SYS_PATH', 'sys' . DS);
require_once(SYS_PATH . 'init.php');
Sys_Init::init();

include(ROOT_PATH . '/helper.php');
//获取上下文执行环境
define('ENV', Libs_Conf::get('ENV', 'app'));
define('ENV_FILE', Libs_Conf::get('ENV', 'app'));
//开发环境开启异常
(Libs_Conf::get('DEBUG', ENV_FILE)) ? ini_set('display_error', 'On') : ini_set('display_error', 'Off');
if (!get_magic_quotes_gpc()) {
    $_GET = addslashes_deep($_GET);
    $_POST = addslashes_deep($_POST);
    $_COOKIE = addslashes_deep($_COOKIE);
}
set_exception_handler('bgnException');
date_default_timezone_set('Asia/Shanghai');
ini_set('default_charset', "utf-8");

if ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
    define('IS_AJAX', true);
} else {
    define('IS_AJAX', false);
}
if (isset($_SERVER['REDIRECT_URL'])) {
    $uri_info = $_SERVER['REDIRECT_URL'];
} elseif (isset($_SERVER['REQUEST_URI'])) {
    $uri_info = $_SERVER['REQUEST_URI'];
} elseif (isset($_SERVER['PATH_INFO'])) {
    $uri_info = $_SERVER['PATH_INFO'];
}
if (strpos($uri_info, '?')) {
    $uri_info = Libs_Tools::leftString('?', $uri_info);
}
$GLOBALS['request_uri_info'] = $uri_info;
$uri_segment = [];
if ($uri_info) {
    $uri_info = rtrim($uri_info, "/") . "/";    // 无论是否/结尾,统一按照/结尾
    $aPathInfo = explode('/', trim($uri_info, "/"));    // 获取 pathinfo
    $controller = (isset($aPathInfo[0]) ? $aPathInfo[0] : '');    // 获取 control
    array_shift($aPathInfo);
    $action = (isset($aPathInfo[0]) ? $aPathInfo[0] : '');   // 获取 action
    array_shift($aPathInfo);
    while ($aPathInfo && is_array($aPathInfo)) {
        $uri_segment[$aPathInfo[0]] = $aPathInfo[1];
        array_shift($aPathInfo);
        array_shift($aPathInfo);
    }
}
$action = $action ? $action : '';


//if (isNeedCheckSession($controller, $action)) {
//    $ps_api = new Ctrs_Api();
//    if(!isset($_GET['session_id']) || empty($_GET['session_id'])) {
//        echo $ps_api->setStatusCode(204)->responseError(10002);
//        die();
//    }
//    $user_info = Libs_Predis::getInstance()->get($_GET['session_id']);
//    $user_info = json_decode($user_info , true);
//
//    Sys_Logs::x()->writeLog("User Logined : " . json_encode($user_info));
//    if(empty($user_info)){
//        echo $ps_api->setStatusCode(204)->responseError(10002);
//        die();
//    }
//    $auth_model = new Models_Auth();
//    $user_auth = $auth_model->isUserCanAccess($user_info['uid'], $controller, $action);
//    if (!$user_auth) {
//        echo $ps_api->setStatusCode(400)->responseError(10037);
//        die();
//    }
//}
//$controllers = Libs_Conf::get('route_map', 'ps');
//$_controller = isset($controllers[$controller]) ? $controllers[$controller] : 'Home';
//加载DB操作类
new \Sys_Container('Libs_Db');
$ps_api = new Ctrs_Api();
if (empty($controller) || empty($action)) {
    echo $ps_api->setStatusCode(400)->responseE(Libs_Conf::get('400', 'exs')['404']);
    die();
}
$controller = 'Ctrs_' . ucfirst($controller);
(new $controller())->$action();