<?php


/**
 * Trait Traits_Osuser
 */
trait Traits_Osuser {

    /**
     * @param int $flag
     * @param int $page
     * @param int $count
     * @return array
     * @throws Exception
     */
    public function GetUsers(int $flag = 0, int $page = 0, int $count = 20): array
    {
        $p = ($page - 1) * $count;
        $c = $page * $count;
        $sql = Models_Osuser::MySql()->xmlsql('os', 'SQL-User', $flag) . " LIMIT {$p} ,{$c}";
        $sql_total = Models_Osuser::MySql()->xmlsql('os', 'SQL-User-Total', $flag);
        $res_total = Models_Osuser::MySql()->query($sql_total);
        $total = 0;
        $res = [];
        if (isset($res_total['total'])) {
            $total = $res_total['total'];
            $res = Models_Osuser::MySql()->queryAll($sql);
        }

        return [
            'total' => $total,
            'data' => $res,
        ];
    }

    /**
     * @param int $user_id
     * @param int $flag
     * @return array
     * @throws Exception
     */
    public function GetUser(int $user_id, int $flag = 0): array
    {
        $sql = Models_Osuser::MySql()->xmlsql('os', 'SQL-User', $flag) . " AND user_id = {$user_id}";
        $res = Models_Osuser::MySql()->query($sql);

        if (empty($res)) return [];
        return $res;
    }

    /**
     * @param int $exec_tags
     * @param string $attributes
     * @param $op_type
     * @return bool
     * @throws Exs_Args
     * @throws Exs_Empty
     * @throws Exs_Error
     */
    public function UpUserRoles(int $exec_tags, string $attributes, $op_type): bool
    {
        Sys_Logs::x()->writeLog('开始更新-- 用户ID ：' . $exec_tags . ' 执行操作 ： ' . $op_type . ' 操作更新元素：' . $attributes);
        if (empty($exec_tags) || empty($attributes) || empty($op_type)) throw  new Exs_Args('角色ID，更新权限，操作类型');
        try {
            $user = $this->GetUser($exec_tags);
        } catch (Exception $exception) {
            throw new Exs_Error('用户信息');
        }
        if (empty($user)) throw new Exs_Empty('用户信息');
        switch ($op_type) {
            case "insert":
                $role_ids = (isset($user['role_ids'])) ? $user['role_ids'] . ',' . $attributes : $attributes;
                $role_ids = explode(',', $role_ids);
                if (!is_array($role_ids) && !isset($role_ids[0])) {
                    throw  new Exs_Args('更新权限为空');
                }
                $role_ids = array_filter(array_flip(array_flip($role_ids)));
                sort($role_ids);
                array_map(function ($val) {
                    if (!is_numeric($val)) throw  new Exs_Args('权限不为数字');
                }, $role_ids);
                break;
            case "delete":
                $before_role_ids = explode(",", $user['role_ids']);
                $del_role_ids = explode(",", $attributes);
                $role_ids = array_diff($before_role_ids, $del_role_ids);
                break;
            default:
                throw  new Exs_Args(Libs_Conf::get('400', 'exs')['412']);
                break;
        }
        Sys_Logs::x()->writeLog('结束更新-- 用户ID ：' . $exec_tags . ' 操作更新元素结果：' . implode(',', $role_ids));

        return Models_Osuser::MySql()->update('users', ['role_ids' => implode(',', $role_ids)], 'user_id=:user_id', ['user_id' => intval($user['user_id'])]);
    }

    /**
     * @param string $user_ids
     * @param int $flag
     * @return int
     * @throws Exs_Args
     */
    public function UpUserFlag(string $user_ids, int $flag): int
    {
        if (empty($user_ids)) throw  new Exs_Args(Libs_Conf::get('400', 'exs')['411']);

        $res = Models_Osuser::MySql()->exec("UPDATE users SET flag = {$flag} where user_id in (" . $user_ids . ")");

        return $res;
    }
}