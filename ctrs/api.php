<?php

class Ctrs_Api {
    /**
     * @var
     */
    protected $status_code;

    /**
     * @var array
     */
    protected static $error_msg = [];


    /**
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->status_code;
    }

    /**
     * @param $status_code
     * @return $this
     */
    public function setStatusCode($status_code)
    {
        $this->status_code = $status_code;
        return $this;
    }

    /**
     * @param $err_msg
     * @return string
     */
    public function responseE($err_msg = 'failure')
    {
        return $this->response([
            'status' => 'failure',
            'info' => [
                'status_code' => $this->getStatusCode(),
                'message' => $err_msg,
                'data' => '',
            ],
        ]);
    }

    /**
     * @param $output_data
     * @param $message
     * @return string
     */
    public function responseS($output_data = [], $message = 'success')
    {
        return $this->response([
            'status' => 'success',
            'info' => [
                'status_code' => $this->getStatusCode(),
                'message' => $message,
                'total' => isset($output_data['total']) ? $output_data['total'] : '',
                'data' => isset($output_data['data']) ? $output_data['data'] : '',
            ],
        ]);
    }

    /**
     * @param $output_data
     * @return string
     */
    public function response($output_data)
    {
        header("Content-type: application/json; charset=utf-8");
        header("Access-Control-Allow-Origin: *");
        return json_encode($output_data);
    }

    /**
     * Fetch an item from the GET array
     *
     * @access public
     * @param
     *            string
     * @return string
     */
    function _get($index = NULL)
    {
        // Check if a field has been provided
        if ($index === NULL and !empty($_GET)) {
            $get = [];

            // loop through the full _GET array
            foreach (array_keys($_GET) as $key) {
                $get[$key] = $this->fetchFromArray($_GET, $key);
            }
            return $get;
        }

        return $this->fetchFromArray($_GET, $index);
    }

    /**
     * Fetch from array
     *
     * This is a helper function to retrieve values from global arrays
     *
     * @access private
     * @param
     *            array
     * @param
     *            string
     * @return string
     */
    public function fetchFromArray(&$array, $index = '')
    {
        if (!isset($array[$index])) {
            return FALSE;
        }
        return $array[$index];
    }

    /**
     * Fetch an item from the POST array
     *
     * @access public
     * @param
     *            string
     * @return string
     */
    public function _post($index = NULL)
    {
        // Check if a field has been provided
        if ($index === NULL and !empty($_POST)) {
            $post = [];

            // Loop through the full _POST array and return it
            foreach (array_keys($_POST) as $key) {
                $post[$key] = $this->fetchFromArray($_POST, $key);
            }
            return $post;
        }

        return $this->fetchFromArray($_POST, $index);
    }
}
