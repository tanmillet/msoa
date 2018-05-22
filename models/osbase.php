<?php

class Models_Osbase {

    public function __construct()
    {
    }

    /**
     * @return Libs_Db
     */
    public static function MySql(): Libs_Db
    {
        return Sys_Container::$container['Libs_Db'];
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        if (count($arguments) == 1) {
            return Sys_Container::$container['Libs_Db']->$name($arguments[0]);
        }
        if (count($arguments) == 2) {
            return Sys_Container::$container['Libs_Db']->$name($arguments[0], $arguments[1]);
        }
        if (count($arguments) == 3) {
            return Sys_Container::$container['Libs_Db']->$name($arguments[0], $arguments[1], $arguments[2]);
        }
        if (count($arguments) == 4) {
            return Sys_Container::$container['Libs_Db']->$name($arguments[0], $arguments[1], $arguments[2], $arguments[3]);
        }
        if (count($arguments) == 5) {
            return Sys_Container::$container['Libs_Db']->$name($arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4]);
        }
    }
}