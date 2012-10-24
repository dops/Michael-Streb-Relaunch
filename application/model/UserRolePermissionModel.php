<?php

/**
 * UserRolePermissionModel
 *
 * @author Michael Streb <michael.streb@topdeals.de>
 */
class UserRolePermissionModel extends AbstractModel
{
    /**
     * Holds all loaded user models.
     * @var array
     */
    protected static $_instances    = array();

    /**
     * The table that holds the data for the model.
     * @var string
     */
    protected static $_sTable   = 'user_role_permission';

    /**
     * The data array contains the model data.
     * @var type
     */
    protected $_aData    = array(
        'user_role_permission_id'   => null,
        'user_role_id'              => null,
        'application'               => null,
        'controller'                => null,
        'action'                    => null,
        'allow'                     => null,
        'time_insert'               => null,
        'time_update'               => null,
    );

    /**
     * The unique id field is the field that holds the system-wide unique id for the model instance.
     * @var int
     */
    public static $_sUniqueIdField  = 'user_role_permission_id';

    /**
     * Contains regular expression to validate data. If a value needs no validation, just don´t name it.
     * @var array
     */
    protected $_aDataValidation  = array(
        'user_role_id'  => '/[0-9]+/',
        'controller'    => '/[a-z]+/i',
        'action'        => '/[a-z]+/i',
    );

    protected function __construct($mData) {
        parent::__construct($mData);
    }

    /**
     * Checks wether teh current instance can be deleted or not.
     * @param   int     $iId    The id of the model to delete.
     * @return  bool
     */
    protected static function _isDeleteAllowed($iId) {
        return true;
    }

    /**
     * Deletes all permissions having the given application, controller and action.
     * @param   str     $application    The name of the application.
     * @param   str     $controller     The name of the controller.
     * @param   str     $action         The nam of the action.
     * @return  bool
     */
    public static function deleteByApplicationControllerAction($application, $controller, $action) {
        $oDb = Mjoelnir_Db::getInstance();
        $oDb->delete(Db_MichaelStreb_Config::TABLE_USER_ROLE_PERMISSION, 'application = "' . $application . '" AND controller = "' . $controller . '" AND action = "' . $action . '"');
        return true;
    }

    #################
    ## GET METHODS ##
    #################

    /**
     * Returns the user role id.
     * @return int
     */
    public function getUserRoleId() {
        return (int) $this->_aData['user_role_id'];
    }

    /**
     * Returns the user role application.
     * @return str
     */
    public function getApplication() {
        return $this->_aData['application'];
    }

    /**
     * Returns the user role controller.
     * @return str
     */
    public function getController() {
        return $this->_aData['controller'];
    }

    /**
     * Returns the user role action.
     * @return str
     */
    public function getAction() {
        return $this->_aData['action'];
    }

    /**
     * Returns the user role allow.
     * @return bool
     */
    public function getAllow() {
        // Admin role (1) has access always.
        if ($this->getUserRoleId() === 1) {
            return true;
        }
        return (bool) $this->_aData['allow'];
    }

    #################
    ## SET METHODS ##
    #################


    /**
     * Sets the permissions user role id.
     * @param   int     $value  The user role id.
     * @return  bool
     */
    public function setUserRoleId($value) {
        if ($this->valueIsValid('user_role_id', $value)) {
            $this->_aData['user_role_id'] = $value;
            return true;
        }

        $this->_sError   = 'Die angegebene Benutzerrollen-ID entspricht nicht den Vorgaben. Vorgabe: ' . str_replace('/', '', $this->_aDataValidation['user_role_id']);

        return false;
    }

    /**
     * Sets the permissions application.
     * @param   str     $value  The user role permission application.
     * @return  bool
     */
    public function setApplication($value) {
        if ($this->valueIsValid('application', $value)) {
            $this->_aData['application'] = $value;
            return true;
        }

        $this->_sError   = 'Der angegebene Anwendungsname entspricht nicht den Vorgaben. Vorgabe: ' . str_replace('/', '', $this->_aDataValidation['application']);

        return false;
    }

    /**
     * Sets the permissions controller.
     * @param   str     $value  The user role permission controller.
     * @return  bool
     */
    public function setController($value) {
        if ($this->valueIsValid('controller', $value)) {
            $this->_aData['controller'] = $value;
            return true;
        }

        $this->_sError   = 'Der angegebene Controller-Name entspricht nicht den Vorgaben. Vorgabe: ' . str_replace('/', '', $this->_aDataValidation['controller']);

        return false;
    }

    /**
     * Sets the permissions action.
     * @param   str     $value  The user role permission action.
     * @return  bool
     */
    public function setAction($value) {
        if ($this->valueIsValid('action', $value)) {
            $this->_aData['action'] = $value;
            return true;
        }

        $this->_sError   = 'Der angegebene Aktions-Name entspricht nicht den Vorgaben. Vorgabe: ' . str_replace('/', '', $this->_aDataValidation['action']);

        return false;
    }

    /**
     * Sets the permissions allow.
     * @param   str     $value  The user role permission allow.
     * @return  bool
     */
    public function setAllow($value) {
        if ($this->valueIsValid('allow', $value)) {
            $this->_aData['allow'] = (bool) $value;
            return true;
        }

        $this->_sError   = 'Die angegebene Freigabe entspricht nicht den Vorgaben. Vorgabe: ' . str_replace('/', '', $this->_aDataValidation['allow']);

        return false;
    }
}