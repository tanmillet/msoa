<?php

final class Libs_Conf {
    /**
     * @param null $key
     * @param string $default
     * @return mixed
     */
    public static function get($key = null, $file_name = '', $default = '')
    {
        if (is_null($key) || empty($key)) {
            return self::getSetValue($default);
        }
        $array = self::getAllConf($file_name);
        if (!isset($array)) {
            return self::getSetValue($default);
        }
        if (!(is_array($array) || $array instanceof ArrayAccess)) {
            return self::getSetValue($array);
        }
        if (static::arrayKeyExists($array, $key)) {
            return (isset($array[$key]) && !empty($array[$key])) ? $array[$key] : self::getSetValue($default);
        }
        if (strpos($key, '.') === false) {
            return $array[$key] ? $array[$key] : self::getSetValue($default);
        }
        foreach (explode('.', $key) as $segment) {
            if ((is_array($array) || $array instanceof ArrayAccess) && static::arrayKeyExists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return self::getSetValue($default);
            }
        }
        return $array;
    }

    private static function getAllConf(string $conf_file_name)
    {
        static $confs = [];
        if (isset($confs[$conf_file_name])) {
            return $confs[$conf_file_name];
        }
        try {
            $path = ROOT_PATH . '/config/' . $conf_file_name . '.php';
            if (is_file($path)) {
                $conf = include $path;
                $confs[$conf_file_name] = $conf;
                return $conf;
            }
        } catch (Exception $exception) {
            bgnException($exception);
        }
        return [];
    }

    /**
     * @param $value
     * @return mixed
     */
    private static function getSetValue($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }

    /**
     * @param $array
     * @param $key
     * @return bool
     */
    private static function arrayKeyExists($array, $key)
    {
        if ($array instanceof ArrayAccess) {
            return $array->offsetExists($key);
        }
        return array_key_exists($key, $array);
    }
}