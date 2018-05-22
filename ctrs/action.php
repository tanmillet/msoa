<?php

/**
 * Class Msoa_User
 * 接口开发权限管理模块
 */
class Ctrs_Action extends Ctrs_Api {

    use Traits_Osaction;

    /**
     * 获取用户全部信息
     */
    public function lists()
    {
        $page = $this->_get('page') ? intval($this->_get('page')) : 1;
        $count = $this->_get('count') ? intval($this->_get('count')) : 20;
        try {
            $out_data = $this->GetActions($page, $count);
        } catch (Exception $exception) {
            echo $this->setStatusCode(500)->responseE($exception->getMessage());
            die();
        }

        echo $this->setStatusCode(200)->responseS($out_data);
        die();
    }

    /**
     * 根据不同的条件进行更新权限信息
     */
    public function up()
    {
        $flag = $this->_get('flag') ? intval($this->_get('flag')) : 0;
        $exec_tags = [
            'id' => 123,
        ];
        $attributes = [
            'flag' => $flag,
        ];
        try {
            $this->UpActions($exec_tags, $attributes);
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