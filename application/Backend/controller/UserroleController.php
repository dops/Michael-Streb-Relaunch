<?php

namespace Backend;

/**
 * UserGroupController
 *
 * @author Michael Streb <michael.streb@topdeals.de>
 */
class UserroleController extends \Mjoelnir_Controller_Abstract
{
    /**
     * An array containing several different user instances.
     * @var array
     */
    protected static $_instances    = array();

    /**
     * Lists all usergroups.
     * @return string
     */
    public function indexAction() {
        $site   = \Mjoelnir_Site::getInstance();
        $site->addBreadcrumb(array('title' => 'Userverwaltung', 'link' => WEB_ROOT . 'user'));
        $site->addBreadcrumb(array('title' => 'Userrollen verwalten', 'link' => WEB_ROOT . 'userrole'));

        $this->_view->assign('WEB_ROOT', WEB_ROOT);
        $this->_view->assign('userroles', \UserroleModel::getAll());

        return $this->_view->fetch('userrole/list.tpl.html');
    }

    /**
     * E$dits a userrole.
     * @return  str
     */
    public function editAction() {
        $site   = \Mjoelnir_Site::getInstance();
        $site->addBreadcrumb(array('title' => 'Userverwaltung', 'link' => WEB_ROOT . 'user'));
        $site->addBreadcrumb(array('title' => 'Userrollen verwalten', 'link' => WEB_ROOT . 'userrole'));
        if (\Mjoelnir_Request::getParameter('id', false)) {
            $site->addBreadcrumb(array('title' => 'Userrolle bearbeiten', 'link' => WEB_ROOT . 'userrole/edit/id/' . \Mjoelnir_Request::getParameter('id')));
        }
        else {
            $site->addBreadcrumb(array('title' => 'Userrolle anlegen', 'link' => WEB_ROOT . 'userrole/edit'));
        }

        $aMessages  = array('error' => array());

        if (\Mjoelnir_Request::getParameter('save', false) || \Mjoelnir_Request::getParameter('save_return', false)) {
            $oUserrole   = \UserroleModel::getInstance(\Mjoelnir_Request::getParameter('id', null));

            if (!$oUserrole->setName(\Mjoelnir_Request::getParameter('name', false)))   { $aMessages['error']['name'] = $oUserrole->getError(); }

            if (count($aMessages['error']) == 0) {
                // Set permissions
                $aPermissions       = \UserRolePermissionModel::getAll(null, null, array('user_role_id' => array('eq' => $oUserrole->getId())));
                $aSetPermissions    = \Mjoelnir_Request::getParameter('permission', array());
                foreach ($aPermissions['rows'] as $permission) {
                    if (isset($aSetPermissions[$permission->getApplication()][$permission->getController()][$permission->getAction()]) && !$permission->getAllow()) {
                        $permission->setAllow(true);
                        $permission->save();
                    }

                    if (!isset($aSetPermissions[$permission->getApplication()][$permission->getController()][$permission->getAction()]) && $permission->getAllow()) {
                        $permission->setAllow(false);
                        $permission->save();
                    }
                }

                // Update permissions on first save
                if ($oUserrole->getId() == 0) {
                    $oUserrole->save();
                    $this->permissionUpdateAction(false);
                }
                else {
                    $oUserrole->save();
                }

                if (\Mjoelnir_Request::getParameter('save', false)) {
                    header('Location: ' . WEB_ROOT . 'userrole/edit/id/' . $oUserrole->getId() . '/messages/1002');
                    exit();
                }
                elseif (\Mjoelnir_Request::getParameter('save_return', false)) {
                    header('Location: ' . WEB_ROOT . 'userrole/list/messages/1002');
                    exit();
                }
            }
        }

        // Create form
        $form   = new \Mjoelnir_Form('userRoleEdit', '', 'post', array(), PATH_TEMPLATE . 'form/');

        if (\Mjoelnir_Request::getParameter('id', false)) {
            $oUserrole   = \UserroleModel::getInstance(\Mjoelnir_Request::getParameter('id'));

            $form->addElement('text', 'name', \Mjoelnir_Request::getParameter('name', $oUserrole->getName()), array('label' => 'Name', 'error' => (isset($aMessages['error']['name'])) ? true : false));
        }
        else {
            $form->addElement('text', 'name', \Mjoelnir_Request::getParameter('name', ''), array('label' => 'Name', 'error' => (isset($aMessages['error']['name'])) ? true : false));
        }

        // Add permission fields
        if (isset($oUserrole)) {
            $aPermissions   = \UserRolePermissionModel::getAll(null, null, array('user_role_id' => array('eq' => $oUserrole->getId())));
            $sEvenOdd       = 'odd';
            foreach ($aPermissions['rows'] as $permission) {
                $sEvenOdd  = ($sEvenOdd == 'even') ? 'odd' : 'even';
                $permissionName = 'permission[' . $permission->getApplication() . '][' . $permission->getController() . '][' . $permission->getAction() . ']';
                $form->addElement('checkbox', $permissionName, $permission->getAllow(), array('label' => $permission->getApplication() . ' - ' . $permission->getController() . ' - ' . $permission->getAction(), 'class' => $sEvenOdd));
            }
        }

        $form->addElement('submit', 'save', 'Speichern');
        $form->addElement('submit', 'save_return', 'Speichern und zurück');

        $this->_view->assign('WEB_ROOT', WEB_ROOT);
        $this->_view->assign('aMessages', $aMessages);
        $this->_view->assign('userForm', $form);

        return $this->_view->fetch('userrole/edit.tpl.html');
    }

    /**
     * Deletes a user.
     */
    public function deleteAction() {
        $userRoleId = \Mjoelnir_Request::getParameter('id', false);
        if ($userRoleId) {
            $bIsDeleted = \UserroleModel::delete($userRoleId);

            if (true === $bIsDeleted) {
                header('Location: ' . WEB_ROOT . 'userrole/list/messages/1003');
                exit();
            }
            else {
                header('Location: ' . WEB_ROOT . 'userrole/list/messages/2012');
                exit();
            }
        }
        else {
            header('Location: ' . WEB_ROOT . 'userrole/list/messages/2011');
            exit();
        }
    }

    /**
     * Adds new permissions and deletes no more needed permissions to and from all user roles.
     * @return str
     */
    public function permissionUpdateAction($bExecuteAsAction = true) {
        if ($bExecuteAsAction) {
            $site   = \Mjoelnir_Site::getInstance();
            $site->addBreadcrumb(array('title' => 'Userverwaltung', 'link' => WEB_ROOT . 'user'));
            $site->addBreadcrumb(array('title' => 'Userrollen verwalten', 'link' => WEB_ROOT . 'userrole'));
            $site->addBreadcrumb(array('title' => 'Rechte updaten', 'link' => WEB_ROOT . 'userrole/permissionUpdate'));
        }

        $oLog    = \Mjoelnir_Log::getInstance();

        // Build an array to check for found permissions.
        $aPermissionModels      = \UserRolePermissionModel::getAll();
        $aExistingPermissions   = array();
        foreach ($aPermissionModels['rows'] as $oPermission) {
            if (!isset($aExistingPermissions[$oPermission->getUserRoleId()][$oPermission->getUserRoleId()])) {
                $aExistingPermissions[$oPermission->getUserRoleId()][$oPermission->getUserRoleId()]    = array();
            }

            if (!isset($aExistingPermissions[$oPermission->getUserRoleId()][$oPermission->getApplication()])) {
                $aExistingPermissions[$oPermission->getUserRoleId()][$oPermission->getApplication()]    = array();
            }

            if (!isset($aExistingPermissions[$oPermission->getUserRoleId()][$oPermission->getApplication()][$oPermission->getController()])) {
                $aExistingPermissions[$oPermission->getUserRoleId()][$oPermission->getApplication()][$oPermission->getController()]    = array();
            }

            if (!isset($aExistingPermissions[$oPermission->getUserRoleId()][$oPermission->getApplication()][$oPermission->getController()][$oPermission->getAction()])) {
                $aExistingPermissions[$oPermission->getUserRoleId()][$oPermission->getApplication()][$oPermission->getController()][$oPermission->getAction()]    = $oPermission->getAllow();
            }
        }

        $aUserRoles = \UserroleModel::getAll();

        // Search for permissions
        $aFoundPermissions  = array();
        $oAppDirHandle    = dir(PATH_CONTROLLER . '../../');
        while (false !== ($sEntry = $oAppDirHandle->read())) {
            if ($sEntry != '.' && $sEntry != '..' && $sEntry != 'model') {
                $sApplicationName   = $sEntry;
                $oControllerDirHandle  = dir(PATH_CONTROLLER . '../../' . $sApplicationName . '/controller/');

                if (!isset($aFoundPermissions[$sApplicationName])) {
                    $aFoundPermissions[$sApplicationName]   = array();
                }

                while (false !== ($sEntry = $oControllerDirHandle->read())) {
                    if ($sEntry != '.' && $sEntry != '..') {
                        $sClassName      = str_replace('.php', '', $sEntry);
                        $sControllerName = strtolower(str_replace('Controller.php', '', $sEntry));

                        if (!isset($aFoundPermissions[$sApplicationName][$sControllerName])) {
                            $aFoundPermissions[$sApplicationName][$sControllerName] = array();
                        }

                        $sQualifiedClassName    = ucfirst(strtolower($sApplicationName)) . '\\' . $sClassName;
                        require_once PATH_CONTROLLER . '../../' . $sApplicationName . '/controller/' . $sEntry;

                        $oReflectionObject  = new \ReflectionClass($sQualifiedClassName);
                        $aClassMethods      = $oReflectionObject->getMethods();

                        if (is_array($aClassMethods)) {
                            foreach ($aClassMethods as $oMethod) {
                                // Check actions only
                                if (strpos($oMethod->name, 'Action') !== false && strpos($oMethod->name, 'Action') === (strlen($oMethod->name) - 6)) {
                                    $sActionName = strtolower(str_replace('Action', '', $oMethod->name));

                                    $aFoundPermissions[$sApplicationName][$sControllerName][$sActionName]   = false;
                                }
                            }
                        }
                    }
                }
            }
        }

        $aMessages  = array('added' => array(), 'deleted' => array());

        // Add not existing permissions
        foreach ($aFoundPermissions as $sApplicationName => $aControllers) {
            foreach ($aControllers as $sControllerName => $aActions) {
                foreach ($aActions as $sActionName => $bAllow) {
                    foreach ($aUserRoles['rows'] as $iUserRoleId => $oUserRole) {
//                        echo $iUserRoleId . ' - ' . $sApplicationName . ' - ' . $sControllerName . ' - ' . $sActionName . ' - isset: ' . isset($aExistingPermissions[$sApplicationName][$sControllerName][$sActionName]) . "\n";
                        if (!isset($aExistingPermissions[$iUserRoleId][$sApplicationName][$sControllerName][$sActionName])) {
//                            echo $sApplicationName . ' - ' . $sControllerName . ' - ' . $sActionName . "\n";
                            // Allow minumum rights needed for system use.
                            $bAllow  = (
                                    $sControllerName == 'error'
                                    || $sActionName == 'logout'
                                    || $sActionName == 'login'
                                    || ($sControllerName == 'index' && $sActionName == 'index'))
                            ? true : false;

                            $oUserRolePermission    = \UserRolePermissionModel::getInstance();
                            $oUserRolePermission->setUserRoleId($oUserRole->getId());
                            $oUserRolePermission->setApplication($sApplicationName);
                            $oUserRolePermission->setController($sControllerName);
                            $oUserRolePermission->setAction($sActionName);
                            $oUserRolePermission->setAllow($bAllow);
                            $oUserRolePermission->save();

                            $aMessages['added'][]   = 'Neue Rechte für Benutzerrolle "' . $oUserRole->getName() . '", Anwendung "' . $sApplicationName . '", Controller "' . $sControllerName . '" und Action "' . $sActionName . '" hinzugefügt.';
                            $oLog->log('Added permissions for application "' . $sApplicationName . '", controller "' . $sControllerName . '" and action "' . $sActionName . '".');
                        }
                    }
                    unset($oUserRole, $oUserRolePermission);
                }
                unset($sActionName, $bAllow);
            }
            unset($sControllerName, $aActions);
        }
        unset($sApplicationName, $aControllers);

        // Remove not found permissions
        foreach ($aExistingPermissions as $iUserRoleId => $aApplications) {
            foreach ($aApplications as $sApplicationName => $aControllers) {
                foreach ($aControllers as $sControllerName => $aActions) {
                    foreach ($aActions as $sActionName => $bAllow) {
                        if (!isset($aFoundPermissions[$sApplicationName][$sControllerName][$sActionName])) {
                            \UserRolePermissionModel::deleteByApplicationControllerAction($sApplicationName, $sControllerName, $sActionName);
                            $aMessages['deleted'][]   = 'Rechte für Anwendung "' . $sApplicationName . '", Controller "' . $sControllerName . '" und Action "' . $sActionName . '" entfernt.';
                            $oLog->log('Deleted permissions for application "' . $sApplicationName . '", controller "' . $sControllerName . '" and action "' . $sActionName . '".');
                        }
                    }
                    unset($sActionName, $bAllow);
                }
                unset($sControllerName, $aActions);
            }
            unset($sApplicationName, $aControllers);
        }
        unset($iUserRoleId, $aApplications);

        if (!$bExecuteAsAction) {
            return true;
        }

        $this->_view->assign('messages', $aMessages);

        return $this->_view->fetch('userrole/permissionUpdate.tpl.html');
    }
}

?>
