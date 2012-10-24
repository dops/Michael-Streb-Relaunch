<?php

namespace Backend;

/**
 * UserController
 *
 * @author Michael Streb <michael.streb@topdeals.de>
 */
class UserController extends \Mjoelnir_Controller_Abstract {

    /**
     * Index action.
     * @return string
     */
    public function indexAction() {
        $site = \Mjoelnir_Site::getInstance();
        $site->addBreadcrumb(array('title' => 'Userverwaltung', 'link' => WEB_ROOT . 'user'));

        $this->_view->assign('WEB_ROOT', WEB_ROOT);
        return $this->_view->fetch('user/index.tpl.html');
    }

    /**
     * Lists all users.
     * @return string
     */
    public function listAction() {
        $site = \Mjoelnir_Site::getInstance();
        $site->addBreadcrumb(array('title' => 'Userverwaltung', 'link' => WEB_ROOT . 'user'));
        $site->addBreadcrumb(array('title' => 'User verwalten', 'link' => WEB_ROOT . 'user/list'));

        $this->_view->assign('WEB_ROOT', WEB_ROOT);
        $this->_view->assign('users', \UserModel::getAll());

        return $this->_view->fetch('user/list.tpl.html');
    }

    /**
     * E$dits a user.
     * @return  str
     */
    public function editAction() {
        $oSite = \Mjoelnir_Site::getInstance();
        $oSite->addBreadcrumb(array('title' => 'Userverwaltung', 'link' => WEB_ROOT . 'user'));
        $oSite->addBreadcrumb(array('title' => 'User verwalten', 'link' => WEB_ROOT . 'user/list'));
        if (\Mjoelnir_Request::getParameter('id', false)) {
            $oSite->addBreadcrumb(array('title' => 'User bearbeiten', 'link' => WEB_ROOT . 'user/edit/id/' . \Mjoelnir_Request::getParameter('id')));
        } else {
            $oSite->addBreadcrumb(array('title' => 'User anlegen', 'link' => WEB_ROOT . 'user/edit'));
        }

        $oUser = \UserModel::getInstance(\Mjoelnir_Request::getParameter('id', null));
        $aUserUserroles = \UserUserroleModel::getAll(null, null, array('user_id' => array('eq' => $oUser->getId())));
        $aUserroleIds = array();
        foreach ($aUserUserroles['rows'] as $iUserUserroleId => $oUserUserrole) {
            $aUserroleIds[] = $oUserUserrole->getUserroleId();
        }

        /**
         * Fetch all existing userroles for later validating and form building.
         */
        $aUserroles = \UserroleModel::getAll();
        $aUserroleList = array();
        foreach ($aUserroles['rows'] as $role) {
            $aUserroleList[$role->getId()] = $role->getName();
        }

        $aError = array();

        if (\Mjoelnir_Request::getParameter('save', false) || \Mjoelnir_Request::getParameter('save_return', false)) {
            $aSelectedUserRoleIds = \Mjoelnir_Request::getParameter('userroleIds', array());

            if (\Mjoelnir_Request::getParameter('firstName', false))    { $oUser->setFirstName(\Mjoelnir_Request::getParameter('firstName')); }
            else                                                        { $aError['firstName'] = 'Bitte geben Sie einen korrekten Vornamen an.'; }

            if (\Mjoelnir_Request::getParameter('lastName', false))     { $oUser->setLastName(\Mjoelnir_Request::getParameter('lastName')); }
            else                                                        { $aError['lastName'] = 'Bitte geben Sie einen korrekten Nachnamen an.'; }

            if (\Mjoelnir_Request::getParameter('email', false))        { $oUser->setEmail(\Mjoelnir_Request::getParameter('email')); }
            else                                                        { $aError['email'] = 'Bitte geben Sie eine korrekte E-Mail Adresse an.'; }

            if (\Mjoelnir_Request::getParameter('userroleIds', false))  { $oUser->setUserroleIds(array_keys($aSelectedUserRoleIds)); }
            else                                                        { $aError['userroleIds'] = 'Bitte geben Sie mindestens eine Benutzer-Rolle an.'; }

            if (strlen(\Mjoelnir_Request::getParameter('password')) > 0) {
                if (\Mjoelnir_Request::getParameter('password') == \Mjoelnir_Request::getParameter('passwordConfirm')) {
                    $oUser->setPassword(\Mjoelnir_Request::getParameter('password'));
                } else {
                    $aError['password'] = 'Das angegebene Passwort stimmt nicht mit der Passwortbestätigung überein.';
                }
            }

            if (\Mjoelnir_Request::getParameter('active', false)) {
                $oUser->setActiveFlag(1);
            } else {
                $oUser->setActiveFlag(0);
            }

            if (\Mjoelnir_Request::getParameter('password_create', false)) {
				$bSendToUser = true;
				$mPassword = \Mjoelnir_Auth::createPassword($oUser, 12, $bSendToUser);
				if (false !== $mPassword) {
					$oUser->setPassword($mPassword);
				}
            }

            if (count($aError) == 0) {
                $oUser->save();

                if (\Mjoelnir_Request::getParameter('save_return', false)) {
                    header('Location: ' . WEB_ROOT . 'user/list');
                    exit();
                }
            }
        }

        $oForm = new \Mjoelnir_Form('userEdit', '', 'post', array(), PATH_TEMPLATE . 'form/');

        $oForm->addElement('text', 'firstName', \Mjoelnir_Request::getParameter('firstName', $oUser->getFirstName()), array('label' => 'Vorname', 'error' => (isset($aError['firstName'])) ? true : false));
        $oForm->addElement('text', 'lastName', \Mjoelnir_Request::getParameter('lastName', $oUser->getLastName()), array('label' => 'Nachname', 'error' => (isset($aError['lastName'])) ? true : false));
        $oForm->addElement('text', 'email', \Mjoelnir_Request::getParameter('email', $oUser->getEmail()), array('label' => 'E-Mail', 'error' => (isset($aError['email'])) ? true : false));
        foreach ($aUserroles['rows'] as $iUserroleId => $oUserrole) {
            if (\Mjoelnir_Request::getParameter('save', false)) {
                $iValue = (isset($aSelectedUserRoleIds[$iUserroleId])) ? 1 : 0;
            } else {
                $iValue = (in_array($iUserroleId, $aUserroleIds)) ? 1 : 0;
            }
            $oForm->addElement('checkbox', 'userroleIds[' . $oUserrole->getId() . ']', $iValue, array('label' => $oUserrole->getName(), 'error' => (isset($aError['userroleIds'])) ? true : false));
        }
        $oForm->addElement('password', 'password', '', array('label' => 'Passwort', 'autocomplete' => 'off', 'error' => (isset($aError['password'])) ? true : false));
        $oForm->addElement('password', 'passwordConfirm', '', array('label' => 'Passwort wiederholen', 'autocomplete' => 'off', 'error' => (isset($aError['passwordConfirm'])) ? true : false));
        $oForm->addElement('checkbox', 'active', \Mjoelnir_Request::getParameter('active', $oUser->getActiveFlag()), array('label' => 'aktiv?', 'error' => (isset($aError['active'])) ? true : false));
        $oForm->addElement('checkbox', 'password_create', '', array('label' => 'Passwort generieren & zusenden', 'error' => false));

        $oForm->addElement('submit', 'save', 'Speichern');
        $oForm->addElement('submit', 'save_return', 'Speichern und zurück');

        $this->_view->assign('WEB_ROOT', WEB_ROOT);
        $this->_view->assign('error', $aError);
        $this->_view->assign('userForm', $oForm);

        return $this->_view->fetch('user/edit.tpl.html');
    }

    /**
     * Deletes a user.
     */
    public function deleteAction() {
        $userId = \Mjoelnir_Request::getParameter('id', false);
        if ($userId) {
            \UserModel::delete($userId);
        }

        header('Location: ' . WEB_ROOT . 'user/list');
        exit();
    }

    /**
     * User login.
     * @param   str $loginHash  The users login hash
     * @return  str
     */
    public function loginAction($loginHash = null) {
        $error = array();
        // Has the user send login informations
        if (\Mjoelnir_Request::getParameter('login', false)) {
            if (strlen(\Mjoelnir_Request::getParameter('email', false)) > 0 && strlen(\Mjoelnir_Request::getParameter('password', false)) > 0) {
                $userId = \UserModel::getUserIdByLogin(\Mjoelnir_Request::getParameter('email'), \Mjoelnir_Request::getParameter('password'));

                if ($userId !== false) {
                    $user = \UserModel::getInstance($userId);
                    if ($user->getActiveFlag() == 1) {
                        $auth = \Mjoelnir_Auth::getInstance();
                        $auth->authenticate($user->getLoginHash());

                        if (RETURN_METHOD == 'json') {
                            echo json_encode(array('status' => 1004, 'message' => \Mjoelnir_Message::getMessage(1004)));
                            die();
                        } else {
                            header('Location: ' . WEB_ROOT);
                            exit();
                        }
                    } else {
                        $error['msg'] = \Mjoelnir_Message::getMessage(2005);
                        if (RETURN_METHOD == 'json') {
                            echo json_encode(array('status' => 2005, 'message' => \Mjoelnir_Message::getMessage(2005)));
                            die();
                        }
                    }
                } else {
                    $error['msg'] = \Mjoelnir_Message::getMessage(2006);
                    if (RETURN_METHOD == 'json') {
                        echo json_encode(array('status' => 2006, 'message' => \Mjoelnir_Message::getMessage(2006)));
                        die();
                    }
                }
            } else {
                if (strlen(\Mjoelnir_Request::getParameter('email', false)) == 0) {
                    $error['email'] = \Mjoelnir_Message::getMessage(2007);
                    if (RETURN_METHOD == 'json') {
                        echo json_encode(array('status' => 2007, 'message' => \Mjoelnir_Message::getMessage(2007)));
                        die();
                    }
                }

                if (strlen(\Mjoelnir_Request::getParameter('password', false)) == 0) {
                    $error['password'] = \Mjoelnir_Message::getMessage(2008);
                    ;
                    if (RETURN_METHOD == 'json') {
                        echo json_encode(array('status' => 2008, 'message' => \Mjoelnir_Message::getMessage(2008)));
                        die();
                    }
                }
            }
        }

        $form = new \Mjoelnir_Form('login', '', 'post', array(), PATH_TEMPLATE . 'form/');
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
     * Logs out the user.
     */
    public function logoutAction() {
        $auth = \Mjoelnir_Auth::getInstance();
        $auth->cancel();

        header('Location: ' . WEB_ROOT . 'user/login');
        exit();
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
