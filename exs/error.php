<?php
/**
 * Class Exs_Error
 */
class Exs_Error extends Exs_Base {

    /**
     * Exs_Args constructor.
     * @param string $messge
     * @param int $code
     */
    public function __construct($messge = '', $code = 0)
    {
        parent::__construct($this->transformExceptionMsg($messge), $code);
    }

    /**
     * @param string $messge
     * @return string
     */
    public function transformExceptionMsg($messge = '')
    {
        if (!isset($messge)) return $messge;
        return sprintf(Libs_Conf::get('400', 'exs')['418'] , $messge);
    }
}