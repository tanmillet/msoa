<?php

/**
 * Class Exs_Base
 */
class Exs_Base extends Exception {

    /**
     * Exs_Base constructor.
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
        return $messge;
    }
}