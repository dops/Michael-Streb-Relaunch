<?php

class Mjoelnir_Db extends Zend_Db
{
    /**
     * A singleton instance of Db.
     * @var array
     */
    public static $_instances;

    /**
     * A db config.
     * @var Db_Accounting
     */
    protected $_config  = null;

    /**
     * Link ids for different connactions.
     * @var array
     */
    protected $_linkIds = array();

    /**
     * Last result.
     * @var Result-ID
     */
    protected $_result;


  /**
   * Der Konstruktor nimmt die Verbindungsdaten fuer die Gewuenschte Datenbank entgegen.
   *
   * @param string $server
   * @param string $user
   * @param string $pass
   * @param string $db
   */
    private function __construct($dbName) {
        $configClassName    = 'Db_' . ucfirst(strtolower($dbName)) . '_Config';
        $this->_config  = new $configClassName();

        $this->connect($this->_config->getDefaultType());
    }


    /**
     * liefert das datenbank-Objekt zu dem angeforderten $account zur�ck.
     * laedt selbst�ndig die accounts-konfiguration.
     * es werden keine redundaten verbindungen aufgebaut.
     *
     * @param string $account
     * @return object
     */
    public static function getInstance($dbName = null)
    {
        $dbName = (is_null($dbName))    ? DEFAULT_DB    : $dbName;
        if (!isset(self::$_instances[$dbName])) {
            self::$_instances[$dbName]   = new Mjoelnir_Db($dbName);
        }

        return self::$_instances[$dbName];
    }

    /**
     * Prueft ob bereits eine Verdindung zur Datenbank besteht und baut wenn noetig eine auf.
     *
     * @return bool
     */
    public function connect($sType) {

    	if (!$this->connected($sType)) {
    		$this->_config->selectServer ($sType);
    		$this->_linkIds[$sType] = new Zend_Db_Adapter_Mysqli (
    			array (
    				'host' => $this->_config->getHost($sType),
    				'username' => $this->_config->getUser($sType),
    				'password' => $this->_config->getPass($sType),
    				'dbname' => $this->_config->getDb($sType),
    				'driver_options' => array(MYSQLI_INIT_COMMAND => 'SET NAMES UTF8;')
    			)
    		);
    	}

        return true;
    }

  	/**
     * Gibt die aktuelle Link-ID zur pruefung zurueck.
     *
     * @return string
     */
	public function connected($type) {
		return isset($this->_linkIds[$type]);
	}

	public function __call ($sMethod, $mArgList) {

		$sType = $this->_selectConnectionType (strtoupper($sMethod));

		if (method_exists($this->_linkIds[$sType], $sMethod)) {
			return call_user_func_array (array ($this->_linkIds[$sType], $sMethod), $mArgList);
		} else {
			throw new Zend_Exception (
				__CLASS__.':'.__FUNCTION__ . ": $sMethod not found"
			);
		}
	}

    /**
     * Sendet einen SQL-String an die Datenbank.
     *
     * @param string $query
     * @return string
     */
    public function query ($query) {
        $connType = $this->_selectConnectionType($query);
        $this->connect($connType);

        $this->_result = $this->_linkIds[$connType]->query ($query);

        return  $this->_result;
    }

    /**
     * returns a select object
     *
     * @return object Zend_Db_Select
     */
    public function select () {

        $sType = 'slave';
        $this->connect ($sType);

        return $this->_linkIds[$sType]->select ();
    }

    /**
     * Selects the connection type on the command occurences in the query.
     * @param   string  $query  The query to execute.
     * @return  string
     */
    protected function _selectConnectionType($query) {

    	$return = null;

        // Remove string values to avoid command matches in input values
        $query  = preg_replace('/["\'`].*?["\'`]/i', '', $query);

		foreach ($this->_config->getReadCommands() as $command) {
			if (strpos($query, $command) !== false) {
				$return = $this->_config->getReadType();
				break;
			}
		}

        foreach ($this->_config->getWriteCommands() as $command) {
			if (strpos($query, $command) !== false) {
				$return = $this->_config->getWriteType();
				break;
			}
		}

		if (is_null($return))
			$return = $this->_config->getDefaultType();

        return $return;
    }

    /**
     * Gibt eine Zeile des letzten Ergebnisses als assoziatives Array zurueck.
     *
     * @param string $result_id
     * @return array
     */
    public function fetchAssoc($query = null)
    {
        if (!is_null($query)) {
            $this->_result = $this->query($query);
        }
        return $this->_result->fetch ();
    }

    /**
     * Returns the number of rows in a result.
     * @param   Mysqli_Result   $result A mysqli result object.
     * @return  int
     */
    public function numRows($result = null) {
        if (is_null($result)) {
            $result = $this->_result;
        }

        return mysqli_num_rows($result);
    }
}
