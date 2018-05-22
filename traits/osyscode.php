<?php

/**
 * Trait Traits_Osuser
 */
trait Traits_Osyscode {

    /**
     * @return array
     * @throws Exception
     */
    public function GetSysInfos(): array
    {
        $sql = Models_Osyscode::MySql()->xmlsql('os', 'SQL-SYSCODE');
        $res = Models_Osyscode::MySql()->queryAll($sql);
        if (empty($res)) return [];
        return $res;
    }
}