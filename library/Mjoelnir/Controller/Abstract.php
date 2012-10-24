<?php

class Mjoelnir_Controller_Abstract
{
    /**
     * A view instance.
     * @var Smarty
     */
    public $_view    = null;

    /**
     * An acl instance.
     * @var Mjoelnir_Acl
     */
    protected $_oAcl    = null;


    public function __construct() {
        $this->_view    = new Mjoelnir_View();
        $this->_oAcl    = Mjoelnir_Acl::getInstance();

        $this->_view->assign('oAcl', $this->_oAcl);
    }
}
