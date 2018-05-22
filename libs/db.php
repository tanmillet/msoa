<?php

class Libs_Db {
    public $configs = [];
    public $dbhs = [];
    public $db_config = [];

    public function __construct($db_config = null)
    {
        $this->db_config = $db_config = Libs_Conf::get('db_servers', ENV_FILE);
    }

    /**
     * @param string $xml_path_name
     * @param string $sql_key
     * @param mixed ...$args
     * @return string
     */
    public function xmlsql(string $xml_path_name = '', string $sql_key = '', ...$args): string
    {
        $base_path = ROOT_PATH . DS . "/traits/xml/" . DS;
        $sql = '';
        if (empty($xml_path_name) || empty($sql_key)) {
            return $sql;
        }
        $xmls = simplexml_load_string(file_get_contents($base_path . $xml_path_name . ".xml"));
        $xmls = json_decode(json_encode((array)$xmls), true);
        $sql = (isset($xmls['SQLS']) && isset($xmls['SQLS'][$sql_key])) ? $xmls['SQLS'][$sql_key] : '';
        if (empty($sql)) return $sql;
        if (isset($args[0])) {
            return sprintf($sql, $args[0]);
        } elseif (isset($args[1])) {
            return sprintf($sql, $args[0], $args[1]);
        } elseif (isset($args[2])) {
            return sprintf($sql, $args[0], $args[1], $args[2]);
        }

        return $sql;
    }

    /**
     * 数据库配置
     *
     * @param string $type
     */
    public function setconfig($type = 'master')
    {
        $db_config = $this->db_config;
        if ($type == 'slave') { // 随机从一台slave服务器读取数据
            $rand_number = array_rand($db_config[$type]);
            $db_config[$type] = $db_config[$type][$rand_number];
        }
        $configs[$type]['dsn'] = "mysql:host={$db_config[$type]['host']}; port={$db_config[$type]['port']}; dbname={$db_config[$type]['dbname']}; charset=utf8;";
        $configs[$type]['username'] = $db_config[$type]['username'];
        $configs[$type]['password'] = $db_config[$type]['password'];
        $this->configs = $configs;
    }

    /**
     * 获取一个PDO对象
     *
     * @param string $type
     *            类型 [master|slave] 主从
     * @return PDO
     */
    public function getPdo($type = 'master')
    {
        $this->setconfig($type);
        $key = $type;
        if (!isset($this->dbhs[$key][$this->configs[$type]['dsn']])) {
            $dbh = new PDO($this->configs[$type]['dsn'], $this->configs[$type]['username'], $this->configs[$type]['password'], []);
            $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->dbhs[$key][$this->configs[$type]['dsn']] = $dbh;
        }

        return $this->dbhs[$key][$this->configs[$type]['dsn']];
    }

    public function close()
    {
        if (!$this->dbhs) {
            return false;
        }
        foreach ($this->dbhs as $key => $dbh) {
            unset($this->dbhs[$key]);
        }
        return true;
    }

    /**
     * @param $sql
     * @param null $params
     * @param string $dbtype
     * @return PDOStatement
     * @throws Exception
     */
    public function execQuery($sql, $params = null, $dbtype = 'slave')
    {
        $pdo = $this->getPdo($dbtype);

        $this->paramParse($sql, $params);
        $sth = $this->preExec($pdo, $sql, $params, $dbtype);
        return $sth;
    }

    /**
     * @param $tableName
     * @param $data
     * @param bool $get_last_insert_id
     * @return bool|string
     * @throws Exception
     */
    public function insert($tableName, $data, $get_last_insert_id = false)
    {
        $sql = "insert into `{$tableName}`(" . join(",", array_keys($data)) . ") values(" . rtrim(str_repeat("?,", count($data)), ",") . ")";
        $params = array_values($data);
        $pdo = $this->getPdo('master');
        $sth = $this->preExec($pdo, $sql, $params, $dbtype = 'master');
        if (is_array($sth) && $sth['error_code']) {
            return false;
        }
        if ($get_last_insert_id) {
            $pdo = $this->getPdo('master');
            return $pdo->lastInsertId();
        } else {
            return true;
        }
    }

    /**
     * @param $tableName
     * @param $data_array
     * @return bool
     */
    public function insertMulti($tableName, $data_array)
    {
        $data = current($data_array);
        $current_parameters = null;
        $sql = "insert into `{$tableName}`(" . join(",", array_keys($data)) . ") values(" . rtrim(str_repeat("?,", count($data)), ",") . ")";
        $pdo = $this->getPdo('master');
        try {
            $sth = $pdo->prepare($sql);
            reset($data_array);
            foreach ($data_array as $params) {
                $current_parameters = array_values($params);
                $sth->execute($current_parameters);
            }
        } catch (Exception $e) {
            $error_log = $e->getMessage() . '||sql:' . $sql . '||data:' . json_encode($current_parameters);
            Sys_Logs::x()->writeLog($error_log, 'sql_error');
            return false;
        }
        return true;
    }

    /**
     * @param $tableName
     * @param $data
     * @param $where
     * @param null $whereParam
     * @return bool|int
     * @throws Exs_Args
     */
    public function update($tableName, $data, $where, $whereParam = null)
    {
        if (is_string($data))
            $data = [
                $data
            ];
        $sql = "UPDATE `{$tableName}` SET ";
        $tmp = [];
        $param = [];

        foreach ($data as $k => $v) {
            if (is_int($k)) { // 如 hitNum=hitNum+1，可以是直接的函数
                $tmp[] = $v;
            } else { // 其他情况全部使用占位符 'title'=>'this is title'
                $tmp[] = "`{$k}`=:k_{$k}";
                $param[":k_" . $k] = $v;
            }
        }
        $where = $this->filters($where);
        $this->paramParse($where, $whereParam);
        $param = array_merge($param, $whereParam);
        $sql .= join(",", $tmp) . " {$where}";
        $result = $this->exec($sql, $param);
        if (is_array($result) && $result['error_code']) {
            return false;
        }
        if (!$result) {
            $result = true;
        }
        return $result;
    }

    /**
     * @param $tableName
     * @param $where
     * @param null $whereParam
     * @param int $limit
     * @return int
     * @throws Exs_Args
     */
    public function delete($tableName, $where, $whereParam = null, $limit = 0)
    {
        $this->paramParse($where, $whereParam);
        $param = $whereParam;
        $sql = "delete from `{$tableName}` where {$where}";
        if ($limit > 0) {
            $limit = ' LIMIT ' . $limit;
            $sql = $sql . $limit;
        }
        return $this->exec($sql, $param);
    }

    /**
     * @param $sql
     * @param null $params
     * @param string $dbtype
     * @return array|mixed|null
     * @throws Exception
     */
    public function query($sql, $params = null, $dbtype = 'slave')
    {
        $fetch_all = false;
        $sql = $sql . ' LIMIT 1';
        return $this->select($sql, $params, $fetch_all, $dbtype);
    }

    /**
     * @param $sql
     * @param null $params
     * @param string $dbtype
     * @return array|mixed|null
     * @throws Exception
     */
    public function queryAll($sql, $params = null, $dbtype = 'slave')
    {
        $fetch_all = true;
        return $this->select($sql, $params, $fetch_all, $dbtype);
    }

    /**
     * @param $sql
     * @param null $params
     * @param bool $fetchAll
     * @param string $dbtype
     * @return array|mixed|null
     * @throws Exception
     */
    protected function select($sql, $params = null, $fetchAll = true, $dbtype = 'slave')
    {
        $sth = $this->execQuery($sql, $params, $dbtype);
        if (is_array($sth) && isset($sth['error_code'])) {
            return null;
        }
        return $fetchAll ? $sth->fetchAll() : $sth->fetch();
    }

    /**
     * @param $sql
     * @param null $params
     * @param string $dbtype
     * @return int
     * @throws Exs_Args
     * @throws Exception
     */
    public function exec($sql, $params = null, $dbtype = 'master')
    {
        $pdo = $this->getPdo($dbtype);
        $this->paramParse($sql, $params);
        $sth = $this->preExec($pdo, $sql, $params, $dbtype);
        if (is_array($sth) && $sth['error_code']) {
            throw new  Exs_Args($sth['error']);
        }
        return $sth->rowCount();
    }

    /**
     *
     * @param PDO $pdo
     * @param string $sql
     * @param array $params
     * @throws Exception
     * @return PDOStatement
     */
    private function preExec($pdo, $sql, $params, $dbtype = 'master')
    {
        try {
            $sth = $pdo->prepare($sql);
            $sth->execute($params);
        } catch (Exception $e) {
            $error_info = $e->getMessage();
            $data['error_code'] = $e->errorInfo[1];
            $data['error'] = $error_info;
            if ($data['error_code'] == 2006) { // 捕获wait_timeout异常
                $this->close();
                $pdo = $this->getPdo('master');
                Sys_Logs::x()->writeLog($error_info, 'reconnect_pdo');
                return $this->preExec($pdo, $sql, $params, $dbtype);
            }
            $error_log = $e->__toString() . '||sql:' . $sql . '||data:' . json_encode($params);
            Sys_Logs::x()->writeLog($error_log, 'sql_error');
            return $data;
        }
        return $sth;
    }

    /**
     * 自动生成条件语句
     *
     * @param array $filters
     * @return string
     */
    public function filters($filters)
    {
        $sql_where = '';
        if (is_array($filters)) {
            foreach ($filters as $f => $v) {
                $f_type = gettype($v);
                if ($f_type == 'array') {
                    $sql_where .= ($sql_where ? " AND " : "") . "(`{$f}` " . $v['operator'] . " '" . $v['value'] . "')";
                } elseif ($f_type == 'string')
                    $sql_where .= ($sql_where ? " OR " : "") . "(`{$f}` LIKE '%{$v}%')";
                else {
                    $sql_where .= ($sql_where ? " AND " : "") . "(`{$f}` = '{$v}')";
                }
            }
        } elseif (strlen($filters)) {
            $sql_where = $filters;
        } else
            return '';
        $sql_where = $sql_where ? " WHERE " . $sql_where : '';
        return $sql_where;
    }

    /**
     * 对sql语句进行预处理，同时对参数进行同步处理 ,以实现在调用时sql和参数多种占位符格式支持
     * 如 $where="id=1" , $params=1 处理成$where="id=:id",$params['id']=1
     *
     * @param string $where
     * @param array $params
     */
    public function paramParse(&$where, &$params)
    {
        if (is_null($params)) {
            $params = [];
            return;
        }

        if (!is_array($params))
            $params = [
                $params
            ];
        $_first = $this->convertEach($params);
        $tmp = [];
        if (!is_int($_first['key'])) {
            foreach ($params as $_k => $_v) {
                $tmp[":" . ltrim($_k, ":")] = $_v;
            }
        } else {
            preg_match_all("/`?([\w_]+)`?\s*[\=<>!]+\s*\?\s+/i", $where . " ", $matches, PREG_SET_ORDER);
            if ($matches) {
                foreach ($matches as $_k => $matche) {
                    $fieldName = ":" . $matche[1]; // 字段名称
                    $i = 0;
                    while (array_key_exists($fieldName, $params)) {
                        $fieldName = ":" . $matche[1] . "_" . ($i++);
                    }
                    $where = str_replace(trim($matche[0]), str_replace("?", $fieldName, $matche[0]), $where);
                    if (array_key_exists($_k, $params)) {
                        $tmp[$fieldName] = $params[$_k];
                    }
                }
            }
        }
        $params = $tmp;

        // ------------------------------------------
        // fix sql like: select * from table_name where id in(:ids)
        preg_match_all("/\s+in\s*\(\s*(\:\w+)\s*\)/i", $where . " ", $matches, PREG_SET_ORDER);

        if ($matches) {
            foreach ($matches as $_k => $matche) {

                $fieldName = trim($matche[1], ":");

                $_val = isset($params[$matche[1]]) ? $params[$matche[1]] : '';

                if (!is_array($_val)) {
                    $_val = explode(",", addslashes($_val));
                }

                $_tmpStrArray = [];
                foreach ($_val as $_item) {
                    $_tmpStrArray[] = is_numeric($_item) ? $_item : "'" . $_item . "'";
                }
                $_val = implode(",", $_tmpStrArray);
                $where = str_replace($matche[0], " In (" . $_val . ") ", $where);

                unset($params[$matche[1]]);
            }
        }
        // ==========================================
    }

    /**
     * @param $array
     * @return array|bool
     */
    public function convertEach(&$array)
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
}
