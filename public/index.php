<?php

$iStartTime = microtime(true);

/**
 * Load config
 */
include '../config/config.php';

/*
 * Load autoloader
 */
include PATH_LIBRARY . 'Autoloader.php';

/**
 * Load function library.
 */
include PATH_LIBRARY . 'Functions.php';

$oBootstrap = new Bootstrap();
$oBootstrap->load();

$iEndTime   = microtime(true);
$iRuntime   = number_format($iEndTime - $iStartTime, 4);
if ($_SERVER['APPLICATION_ENV'] == 'development') {
    echo 'Script Laufzeit: ' . $iRuntime . '<br />';
}
