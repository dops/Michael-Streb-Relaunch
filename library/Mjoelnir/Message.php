<?php

/**
 * This class deals with messages given vi url. In the url only the message codes are named. Additionaly a message file is needed at /[project_root]/config/Messages/[application_name].php.
 * Example: /var/www/topdeals/htdocs/accounting/config/Messages/Accounting.php
 *
 * @author Michael Streb <michael.streb@topdeals.de>
 */
class Mjoelnir_Message {

    const MESSAGE_CODE_DELIMITER    = ',';

    /**
     * The singleton instance of the class.
     * @var Mjoelnir_Message
     */
    protected static $_instance = null;

    /**
     * A request object.
     * @var Mjoelnir_Request
     */
    protected $_oRequest    = null;

    /**
     * All message codes fetched form the request.
     * @var type
     */
    protected $_aMessageCodes   = array();

    /**
     * The type of a message can be detected by its id. This array configures at which id a specific type beginns.
     * @var array
     */
    protected $_aMessageTypesForIdRange = array(1000 => 'success', 2000 => 'error', 3000 => 'info');

    /**
     * The basic message file path to search for message files for the current application. This base path can be overwritten in teh config file by defining a constant
     * "MESSAGES_FILE_BASE_PATH".
     * @var str
     */
    protected static $_sMessageFileBasePath    = 'config/Messages/';

    /**
     * The name of the constant that could be defined in the config, to overwrite the message file base path.
     * @var str
     */
    protected static $_sBasePathOverridingConstant = 'MESSAGES_FILE_BASE_PATH';

    /**
     * The final path to the message file. Has to be set via the constructor.
     * @var str
     */
    protected static $_sMessageFilePath    = null;


    protected function __construct(Mjoelnir_Request $oRequest) {
        $this->_oRequest    = $oRequest;

        $this->_loadMessageFile();
    }

    /**
     * Return a singleton instance of Topdeals_Message.
     * @param Mjoelnir_Request $oRequest
     * @return  Mjoelnir_Message
     */
    public static function getInstance(Mjoelnir_Request $oRequest) {
        if (is_null(self::$_instance)) {
            self::$_instance    = new Mjoelnir_Message($oRequest);
        }

        return self::$_instance;
    }

    /**
     * Checks if a different message file base path is set in the config, and overrides the base path if it is so. Allways returns true.
     * @return  bool
     */
    protected static function _validateMessageFileBasePath() {
        if (defined(self::$_sBasePathOverridingConstant)) {
            self::$_sMessageFileBasePath    = constant(self::$_sBasePathOverridingConstant);
        }

        return true;
    }

    /**
     * Sets the message file path depending on the application name. Always returns true.
     * @return  bool
     */
    protected static function _setMessageFilePath() {
        $oSite  = Mjoelnir_Site::getInstance();
        $sApplicationName   = ucfirst(strtolower($oSite->getApplicationName()));

        self::$_sMessageFilePath    = DOCUMENT_ROOT . '../' . self::$_sMessageFileBasePath . $sApplicationName . '.php';

        return true;
    }

    /**
     * Fetches the message codes from the request, and returns the number of valid codes found.
     * @return  int
     */
    protected function _fetchMessageCodes() {
        $sMessageCodes  = $this->_oRequest->getParameter('messages', false);

        if (false !== $sMessageCodes) {
            $this->_aMessageCodes   = explode(self::MESSAGE_CODE_DELIMITER, $sMessageCodes);

            // Delete non numeric message codes
            foreach ($this->_aMessageCodes as $iKey => $sCode) {
                if (!is_numeric($sCode)) {
                    unset ($this->_aMessageCodes[$iKey]);
                }
            }
        }

        return count($this->_aMessageCodes);
    }

    /**
     * Includes the message file. Returns true on success, otherwise false.
     * @return  bool
     */
    protected static function _loadMessageFile() {
        self::_validateMessageFileBasePath();
        self::_setMessageFilePath();

        if (file_exists(self::$_sMessageFilePath)) {
            include_once self::$_sMessageFilePath;

            return true;
        }

        $oLogger    = Mjoelnir_Log::getInstance();
        $oLogger->log('Could not load message file "' . self::$_sMessageFilePath . '".');

        return false;
    }

    /**
     * Returns a single message if it is defined. If not, an empty string is returned.
     * @param   int $iCode  The integer value of the requested message.
     * @return  array
     */
    public static function getMessage($iCode) {
        self::_loadMessageFile();

        $sMessageConstantName   = 'MESSAGE_' . $iCode;

        if (defined($sMessageConstantName)) {
            return constant($sMessageConstantName);
        }

        return false;
    }

    /**
     * Returns all requested messages.
     * @return  array
     */
    public function getAllMessages() {
        $this->_fetchMessageCodes();

        $aMessages   = array();

        foreach ($this->_aMessageCodes as $iCode) {
            $sMessageConstantName   = 'MESSAGE_' . $iCode;
            if (defined($sMessageConstantName)) {
                $aMessages[$this->_aMessageTypesForIdRange[floor($iCode / 1000) * 1000]][]    = constant($sMessageConstantName);
            }
        }

        return $aMessages;
    }
}