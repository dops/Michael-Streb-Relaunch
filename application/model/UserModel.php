<?php

/**
 * Description of UserModel
 *
 * @author td-office
 */
class UserModel extends AbstractModel
{
    /**
     * Holds all loaded user models.
     * @var array
     */
    public static $_instances    = array();

    /**
     * The table that holds teh data for the UserModel.
     * @var string
     */
    protected static $_sTable   = Db_MichaelStreb_Config::TABLE_USER;

    /**
     * Contains all data reffered to a singel user.
     * @var array
     */
    protected $_aData   = array(
        'user_id'               => null,
        'email'                 => null,
        'nick'                  => null,
        'password'              => null,
        'first_name'            => null,
        'last_name'             => null,
        'company'               => null,
        'street'                => null,
        'street_number'         => null,
        'zipcode'               => null,
        'city'                  => null,
        'country_id'            => null,
        'contact_name'          => null,
        'phone'                 => null,
        'fax'                   => null,
        'login_hash'            => null,
        'active'                => null,
        'time_insert'           => null,
        'time_update'           => null,
    );

    /**
     * Additional data which is not saved within the model.
     * @var array
     */
    protected $_aAdditionalData = array(
        'userroleIds'   => array()
    );

    /**
     * The unique id field is the field that holds the system-wide unique id for the model instance.
     * @var int
     */
    public static $_sUniqueIdField    = 'user_id';

    /**
     * Contains regular expression to validate user data. If a user value needs no validation, just don´t name it.
     * @var array
     */
    protected $_aDataValidation  = array(
        'active'    => '/[0|1]{1}/',
    );


    protected function __construct($mData) {
        parent::__construct($mData);
    }

    /**
     * Fetches a user id using login data.
     * @param   str $email      The users email address.
     * @param   str $Spassword  The users uncrypted password.
     * @return  int
     */
    public static function getUserIdByLogin($email, $password) {
        $oDb = Mjoelnir_Db::getInstance();

        $oSql = $oDb->select()
            ->from (Db_MichaelStreb_Config::TABLE_USER, 'user_id')
            ->where ('email = ?', $email)
            ->where ('password = ?', self::_crypt($password));
        $oRes = $oSql->query();
        $aData = $oRes->fetch();

        Mjoelnir_Log::getInstance()->log(sprintf ('$email: %s', $email));

        if (isset($aData['user_id'])) {
            return (int) $aData['user_id'];
        }

        return false;
    }


    public static function getCurrentUser() {
        $oDb     = Mjoelnir_Db::getInstance();
        $oAuth   = Mjoelnir_Auth::getInstance();

        if ($oAuth->getAuthValue()) {
            $oSql = $oDb->select()
                ->from (self::$_sTable)
                ->where ('login_hash = ?', $oAuth->getAuthValue());
            $oRes = $oSql->query ();
            $aData = $oRes->fetch ();

            if (isset($aData['user_id'])) {
                return UserModel::getInstance($aData);
            }
        }

        return false;
    }

    /**
     * Saves the userdata.
     */
    public function save() {
        if (!is_null($this->_aData['user_id'])) {
            $aUpdate = $this->_aData;
            $aUpdate['time_update'] = time();

            // login hash is needed, but partners normally don't have a loginhash
            // so create one if none exists
            if (empty($aUpdate['login_hash'])) {
                    $aUpdate['login_hash'] = self::_crypt($this->_aData['first_name'] . $this->_aData['last_name'] . $this->_aData['email']);
            }
            unset($aUpdate['user_id'], $aUpdate['time_insert']);

            $this->_oDb->update(self::$_sTable, $aUpdate, 'user_id = ' . $this->_aData['user_id']);
        }
        else {
            $this->_aData['login_hash']  = self::_crypt($this->_aData['first_name'] . $this->_aData['last_name'] . $this->_aData['email']);
            $this->_aData['time_insert'] = time();
            $this->_oDb->insert(self::$_sTable, $this->_aData);
        }

        if (count($this->_aAdditionalData) && method_exists($this, '_saveAdditionalData')) {
            $this->_saveAdditionalData();
        }
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
     * Crypts a string.
     * @param   str $string The string to crypt.
     * @return type
     */
    public static function _crypt($string) {
        return md5(ENCRYPT_PREFIX . trim($string));
    }

    /**
     * Sicheres Passwort erzeugen
     *
     * @return string
     */
    public static function generatePassword() {
        $arr = array_merge(range('a', 'z'), range('A', 'Z'), range('0', '9'));
        shuffle($arr);
        $str = str_shuffle(substr(implode('', $arr), 0, 6));

        return $str;
    }

    protected function _readAdditionalData() {
        $aUserroles                             = \UserUserroleModel::getAll(null, null, array('user_id' => array('eq' => $this->getId())));
        $this->_aAdditionalData['userroleIds']  = array_keys($aUserroles);
    }

        /**
     * Saves additional data that dos not belong to the model directly.
     * @return bool
     */
    protected function _saveAdditionalData() {
        foreach ($this->_aAdditionalData as $sDataType => $mData) {
            if ($sDataType == 'userroleIds') {
                // get all current user user-roles
                $aUserUserroles = UserUserroleModel::getAll(NULL, NULL, array('user_id' => array('eq' => $this->getId())));

                // save / update new user user-roles
                foreach ($mData as $iUserroleId) {
                    $aFilter    = array(
                        'user_id'       => array('eq' => $this->getId()),
                        'user_role_id'  => array('eq' => $iUserroleId)
                    );
                    $aTemp          = UserUserroleModel::getAll(null, null, $aFilter);
                    $oUserUserrole  = (isset($aUserUserrole['rows'][0])) ? $aTemp['rows'][0] : UserUserroleModel::getInstance();
                    $oUserUserrole->setUserroleId($iUserroleId);
                    $oUserUserrole->setUserId($this->getId());
                    $oUserUserrole->save();
                    unset($aTemp);
                }

                // Delete all unchecked user user-roles
                foreach ($aUserUserroles['rows'] as $iUserUserroleId => $oUserUserrole) {
                    if (!in_array($oUserUserrole->getUserroleId(), $mData)) {
                        UserUserroleModel::delete($iUserUserroleId);
                    }
                }
            }
        }

        return true;
    }

    #################
    ## GET METHODS ##
    #################

    /**
     * Returns the users email.
     * @return str
     */
    public function getEmail() {
        return $this->_aData['email'];
    }

    /**
     * Returns the users nickname
     * @return str
     */
    public function getNickName() {
		return $this->_aData['nick'];
    }

    /**
     * Returns the users first name.
     * @return str
     */
    public function getFirstName() {
        return $this->_aData['first_name'];
    }

    /**
     * Returns the users last name.
     * @return str
     */
    public function getLastName() {
        return $this->_aData['last_name'];
    }

    /**
     * Returns the users company name.
     * @return str
     */
    public function getCompanyName() {
        return $this->_aData['company'];
    }

    /**
     * Returns the users street name.
     * @return str
     */
    public function getStreetName() {
        return $this->_aData['street'];
    }

    /**
     * Returns the users street number.
     * @return str
     */
    public function getStreetNumber() {
        return $this->_aData['street_number'];
    }
    /**
     * Returns the users zipcode.
     * @return str
     */
    public function getZipCode() {
        return $this->_aData['zipcode'];
    }

    /**
     * Returns the users city.
     * @return str
     */
    public function getCity() {
        return $this->_aData['city'];
    }

    /**
     * Returns the users CountryId.
     * @param   int $bReturnModel   If set to true a model is returned instead of an id.
     * @return str
     */
    public function getCountryId($bReturnModel = false) {
        if (true === $bReturnModel) {
            return \CountryModel::getInstance((int) $this->_aData['country_id']);
        }
        return $this->_aData['country_id'];
    }

    /**
     * Returns the users Contact Name.
     * @return str
     */
    public function getContactName() {
        return $this->_aData['contact_name'];
    }

    /**
     * Returns the users PhoneNumber.
     * @return str
     */
    public function getPhoneNumber() {
        return $this->_aData['phone'];
    }

    /**
     * Returns the users FaxNumber.
     * @return str
     */
    public function getFaxNumber() {
        return $this->_aData['fax'];
    }

    /**
     * Returns the users role id.
     * @return str
     */
    public function getUserroleIds() {
        return $this->_aAdditionalData['userroleIds'];
    }

    /**
     * Returns the users login hash.
     * @return str
     */
    public function getLoginHash() {
        return $this->_aData['login_hash'];
    }

    /**
     * Returns the status of the active flag.
     * @return int
     */
    public function getActiveFlag() {
        return $this->_aData['active'];
    }


    /**
     * Returns the users debitor account id.
     * @param   int $bReturnModel   If set to true a model is returned instead of an id.
     * @return str
     */
    public function getDebitorAccountId($bReturnModel = false) {
        if (true === $bReturnModel) {
            return \AccountModel::getInstance((int) $this->_aData['debitor_account_id']);
        }
        return $this->_aData['debitor_account_id'];
    }

    /**
     * Returns the users creditor account id.
     * @param   int $bReturnModel   If set to true a model is returned instead of an id.
     * @return str
     */
    public function getCreditorAccountId($bReturnModel = false) {
        if (true === $bReturnModel) {
            return \AccountModel::getInstance((int) $this->_aData['creditor_account_id']);
        }
        return $this->_aData['creditor_account_id'];
    }

    #################
    ## SET METHODS ##
    #################

    /**
     * Sets the users nick-name.
     * @param   str     $value  The users nick-name.
     * @return  bool
     */
    public function setNickName($value) {
        if (
            (isset($this->_aDataValidation['nick']) && preg_match($this->_aDataValidation['nick'], $value))
            || !isset($this->_aDataValidation['nick'])
        ) {
            $this->_aData['nick'] = $value;
            return true;
        }
        return false;
    }

    /**
     * Sets the users first name.
     * @param   str     $value  The users first name.
     * @return  bool
     */
    public function setFirstName($value) {
        if (
            (isset($this->_aDataValidation['first_name']) && preg_match($this->_aDataValidation['first_name'], $value))
            || !isset($this->_aDataValidation['first_name'])
        ) {
            $this->_aData['first_name'] = $value;
            return true;
        }
        return false;
    }


    /**
     * Sets the users last name.
     * @param   str     $value  The users last name.
     * @return  bool
     */
    public function setLastName($value) {
        if (
            (isset($this->_aDataValidation['last_name']) && preg_match($this->_aDataValidation['last_name'], $value))
            || !isset($this->_aDataValidation['last_name'])
        ) {
            $this->_aData['last_name'] = $value;
            return true;
        }
        return false;
    }

    /**
     * Sets the users company name.
     * @param   str     $value  The users company name.
     * @return  bool
     */
    public function setCompanyName($value) {
        if (
            (isset($this->_aDataValidation['company']) && preg_match($this->_aDataValidation['company'], $value))
            || !isset($this->_aDataValidation['company'])
        ) {
            $this->_aData['company'] = $value;
            return true;
        }
        return false;
    }

    /**
     * Sets the users street.
     * @param   str     $value  The users street name.
     * @return  bool
     */
    public function setStreetName($value) {
        if (
            (isset($this->_aDataValidation['street']) && preg_match($this->_aDataValidation['street'], $value))
            || !isset($this->_aDataValidation['street'])
        ) {
            $this->_aData['street'] = $value;
            return true;
        }
        return false;
    }

    /**
     * Sets the users street number.
     * @param   str     $value  The users street number.
     * @return  bool
     */
    public function setStreetNumber($value) {
        if (
            (isset($this->_aDataValidation['street_number']) && preg_match($this->_aDataValidation['street_number'], $value))
            || !isset($this->_aDataValidation['street_number'])
        ) {
            $this->_aData['street_number'] = $value;
            return true;
        }
        return false;
    }

    /**
     * Sets the users zipcode.
     * @param   str     $value  The users zipcode.
     * @return  bool
     */
    public function setZipCode($value) {
        if (
            (isset($this->_aDataValidation['zipcode']) && preg_match($this->_aDataValidation['zipcode'], $value))
            || !isset($this->_aDataValidation['zipcode'])
        ) {
            $this->_aData['zipcode'] = $value;
            return true;
        }
        return false;
    }

    /**
     * Sets the users city.
     * @param   str     $value  The users city.
     * @return  bool
     */
    public function setCity($value) {
        if (
            (isset($this->_aDataValidation['city']) && preg_match($this->_aDataValidation['city'], $value))
            || !isset($this->_aDataValidation['city'])
        ) {
            $this->_aData['city'] = $value;
            return true;
        }
        return false;
    }

    /**
     * Sets the users country id.
     * @param   int     $value  The users country id.
     * @return  bool
     */
    public function setCountryId($value) {
        if (
            (isset($this->_aDataValidation['country_id']) && preg_match($this->_aDataValidation['country_id'], $value))
            || !isset($this->_aDataValidation['country_id'])
        ) {
            $this->_aData['country_id'] = $value;
            return true;
        }
        return false;
    }

    /**
     * Sets the name of the users contact person.
     * @param   str     $value  The name of the users contact person.
     * @return  bool
     */
    public function setContactName($value) {
        if (
            (isset($this->_aDataValidation['contact_name']) && preg_match($this->_aDataValidation['contact_name'], $value))
            || !isset($this->_aDataValidation['contact_name'])
        ) {
            $this->_aData['contact_name'] = $value;
            return true;
        }
        return false;
    }


    /**
     * Sets the users telephone number.
     * @param   str     $value  The users telephone number.
     * @return  bool
     */
    public function setPhoneNumber($value) {
        if (
            (isset($this->_aDataValidation['phone']) && preg_match($this->_aDataValidation['phone'], $value))
            || !isset($this->_aDataValidation['phone'])
        ) {
            $this->_aData['phone'] = $value;
            return true;
        }
        return false;
    }

    /**
     * Sets the users fax number.
     * @param   str     $value  The users fax number.
     * @return  bool
     */
    public function setFaxNumber($value) {
        if (
            (isset($this->_aDataValidation['fax']) && preg_match($this->_aDataValidation['fax'], $value))
            || !isset($this->_aDataValidation['fax'])
        ) {
            $this->_aData['fax'] = $value;
            return true;
        }
        return false;
    }


    /**
     * Sets the users email.
     * @param   str $value  The users email.
     * @return  bool
     */
    public function setEmail($value) {
        if (
            (isset($this->_aDataValidation['email']) && preg_match($this->_aDataValidation['email'], $value))
            || !isset($this->_aDataValidation['email'])
        ) {
            /**
             * @todo: check if email already exists
             */

            $this->_aData['email'] = $value;
            return true;
        }
        return false;
    }

    /**
     * Sets the users role ids.
     * @param   mixed     $value  The users role.
     * @return  bool
     */
    public function setUserroleIds($value) {
        if (!is_array($value)) {
            $value  = (is_null($value)) ? array() : array($value);
        }

        if (count($value) === 0) {
            $this->_sError  = Mjoelnir_Message::getMessage(2011);
            return false;
        }

        $aValidatedValue    = array();
        foreach ($value as $iUserroleId) {
            if (
                (isset($this->_aDataValidation['user_role_id']) && preg_match($this->_aDataValidation['user_role_id'], $iUserroleId))
                || !isset($this->_aDataValidation['user_role_id'])
            ) {
                $aValidatedValue[] = $iUserroleId;
            }
            else {
                $this->_sError  = Mjoelnir_Message::getMessage(2021);
                return false;
            }
        }

        $this->_aAdditionalData['userroleIds']  = $aValidatedValue;

        return true;
    }


    /**
     * Sets the users password.
     * @param   str     $value  The users password.
     * @return  bool
     */
    public function setPassword($value) {
        if (
            (isset($this->_aDataValidation['password']) && preg_match($this->_aDataValidation['password'], $value))
            || !isset($this->_aDataValidation['password'])
        ) {
            $this->_aData['password'] = self::_crypt($value);
            return true;
        }
        return false;
    }

    /**
     * Sets the users active flage.
     * @param   int     $value  The flag value.
     * @return  bool
     */
    public function setActiveFlag($value) {
        if (
            (isset($this->_aDataValidation['active']) && preg_match($this->_aDataValidation['active'], $value))
            || !isset($this->_aDataValidation['active'])
        ) {
            $this->_aData['active'] = (int) $value;
            return true;
        }
        return false;
    }

    /**
     * Sets the users debitor account id.
     * @param   int     $value  The account id.
     * @return  bool
     */
    public function setDebitorAccountId($value) {
        if (
            (isset($this->_aDataValidation['debitor_account_id']) && preg_match($this->_aDataValidation['debitor_account_id'], $value))
            || !isset($this->_aDataValidation['debitor_account_id'])
        ) {
            $this->_aData['debitor_account_id'] = (int) $value;
            return true;
        }
        return false;
    }

    /**
     * Sets the users creditor account id.
     * @param   int     $value  The flag value.
     * @return  bool
     */
    public function setCreditorAccountId($value) {
        if (
            (isset($this->_aDataValidation['creditor_account_id']) && preg_match($this->_aDataValidation['creditor_account_id'], $value))
            || !isset($this->_aDataValidation['creditor_account_id'])
        ) {
            $this->_aData['creditor_account_id'] = (int) $value;
            return true;
        }
        return false;
    }
}

?>
