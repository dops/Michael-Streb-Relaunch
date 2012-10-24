<?php

/**
 * UserRoleModel
 *
 * @author Michael Streb <michael.streb@topdeals.de>
 */
class UserroleModel extends AbstractModel
{
    /**
     * Holds all loaded user models.
     * @var array
     */
    protected static $_instances    = array();

    /**
     * The table that holds teh data for the UserModel.
     * @var string
     */
    protected static $_sTable   = Db_MichaelStreb_Config::TABLE_USER_ROLE;

    /**
     * Contains all data reffered to a singel user.
     * @var array
     */
    protected $_aData    = array(
        'user_role_id'  => null,
        'name'          => null,
        'time_insert'   => null,
        'time_update'   => null,
    );
    
    /**
     * The unique id field is the field that holds the system-wide unique id for the model instance.
     * @var int
     */
    public static $_sUniqueIdField  = 'user_role_id';
    
    /**
     * Contains regular expression to validate user data. If a user value needs no validation, just don´t name it.
     * @var array
     */
    protected $_aDataValidation  = array(
        'name'  => '/[\w\s]/',
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
        // Do not delete admin user role
        if ($iId == 1) {
            return false;
        }
        
        return true;
    }

    #################
    ## GET METHODS ##
    #################
    
    /**
     * Returns the user roles name.
     * @return  str
     */
    public function getName() {
        return $this->_aData['name'];
    }
    
    #################
    ## SET METHODS ##
    #################
    
    /**
     * Set the user roles name.
     * @param   str $value  The name of the user role.
     * @return  bool
     */
    public function setName($value) {
        if (
            (isset($this->_aDataValidation['name']) && preg_match($this->_aDataValidation['name'], $value))
            || !isset($this->_aDataValidation['name'])
        ) {
            $this->_aData['name'] = $value;
            return true;
        }
        
        $this->_sError   = 'Der angegebene Benutzerrollenname entspricht nicht den Vorgaben. Vorgabe: ' . str_replace('/', '', $this->_aDataValidation['name']);
        return false;
    }
}

?>
