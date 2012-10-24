<?php

/**
 * Set parameters needed for cron execution
 */
$_SERVER['APPLICATION_ENV'] = $argv[1];
$pathInfo                   = pathinfo(__FILE__);
$_SERVER['DOCUMENT_ROOT']   = $pathInfo['dirname'];

$_FilterUserId				= $argc > 2 ? $argv[2] : "";


/**
 * Load config
 */
include dirname (__FILE__).'/../config/config.php';

/**
 * Load autoloader
 */
set_include_path(get_include_path() . ':' . PATH_LIBRARY);
include PATH_LIBRARY . '/Autoloader.php';

class GenerateNewUserPassword {

	protected $_UserIdFilter = "";

    public function __construct($UserIdFilter) {
        set_time_limit(0);

		if (!empty($UserIdFilter)) $this->_UserIdFilter = $UserIdFilter;

        $this->_dbAccounting    = Mjoelnir_Db::getInstance();
        $this->_dbTopdeals      = Mjoelnir_Db::getInstance('Topdeals');
        $this->_log             = Mjoelnir_Log::getInstance();

        $this->_process();
    }

    protected function _process() {

        $this->_log->log('Start update process.');

        $filename = $this->_createNewExportFile();
        $oPartnerlist = $this->_loadPartnerList();

        $this->_log->log($oPartnerlist->rowCount() . ' partners found.');
        $partners   = $oPartnerlist->fetchAll();

        foreach($partners as $partner) {
            $this->_log->log('Partner : ' . $partner["partnerNick"]);
            $oUser = UserModel::getInstance($partner["partnerId"]);
            $newPassword = UserModel::generatePassword();
            $oUser->setPassword($newPassword);
            $oUser->save();
            $partner["password"] = $newPassword;

            $this->_writeDataToExportFile($filename, $partner);
        }
    }


    protected function _loadPartnerList() {

        $this->_log->log('Load partnerlist');

        $sql = "
            SELECT
                i.item_id, i.title,
                s.user_id AS sellerId, s.nick AS sellerNick, s.first_name AS sellerFirstName, s.last_name AS sellerName, s.email AS sellerEmail
                ,p.user_id as partnerId, p.nick AS partnerNick, p.company AS partnerCompany, p.first_name AS partnerFirstName, p.last_name AS partnerName, p.email AS partnerEmail
            FROM
                item AS i
            LEFT JOIN
                user AS p
            ON
                p.user_id = i.seller_user_id
            LEFT JOIN
                user AS s
            ON
                s.user_id = i.sales_agent_user_id
            WHERE
                1
        ";

        if ($this->_UserIdFilter !== "" && is_numeric($this->_UserIdFilter)) {
            $sql .= "
                AND p.user_id IN (" . $this->_UserIdFilter . ")
            ";
        }
        elseif ($this->_UserIdFilter == 'all') {

        }
        else { # select only partners that are inactive and don't have a login_hash
            $sql .= "
                AND p.login_hash = ''
            ";
        }

        $sql .= "
            GROUP BY
                p.nick, s.nick
            ORDER BY
                s.nick, i.item_id
        ";

        return $this->_dbAccounting->query($sql);
    }

    protected function _createNewExportFile() {
        $filename = "PartnerPasswordExport.csv";
        $filename = date('Y-m-d_H-i-s') . "_". $filename;
        $this->_log->log('Create new export file (and write the utf-8 BOM).');
        file_put_contents(PATH_LOG . $filename,  chr(239) . chr(187) . chr(191));
        return $filename;
    }

    protected function _writeDataToExportFile($filename, $data = array()) {
        file_put_contents(PATH_LOG . $filename, implode(";", $data) . "\n" , FILE_APPEND);
        return true;
    }

}

new GenerateNewUserPassword($_FilterUserId);

?>
