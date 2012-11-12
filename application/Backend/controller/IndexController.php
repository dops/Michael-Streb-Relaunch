<?php

namespace Backend;

class IndexController extends \Mjoelnir_Controller_Abstract
{
    public function indexAction() {
        $user   = \UserModel::getCurrentUser();

        $this->_view->setTemplate('index/index.tpl.html');
        
        return $this->_view;
    }

    public function testAction () {
    	$d = \Mjoelnir_Db::getInstance();

    	$s = $d->select ()
    		->from ('country');
    	$s = $s->query ();
    	$r = $s->fetch ()->num;

    	echo 'rc: ' . $s->rowCount ();

    	print_r(get_class_methods ($s));
    	echo 'num: ' .
    	print_r($r);
    	exit;
    }
}
