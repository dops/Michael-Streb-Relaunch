<?php

namespace Backend;

class ReferenceController extends \Mjoelnir_Controller_Abstract
{
    public function indexAction() {
        
        $aReferences    = \ReferenceModel::getAll();
        
        $this->_view->assign('aReferences', $aReferences);
        
        return $this->_view->fetch('reference/index.tpl.html');
    }
    
    public function editAction() {
        
        $oForm  = new \Mjoelnir_Form('referenceEdit');
        
        $oReference = \ReferenceModel::getInstance(\Mjoelnir_Request::getParameter('id', false));

        $aMessages  = array('error' => array());
        if (\Mjoelnir_Request::getParameter('save', false) || \Mjoelnir_Request::getParameter('saveReturn', false)) {
            if ($oReference->setName(\Mjoelnir_Request::getParameter('name', false))) { $aMessages['error']['name'] = $oReference->getError(); }
            if ($oReference->setUrl(\Mjoelnir_Request::getParameter('url', false))) { $aMessages['error']['url'] = $oReference->getError(); }
            
            $oReference->save();
            
            if (\Mjoelnir_Request::getParameter('saveReturn', false)) {
                \Mjoelnir_Redirect::redirect('/reference', 200);
            }
            else {
                \Mjoelnir_Redirect::redirect('/reference/edit/id/' . $oReference->getId() . '/message/1000', 200);
            }
        }

        $oForm->addElement('hidden', 'id', (\Mjoelnir_Request::getParameter('id', false)) ? \Mjoelnir_Request::getParameter('id') : $oReference->getId());
        $oForm->addElement('text', 'name', (\Mjoelnir_Request::getParameter('name', false)) ? \Mjoelnir_Request::getParameter('name') : $oReference->getName(), array('label' => 'Name', 'required' => 'required'));
        $oForm->addElement('text', 'url', (\Mjoelnir_Request::getParameter('url', false)) ? \Mjoelnir_Request::getParameter('url') : $oReference->getUrl(), array('label' => 'URL'));
        $oForm->addElement('submit', 'save', 'Speichern');
        $oForm->addElement('submit', 'saveReturn', 'Speichern und zurück');

        $this->_view->assign('sTitle', 'Reference bearbeiten');
        $this->_view->assign('sForm', $oForm->__toString());
        
        return $this->_view->fetch('reference/edit.tpl.html');
    }
}