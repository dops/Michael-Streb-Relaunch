<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UserUserroleModel
 *
 * @author Michael Streb <michael.streb@topdeals.de>
 */
class UserUserroleModel extends AbstractModel
{
    /**
     * Holds all loaded user models.
     * @var array
     */
    public static $_instances    = array();

    /**
     * The table that holds the data for the UserModel.
     * @var string
     */
    protected static $_sTable   = Db_MichaelStreb_Config::TABLE_USER_USER_ROLE;

    /**
     * Contains all data reffered to a singel user.
     * @var array
     */
    protected $_aData   = array(
        'user_user_role_id' => null,
        'user_id'           => null,
        'user_role_id'      => null,
        'time_insert'       => null,
    );

    /**
     * The unique id field is the field that holds the system-wide unique id for the model instance.
     * @var int
     */
    public static $_sUniqueIdField    = 'user_user_role_id';

    /**
     * Contains regular expression to validate user data. If a user value needs no validation, just don´t name it.
     * @var array
     */
    protected $_aDataValidation  = array(
        'user_id'       => '/[0-9]+/',
        'user_role_id'  => '/[0-9]+/',
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

    #################
    ## GET METHODS ##
    #################

    /**
     * Returns the user id.
     * @param   int $bReturnModel   If set to true a model is returned instead of an id.
     * @return  mixed
     */
    public function getUserId($bReturnModel = false) {
        if (true === $bReturnModel) {
            return \UserModel::getInstance((int) $this->_aData['user_id']);
        }
        return (int) $this->_aData['user_id'];
    }

    /**
     * Returns the user role id.
     * @param   int $bReturnModel   If set to true a model is returned instead of an id.
     * @return  mixed
     */
    public function getUserroleId($bReturnModel = false) {
        if (true === $bReturnModel) {
            return \UserroleModel::getInstance((int) $this->_aData['user_role_id']);
        }
        return (int) $this->_aData['user_role_id'];
    }

    #################
    ## SET METHODS ##
    #################

    /**
     * Set the user id.
     * @param   int $name
     * @return  bool
     */
    public function setUserId($value) {
        if ($this->valueIsValid('user_id', $value)) {
            $this->_aData['user_id'] = $value;
            return true;
        }

        $this->_sError   = 'Die angegebene Benutzer-ID entspricht nicht den Vorgaben. Vorgabe: ' . str_replace('/', '', $this->_aDataValidation['user_id']);

        return false;
    }

    /**
     * Set the user role id.
     * @param   int $name
     * @return  bool
     */
    public function setUserroleId($value) {
        if ($this->valueIsValid('user_role_id', $value)) {
            $this->_aData['user_role_id'] = $value;
            return true;
        }

        $this->_sError   = 'Die angegebene Benutzerrollen-ID entspricht nicht den Vorgaben. Vorgabe: ' . str_replace('/', '', $this->_aDataValidation['user_role_id']);

        return false;
    }
}
