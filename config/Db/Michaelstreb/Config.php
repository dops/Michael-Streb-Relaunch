<?php

class Db_Michaelstreb_Config
{
    /**
     * The default connection type.
     * @var string
     */
    protected $_defaultType  = 'master';

    /**
     * If a connection to the default type could not be established, the fallback type will be used.
     * @var string
     */
    protected $_fallbackType    = 'slave';

    /**
     * The prefered write connection.
     * @var string
     */
    protected $_preferedTypeWrite   = 'master';

    /**
     * The prefered connection to read.
     * @var string
     */
    protected $_preferedTypeRead    = 'slave';

    /**
     * The commands that need to done on a write connection.
     * @var array
     */
    protected $_writeCommands   = array('INSERT', 'UPDATE', 'ALTER', 'DROP', 'TRUNCATE');

    /**
     * The commands the need to be done on a read connection.
     * @var array
     */
    protected $_readCommands    = array('SELECT', 'SHOW');

	/**
	 * server index for configuration selection
	 * @var int $_iServerIndex
	 */
	protected $_iServerIndex = 0;

	/**
	 * server statistics array for connection selector
	 *
	 * @var array $_aServerStatistic
	 */
	protected $_aServerStatistic = array ();

    /**
     * Connection data for all connection types.
     * @var array
     */
    protected $_data = array (
        'development'   => array (
            'master'    => array (
            	0 => array (
                    'host'  =>  'localhost',
                    'user'  =>  'root',
                    'pass'  =>  'root',
                    'db'    =>  'michaelstreb',
                ),
            ),
            'slave' => array (
                0 => array (
                    'host'  =>  'localhost',
                    'user'  =>  'root',
                    'pass'  =>  'root',
                    'db'    =>  'michaelstreb',
                ),
            ),
        ),
        'stage' => array(
            'master'    => array(
                0   => array(
                    'host'  => 'localhost',
                    'user'  => 'lifeofmycar',
                    'pass'  =>  'EB2JPHPSjEXVu6Z5',
                    'db'    =>  'lifeofmycar',
                ),
            ),
            'slave'    => array(
                0   => array(
                    'host'  => 'localhost',
                    'user'  => 'lifeofmycar',
                    'pass'  =>  'EB2JPHPSjEXVu6Z5',
                    'db'    =>  'lifeofmycar',
                ),
            ),
        ),
        'production'    => array (
            'master'    => array (
            	0 => array (
                    'host'  =>  '10.10.100.9',
                    'user'  =>  'lifeofmycar',
                    'pass'  =>  'EB2JPHPSjEXVu6Z5',
                    'db'    =>  'lifeofmycar',
                ),
            ),
            'slave' => array (
            	0 => array (
                    'host'  =>  '10.10.100.10',
                    'user'  =>  'lifeofmycar',
                    'pass'  =>  'EB2JPHPSjEXVu6Z5',
                    'db'    =>  'lifeofmycar',
                ),
            ),
        ),
    );


    /**
     * Tabel name constants.
     */
    const TABLE_USER                    = 'user';
    const TABLE_USER_ROLE               = 'user_role';
    const TABLE_USER_ROLE_PERMISSION    = 'user_role_permission';
    const TABLE_USER_USER_ROLE          = 'user_user_role';
    const TABLE_REFERENCE               = 'reference';
    
    /**
     * sets the server configuration index for
     */
    public function selectServer ($sType) {

		# basics
    	$iServerCount = count ($this->_data[APPLICATION_ENV][$sType]);

		# initialize statistics, if possible
		$iServerCountMaster = count ($this->_data[APPLICATION_ENV]['master']);
		$iServerCountSlave  = count ($this->_data[APPLICATION_ENV]['slave']);
		for ($i = 0; $i < $iServerCountMaster; $i++) {
			if (!isset($this->_aServerStatistic['master']))
				$this->_aServerStatistic['master'] = array ();
			if (!isset($this->_aServerStatistic['master'][$i]))
				$this->_aServerStatistic['master'][$i] = 0;
		}
		for ($i = 0; $i < $iServerCountSlave; $i++) {
			if (!isset($this->_aServerStatistic['slave']))
				$this->_aServerStatistic['slave'] = array ();
			if (!isset($this->_aServerStatistic['slave'][$i]))
				$this->_aServerStatistic['slave'][$i] = 0;
		}

		# select server configuration index for the given type using statistics
    	if ($iServerCount > 1) {
    		asort ($this->_aServerStatistic[$sType]);
    		$this->_iServerIndex = key( array_slice ($this->_aServerStatistic[$sType], 0, 1, true));
    	}

    	# update statistics
   		$this->_aServerStatistic[$sType][$this->_iServerIndex] += 1;
    }

    #################
    ## GET METHODS ##
    #################

    /**
     * Returns the host for the given type.
     * @param   string  $type   The connection type.
     * @return  string
     */
    public function getHost($sType) {
        if (isset($this->_data[APPLICATION_ENV][$sType][$this->_iServerIndex])) {
            return $this->_data[APPLICATION_ENV][$sType][$this->_iServerIndex]['host'];
        }
        else {
            $this->_data[APPLICATION_ENV][$this->_defaultType][$this->_iServerIndex]['host'];
        }
    }

    /**
     * Returns the user for the given type.
     * @param   string  $sType   The connection type.
     * @return  string
     */
    public function getUser($sType) {
        if (isset($this->_data[APPLICATION_ENV][$sType][$this->_iServerIndex])) {
            return $this->_data[APPLICATION_ENV][$sType][$this->_iServerIndex]['user'];
        }
        else {
            $this->_data[APPLICATION_ENV][$this->_defaultType][$this->_iServerIndex]['user'];
        }
    }

    /**
     * Returns the pass for the given type.
     * @param   string  $sType   The connection type.
     * @return  string
     */
    public function getPass($sType) {
        if (isset($this->_data[APPLICATION_ENV][$sType][$this->_iServerIndex])) {
            return $this->_data[APPLICATION_ENV][$sType][$this->_iServerIndex]['pass'];
        }
        else {
            $this->_data[APPLICATION_ENV][$this->_defaultType][$this->_iServerIndex]['pass'];
        }
    }

    /**
     * Returns the dbname for the given type.
     * @param   string  $sType   The connection type.
     * @return  string
     */
    public function getDb($sType) {
        if (isset($this->_data[APPLICATION_ENV][$sType][$this->_iServerIndex])) {
            return $this->_data[APPLICATION_ENV][$sType][$this->_iServerIndex]['db'];
        }
        else {
            $this->_data[APPLICATION_ENV][$this->_defaultType][$this->_iServerIndex]['db'];
        }
    }

    /**
     * Returns the connection type to use for read opperations.
     * @return string
     */
    public function getReadType() {
        return $this->_preferedTypeRead;
    }

    /**
     * Returns the connection type to use for write opperations.
     * @retunr string
     */
    public function getWriteType() {
        return $this->_preferedTypeWrite;
    }

    /**
     * Returns the connection type used for connections by default.
     * @return string
     */
    public function getDefaultType() {
        return $this->_defaultType;
    }

    /**
     * Returns the connection type used as fallback if the default issn´t reachable.
     * @return string
     */
    public function getFallbackType() {
        return $this->_fallbackType;
    }

    /**
     * Returns the commands that are able to use with a read connection.
     * @return array
     */
    public function getReadCommands() {
        return $this->_readCommands;
    }

    /**
     * Returns the commands that are able to use with a write connection.
     * @return array
     */
    public function getWriteCommands() {
        return $this->_writeCommands;
    }
}
