<?php

class Mjoelnir_Log {

	private static $_oInstance = null;

	private $_hFile = null;

	public function __construct () {
		try {
			$this->_hFile = fopen (PATH_LOG . 'application.log', 'a');
		} catch (Exception $oEx) {
			exit ('Log Dircectory not writable - exiting');
		}
	}

	public function __destruct () {
		if (!is_null($this->_hFile) AND $this->_hFile !== false)
			fclose ($this->_hFile);
	}

	public static function getInstance ()
    {
        if (is_null(self::$_oInstance)) {
            self::$_oInstance = new Mjoelnir_Log ();
        }

        return self::$_oInstance;
    }

    /**
     * Writes the log message to the file
     *
     * @param string $sMessage
     */
    final public function log ($sMessage) {

    	$oCaller = $this->getCaller ();

    	if (!is_null($this->_hFile))
    		fputs ($this->_hFile, date('[Y-m-d H:i:s] ') .sprintf('%s::%s() - ', $oCaller->class, $oCaller->function).  $sMessage . "\n");
    }

	/**
	 * make a debug backtrace and returns a StdClass
	 * width:
	 * 	-> class
	 *	-> function
	 *
	 * @return object $oCaller
	 */
	final private function getCaller () {

		$oCaller = new StdClass;
		$oCaller->class = null;
		$oCaller->function = null;

		$aTrace = debug_backtrace();
		$aCaller = $aTrace[2];

		$oCaller->function = $aCaller['function'];
		if (isset($aCaller['class']))
			$oCaller->class = $aCaller['class'];

		return $oCaller;
	}

}