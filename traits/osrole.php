<?php


/**
 * Trait Traits_Osuser
 */
trait Traits_Osrole {

    use Traits_Osyscode;

    /**
     * @param int $page
     * @param int $count
     * @return array
     * @throws Exception
     */
    public function GetRoles(int $page = 1, int $count = 20): array
    {
        $p = ($page - 1) * $count;
        $c = $page * $count;
        $sql = Models_Osrole::MySql()->xmlsql('os', 'SQL-Role') . " LIMIT {$p} ,{$c}";
        $sql_total = Models_Osrole::MySql()->xmlsql('os', 'SQL-Role-Total');
        $res_total = Models_Osrole::MySql()->query($sql_total);
        $total = 0;
        $res = [];
        if (isset($res_total['total'])) {
            $total = $res_total['total'];
            $res = Models_Osrole::MySql()->queryAll($sql);
        }
        return [
            'total' => $total,
            'data' => $res,
        ];
    }

    /**
     * @param int $role_id
     * @return array
     * @throws Exception
     */
    public function GetUserByRole(int $role_id): array
    {
        $sql = Models_Osrole::MySql()->xmlsql('os', 'SQL-User-Role', 1) . " AND role_id LIKE %{$role_id}%";
        $res = Models_Osrole::MySql()->query($sql);
        if (empty($res)) return [];

        return $res;
    }

    /**
     * @param int $role_id
     * @return array
     * @throws Exception
     */
    public function GetRoleAction(int $role_id): array
    {
        $sql = Models_Osrole::MySql()->xmlsql('os', 'SQL-ROLEACTION', $role_id);
        $res = Models_Osrole::MySql()->query($sql);
        if (empty($res)) return [];

        return $res;
    }

    public function InsertRole()
    {

    }

    public function DelRole()
    {
        //每个用户角色组进行删除
    }


    /**
     * @param string $sys_code
     * @param int $role_id
     * @param string $op_type
     * @return bool
     * @throws Exs_Args
     */
    public function UpRoles(string $sys_code, int $role_id, string $op_type)
    {
        if (empty($sys_code) || empty($role_id) || empty($op_type)) throw  new Exs_Args(Libs_Conf::get('400', 'exs')['410']);

        return true;
    }

    /**
     * @param string $sys_code
     * @param int $role_id
     * @param string $action_ids
     * @param string $op_type
     * @return bool
     * @throws Exs_Args
     * @throws Exs_Empty
     * @throws Exs_Error
     */
    public function UpRoleActions(string $sys_code, int $role_id, string $action_ids, string $op_type): bool
    {
        if (empty($sys_code) || empty($role_id) || empty($action_ids) || empty($op_type)) throw  new Exs_Args('系统CODE，角色ID，权限信息，操作类型');
        try {
            $sys_infos = $this->GetSysInfos();
        } catch (Exception $exception) {
            throw  new Exs_Args($exception->getMessage());
        }
        if (!isset($sys_infos[0])) throw  new Exs_Args('系统CODE');
        $sys_codes = [];
        array_map(function ($val) use (&$sys_codes) {
            $sys_codes[] = $val['sys_code'];
        }, $sys_infos);
        if (!is_array($sys_codes) || !isset($sys_codes[0]) || !in_array($sys_code, $sys_codes)) throw  new Exs_Args('系统CODE');
        try {
            $is_role_action = $this->GetRoleAction($role_id);
        } catch (Exception $exception) {
            throw  new Exs_Error('角色ID信息查询');
        }
        switch ($op_type) {
            case "insert":
                $action_ids = explode(',', $action_ids);
                if (!is_array($action_ids) && !isset($action_ids[0])) {
                    throw  new Exs_Args('权限信息不能为空');
                }
                $action_ids = array_filter(array_flip(array_flip($action_ids)));
                sort($action_ids);
                array_map(function ($val) {
                    if (!is_numeric($val)) throw  new Exs_Args('权限信息不为数字');
                }, $action_ids);
                if (!empty($is_role_action)) throw  new Exs_Args('权限信息不能为空');
                try {
                    Models_Osrole::MySql()->insert("role_actions", [
                        'action_ids' => implode(",", $action_ids),
                        'role_id' => $role_id,
                        'created_at' => date("Y-m-d H:i:s"),
                    ]);
                } catch (Exception $exception) {
                    throw  new Exs_Args($exception->getMessage());
                }
                break;
            case "updatedf":
                break;
            case "update":
                //更新是否存在以及赋予用户的角色 如果是则进行提示不可以进行修改 TODO
//                $user_ower_actions = $this->GetUserByRole($role_id);
//                if (!empty($user_ower_actions)) throw  new Exs_Args(Libs_Conf::get('400', 'exs')['415']);

                if (empty($is_role_action)) throw  new Exs_Empty('角色权限信息不存在');
                $action_ids = (isset($is_role_action['action_ids'])) ? $is_role_action['action_ids'] . "," . $action_ids : $action_ids;
                $action_ids = explode(',', $action_ids);
                if (!is_array($action_ids) && !isset($action_ids[0])) {
                    throw  new Exs_Args('权限信息不能为空');
                }
                $action_ids = array_filter(array_flip(array_flip($action_ids)));
                sort($action_ids);
                array_map(function ($val) {
                    if (!is_numeric($val)) throw  new Exs_Args('权限信息不为数字');
                }, $action_ids);
                Models_Osrole::MySql()->update('role_actions', ['action_ids' => implode(",", $action_ids)], 'role_id=:role_id', ['role_id' => intval($role_id)]);
                break;
            case "deletef":
            case "delete":
                //更新是否存在以及赋予用户的角色 如果是则进行提示不可以进行修改 TODO
//                $user_ower_actions = $this->GetUserByRole($role_id);
//                if (!empty($user_ower_actions)) throw  new Exs_Args(Libs_Conf::get('400', 'exs')['415']);
                $before_actions_ids = (isset($is_role_action['action_ids'])) ? explode(",", $is_role_action['action_ids']) : [];
                $del_role_ids = explode(",", $action_ids);
                $action_ids = array_diff($before_actions_ids, $del_role_ids);
                Models_Osrole::MySql()->update('role_actions', ['action_ids' => implode(",", $action_ids)], 'role_id=:role_id', ['role_id' => intval($role_id)]);
                break;
            default:
                throw  new Exs_Args('操作类型');
                break;
        }
        return true;
    }
}