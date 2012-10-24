<?php

namespace Backend;

/**
 * ErrorController
 * Shows pages for several error cases.
 *
 * @author Michael Streb <michael.streb@topdeals.de>
 */
class ErrorController extends \Mjoelnir_Controller_Abstract
{
    public function forbiddenAction() {
        header('HTTP/1.0 403 Forbidden');
        return $this->_view->fetch('error/forbidden.tpl.html');
    }
}

?>
