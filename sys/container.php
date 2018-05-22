<?php

class Sys_Container {
    public static $container = [];

    public function __construct($class_name)
    {
        if (isset(self::$container[$class_name])) {
            Sys_Logs::x()->writeLog('读取缓存实例：' . $class_name);
            return self::$container[$class_name];
        }
        $class_obj = new $class_name();
        self::$container[$class_name] = $class_obj;
        Sys_Logs::x()->writeLog('新生成实例：' . $class_name);
        return $class_obj;
    }
}