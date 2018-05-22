<?php

class Sys_Logs {
    public static $myself = [];

    public static function x()
    {
        if (!is_object(self::$myself)) {
            self::$myself = new self();
        }
        return self::$myself;
    }

    /**
     * @param String $type 类型mysql\redis\memcache...
     * @param String $errorlog 日志文本
     * @param boolean $die 写完日志后是否die掉，默认false，不die掉
     * @param String $ext 日志文件后缀,默认后缀为 .php
     * */
    public function writeLog1($type, $errorlog, $die = false, $ext = '.php')
    {
        $path = DATA_PATH . $type . DS;
        if (!is_dir($path)) {
            if (!@mkdir($path)) {
                return false;
            }
        }
        $file = $path . $type . date('Ymd') . $ext;
        file_put_contents($file, '<?php exit;?>' . $errorlog . "\n", FILE_APPEND);
        if ($die) {
            die(" {$type} Invalid");
        } else {
            return true;
        }
    }

    function writeLog($msg, $name = null, $log_dir = null)
    {
        if (!$name) {
            $name = date('Y-m-d_H', time());
        } else {
            if ($log_dir === null) {
                $name .= '_' . date('H', time());
            }
        }
        if (isset($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR']) {
            $name .= '_' . $_SERVER['SERVER_ADDR'];
        } else {
            if (isset($GLOBALS['local_ip'])) {
                $name .= '_' . $GLOBALS['local_ip'];
            }
        }
        if ($log_dir === null) {
            $log_dir = '/' . Libs_Conf::get('LOG_DIR_PREFIX', ENV_FILE) . '/' . date('Ym', time()) . '/' . date('d', time());
        }

        Libs_Tools::_mkdirs($log_dir, Libs_Conf::get('LOG_DIR', ENV_FILE));
        $log_path = Libs_Conf::get('LOG_DIR', ENV_FILE) . $log_dir;
        $log_file = $log_path . "/" . $name . ".log";

        if (is_array($msg) || is_object($msg)) {
            $msg = json_encode($msg);
        }
        $msg = '[' . date("Y-m-d H:i:s", time()) . '] ' . $msg . "\n";
        return file_put_contents($log_file, $msg, FILE_APPEND);
    }

    function writeIniFile($path, $assoc_array)
    {
        $content = '';
        foreach ($assoc_array as $key => $item) {
            if (is_array($item)) {
                $content .= "\n[{$key}]\n";
                foreach ($item as $key2 => $item2) {
                    if (is_numeric($item2) || is_bool($item2))
                        $content .= "{$key2} = {$item2}\n";
                    else
                        $content .= "{$key2} = \"{$item2}\"\n";
                }
            } else {
                if (is_numeric($item) || is_bool($item))
                    $content .= "{$key} = {$item}\n";
                else
                    $content .= "{$key} = \"{$item}\"\n";
            }
        }
        if (!$handle = fopen($path, 'w')) {
            return false;
        }

        if (!fwrite($handle, $content)) {
            return false;
        }

        fclose($handle);
        return true;
    }
}