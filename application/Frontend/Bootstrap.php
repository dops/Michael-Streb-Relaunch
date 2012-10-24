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


    public function __construct() {
        $this->_setIncludePaths();

        $this->_oSite    = Mjoelnir_Site::getInstance();
        $this->_oUser    = UserModel::getCurrentUser();
        
        $this->_prepareAcl();
    }


    public function start() {
        $this->_oSite->setPageTitle('Michael Streb');
        $this->_oSite->addCssFile('common.css');
//        $this->_oSite->addCssFile('/topdeals.partnerbackend.css');
//        $this->_oSite->addJsFile('/jquery/jquery-1.7.1.min.js', 'header');
    }


    public function end() {
        $this->_oSite->addDebugContent('<div id="memoryPeakUsage">Memory peak usage: ' . round(memory_get_peak_usage() / 1024 / 1024, 2) . '</div>');
    }


    protected function _setIncludePaths() {
        set_include_path(get_include_path() . ':' . PATH_LIBRARY);
    }

    /**
     * Set the user permissions in the acl.
     * @return  bool
     */
    protected function _prepareAcl() {
        if ($this->_oUser instanceof UserModel) {
            $aUserUserroles = \UserUserRoleModel::getAll(NULL, NULL, array('user_id' => array('eq' => $this->_oUser->getId())));
            $oAcl           = Mjoelnir_Acl::getInstance();

            foreach ($aUserUserroles['rows'] as $iUserUserrole => $oUserUserrole) {
                $aPermissions   = \UserRolePermissionModel::getAll(null, null, array('user_role_id' => array('eq' => $oUserUserrole->getUserroleId())));

                if ($oUserUserrole->getUserroleId() == ADMIN_USER_ROLE_ID) {
                    $oAcl->setAsAdmin();
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