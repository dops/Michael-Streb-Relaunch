<?php

class Mjoelnir_Db
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
     * l�dt selbst�ndig die accounts-konfiguration.
     * es werden keine redundaten verbindungen aufgebaut.
     *
     * @param string $account
     * @return datenbank
     */
    public static function getInstance($dbName = null)
    {
        $dbName = (is_null($dbName))    ? DEFAULT_DB    : $dbName;
        if (!isset(self::$_instances[$dbName])) {
            self::$_instances[$dbName]   = new Mjoelnir_Db($dbName);
        }

        return self::$_instances[$dbName];
    }

  // versucht eine verbindung zur datenbank herzustellen. gibt bei erfolg oder bereits vorhandener verbindung true zurueck
    /**
     * Prueft ob bereits eine Verdindung zur Datenbank besteht und baut wenn noetig eine auf.
     *
     * @return bool
     */
    public function connect($type) {
        if (!$this->connected($type)) {
            $this->_linkIds[$type] = mysql_connect($this->_config->getHost($type), $this->_config->getUser($type), $this->_config->getPass($type), true);
            if ($this->_linkIds[$type] === false) {
                /* TODO: Implement fallback */
                echo mysql_error();
                throw new Exception('connection attemp faild');
            }

            $this->selectDb($this->_config->getDb($type), $this->_linkIds[$type]);
            //if ($this->_charset) { $this->setCharset($this->_charset); }
        }

        return true;
    }

    /**
     * Unterbricht die bestehende Verbindung und loescht die Link-ID.
     *
     * @return string
     */
    public function close($type = null) {
        $connections    = (is_null($type))  ? array_keys($this->_linkIds)   : array($type);

        foreach ($connections as $connection) {
            $result = mysql_close($this->_linkIds[$connection]);
            if ($result !== false) {
                unset($this->_linkIds[$connection]);
            }
        }

        return true;
    }

  // pr�ft ob eine verbindung zur datenbank besteht. gibt bei einer bestehenden verbindung true zurueck.
  /**
   * Gibt die aktuelle Link-ID zur pruefung zurueck.
   *
   * @return string
   */
  public function connected($type)
  {
    return isset($this->_linkIds[$type]);
  }

  // f�hrt die in $query �bergebene datenbankabfrage aus und gibt das ergebnis dieser abfrage zur�ck.
  /**
   * Sendet einen SQL-String an die Datenbank.
   *
   * @param string_type $query
   * @return string
   */
    public function query($query) {
        $connType   = $this->_selectConnectionType($query);

        $this->connect($connType);
        $this->_result = mysql_query($query, $this->_linkIds[$connType]);

        return  $this->_result;
    }

    /**
     * Selects the connection type on the command occurences in the query.
     * @param   string  $query  The query to execute.
     * @return  string
     */
    protected function _selectConnectionType($query) {
        // Remove string values to avoid command matches in input values
        $query  = preg_replace('/["\'`].*?["\'`]/i', '', $query);

        $return = $this->_config->getDefaultType();

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

        return $return;
    }

    // gibt die resource-kennung bei aktiver verbindung zurück
  /**
   * Gibt die aktuelle Link-ID zurueck.
   *
   * @return string
   */
  public function getLinkId()
  {
    return $this->_linkId;
  }

  // Gibt die letzte komplette Fehlermeldung aus
  /**
   * Gibt die letzte Fehlermeldung komplett zurueck.
   *
   * @return string
   */
  public function getError($linkId)
  {
    return mysql_error($linkId);
  }

    /**
     * Wechselt die zu benutzende Datenbank
     *
     * @param string $db
     * @return bool
     */
    public function selectDb($db, $linkId) {
        if (!is_string($db)) {
            throw new exception('illegal parameter');
        }

    if (!mysql_select_db($db, $linkId)) {
        ////throw new Exception('db selection faild');
    }

    return true;
  }

  // Ein INSERT INTO $table aus den Keys und Values von $array_insert zusammenbasteln
  /**
   * Sendet eine INSERT-Anweisung an die Datenbank. $table enthaelt dabei den Namen der Tabelle
   * und $array_insert die Felder (Indiezees) und Werte (Values).
   *
   * @param string $table
   * @param array $array_insert
   * @return string
   */
  public function insert($table, $array_insert)
  {
    if (!is_string($table) or !is_array($array_insert)) { throw new exception('illegal parameter'); }

    $string_values = '';
    $string_keys   = '';

    foreach($array_insert as $key => $value)
    {
      $string_keys   .= strlen($string_keys)   ? ', ' : '';
      $string_values .= strlen($string_values) ? ', ' : '';

      $string_keys   .= $key;
	  $string_values .= "'".$this->disarm_value($value)."'";
    }

    $query  = "INSERT INTO $table ";

	// Die range()-Funktion wird benutzt, um ein genauso grosses sequentielles Array
	// wie das uebergebene Array zu erstellen. Wenn die beiden Arrays nicht gleich sind,
	// muss es sich um ein assoziatives Array handeln.
	if (array_keys($array_insert) !== range(0, sizeof($array_insert)-1))
	{
     $query .= "($string_keys) ";
	}

    $query .= "VALUES ($string_values)";

		return $this->query($query);
  }

  // liefert den auto-incrementwert der letzten insert-operation oder false wenn kein auto-increment benutzt wird
  /**
   * Gibt die ID (auto-increment) der letzten INSERT-Anweisung zurueck.
   *
   * @return string
   */
  public function getInsertId()
  {
    return mysql_insert_id($this->getLinkId());
  }

  function escapeString($value)
  {
    $this->connect();
    return mysql_real_escape_string($value, $this->getLinkId());
  }

  // "entsch�rft" $value f�r datenbank-operationen. gibt die entsch�rfte $value zur�ck.
  /**
   * "Entschaerft" einen einzutragenden Value und gibt diesen zurueck.
   *
   * @param string $value
   * @return string
   */
  function disarmValue($value) { return $this->escape_string($value); }

  /**
   * Gibt die Anzahl der Zeile des letztes Ergebnisses zurueck.
   *
   * @param string $result_id
   * @return integer
   */
  public function numRows($resultId = false)
  {
  	if (!$resultId) { $resultId = $this->_result; }
    return mysql_num_rows($resultId);
  }

  public function affectedRows($resultId = 0)
  {
  	if (!$resultId) { $resultId = $this->_result; }

  	if (!$resultId) { return false; }

  	return mysql_affected_rows($this->_linkId);
  }

  public function fetchResult($resultId = false)
  {
    if (!$resultId) { $resultId = $this->_result; }
    return mysql_result($resultId, 0, 0);
  }

  /**
   * Gibt eine Zeile des letzten Ergebnisses als numerisches Array zurueck.
   *
   * @param string $result_id
   * @return array
   */
  public function fetchNum($resultId = false)
  {
    if (!$resultId) { $resultId = $this->_result; }
    return mysql_fetch_array($resultId, MYSQL_NUM);
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
            $this->query($query);
        }
        return mysql_fetch_assoc($this->_result);
    }

  /**
   * Fetches all data matching the query and returns it as array.
   * @param string $sql
   * @param string $type
   * @return array
   */
  public function fetchAll($sql, $type = 'assoc') {
      $return   = array();
      $this->query($sql);

      switch ($type) {
          case  'num':      $fetchMethod    = 'fetchNum';       break;
          case  'object':   $fetchMethod    = 'fetchObject';    break;
          default:          $fetchMethod    = 'fetchAssoc';
      }

      while ($row = $this->$fetchMethod()) {
          $return[] = $row;
      }

      return $return;
  }

  /**
   * Gibt eine Zeile des letzten Ergebnisses als Objekt zurueck.
   *
   * @param string $class
   * @param array $constructor_params
   * @param string $result_id
   * @return object
   */
  public function fetchObject($class, $constructorParams = false, $resultId = false)
  {
    if (!$objectData = $this->fetch_assoc($resultId)) { return false; }

		if ($constructorParams)
    {
      foreach ($constructorParams as $paramNr => $param)
      {
        if ($constructorParamString) { $constructorParamString .= ','; }

        if (is_string($param) and isset($objectData[$param]))
        {
          $constructorParamString .= "'" . $objectData[$param] . "'";
        }
        else
        {
          $constructorParamString .= '&$constructor_params[' . $paramNr . ']';
        }
      }
    }
    else
    {
      foreach ($objectData as $param)
      {
        if ($constructorParamString) { $constructorParamString .= ','; }

        $constructorParamString .= "'" . $param . "'";
      }
    }

		eval('$object = new ' . $class . '(' . $constructorParamString . ');');

		return $object;
  }

  /**
   * Aendert den zu nutzenden Zeichensatz.
   *
   * @param string $charset
   * @return string
   */
  public function setCharset($charset)
  {
    if (!$charset) { throw new exception('illegal parameter'); }
  	if (!$this->query("SET NAMES '$charset'")) { throw new Exception('set charset faild'); }
  }

  /**
   * Updatet einen oder mehrere Datenbankeintraege.
   *
   * @param string $table
   * @param array $update_data
   * @return bool
   */
  public function update($table, $updateData)
  {
  	if (!$table or !count($updateData['fields'])) { throw new exception('illegal parameter'); }

  	foreach ($updateData['fields'] as $field => $value)
  	{
  		$set[]	=	$field.' = "'.$this->disarm_value($value).'"';
  	}
  	$set = implode(', ', $set);

  	if (count($updateData['conditions']))
  	{
  		foreach ($updateData['conditions'] as $field => $value)
  		{
  			$where[]	= $field.' = "'.$this->disarmValue($value).'"';
  		}

  		$where = implode(' AND ', $where);
  	}

  	$sql = 'UPDATE				'.$table.'
  					SET						'.$set;

  	if ($where)
  	{
  		$sql .= ' WHERE '.$where;
  	}
  	if ($updateData['limit'])
  	{
  	  $sql .= ' LIMIT '.$updateData['limit'];
  	}

  	return $this->query($sql);
  }
}
