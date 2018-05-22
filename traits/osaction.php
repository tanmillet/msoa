<?php


/**
 * Trait Traits_Osuser
 */
trait Traits_Osaction {

    /**
     * @param int $flag
     * @param int $page
     * @param int $count
     * @return array
     * @throws Exception
     */
    public function GetActions(int $flag = 0, int $page = 0, int $count = 20): array
    {
        $p = ($page - 1) * $count;
        $c = $page * $count;
        $sql = Models_Osaction::MySql()->xmlsql('os', 'SQL-Action', $flag) . " LIMIT {$p} ,{$c}";
        $sql_total = Models_Osaction::MySql()->xmlsql('os', 'SQL-Action-Total', $flag);
        $res_total = Models_Osaction::MySql()->query($sql_total);
        $total = 0;
        $res = [];
        if (isset($res_total['total'])) {
            $total = $res_total['total'];
            $res = Models_Osaction::MySql()->queryAll($sql);
        }

        return [
            'total' => $total,
            'data' => $res,
        ];
    }

    /**
     * @param array $exec_tags
     * @param array $attributes
     * @return bool
     * @throws Exs_Args
     */
    public function UpActions(array $exec_tags, array $attributes = []): bool
    {
        if (isset($attributes[0]) || isset($exec_tags[0])) throw  new Exs_Args('角色ID，更新权限，操作类型');

        return true;
    }
}