<?php
/*
 * 递归方式对变量中的特殊字符去除转义
 * @access public
 * @param mix $value
 * return $value
 */
if (!function_exists('addslashes_deep')) {
    function addslashes_deep($value)
    {
        if (empty($value)) {
            return $value;
        } else {
            return is_array($value) ? array_map('addslashes_deep', $value) : addslashes($value);
        }
    }
}
/*
* 递归方式对变量中的特殊字符去除转义
* @access public
* @param mix $value
* return $value
*/
if (!function_exists('stripslashes_deep')) {
    function stripslashes_deep($value)
    {
        $value = is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
        return $value;
    }
}
if (!function_exists('bgnException')) {
    function bgnException($exception)
    {
        $log_data = '';
        $log_data .= date("Y-m-d H:i:s") . ' ' . $exception->__toString();
        Sys_Logs::x()->writeLog($log_data, 'exception_error');
        //开发环境直接打印出异常
        if (Libs_Conf::get('DEBUG', Libs_Conf::get('ENV', 'app'))) {
            $ps_api = new Ctrs_Api();
            echo $ps_api->setStatusCode(500)->responseE($exception->getMessage());
            die();
        }
    }
}
if (!function_exists('isNeedCheckSession')) {
    function isNeedCheckSession($controller, $action)
    {
        global $no_need_check_session;
        if (!array_key_exists($controller, $no_need_check_session = Libs_Conf::get('no_need_check_session', 'ps'))) {
            return true;
        }
        if (is_array($no_need_check_session[$controller])) {
            if (!$no_need_check_session[$controller]) {
                return false;
            }
            if (in_array($action, $no_need_check_session[$controller])) {
                return false;
            }
        }
        return true;
    }
}

