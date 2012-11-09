<?php

// If executing from console, set the useragent to topdealsCron.
if (!isset($_SERVER['HTTP_USER_AGENT'])) {
    $_SERVER['HTTP_USER_AGENT'] = 'topdealsCron';
}

/**
 * Env vars
 */
define('APPLICATION_ENV', (isset($_SERVER['APPLICATION_ENV'])) ? $_SERVER['APPLICATION_ENV'] : 'production');
define('APPLICATION_NAME', (isset($_SERVER['APPLICATION_NAME'])) ? $_SERVER['APPLICATION_NAME'] : 'www');
define('APPLICATION_LOG_MAIL', (isset($_SERVER['APPLICATION_LOG_MAIL'])) ? $_SERVER['APPLICATION_LOG_MAIL'] : 'it@topdeals.de');

/**
 * Path definitions
 */
define('DOCUMENT_ROOT', 	$_SERVER['DOCUMENT_ROOT'] . '/');
define('WEB_ROOT', 		'/');
define('PATH_LIBRARY', 		DOCUMENT_ROOT . '../library/');
define('SMARTY_DIR',		PATH_LIBRARY  . 'Smarty/');
define('PATH_LOG', 		DOCUMENT_ROOT . '../var/log/');
define('PATH_MODEL', 		DOCUMENT_ROOT . '../application/model/');
define('PATH_APPLICATION', 	DOCUMENT_ROOT . '../application/' . APPLICATION_NAME . '/');
define('PATH_TEMPLATE', 	DOCUMENT_ROOT . '../application/' . APPLICATION_NAME . '/view/template/');
define('PATH_TEMPLATE_CACHE', 	DOCUMENT_ROOT . '../var/smarty/templates_cache/');
define('PATH_TEMPLATE_COMPILE', DOCUMENT_ROOT . '../var/smarty/templates_compile/');
define('PATH_CONTROLLER', 	DOCUMENT_ROOT . '../application/' . APPLICATION_NAME . '/controller/');
define('PATH_CONFIG', 		DOCUMENT_ROOT . '../config/');
define('PATH_CSS', 		'css/');
define('PATH_JS', 		'js/');
define('PATH_IMAGES', 		'images/');
define('PATH_USERFILES',        '../var/userFiles/');

/**
 * Default page and action
 */
define('DEFAULT_PAGE', 'index');
define('DEFAULT_ACTION', 'index');

/**
 * Smarty configuration / cache settings
 */
switch ($_SERVER['APPLICATION_ENV']) {
    case 'development':
        define ('SMARTY_CACHING', false);
        define ('SMARTY_COMPILE_CHECK', true);
        define ('SMARTY_COMPILE_FORCE', true);
        define ('SMARTY_CACHING_LIFETIME', 0);
    break;
    default:
        # production
        define ('SMARTY_CACHING', false);
        define ('SMARTY_COMPILE_CHECK', false);
        define ('SMARTY_COMPILE_FORCE', false);
        define ('SMARTY_CACHING_LIFETIME', -1);
    break;
}

/**
 * The user role id used for the admin user role.
 */
define('ADMIN_USER_ROLE_ID', 1);

/**
 * Authentification
 */
define('AUTH_EXPIRE', 60*60*24);

/**
 * Default database to use
 */
define('DEFAULT_DB', 'Michaelstreb');

/**
 * Error reporting
 */
ini_set('display_errors', true);

/**
 * Encryption prefix
 */
define('ENCRYPT_PREFIX', 'LfLarUiitrWew4764Fvbnase234fsdtrsrdnzFFFF');

/**
 * Set locale
 */
setlocale (LC_ALL, 'de_DE@euro', 'de_DE', 'de', 'ge');

/**
 * Set return method depending on user agent
 */
switch ($_SERVER['HTTP_USER_AGENT']) {
    case 'topdealsCurlClient':  define('RETURN_METHOD', 'json');  break;
    default:                    define('RETURN_METHOD', 'html');
}

/**
 * Page title prefix
 */
define('PAGE_TITLE_PREFIX', 'Michael Streb');
define('PAGE_TITLE_GLUE', '::');

/**
 * Email adresses
 */

/**
 * Diverent
 */
define('ITEMS_PER_PAGE', 15);