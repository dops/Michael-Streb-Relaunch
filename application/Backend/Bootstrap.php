<?php

/**
 * Description of Bootstrap
 *
 * @author td-office
 */
class Bootstrap
{
    /**
     * The instance of site.
     * @var Mjoelnir_Site
     */
    protected $_oSite    = null;

    /**
     * The instance of the current user.
     * @var UserModel
     */
    protected $_oUser    = null;

    /**
     * Construstor.
     */
    public function __construct() {
        $this->_setIncludePaths();

        $this->_oSite    = Mjoelnir_Site::getInstance();
        $this->_oUser    = UserModel::getCurrentUser();
        
        $this->_oSite->setDefaultPage('user');
        $this->_oSite->setDefaultAction('login');
        $this->_oSite->setPageTitle('Michael Streb Admin');

        $this->_prepareAcl();
    }
    
    /**
     * Loads the page. If the user has missing permissions, he will be redirected to the default page/action. Keep in mind the every user needed minimum permissions to
     * view the default page/action.
     */
    public function load() {
        $oAcl   = Mjoelnir_Acl::getInstance();
        $oLog   = Mjoelnir_Log::getInstance();
        
        $this->_oSite->setDefaultPage('user');
        $this->_oSite->setDefaultAction('login');
        
        try {
            if (UserModel::getCurrentUser() !== false) {
                // Authorized user
                if (
                    !$oAcl->isAllowed(strtolower(APPLICATION_NAME), strtolower($this->_oSite->getPage()), strtolower($this->_oSite->getAction()))
                ) {
                    $oLog->log('The user tried to access a not defined permission.');

                    if (RETURN_METHOD == 'json') {
                        echo json_encode(array('error' => true, 'status' => 403, 'message' => Mjoelnir_Message::getMessage(2010)));
                        exit();
                    }

                    header('Location: ' . WEB_ROOT . 'error/forbidden');
                    exit();
                }
            }
            else {
                // Not authorized user
                if ($this->_oSite->getPage() != $this->_oSite->getDefaultPage() || $this->_oSite->getAction() != $this->_oSite->getDefaultAction()) {
                    if (RETURN_METHOD == 'json') {
                        echo json_encode(array('error' => true, 'status' => 401, 'message' => Mjoelnir_Message::getMessage(2009)));
                        exit();
                    }

                    header('Location: ' . WEB_ROOT . '' . $this->_oSite->getDefaultPage() . '/' . $this->_oSite->getDefaultAction());
                    exit();
                }
            }
        }
        catch (Mjoelnir_Acl_Exception $e) {
            $oLog->log('The user tried to access a not defined permission.');
            header('Location: ' . WEB_ROOT . 'error/forbidden');
            exit();
        }
        
        $this->_oSite->addCssFile('admin.css');
        $this->_oSite->addCssFile('icons.css');
        $this->_oSite->addCssFile('paging.css');
        $this->_oSite->addJsFile('jquery/jquery-1.8.0.min.js', 'header');
        $this->_oSite->addJsFile('jquery/jquery-ui-1.8.23.custom.min.js', 'header');
        $this->_oSite->addJsFile('jquery/jquery.easing.1.3.js', 'header');
        $this->_oSite->addJsFile('form.js', 'header');
        $this->_oSite->addJsFile('ajax.js', 'header');
        $this->_oSite->addDebugContent('<div id="memoryPeakUsage">Memory peak usage: ' . round(memory_get_peak_usage() / 1024 / 1024, 2) . '</div>');
        $this->_oSite->setBaseTemplate('layout.tpl.html');
        
        $oMessages  = Mjoelnir_Message::getInstance(Mjoelnir_Request::getInstance());
        
        $view   = $this->_oSite->run();
        
        $view->setTemplateDir(PATH_TEMPLATE);

        $view->assign('baseUrl', (preg_match('/HTTP\//', $_SERVER['SERVER_PROTOCOL'])) ? 'http://' . $_SERVER['HTTP_HOST'] : 'https://' . $_SERVER['HTTP_HOST']);
        $view->assign('applicationEnv', APPLICATION_ENV);
        $view->assign('WEB_ROOT', WEB_ROOT);
        $view->assign('aMessages', $oMessages->getAllMessages());
        $view->assign('oAcl', $oAcl);
        $view->assign('oCurrentUser', UserModel::getCurrentUser());
        
        $this->_oSite->display($view);
    }

    /**
     * Sets the include paths.
     * @return  true
     */
    protected function _setIncludePaths() {
        set_include_path(get_include_path() . ':' . PATH_LIBRARY);

        return true;
    }

    /**
     * Set the user permissions in the acl.
     * @return  bool
     */
    protected function _prepareAcl() {
        if ($this->_oUser instanceof UserModel) {
            $aUserUserroles = \UserUserroleModel::getAll(null, null, array('user_id' => array('eq' => $this->_oUser->getId())));
            $oAcl           = Mjoelnir_Acl::getInstance();
            foreach ($aUserUserroles['rows'] as $iUserUserrole => $oUserUserrole) {
                $aPermissions   = \UserRolePermissionModel::getAll(null, null, array('user_role_id' => array('eq' => $oUserUserrole->getUserroleId())));

                if ($oUserUserrole->getUserroleId() == ADMIN_USER_ROLE_ID) {
                    $oAcl->setAsAdmin();
                    break;
                }
                else {
                    foreach ($aPermissions['rows'] as $oPermission) {
                        $oAcl->addPermission($oPermission);
                    }
                }
            }
        }

        return true;
    }
}