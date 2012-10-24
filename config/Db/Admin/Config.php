<?php

class Db_Topdeals_Config
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
     * Connection data for all connection types.
     * @var array
     */
    protected $_data = array(
        'development'   => array(
            'master'    => array(
                'host'  =>  'localhost',
                'user'  =>  'topdeals_dev',
                'pass'  =>  'PV2nVY7H5LfM3vv4',
                'db'    =>  'topdeals',
            ),
            'slave' => array(
                'host'  =>  'localhost',
                'user'  =>  'topdeals_dev',
                'pass'  =>  'PV2nVY7H5LfM3vv4',
                'db'    =>  'topdeals',
            ),
        ),
        'production'    => array(
            'master'    => array(
                'host'  =>  '10.10.100.9',
                'user'  =>  'topdeals_rw',
                'pass'  =>  'eco104-h4HUSec',
                'db'    =>  'topdeals',
            ),
            'slave' => array(
                'host'  =>  '10.10.100.10',
                'user'  =>  'topdeals_rw',
                'pass'  =>  'eco104-h4HUSec',
                'db'    =>  'topdeals',
            ),
        ),  
    );
    
    
    /**
     * Tabel name constants.
     */
    const TABLE_USER                    = 'users';
    const TABLE_PARTNER                 = 'users';
    const TABLE_COUNTRY                 = 'countries';
    const TABLE_ITEM                    = 'items';
	const TABLE_AHA_ITEM                = 'aha_items_data';
    const TABLE_COUPON                  = 'coupon';
    const TABLE_ITEM_SESSION            = 'items_session';
	const TABLE_ITEM_STATS				= 'items_stats';
    const TABLE_WINNER                  = 'winners';


    #################
    ## GET METHODS ##
    #################
    
    public function selectServer ($sType) { 
    	// dummy 
    }
    
    /**
     * Returns the host for the given type.
     * @param   string  $type   The connection type.
     * @return  string
     */
    public function getHost($type) {
        if (isset($this->_data[APPLICATION_ENV][$type])) {
            return $this->_data[APPLICATION_ENV][$type]['host'];
        }
        else {
            $this->_data[APPLICATION_ENV][$this->_defaultType]['host'];
        }
    }
    
    /**
     * Returns the user for the given type.
     * @param   string  $type   The connection type.
     * @return  string
     */
    public function getUser($type) {
        if (isset($this->_data[APPLICATION_ENV][$type])) {
            return $this->_data[APPLICATION_ENV][$type]['user'];
        }
        else {
            $this->_data[APPLICATION_ENV][$this->_defaultType]['user'];
        }
    }
    
    /**
     * Returns the pass for the given type.
     * @param   string  $type   The connection type.
     * @return  string
     */
    public function getPass($type) {
        if (isset($this->_data[APPLICATION_ENV][$type])) {
            return $this->_data[APPLICATION_ENV][$type]['pass'];
        }
        else {
            $this->_data[APPLICATION_ENV][$this->_defaultType]['pass'];
        }
    }
    
    /**
     * Returns the dbname for the given type.
     * @param   string  $type   The connection type.
     * @return  string
     */
    public function getDb($type) {
        if (isset($this->_data[APPLICATION_ENV][$type])) {
            return $this->_data[APPLICATION_ENV][$type]['db'];
        }
        else {
            $this->_data[APPLICATION_ENV][$this->_defaultType]['db'];
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
