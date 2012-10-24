<?php

class Mjoelnir_Request
{
    protected static $_instace = null;

    /**
     * Optional parameters given in the url, GET or POST array.
     * @var array
     */
    protected static $_parameters  = array();

    /**
     * Hase the parameter fetching already been executed or not.
     * @var bool
     */
    protected static $_initiated    = false;

    /**
     * Initial reading of request data.
     */
    protected static function init() {
        $tmp        = explode('?', $_SERVER['REQUEST_URI']);

        if (strlen(WEB_ROOT) > 1) {
            $tmpRequest = str_replace(WEB_ROOT . '/', '', $tmp[0]);
        }
        else {
            $tmpRequest = substr($tmp[0], 1);
        }

        $request    = explode('/', $tmpRequest);

        self::$_parameters['page']    = (isset($request[0]))  ? $request[0] : null;
        self::$_parameters['action']  = (isset($request[1]))  ? $request[1] : null;

        unset($request[0], $request[1]);

        $matches    = array();
        $request    = implode('/', $request);
        preg_match_all('/([^\/])+\/([^\/])+/i', $request, $matches);

        foreach ($matches[0] as $keyValue) {
            list($key, $value) = explode('/', $keyValue);
            self::$_parameters[$key]    = $value;
        }
        unset($tmp, $tmpRequest, $matches, $request);

        self::$_parameters  = array_merge(self::$_parameters, $_GET, $_POST, $_FILES);

        self::$_initiated   = true;
    }


    /**
     * Returns a singleton instance of Request.
     * @return  Request
     */
    public static function getInstance() {
        if (!self::$_instace instanceof \Mjoelnir_Request) {
            self::$_instace = new \Mjoelnir_Request();
        }

        return self::$_instace;
    }

    /**
     * Returns the value for a parameter if exists or the default value.
     * @param   string  $name       The name of the parameter to return.
     * @param   mixed   $default    The default value to return if the parameter does not exist.
     * @return  string
     */
    public static function getParameter($name, $default = null) {
        if (self::$_initiated === false) {
            self::init();
        }

        if (isset(self::$_parameters[$name])) {
            return self::$_parameters[$name];
        }
        else {
            return $default;
        }
    }

    /**
     * Returns all parameters sent with the request.
     * @return array
     */
    public static function getAllParameters() {
        return self::$_parameters;
    }
}
