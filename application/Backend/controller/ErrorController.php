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
        $this->_view->setTemplate('error/forbidden.tpl.html');
        return $this->_view;
    }
}

?>
