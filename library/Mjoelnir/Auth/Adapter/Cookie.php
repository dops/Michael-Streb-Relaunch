<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Cookie
 *
 * @author td-office
 */
class Mjoelnir_Auth_Adapter_Cookie implements Mjoelnir_Auth_Adapter_Interface
{
    /**
     * The parameter to validate for authentification.
     * @var str
     */
    static $_authParam   = 'loginHash';

    /**
     * Sets auth information for the user.
     * @param   string  $value  The value to set for authentification.
     * @return  bool
     */
    public function authenticate($value) {
        $matches    = array();
        preg_match('/(\.[a-z-]+\.[a-z]+)$/i', $_SERVER['HTTP_HOST'], $matches);
        $res = setcookie(self::$_authParam, $value, (time() + AUTH_EXPIRE), '/', $matches[1]);
        return $res;
    }

    /**
     * Checks if a user is already authed.
     * @return bool
     */
    public function isAuthed() {
        if (isset($_COOKIE[self::$_authParam])) {
            return true;
        }
        return false;
    }

    /**
     * Return the value set during the authentification.
     * @return string
     */
    public function getAuthValue() {
        if ($this->isAuthed()) {
            return $_COOKIE[self::$_authParam];
        }

        return null;
    }

    /**
     * Cancels teh authentification.
     * @return bool
     */
    public function cancel() {
        $matches    = array();
        preg_match('/(\.[a-z]+\.[a-z]+)$/i', $_SERVER['HTTP_HOST'], $matches);
        return setcookie(self::$_authParam, null, time() - 1, '/', $matches[1]);
    }
}