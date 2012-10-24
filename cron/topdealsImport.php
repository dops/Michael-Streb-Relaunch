<?php

/**
 * Set parameters needed for cron execution
 */
$_SERVER['APPLICATION_ENV'] = $argv[1];
$pathInfo                   = pathinfo(__FILE__);
$_SERVER['DOCUMENT_ROOT']   = $pathInfo['dirname'];

/**
 * Load config
 */
include dirname (__FILE__).'/../config/config.php';

/**
 * Load autoloader
 */
set_include_path(get_include_path() . ':' . PATH_LIBRARY);
include PATH_LIBRARY . '/Autoloader.php';

class TopdealsImport
{
    /**
     * A db instance with connection to accounting db.
     * @var Mjoelnir_Db
     */
    protected $_dbAccounting  = null;

    /**
     * A db instance with connection to topdeals db.
     * @var Mjoelnir_Db
     */
    protected $_dbTopdeals  = null;

    /**
     * A log instance.
     * @var Mjoelnir_Log
     */
    protected $_log = null;


    /**
     * Maps the source tables to teh destination tables.
     * @var array
     */
    protected $_tableMapping    = array(
        Db_Topdeals_Config::TABLE_COUNTRY       => Db_Accounting_Config::TABLE_COUNTRY,
        Db_Topdeals_Config::TABLE_USER          => Db_Accounting_Config::TABLE_USER,
        Db_Topdeals_Config::TABLE_ITEM          => Db_Accounting_Config::TABLE_ITEM,
        Db_Topdeals_Config::TABLE_COUPON        => Db_Accounting_Config::TABLE_COUPON,
    );

    /**
     * Maps the source fields to the destination fields.
     * @var array
     */
    protected $_fieldMapping    = array(
        Db_Topdeals_Config::TABLE_COUNTRY   => array(
            'country_id'    => 'country_id',
            'language_id'   => 'language_id',
            'country'       => 'name',
            'time_insert'   => 'time_insert',
            'time_update'   => 'time_update',
        ),
        Db_Topdeals_Config::TABLE_USER  => array(
            'id'            => 'user_id',
            'nick'          => 'nick',
            'company'       => 'company',
            'first_name'    => 'first_name',
            'name'          => 'last_name',
            'address'       => 'street',
            'zip'           => 'zipcode',
            'city'          => 'city',
            'country'       => 'country_id',
            'phone'         => 'phone',
            'email'         => 'email',
            'password'      => 'password',
            'hash'          => 'hash',
            'login_hash'    => 'login_hash',
            'reg_date'      => 'time_insert',
            'time_update'   => 'time_update',
        ),
        Db_Topdeals_Config::TABLE_ITEM  => array(
            'id'                => 'item_id',
            'user'              => 'seller_user_id',
            'title'             => 'title',
            'commercial_model'  => 'commercial_model',
            'bn_only'           => 'is_buy_now',
            'sales_agent_id'    => 'sales_agent_user_id',
            'time_insert'       => 'time_insert',
            'time_update'       => 'time_update',
        ),
        Db_Topdeals_Config::TABLE_COUPON    => array(),
    );

    /**
     * Default values to set for not legal ones.
     * @var array
     */
    protected $_defaultFieldValues  = array(
        'country'   => 83,
    );


    protected $_additionalFieldValues   = array();

    /**
     * Conditions to reduce source data.
     * @var array
     */
    protected $_selectConditions    = array(
        Db_Topdeals_Config::TABLE_USER  => array(
            'accounttype LIKE "seller" OR user_type LIKE "admin"',
        ),
    );

    /**
     * Sometimes field definitions arent´t realy good. Because of that the values from the source table might not fit to the destination tabel. Therefor the values have to be
     * converted.
     * @var array
     */
    protected $_convertValues   = array(
        Db_Topdeals_Config::TABLE_ITEM  => array(
            'bn_only'   => array('y' => 1, 'n' => 0),
        ),
    );


    /**
     * Constructor.
     */
    public function __construct() {
        set_time_limit(0);

        $this->_dbAccounting    = Mjoelnir_Db::getInstance();
        $this->_dbTopdeals      = Mjoelnir_Db::getInstance('Topdeals');
        $this->_log             = Mjoelnir_Log::getInstance();

        $this->_process();
    }

    /**
     * Calls all function to copy the data from topdeals to accounting.
     * @return bool
     */
    protected function _process() {
        $iStartTime = microtime(true);
        $this->_log->log('Start import process.');
        $iExecutionTime = time();
        $iLastExecTime  = $this->_getLastExecTime();

        foreach ($this->_fieldMapping as $table => $fields) {
            $this->_update($table, $iLastExecTime);
        }

        $iEndTime = microtime(true);
        $this->_log->log('Finished import process. ' . ($iEndTime - $iStartTime) . ' seconds needed.');

        $this->_setLastExecTime($iExecutionTime);

        return true;
    }

    /**
     * Reads the timestamp from log file.
     * @return str
     */
    protected function _getLastExecTime() {
        if (file_exists(PATH_LOG . 'topdealsImportLastExecTime.txt')) {
            return file_get_contents(PATH_LOG . 'topdealsImportLastExecTime.txt');
        }

        return 0;
    }

    /**
     * Saves the last execution time to log file.
     * @param   int $time   The timestamp of the last execution.
     * @return  bool
     */
    protected function _setLastExecTime($time) {
        file_put_contents(PATH_LOG . 'topdealsImportLastExecTime.txt', $time);
        return true;
    }

    /**
     * Reads new and updated (since last execution) data from source table and inserts it to the corresponding desitination table.
     * @param   str     $sSourceTable   The name of the source table.
     * @param   int     $lastExecTime   The time of the last update process.
     * @return  bool
     */
    protected function _update($sSourceTable, $lastExecTime) {
        $iStartTime = microtime(true);
        $this->_log->log('Start update of table "' . $sSourceTable . '".');

        $sMethodName    = '_update' . ucfirst(strtolower($sSourceTable));
        if (method_exists($this, $sMethodName)) {
            $this->$sMethodName($lastExecTime);
        }
        else {
            $select = $this->_dbTopdeals->select()
                ->from($sSourceTable, array_keys($this->_fieldMapping[$sSourceTable]))
                ->where($this->_dbTopdeals->quoteInto('time_insert >= ?', (int) $lastExecTime) . ' OR ' . $this->_dbTopdeals->quoteInto('time_update >= ?', (int) $lastExecTime));
            if (isset($this->_selectConditions[$sSourceTable])) {
                foreach ($this->_selectConditions[$sSourceTable] as $sCondition) {
                    $select->where($sCondition);
                }
            }
            $stmtTopdeals   = $select->query();

            while ($aDataTopdeals = $stmtTopdeals->fetch()) {
                $aDataTopdeals  = $this->_validateValues($sSourceTable, $aDataTopdeals);

                if (!isset($stmtAccounting)) {
                    $aUpdateFields  = array();
                    foreach ($aDataTopdeals as $key => $value) {
                        if (($key !== 'password') && ($key !== 'login_hash') && ($key !== 'hash')) { // do not update passwords when updating a row
                            $aUpdateFields[]  = $this->_fieldMapping[$sSourceTable][$key] . ' = ?';
                        }
                    }
                    reset($aDataTopdeals);

                    $aInsertFields  = $this->_fieldMapping[$sSourceTable];
                    $aInsertValues  = array_fill(0, count($aDataTopdeals), '?');
                    if (isset($this->_additionalFieldValues[$sSourceTable]) && count($this->_additionalFieldValues[$sSourceTable]) > 0) {
                        $aInsertFields    = array_merge($aInsertFields, array_keys($this->_additionalFieldValues[$sSourceTable]));
                        $aInsertValues    = array_merge($aInsertValues, $this->_additionalFieldValues[$sSourceTable]);
                    }

                    $sql  = '
                        INSERT INTO
                            ' . $this->_tableMapping[$sSourceTable] . '
                            (' . implode(', ', $aInsertFields) . ')
                        VALUES
                            (' . implode(', ', $aInsertValues) . ')
                        ON DUPLICATE KEY UPDATE
                            ' . implode(', ', $aUpdateFields);
                    $stmtAccounting   = new Zend_Db_Statement_Mysqli($this->_dbAccounting, $sql);
                }

                try {
                    $stmtAccounting->execute($this->_duplicateArray($aDataTopdeals));
                } catch (Zend_Db_Statement_Mysqli_Exception $e) {
                    $message    = 'The following error occured while updating the accounting db:' . "\n\n";
                    $message    .= $e->getMessage() . "\n\n";
                    $message    .= 'SQL: ' . $sql . "\n\n";
                    $message    .= 'Original Data: ' . var_export($aDataTopdeals, true);
                    $message    .= 'Insert Data: ' . var_export($this->_duplicateArray($aDataTopdeals), true);

                    mail(APPLICATION_LOG_MAIL, 'ERROR while updating accounting db', $message);

                    $this->_log->log('Finished import process with error. E-mail sent to ' . APPLICATION_LOG_MAIL . '.');
                    $this->_log->log($e->getMessage());
                    $this->_log->log('SQL: ' . $sql);
                    $this->_log->log('Original Data: ' . var_export($aDataTopdeals, true));
                    $this->_log->log('Insert Data: ' . var_export($this->_duplicateArray($aDataTopdeals), true));

                    die();
                }
            }
        }

        $iEndTime = microtime(true);
        $this->_log->log('Finished update of table "' . $sSourceTable . '". ' . ($iEndTime - $iStartTime) . ' seconds needed.');

        return true;
    }

    protected function _updateUsers($lastExecTime) {
        // Select all new and updated users from topdeals db
        $oSelect        = $this->_dbTopdeals->select()
                ->from(Db_Topdeals_Config::TABLE_USER, array_keys($this->_fieldMapping[Db_Topdeals_Config::TABLE_USER]));
        if (isset($this->_selectConditions[Db_Topdeals_Config::TABLE_USER])) {
            foreach ($this->_selectConditions[Db_Topdeals_Config::TABLE_USER] as $sCondition) {
                $oSelect->where($sCondition);
            }
        }
        $oSelect->where('time_insert >= ' . $lastExecTime . ' OR time_update >= ' . $lastExecTime);
        $oStmt          = $this->_dbTopdeals->query($oSelect);
        $aNewAndUpdated = $oStmt->fetchAll();

        $this->_log->log('Fetched ' . count($aNewAndUpdated) . ' user to insert or update.');

        // Fetch all existing user without debitor and creditor accounts
        $oSelect        = $this->_dbAccounting->select()
                ->from(Db_Accounting_Config::TABLE_USER, array('user_id', 'debitor_account_id', 'creditor_account_id'));
        $oStmt          = $this->_dbAccounting->query($oSelect);
        $aTmpExisting   = $oStmt->fetchAll();
        $aExisting      = array();
        foreach ($aTmpExisting as $key => $aUser) {
            $aExisting[$aUser['user_id']]   = $aUser;
            unset($aTmpExisting[$key]);
        }
        unset($aTmpExisting);

        // Itterate over new and updated users
        foreach ($aNewAndUpdated as $aUser) {
            $iDebitorAccountId  = null;
            $iCreditorAccountId = null;

            // Create debitor account if missing
            if ((isset($aExisting[$aUser['id']]) && is_null($aExisting[$aUser['id']]['debitor_account_id']))
                    || !isset($aExisting[$aUser['id']])
            ) {
                $aMaxAccountNumbers = $this->_getMaxAccountNumbers();

                $iNextDebitorAccountNumber  = $aMaxAccountNumbers['debitor'] + 1;
                $oAccount   = AccountModel::getInstance();
                $oAccount->setAccountCategoryId(10);
                $oAccount->setAccountNumber($iNextDebitorAccountNumber);
                $oAccount->setName($aUser['company']);
                $oAccount->setDescription($aUser['company']);
                $oAccount->save();

                $iDebitorAccountId  = $oAccount->getId();

                $this->_log->log('New debitor account (id: ' . $iDebitorAccountId . ') created for user ' . $aUser['email'] . '.');
            }

            // Create creditor account if missing
            if ((isset($aExisting[$aUser['id']]) && is_null($aExisting[$aUser['id']]['creditor_account_id']))
                    || !isset($aExisting[$aUser['id']])
            ) {

                $iNextCreditorAccountNumber = $aMaxAccountNumbers['creditor'] + 1;
                $oAccount   = AccountModel::getInstance();
                $oAccount->setAccountCategoryId(10);
                $oAccount->setAccountNumber($iNextCreditorAccountNumber);
                $oAccount->setName($aUser['company']);
                $oAccount->setDescription($aUser['company']);
                $oAccount->save();

                $iCreditorAccountId  = $oAccount->getId();

                $this->_log->log('New creditor account (id: ' . $iCreditorAccountId . ') created for user ' . $aUser['email'] . '.');
            }

            // Insert or update user
            $aUser          = $this->_validateValues(Db_Topdeals_Config::TABLE_USER, $aUser);

            $aInsertFields  = array_merge($this->_fieldMapping[Db_Topdeals_Config::TABLE_USER], array('debitor_account_id', 'creditor_account_id'));
            $aInsertData    = array_merge(array_combine($this->_fieldMapping[Db_Topdeals_Config::TABLE_USER], $aUser), array('debitor_account_id' => $iDebitorAccountId, 'creditor_account_id' => $iCreditorAccountId));

            if (isset($this->_additionalFieldValues[Db_Topdeals_Config::TABLE_USER]) && count($this->_additionalFieldValues[Db_Topdeals_Config::TABLE_USER]) > 0) {
                $aInsertFields  = array_merge($aInsertFields, array_keys($this->_additionalFieldValues[Db_Topdeals_Config::TABLE_USER]));
                $aInsertData    = array_merge($aInsertData, $this->_additionalFieldValues[Db_Topdeals_Config::TABLE_USER]);
            }

            $aInsertData  = $this->_validateValues(Db_Topdeals_Config::TABLE_USER, $aInsertData);

            $aFieldUpdates  = array();
            foreach ($aInsertFields as $sField) {
                switch ($sField) {
                    case 'password':
                    case 'login_hash':
                    case 'hash':
                        // skip these fields to prevent update
                        break;
                    case 'debitor_account_id':
                        if (!is_null($iDebitorAccountId)) {
                            $aFieldUpdates[]    = 'debitor_account_id = IF(debitor_account_id IS NULL, ' . $iDebitorAccountId . ', debitor_account_id)';
                        }
                        break;
                    case 'creditor_account_id':
                        if (!is_null($iCreditorAccountId)) {
                            $aFieldUpdates[]    = 'creditor_account_id = IF(creditor_account_id IS NULL, ' . $iCreditorAccountId . ', creditor_account_id)';
                        }
                        break;
                    default:    $aFieldUpdates[]    = $sField . ' =  ' . $this->_dbAccounting->quote($aInsertData[$sField]);
                }
            }

            // Insert user
            $sSql   = '
                INSERT INTO
                    ' . Db_Accounting_Config::TABLE_USER . '
                (
                    ' . implode(', ', $aInsertFields) . '
                )
                VALUES (
                    ' . implode(', ', array_map(function($value) { return Mjoelnir_Db::getInstance()->quote((mb_detect_encoding($value, 'UTF-8') == 'UTF-8') ? $value : utf8_encode($value)); }, $aInsertData)) . '
                )
                ON DUPLICATE KEY UPDATE';
            $sSql   .= ' ' . implode(', ', $aFieldUpdates);
            $oStmt  = $this->_dbAccounting->query($sSql);

            // Isert user user-role
            // Set admin user role
            if (in_array($aInsertData['email'], array('michael.streb@topdeals.de'))) {
                $iUserroleId    = 1;
            }
            else if (in_array($aInsertData['email'], array('kay.steuwe@topdeals.de'))) {
                $iUserroleId    = 7;
            }
            else {
                $iUserroleId    = 8;
            }
            $sSql   = '
                INSERT INTO
                    ' . Db_Accounting_Config::TABLE_USER_USER_ROLE . '
                (user_id, user_role_id, time_insert)
                VALUES (
                    ' . $aUser['id'] . ', ' . $iUserroleId . ', UNIX_TIMESTAMP()
                )
                ON DUPLICATE KEY UPDATE
                    user_user_role_id = user_user_role_id';
            $oStmt  = $this->_dbAccounting->query($sSql);
        }
    }


    protected function _updateItems($iLastExecTime) {
        $sSql   = '
            INSERT INTO
                accounting.item
            (
                item_id, seller_user_id, title, commercial_model, commission_value, price_min, price_min_intern, is_buy_now, sales_agent_user_id, time_insert, time_update
            )
            SELECT
                i.id,
                i.user,
                i.title,
                i.commercial_model,
                i.commission_value,
                aid.price_min,
                aid.price_min_intern,
                IF(i.bn_only = "y", 1, 0) as bn_only,
                i.sales_agent_id,
                i.time_insert,
                i.time_update
            FROM
                topdeals.items AS i
            LEFT JOIN
                topdeals.aha_items_data AS aid
            ON
                i.id = aid.item_id
            WHERE
                i.time_insert >= ' . $iLastExecTime . '
            OR
                i.time_update >= ' . $iLastExecTime . '
            ON DUPLICATE KEY UPDATE
                accounting.item.item_id = i.id,
                accounting.item.seller_user_id = i.user,
                accounting.item.title = i.title,
                accounting.item.commercial_model = i.commercial_model,
                accounting.item.commission_value = i.commission_value,
                accounting.item.price_min = aid.price_min,
                accounting.item.price_min_intern = aid.price_min_intern,
                accounting.item.is_buy_now = IF(i.bn_only = "y", 1, 0),
                accounting.item.sales_agent_user_id = i.sales_agent_id,
                accounting.item.time_insert = i.time_insert,
                accounting.item.time_update = i.time_update
        ';

        $this->_dbTopdeals->query($sSql);
    }


    /**
     * Returns the maximum account numbers for debitor and creditor accounts, identified by the account number ranges.
     * @return array
     */
    protected function _getMaxAccountNumbers() {
        $oSelectDebitor     = $this->_dbAccounting->select()
                ->from(Db_Accounting_Config::TABLE_ACCOUNT, array(new Zend_Db_Expr('MAX(account_number)')))
                ->where(new Zend_Db_Expr('account_number BETWEEN ' . DEBITOR_ACCOUNT_MIN_VALUE . ' AND ' . DEBITOR_ACCOUNT_MAX_VALUE));
        $oSelectCreditor    = $this->_dbAccounting->select()
                ->from(Db_Accounting_Config::TABLE_ACCOUNT, array(new Zend_Db_Expr('MAX(account_number)')))
                ->where(new Zend_Db_Expr('account_number BETWEEN ' . CREDITOR_ACCOUNT_MIN_VALUE . ' AND ' . CREDITOR_ACCOUNT_MAX_VALUE));
        $oSelect            = $this->_dbAccounting->select()
                ->from(array(), array('debitor' => $oSelectDebitor, 'creditor' => $oSelectCreditor));
        $oStmt              = $this->_dbAccounting->query($oSelect);
        $aMaxAccountNumbers = $oStmt->fetchAll();

        if (is_null($aMaxAccountNumbers[0]['debitor'])) {
            $aMaxAccountNumbers[0]['debitor']   = DEBITOR_ACCOUNT_MIN_VALUE - 1;
        }

        if (is_null($aMaxAccountNumbers[0]['creditor'])) {
            $aMaxAccountNumbers[0]['creditor']  = CREDITOR_ACCOUNT_MIN_VALUE - 1;
        }

        return $aMaxAccountNumbers[0];
    }


    protected function _updateCoupon($lastExecTime) {
        $sql = '
            INSERT INTO
                accounting.coupon
            (
                coupon_id, item_id, code, winners_id, seller_user_id, customer_user_id, price, valid_from, valid_to, time_paid, time_redemption, time_reversal, time_insert, time_update
            )
            SELECT
                td_c.id,
                td_c.item_id,
                td_c.code,
                td_c.winners_id,
                td_c.user_id,
                td_w.winner,
                td_w.bid,
                td_c.valid_from,
                td_c.valid_to,
                td_is.paid_time AS time_paid,
                UNIX_TIMESTAMP(td_c.redemption) AS time_redemption,
                UNIX_TIMESTAMP(td_w.reversal) AS time_reversal,
                IF(td_c.time_insert > 0, td_c.time_insert, UNIX_TIMESTAMP(td_c.creation)) AS time_insert,
                td_c.time_update
            FROM
                topdeals.coupon AS td_c
            LEFT JOIN
                topdeals.winners AS td_w
            ON
                td_c.winners_id = td_w.id
            LEFT JOIN
                topdeals.items_session AS td_is
            ON
                td_w.is_id = td_is.is_id
            WHERE
                td_c.winners_id IS NOT NULL
            AND
                (
                    (
                        ( td_is.paid_time IS NOT NULL AND td_is.paid_time >=  ' . (int) $lastExecTime . ' )
                    OR
                        ( td_c.redemption IS NOT NULL AND td_c.redemption >=  ' . (int) $lastExecTime . ' )
                    OR
                        ( td_w.reversal IS NOT NULL AND UNIX_TIMESTAMP(td_w.reversal) >=  ' . (int) $lastExecTime . ' )
                    )
                OR
                    (
                        ( td_is.paid_time IS NOT NULL AND td_c.redemption IS NOT NULL AND td_w.reversal IS NOT NULL )
                    AND
                        ( td_c.time_update IS NOT NULL AND td_c.time_update >= 0 )

                    )
                )
            ON DUPLICATE KEY UPDATE
                accounting.coupon.item_id = td_c.item_id,
                accounting.coupon.code = td_c.code,
                accounting.coupon.winners_id = td_c.winners_id,
                accounting.coupon.seller_user_id = td_c.user_id,
                accounting.coupon.customer_user_id = td_w.winner,
                accounting.coupon.price = td_w.bid,
                accounting.coupon.valid_from = td_c.valid_from,
                accounting.coupon.valid_to = td_c.valid_to,
                accounting.coupon.time_paid = td_is.paid_time,
                accounting.coupon.time_redemption = UNIX_TIMESTAMP(td_c.redemption),
                accounting.coupon.time_reversal = UNIX_TIMESTAMP(td_w.reversal),
                accounting.coupon.time_update = td_c.time_update
        ';
        $this->_dbTopdeals->query($sql);
    }

        /**
     * Creates an array with double size of the original and fills it with teh original values twice.
     * @param   array   $array  The array to double.
     * @return  array
     */
    protected function _duplicateArray($array) {
        $return         = array();
        $iNumElements   = count($array);
        $i              = 0;

        foreach ($array as $key => $value) {
            $return[$i]                 = $value;
			if (($key !== 'password') && ($key !== 'login_hash') && ($key !== 'hash')) {
				$return[$i + $iNumElements] = $value;
			}
            $i++;
        }

        ksort($return);

        return $return;
    }

    /**
     * Validates teh values of an array and sets them to default values if nessessary.
     * @param   str     $sSourceTable   The name of the source table.
     * @param   array   $aDataTopdeals  The array to be validated.
     * @return  array
     */
    protected function _validateValues($sSourceTable, $aDataTopdeals) {
        foreach ($aDataTopdeals as $key => &$value) {
            // Set default values
            if ($sSourceTable == 'users' && $key == 'country' && $value == 0) {
                $value  = $this->_defaultFieldValues[$key];
            }

            // Convert values
            if (
                    isset ($this->_convertValues[$sSourceTable])
                    && isset ($this->_convertValues[$sSourceTable][$key])
                    && isset ($this->_convertValues[$sSourceTable][$key][$value])
            ) {
                $value  = $this->_convertValues[$sSourceTable][$key][$value];
            }
        }

        return $aDataTopdeals;
    }
}

new TopdealsImport();

?>
