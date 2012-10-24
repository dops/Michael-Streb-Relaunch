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
     * Things to do befor anything else.
     * @return  bool
     */
    public function start() {
        $this->_oSite->addCssFile('admin.css');
        $this->_oSite->addCssFile('icons.css');
        $this->_oSite->addCssFile('paging.css');
        $this->_oSite->addJsFile('jquery/jquery-1.8.0.min.js', 'header');
        $this->_oSite->addJsFile('jquery/jquery-ui-1.8.23.custom.min.js', 'header');
        $this->_oSite->addJsFile('jquery/jquery.easing.1.3.js', 'header');
        $this->_oSite->addJsFile('form.js', 'header');
        $this->_oSite->addJsFile('ajax.js', 'header');

        return true;
    }

    /**
     * Things to do after anything else.
     * @return  bool
     */
    public function end() {
        $this->_oSite->addDebugContent('<div id="memoryPeakUsage">Memory peak usage: ' . round(memory_get_peak_usage() / 1024 / 1024, 2) . '</div>');

        return true;
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