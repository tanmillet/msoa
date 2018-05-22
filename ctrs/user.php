<?php

/**
 * Class Msoa_User
 * 接口开发权限管理模块
 */
class Ctrs_User extends Ctrs_Api {

    use Traits_Osuser;

    /**
     * 获取用户全部信息
     */
    public function lists()
    {
        $flag = $this->_get('flag') ? intval($this->_get('flag')) : 0;
        $page = $this->_get('page') ? intval($this->_get('page')) : 1;
        $count = $this->_get('count') ? intval($this->_get('count')) : 20;
        try {
            $out_data = $this->GetUsers($flag, $page, $count);
        } catch (Exception $exception) {
            echo $this->setStatusCode(500)->responseE($exception->getMessage());
            die();
        }

        echo $this->setStatusCode(200)->responseS($out_data);
        die();
    }

    /**
     * 根据不同的条件进行更新用户信息
     */
    public function uprole()
    {
        $user_id = $this->_get('user_id') ? intval($this->_get('user_id')) : 0;
        $role_ids = $this->_get('role_ids') ? $this->_get('role_ids') : '';
        $op_type = $this->_get('op_type') ? $this->_get('op_type') : '';
        try {
            $this->UpUserRoles($user_id, $role_ids, $op_type);
        } catch (Exs_Args $exs_Args) {
            echo $this->setStatusCode(400)->responseE($exs_Args->getMessage());
            die();
        } catch (Exception $exception) {
            echo $this->setStatusCode(500)->responseE($exception->getMessage());
            die();
        }

        echo $this->setStatusCode(200)->responseS();
        die();
    }

    /**
     * 更新OA用户为MSOA用户
     */
    public function upflag()
    {
        $user_ids = $this->_get('user_ids') ? $this->_get('user_ids') : 0;
        $flag = $this->_get('flag') ? intval($this->_get('flag')) : 0;
        try {
            $this->UpUserFlag($user_ids, $flag);
        } catch (Exs_Args $exs_Args) {
            echo $this->setStatusCode(400)->responseE($exs_Args->getMessage());
            die();
        } catch (Exception $exception) {
            echo $this->setStatusCode(500)->responseE($exception->getMessage());
            die();
        }

        echo $this->setStatusCode(200)->responseSuccess();
        die();
    }

}