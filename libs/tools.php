<?php

class Libs_Tools {

    /**
     * @param $value
     * @return int|null|string|string[]
     */
    public static function bigIntval($value)
    {
        $value = trim($value);
        if (ctype_digit($value)) {
            return $value;
        }
        $value = preg_replace("/[^0-9](.*)$/", '', $value);
        if (ctype_digit($value)) {
            return $value;
        }
        return 0;
    }

    /**
     * @param $msg
     * @return string
     */
    public static function tcharset($msg)
    {
        return (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? iconv('UTF-8', 'gbk', $msg) : $msg;
    }

    /**
     * 将当前字符串从 BeginString 向右截取
     *
     * @param string $BeginString
     * @param boolean $self
     * @return String
     */
    public static function rightString($String, $BeginString, $self = false)
    {
        $Start = strpos($String, $BeginString);
        if ($Start === false)
            return null;
        if (!$self)
            $Start += strlen($BeginString);
        $newString = substr($String, $Start);
        return $newString;
    }

    /**
     * 将当前字符串从 BeginString 向左截取
     *
     * @param string $BeginString
     * @param boolean $self
     * @return String
     */
    public static function leftString($BeginString, $String, $self = false)
    {
        $Start = strpos($String, $BeginString);
        if ($Start === false)
            return null;
        if ($self)
            $Start += strlen($BeginString);
        $newString = substr($String, 0, $Start);
        return $newString;
    }

// 去除首尾全角及半角空格,多个空格合并为一个
    public static function _trim($str)
    {
        $str = preg_replace('/( |　|\r\n|\r|\n)+/', ' ', $str);
        return trim(preg_replace("/^　+|　+$/ ", " ", $str));
    }

// 帖子的空格特殊处理
    public static function trimSpecialSpace($content)
    {
        $content = trim($content, " ");
        return trim($content);
    }

    public static function subString($String, $BeginString, $EndString = null)
    {
        $Start = strpos($String, $BeginString);
        if ($Start === false)
            return null;
        $Start += strlen($BeginString);
        $String = substr($String, $Start);
        if (!$EndString)
            return $String;
        $End = strpos($String, $EndString);
        if ($End == false)
            return null;
        return substr($String, 0, $End);
    }

    public static function _mkdir($dir)
    {
        if (file_exists($dir))
            return true;
        $u = umask(0);
        $r = @mkdir($dir, 0777);
        umask($u);
        return $r;
    }

    public static function _mkdirs($dir, $rootpath = '')
    {
        if ($rootpath == '.') {
            $rootpath = realpath($rootpath);
        }
        $forlder = explode('/', $dir);
        $path = '';
        for ($i = 0; $i < count($forlder); $i++) {
            if ($current_dir = trim($forlder[$i])) {
                if ($current_dir == '.')
                    continue;
                $path .= '/' . $current_dir;
                if ($current_dir == '..') {
                    continue;
                }
                if (file_exists($rootpath . $path)) {
                    @chmod($rootpath . $path, 0777);
                } else {
                    if (!static::_mkdir($rootpath . $path)) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    public static function isEmail($email)
    {
        return preg_match('/^\w[_\-\.\w]+@\w+\.([_-\w]+\.)*\w{2,4}$/', $email);
    }

    public static function isMobile($phone)
    {
        return preg_match("/^1\d{10}$/", $phone);
    }

    public static function isDateValid($str)
    {
        if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $str))
            return FALSE;
        $stamp = strtotime($str);
        if (!is_numeric($stamp))
            return FALSE;
        if (checkdate(date('m', $stamp), date('d', $stamp), date('Y', $stamp))) {
            return TRUE;
        }
        return FALSE;
    }

    public static function isIntval($mixed)
    {
        return (preg_match('/^\d+$/', $mixed) == 1);
    }

    public static function getIP()
    {
        if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $onlineip = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $onlineip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $onlineip = getenv('REMOTE_ADDR');
        } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $onlineip = $_SERVER['REMOTE_ADDR'];
        }

        preg_match("/[\d\.]{7,15}/", $onlineip, $onlineipmatches);
        $onlineip = $onlineipmatches[0] ? $onlineipmatches[0] : null;
        unset($onlineipmatches);
        return $onlineip;
    }


    /**
     * 语言文件处理
     *
     * @param string $language
     * @param array $pars
     * @return string
     */
    public static function L($language = '', $params = [])
    {
        static $LANG = [];
        if (!$LANG) {
            if (!file_exists(ROOT_PATH . '/language/message.php')) {
                return $language;
            }
            require ROOT_PATH . '/language/message.php';
        }
        if (!array_key_exists($language, $LANG)) {
            return $language;
        } else {
            $language = $LANG[$language];
            if ($params) {
                $language = vsprintf($language, $params);
            }
            return $language;
        }
    }

    public static function loadNotification($language = '', $params = [])
    {
        static $NOTIFICATION = [];
        if (!$NOTIFICATION) {
            if (!file_exists(ROOT_PATH . '/language/notification.php')) {
                return $language;
            }
            require ROOT_PATH . '/language/notification.php';
        }
        if (!array_key_exists($language, $NOTIFICATION)) {
            return $language;
        } else {
            $language = $NOTIFICATION[$language];
            if ($params) {
                $language = str_replace(array_keys($params), array_values($params), $language);
            }
            return $language;
        }
    }

    public static function checkCharLength($string, $min_length, $max_length = '', $encode = 'UTF-8')
    {
        if (!$encode) {
            return false;
        }
        $length = (strlen($string) + mb_strlen($string, $encode)) / 2;
        if (!isIntval($max_length)) {
            if ($length >= $min_length) {
                return true;
            }
            return false;
        }
        if ($length >= $min_length && $length <= $max_length) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 不区分中英文，任何字符都算1个长度
     */
    public static function checkNotMixedCharLength($string, $min_length, $max_length = '', $encode = 'UTF-8')
    {
        if (!$encode) {
            return false;
        }
        $length = mb_strlen($string, $encode);
        if (!isIntval($max_length)) {
            if ($length >= $min_length) {
                return true;
            }
            return false;
        }
        if ($length >= $min_length && $length <= $max_length) {
            return true;
        } else {
            return false;
        }
    }

    public static function checkIntval($ids_array)
    {
        if (!is_array($ids_array) || !$ids_array) {
            return false;
        }
        foreach ($ids_array as $id) {
            if (!isIntval($id)) {
                return false;
            }
        }
        return true;
    }

    public static function getDateByUnixTime($time = '')
    {
        if ($time) {
            return date("Y-m-d H:i:s", $time);
        }
        return date("Y-m-d H:i:s");
    }

    public static function cutstr($string, $length = 20, $dot = '...', $htmlencode = true, $charset = 'utf-8')
    {
        if (strlen($string) <= $length) {
            if ($htmlencode) {
                return htmlspecialchars($string);
            } else {
                return $string;
            }
        }
        $strcut = '';
        if (strtolower($charset) == 'utf-8') {
            $n = $tn = $noc = 0;
            while ($n < strlen($string)) {
                $t = ord($string[$n]);
                if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                    $tn = 1;
                    $n++;
                    $noc++;
                } elseif (194 <= $t && $t <= 223) {
                    $tn = 2;
                    $n += 2;
                    $noc += 2;
                } elseif (224 <= $t && $t < 239) {
                    $tn = 3;
                    $n += 3;
                    $noc += 2;
                } elseif (240 <= $t && $t <= 247) {
                    $tn = 4;
                    $n += 4;
                    $noc += 2;
                } elseif (248 <= $t && $t <= 251) {
                    $tn = 5;
                    $n += 5;
                    $noc += 2;
                } elseif ($t == 252 || $t == 253) {
                    $tn = 6;
                    $n += 6;
                    $noc += 2;
                } else {
                    $n++;
                }
                if ($noc >= $length) {
                    break;
                }
            }
            if ($noc > $length) {
                $n -= $tn;
            }
            $strcut = substr($string, 0, $n);
        } else {
            for ($i = 0; $i < $length; $i++) {
                $strcut .= ord($string[$i]) > 127 ? $string[$i] . $string[++$i] : $string[$i];
            }
        }
        $original_strlen = strlen($string);
        $new_strlen = strlen($strcut);
        if ($htmlencode) {
            $strcut = htmlspecialchars($strcut);
        }
        return $strcut . ($original_strlen > $new_strlen ? $dot : '');
    }

    public static function convertEach(&$array)
    {
        $res = [];
        $key = key($array);
        if ($key !== null) {
            next($array);
            $res[1] = $res['value'] = $array[$key];
            $res[0] = $res['key'] = $key;
        } else {
            $res = false;
        }
        return $res;
    }

    public static function getImageRelativePath($image_url)
    {
        $image_url = parse_url($image_url);
        return isset($image_url['path']) ? $image_url['path'] : '';
    }

    public static function getImageAbsolutePath($image_url)
    {
        return IMAGE_DOMAIN . $image_url;
    }

    public static function getFbShortTime($fb_time)
    {
        switch ($fb_time) {
            case '00:00:00 - 00:59:59':
                $short_time = '0-1';
                break;
            case '01:00:00 - 01:59:59':
                $short_time = '1-2';
                break;
            case '02:00:00 - 02:59:59':
                $short_time = '2-3';
                break;
            case '03:00:00 - 03:59:59':
                $short_time = '3-4';
                break;
            case '04:00:00 - 04:59:59':
                $short_time = '4-5';
                break;
            case '05:00:00 - 05:59:59':
                $short_time = '5-6';
                break;
            case '06:00:00 - 06:59:59':
                $short_time = '6-7';
                break;
            case '07:00:00 - 07:59:59':
                $short_time = '7-8';
                break;
            case '08:00:00 - 08:59:59':
                $short_time = '8-9';
                break;
            case '09:00:00 - 09:59:59':
                $short_time = '9-10';
                break;
            case '10:00:00 - 10:59:59':
                $short_time = '10-11';
                break;
            case '11:00:00 - 11:59:59':
                $short_time = '11-12';
                break;
            case '12:00:00 - 12:59:59':
                $short_time = '12-13';
                break;
            case '13:00:00 - 13:59:59':
                $short_time = '13-14';
                break;
            case '14:00:00 - 14:59:59':
                $short_time = '14-15';
                break;
            case '15:00:00 - 15:59:59':
                $short_time = '15-16';
                break;
            case '16:00:00 - 16:59:59':
                $short_time = '16-17';
                break;
            case '17:00:00 - 17:59:59':
                $short_time = '17-18';
                break;
            case '18:00:00 - 18:59:59':
                $short_time = '18-19';
                break;
            case '19:00:00 - 19:59:59':
                $short_time = '19-20';
                break;
            case '20:00:00 - 20:59:59':
                $short_time = '20-21';
                break;
            case '21:00:00 - 21:59:59':
                $short_time = '21-22';
                break;
            case '22:00:00 - 22:59:59':
                $short_time = '22-23';
                break;
            case '23:00:00 - 23:59:59':
                $short_time = '23-24';
                break;
            default:
                $short_time = null;
        }
        return $short_time;
    }

    public static function templatePath($file_name)
    {
        return ROOT_PATH . '/views/template/' . $file_name;
    }

    public static function secondToTime($time)
    {
        if (is_numeric($time)) {
            $value = [
                "years" => 0,
                "days" => 0,
                "hours" => 0,
                "minutes" => 0,
                "seconds" => 0
            ];
            if ($time >= 31556926) {
                $value["years"] = floor($time / 31556926);
                $time = ($time % 31556926);
            }
            if ($time >= 86400) {
                $value["days"] = floor($time / 86400);
                $time = ($time % 86400);
            }
            if ($time >= 3600) {
                $value["hours"] = floor($time / 3600);
                $time = ($time % 3600);
            }
            if ($time >= 60) {
                $value["minutes"] = floor($time / 60);
                $time = ($time % 60);
            }
            $value["seconds"] = floor($time);
            return (array)$value;
        } else {
            return (bool)FALSE;
        }
    }

    public static function getContrastField()
    {
        return [
            'spend',
            'effective_count',
            'reach_count',
            'action_value',
            'click_count',
            'link_click_count',
            'buy_count',
            'impression_count'
        ];
    }

    public static function getFieldRate($field_one, $field_two)
    {
        if ($field_two == 0) {
            return '0%';
        }
        return round(($field_one / $field_two) * 100, 2) . '%';
    }

    public static function &loadClass($class, $data = null, $instantiate = true, $init_class = false)
    {
        static $objects = [];
        if ($data) {
            $class_key = md5($class . '_' . md5(serialize($data)));
        } else {
            $class_key = $class;
        }

        // Does the class exist? If so, we're done...
        if (isset($objects[$class_key]) && !$init_class) {
            return $objects[$class_key];
        }
        require_once ROOT_PATH . '/libraries/' . $class . '.php';

        if ($instantiate == FALSE) {
            $objects[$class_key] = TRUE;
            return $objects[$class_key];
        }

        $name = 'Bgn' . $class;
        if ($data) {
            $new_class = new $name($data);
            $objects[$class_key] = &instantiateClass($new_class);
        } else {
            $new_class = new $name();
            $objects[$class_key] = &instantiateClass($new_class);
        }

        return $objects[$class_key];
    }

    public static function &loadModel($class, $instantiate = TRUE)
    {
        static $models = [];

        // Does the class exist? If so, we're done...
        if (isset($models[$class])) {
            return $models[$class];
        }
        require_once ROOT_PATH . '/models/' . $class . '.php';

        if ($instantiate == FALSE) {
            $models[$class] = TRUE;
            return $models[$class];
        }

        $split_class = explode('/', $class);
        $instance_class = '';
        foreach ($split_class as $list) {
            $instance_class .= ucfirst($list);
        }

        $name = $instance_class . 'Model';
        $new_class = new $name();

        $models[$class] = &instantiateClass($new_class);
        return $models[$class];
    }

    public static function &instantiateClass(&$class_object)
    {
        return $class_object;
    }
}