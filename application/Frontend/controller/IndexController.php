<?php

namespace Frontend;

class IndexController extends \Mjoelnir_Controller_Abstract
{
    public function indexAction() {
        $user   = \UserModel::getCurrentUser();

        $oSite  = \Mjoelnir_Site::getInstance();
        $oSite->addBreadcrumb(array('title' => 'Übersicht', 'link' => WEB_ROOT));
        $this->_view->assign ('user', $user);

        return $this->_view->fetch('index/index.tpl.html');
    }

    public function logoutAction () {

	$oAuth = \Mjoelnir_Auth::getInstance();
	$oAuth->cancel();

    	header('Location: ' . WEB_ROOT . 'user/login');
        exit();
    }
}