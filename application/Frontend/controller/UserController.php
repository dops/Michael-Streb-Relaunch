<?php

namespace Frontend;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UserController
 *
 * @author td-office
 */
class UserController extends \Mjoelnir_Controller_Abstract
{
    /**
     * An array containing several different user instances.
     * @var array
     */
    protected static $_instances    = array();

    /**
     * User login.
     * @param   str $loginHash
     * @return  str
     */
    public function loginAction($loginHash = null) {
        $error  = array();
        // Has the user send login informations
        if (\Mjoelnir_Request::getParameter('login', false)) {
            if (strlen(\Mjoelnir_Request::getParameter('email', false)) > 0 && strlen(\Mjoelnir_Request::getParameter('password', false)) > 0) {
                $userId = \UserModel::getUserIdByLogin(\Mjoelnir_Request::getParameter('email'), \Mjoelnir_Request::getParameter('password'));

                if ($userId !== false) {
                    $user   = \UserModel::getInstance($userId);
                    $auth   = \Mjoelnir_Auth::getInstance();
                    $auth->authenticate($user->getLoginHash());
                    header('Location: ' . WEB_ROOT);
                    exit();
                }
                else {
                    $error['msg']   = 'Die Login-daten waren nicht korrekt.';
                }
            }
            else {
                if (strlen(\Mjoelnir_Request::getParameter('email', false)) == 0) {
                    $error['email'] =   'Bitte geben Sie eine E-Mail Adresse an.';
                }

                if (strlen(\Mjoelnir_Request::getParameter('password', false)) == 0) {
                    $error['password'] =   'Bitte geben Sie ein Passwort an.';
                }
            }
        }

        $form   = new \Mjoelnir_Form('login', null, 'post', array(), PATH_TEMPLATE . 'form/');
        $form->useSsl();
        $form->addElement('text', 'email', '', array('label' => 'E-Mail', 'error' => (isset($error['email'])) ? true : false));
        $form->addElement('password', 'password', '', array('label' => 'Passwort', 'error' => (isset($error['password'])) ? true : false));
        $form->addElement('submit', 'login', 'Einloggen');

        if (count($error) > 0) {
            $this->_view->assign('error', $error);
        }

        $this->_view->assign('form', $form->__toString());

        return $this->_view->fetch('user/login.tpl.html');
    }

    /**
     * Encrypts a given string.
     * @param   str $password   The password to encrypt.
     * @return  str
     */
    protected function cryptPassword($password) {
        return md5($password);
    }
}