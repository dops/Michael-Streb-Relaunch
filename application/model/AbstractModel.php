<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AbstractModel
 *
 * @author td-office
 */
abstract class AbstractModel
{
    /**
     * An instance of a db layer.
     * @var Mjoelnir_Db
     */
    protected $_oDb  = null;


    /**
     * The table that holds the data for the model. Has to be set in child class.
     * @var string
     */
    protected static $_sTable   = null;

    /**
     * The data array contains the model data. Has to be set in child class.
     * @var type
     */
    protected $_aData    = array();

    /**
     * The unique id field is the field that holds the system-wide unique id for the model instance.
     * @var int
     */
    public static $_sUniqueIdField  = null;

    /**
     * Contains regular expression to validate data. If a value needs no validation, just don´t name it. Can be set in child class.
     * @var array
     */
    protected $_aDataValidation  = array();

    /**
     * Saves one error message to return it when AbstractModel::getError() is called.
     * @var str
     */
    protected $_sError   = '';

    /**
     * An instance of Topdeals_Log
     * @var Mjoelnir_Log
     */
    protected $_oLog = null;

    /**
     * Constructor.
     * @param   mixed   $aData   $data contains either a model id or an array with model data.
     */
    protected function __construct($aData) {
        $this->_oDb  = Mjoelnir_Db::getInstance();
        $this->_oLog = Mjoelnir_Log::getInstance();

        if (!is_null($aData)) {
            if (is_array($aData)) {
                $aOverflow  = array_diff_key($aData, $this->_aData);

                if (count($aOverflow) > 0) {
                    foreach ($aOverflow as $sOverflowKey) {
                        unset($aData[$sOverflowKey]);
                    }
                }

                $aTmp   = array_replace($this->_aData, $aData);
                if (!is_null($aTmp)) {
                    $this->_aData    = $aTmp;
                }
            }
            else {
                $this->_readData($aData);
            }
        }
    }

    /**
     * Returns an instance of the model. If the model has already been loaded, a reference to this object will be returned. Otherwise a new instance will be created and returned.
     * @param   mixed                   $mData   $data could be either an id or an array with clearing data.
     * @return  AccountCategoryModel
     */
    public static function getInstance($mData = null) {
        $sCalledClass   = get_called_class();

        if (is_null($mData)) {
            return new $sCalledClass($mData);
        }
        else {
            $iInstanceId    = (is_array($mData)) ? $mData[$sCalledClass::$_sUniqueIdField]    : $mData;

            if (!isset($sCalledClass::$_instances[$iInstanceId])) {
                $oClass                                     = new $sCalledClass($mData);
                $sCalledClass::$_instances[$iInstanceId]    = $oClass;
            }

            return $sCalledClass::$_instances[$iInstanceId];
        }
    }

    /**
     * Reads the data from the database.
     * @param   int $iId The id of the user to fetch the data fro.
     */
    protected function _readData($iId) {
        $sCalledClass   = get_called_class();

        $oSql = $this->_oDb->select()
                ->from($sCalledClass::$_sTable, array_keys($this->_aData))
                ->where($sCalledClass::$_sUniqueIdField . ' = ?', (int) $iId);
        $oRes = $oSql->query();
        $aData = $oRes->fetch();

        if (is_array($this->_aData) && count($this->_aData) > 0 && is_array($aData) && count($aData) > 0) {
            $this->_aData    = array_merge($this->_aData, $aData);

            if (property_exists($sCalledClass, '_aAdditionalData') && method_exists($sCalledClass, '_saveAdditionalData')) {
                $this->_readAdditionalData();
            }

            return true;
        }

        return false;
    }

    /**
     * Returns all records. If a limit and a start value is given, the result will be limited to this range.
     * @param   int     $iStart      The position to start reading from.
     * @param   int     $iLimit      The max number of results to return.
     * @param   array   $aFilter     An array naming multiple fields and values to filter the result. Always using equal matching.
     * @param   array   $aOrder      An array naming multiple fields and values to order the result. The key names the field and the value the direction.
     * @return  array
     */
    public static function getAll($iStart = null, $iLimit = null, $aFilter = array(), $aOrder = array()) {
        $oDb            = Mjoelnir_Db::getInstance();
        $sCalledClass   = get_called_class();

        $oSelect    = $oDb->select()->from($sCalledClass::$_sTable, new Zend_Db_Expr('SQL_CALC_FOUND_ROWS *'));

        if (count($aFilter) > 0) {
            self::_getFilter($oSelect, $aFilter);
        }

        if (count($aOrder) > 0) {
            foreach ($aOrder as $field => $direction) {
                $oSelect->order($field . ' ' . $direction);
            }
        }

        if (!is_null($iLimit) && is_null($iStart))    { $oSelect->limit($iLimit); }
        if (is_null($iLimit) && is_null($iStart))     { $oSelect->limit($iStart . ', ' . $iLimit); }

        $oRes = $oSelect->query();

        $aReturn            = array('rows' => array(), 'count' => 0);
        $aTempResult        = $oRes->fetchAll();
        $aTempCount         = $oDb->select()->from(null, new Zend_Db_Expr('FOUND_ROWS() AS count'))->query()->fetchAll();
        $aReturn['count']   = $aTempCount[0]['count'];

        foreach ($aTempResult as $aData) {
            $oInstance                              = $sCalledClass::getInstance($aData);
            $aReturn['rows'][$oInstance->getId()]   = $oInstance;
        }

        return $aReturn;
    }

    /**
     * Applies different filters to the database request.
     * @param   Zend_Db_Select  $oSelect    A Zend select object.
     * @param   array           $aParams    A array containing the fields and the comparision values.
     */
    private function _getFilter($oSelect, $aParams) {
        foreach ($aParams as $sFieldName => $aConditions) {
            foreach ($aConditions as $sComparisonOperator => $mValue) {
                // Equal
                if ($sComparisonOperator === 'eq') {
                    $oSelect->where($sFieldName . ' = ?', $mValue);
                }

                // Not equal
                if ($sComparisonOperator === 'neq') {
                    $oSelect->where($sFieldName . ' != ?', $mValue);
                }

                // Like
                if ($sComparisonOperator === 'like') {
                    $oSelect->where($sFieldName . ' LIKE ?', $mValue);
                }

                // Lighter
                if ($sComparisonOperator === 'l') {
                    $oSelect->where($sFieldName . ' < ?', $mValue);
                }

                // Lighter than
                if ($sComparisonOperator === 'lt') {
                    $oSelect->where($sFieldName . ' <= ?', $mValue);
                }

                // Greater
                if ($sComparisonOperator === 'g') {
                    $oSelect->where($sFieldName . ' > ?', $mValue);
                }

                // Greater than
                if ($sComparisonOperator === 'gt') {
                    $oSelect->where($sFieldName . ' >= ?', $mValue);
                }

                // Between
                if ($sComparisonOperator === 'bt') {
                    $oSelect->where($sFieldName . ' ?', new Zend_Db_Expr('BETWEEN ' . $mValue[0] . ' AND ' . $mValue[1]));
                }

                // In
                if ($sComparisonOperator === 'in') {
                    $oSelect->where($sFieldName . ' IN (?)', implode(', ', array_map(function($tmp) { return '"' . $tmp .'"'; }, $mValue)));
                }
            }
        }
    }

    /**
     * Saves the permission data.
     * @return bool
     */
    public function save() {
        $sCalledClass   = get_called_class();
        $iNow           = time();
        $aInsertData    = array();
        $aUpdateData    = array();
        $iId            = $this->_aData[$sCalledClass::$_sUniqueIdField];

        foreach ($this->_aData as $sFieldname => $mValue) {
            switch ($sFieldname) {
                case $sCalledClass::$_sUniqueIdField:
                    if (is_null($mValue)) {
                       $aInsertData[]  = $sFieldname . ' = NULL';
                    }
                    else {
                        $aInsertData[]  = $sFieldname . ' = ' . $this->_oDb->quote($mValue);
                    }
                    break;

                case 'time_insert':
                    if (is_null($this->_aData[$sCalledClass::$_sUniqueIdField])) {
                        $this->_aData['time_insert']    = $iNow;
                    }
                    $aInsertData[]  = $sFieldname . ' = ' . $this->_oDb->quote($iNow);
                    break;

                case 'time_update':
                    if (is_null($this->_aData[$sCalledClass::$_sUniqueIdField])) {
                        $this->_aData['time_update']    = $iNow;
                    }
                    $aUpdateData[]  = $sFieldname . ' = ' . $this->_oDb->quote($iNow);
                    break;

                default:
                    if (is_null($mValue)) {
                        if (!$this->_noInserWithNull($sFieldname)) {
                            $aInsertData[]  = $sFieldname . ' = NULL';
                            $aUpdateData[]  = $sFieldname . ' = NULL';
                        }
                    }
                    else {
                        $aInsertData[]  = $sFieldname . ' = ' . $this->_oDb->quote($mValue);
                        $aUpdateData[]  = $sFieldname . ' = ' . $this->_oDb->quote($mValue);
                    }
            }
        }

        $sSql   = '
            INSERT INTO
                ' . $sCalledClass::$_sTable . '
            SET
                ' . implode(', ', $aInsertData) . '
            ON DUPLICATE KEY UPDATE
                ' . implode(', ', $aUpdateData) . '
        ';
        try {
            $this->_oDb->query($sSql);
        }
        catch (Zend_Db_Statement_Exception $e) {
            $this->_oLog->log($e->getMessage());
            $this->_oLog->log($sSql);
            $this->_oLog->log($e->getTraceAsString());
        }

        if (is_null($iId)) {
            $this->_aData[$sCalledClass::$_sUniqueIdField]  = $this->_oDb->lastInsertId();
        }

        // Save additional data.
        if (property_exists($sCalledClass, '_aAdditionalData') && method_exists($sCalledClass, '_saveAdditionalData')) {
            $this->_saveAdditionalData();
        }

        return true;
    }

    /**
     * Returns true or false depending on if a field might be set ot null or not.
     * @param   str $sFieldname The name of the field to check.
     * @return  bool
     */
    protected function _noInserWithNull($sFieldname) {
        if (isset($this->_aNoInsertWithNull) && array_search($sFieldname, $this->_aNoInsertWithNull) !== false) {
            return true;
        }
        return false;
    }

    /**
     * Checks if the deletion of a model is allowed. Has to return true or false.
     */
    abstract protected static function _isDeleteAllowed($iId);

    /**
     * Deletes the category.
     * @return  bool
     */
    public static function delete($iId) {
        $oDb                = Mjoelnir_Db::getInstance();
        $sCalledClass       = get_called_class();
        $bIsDeleteAllowed   = $sCalledClass::_isDeleteAllowed($iId);

        if (true === $bIsDeleteAllowed) {
            $sSql   = 'DELETE FROM ' . $sCalledClass::$_sTable . ' WHERE ' . $sCalledClass::$_sUniqueIdField . ' = ' . (int) $iId;
            $oStmt  = $oDb->query($sSql);

            if ($oStmt->rowCount() === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validates the given value, and return true if it is, or flase if it´s not.
     * @param   str     $sName   The name of the value to validate.
     * @param   mixed   $mValue  The value to validate.
     * @return  bool
     */
    public function valueIsValid($sName, $mValue) {
        if (
            (isset($this->_aDataValidation[$sName]) && preg_match($this->_aDataValidation[$sName], $mValue))
            || !isset($this->_aDataValidation[$sName])
        ) {
            return true;
        }
        return false;
    }

    /**
     * Searches for a value translation. If found, the translation will be removed, otherwise just the original value is given back.
     * @param   str     $sParamName The name of the parameter.
     * @param   mixed   The parameter value. It can be an integer or a string.
     * @return  mixed
     */
    protected function _translateValue($sParamName, $mValue) {
        if (!is_array($mValue) && property_exists($this, '_aValueTranslation')) {
            if (isset($this->_aValueTranslation[$sParamName]) && isset($this->_aValueTranslation[$sParamName][$mValue])) {
                return $this->_aValueTranslation[$sParamName][$mValue];
            }
        }

        return $mValue;
    }


    #################
    ## GET METHODS ##
    #################

    /**
     * Returns the user role id.
     * @return int
     */
    public function getId() {
        $sCalledClass   = get_called_class();
        return (int) $this->_aData[$sCalledClass::$_sUniqueIdField];
    }

    /**
     * Returns the insert timestamp.
     * @return  int
     */
    public function getTimeInsert() {
        return (int) $this->_aData['time_insert'];
    }

    /**
     * Returns the update timestamp.
     * @return  int
     */
    public function getTimeUpdate() {
        return (int) $this->_aData['time_update'];
    }

    /**
     * Returns a saved error message.
     * @return type
     */
    public function getError() {
        return $this->_sError;
    }
}

?>
