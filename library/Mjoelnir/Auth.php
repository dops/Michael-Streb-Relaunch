<?php

/**
 * Provides several functions used for user authentification.
 *
 * @package Topdeals
 * @subpackage Auth
 * @author Michael Streb <michael.streb@topdeals.de>
 */
class Mjoelnir_Auth implements Mjoelnir_Auth_Adapter_Interface
{
    /**
     * Singleton instance.
     * @var Mjoelnir_Auth
     */
    protected static $_instance = null;

    /**
     * The configured adapter
     * @var str
     */
    protected $_adapterName = null;

    /**
     * Defindes a default anthentification type
     * @var str
     */
    protected $_defaultAdapterName  = 'Cookie';

    /**
     * An adapter to write and read auth information.
     * @var mixed
     */
    protected $_adapter = null;


    protected function __construct() {
        $this->_getAdapter();
    }

    /**
     * Returns a singleton instance.
     * @return Mjoelnir_Auth
     */
    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance    = new Mjoelnir_Auth();
        }

        return self::$_instance;
    }


    protected function _getAdapter() {
        $this->_adapterName = (defined('AUTH_ADAPTER_NAME')) ? AUTH_ADAPTER_NAME : $this->_defaultAdapterName;
        $adapterClassName   = 'Mjoelnir_Auth_Adapter_' . $this->_adapterName;
        $this->_adapter     = new $adapterClassName();
    }


    public function authenticate($value) {
        return $this->_adapter->authenticate($value);
    }


    public function isAuthed() {
        return $this->_adapter->isAuthed();
    }


    public function getAuthValue() {
        return $this->_adapter->getAuthValue();
    }


    public function cancel() {
        return $this->_adapter->cancel();
    }


	public function createPassword (UserModel $oUser, $length = 12, $bSendToUser = false) {
		$mPassword = false;

		if (!$oUser instanceof UserModel) {
			return $mPassword;
		}

		if ($length < 8) {
			$length = 8;
		} elseif ($length > 24) {
			$length = 24;
		}

		$a = range ('a', 'z');
		$aC = range ('A', 'Z');
		$n = range (2, 9);
		$ignore = array ('l', 'o', 'O', '0');

		$list = array_merge ($a, $aC, $n);
		$list = array_diff ($list, $ignore);

		$list_cnt = count ($list) - 1;
		for ($l = 0; $l < $length; $l++) {
			$mPassword .= $list[rand(0, $list_cnt)];
		}

		if (true === $bSendToUser) {
			mail (
				\UserModel::getCurrentUser()->getEmail(),
				'Neues Passwort fuer ' . $oUser->getContactName() .', '. $oUser->getCompanyName(),
				'E: ' . $oUser->getEmail() . "\n" .
				'P: ' . $mPassword . "\n",
				'From: accounting@' . $_SERVER['HTTP_HOST']
			);
		}

		return $mPassword;
	}
}

?>
