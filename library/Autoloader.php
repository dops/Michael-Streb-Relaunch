<?php

/**
 * Autoloader for pages and library classes.
 * @param string $className
 */
function Topdeals_Autoloader ($className) {
    if (strpos($className, '\\')) {
        $className  = substr($className, strpos($className, '\\') + 1);
    }

    $fileExtension  = '.php';

    if (strpos($className, 'Controller') !== false && strpos($className, 'Controller') === (strlen($className) - 10)) {
        $pathToFile = PATH_CONTROLLER . $className . $fileExtension;
        if (file_exists($pathToFile)) {
            require_once $pathToFile;
        }
    }
    elseif (strpos($className, 'Model') !== false && strpos($className, 'Model') === (strlen($className) - 5)) {
        $pathToFile = PATH_MODEL . $className . $fileExtension;
        if (file_exists($pathToFile)) {
            require_once $pathToFile;
        }
    }
    elseif (strpos($className, 'Config') !== false && strpos($className, 'Config') === (strlen($className) - 6)) {
        $pathToFile = PATH_CONFIG . str_replace('_', '/', $className) . $fileExtension;
        if (file_exists($pathToFile)) {
            require_once $pathToFile;
        }
    }
    elseif (strpos($className, 'Bootstrap') !== false && strpos($className, 'Bootstrap') === (strlen($className) - 9)) {
        $pathToFile = PATH_APPLICATION . 'Bootstrap' . $fileExtension;
        if (file_exists($pathToFile)) {
            require_once $pathToFile;
        }
    }
    else {
        if ($className == 'Smarty') {
            $fileExtension  = '.class.php';
            $pathToFile     = PATH_LIBRARY . 'Smarty/' . str_replace('_', '/', $className) . $fileExtension;
        }
        else {
            $pathToFile = PATH_LIBRARY . str_replace('_', '/', $className) . $fileExtension;
        }

        if (file_exists($pathToFile)) {
            require_once $pathToFile;
        }
    }
}

spl_autoload_register ('Topdeals_Autoloader');

/**
 * Autoloader for FPDF
 */

function Fpdf_Autoloader($className) {
    if ($className === 'FPDF') {
        require_once PATH_LIBRARY . 'Fpdf/fpdf.php';
    }
    if ($className === 'TTFParser') {
        require_once PATH_LIBRARY . 'Fpdf/makefont/ttfparser.php';
    }
}

spl_autoload_register ('Fpdf_Autoloader');