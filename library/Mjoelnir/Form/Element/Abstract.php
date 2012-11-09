<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Abstract
 *
 * @author td-office
 */
class Mjoelnir_Form_Element_Abstract
{
    /**
     * The type has to be set in the child class.
     * @var str
     */
    protected $_sType  = '';

    /**
     * The name of the element.
     * @var str
     */
    protected $_sName   = null;

    /**
     * The value of the element.
     * @var mixed
     */
    protected $_mValue  = null;

    /**
     * Additional options for the element.
     * @var array
     */
    protected $_aOptions    = array();

    /**
     * The path to teh templates to use.
     * @var str
     */
    protected $_sTemplateDir    = null;

    /**
     * A prefix to add before tehe element output.
     * @var str
     */
    protected $_sPrefix = '';

    /**
     * A suffix to add after tehe element output.
     * @var str
     */
    protected $_sSuffix = '';

    /**
     * A smarty instance of the element view.
     * @var Smarty
     */
    protected $_oView   = null;

    /**
     * Constructor.
     * @param   str     $sName          The name of the element. Because form element values are delivered by their name, teh element will be registered under his name. This means that further elements will override a first one with the same name.
     * @param   mixed   $mValue         The value of the element. Can be a string or an array, depending on the requested element type.
     * @param   array   $aOptions       The options array can name additional attributes to set in the element output. label = displays a label; description = displays a discription under the input element; html attributes
     * @param   str     $sTemplateDir   The path to the templates to use.
     * @param   str     $sPrefix        The prefix will be place dirct before the element output.
     * @param   str     $sSuffix        The suffix will be place dirct behind the element output.
     */
    public function __construct($sName, $mValue, $aOptions = array(), $sTemplateDir = null, $sPrefix = '', $sSuffix = '') {
        $this->_sName        = $sName;
        $this->_mValue       = $mValue;
        $this->_aOptions     = $aOptions;
        $this->_sTemplateDir = $sTemplateDir;
        $this->_sPrefix      = $sPrefix;
        $this->_sSuffix      = $sSuffix;
    }

    /**
     * Inserts the element attributes into the template.
     * @return bool
     */
    protected function _render() {
        $aTmpOptions   = $this->_aOptions;

        // Check for special options and delete them from tem option array.
        if (isset($aTmpOptions['label'])) {
            $this->_oView->assign('label', $aTmpOptions['label']);
        }

        if (isset($aTmpOptions['description'])) {
            $this->_oView->assign('description', $aTmpOptions['description']);
        }

        if (isset($aTmpOptions['required']))    { $this->_oView->assign('required', true); }
        else                                    { $this->_oView->assign('required', false); }

        $aClasses  = array();
        if (isset($aTmpOptions['error']) && $aTmpOptions['error'] === true) {
            $aClasses[] = 'error';
        }
        unset($aTmpOptions['label'], $aTmpOptions['description'], $aTmpOptions['error']);

        $aOptions   = array();
        foreach ($aTmpOptions as $sParam => $mValue) {
            if ($sParam == 'class') {
                $aClasses[] = $mValue;
            }
            $aOptions[] .= $sParam . '="' . $mValue . '"';
        }

        $this->_oView->assign('wrapperId', 'formElementWrapper' . ucfirst(strtolower($this->_sName)));
        $this->_oView->assign('elementId', 'formElement' . ucfirst(strtolower($this->_sName)));
        $this->_oView->assign('classes', implode(' ', $aClasses));
        $this->_oView->assign('name', $this->_sName);
        $this->_oView->assign('value', $this->_mValue);
        $this->_oView->assign('prefix', $this->_sPrefix);
        $this->_oView->assign('suffix', $this->_sSuffix);
        $this->_oView->assign('options', implode(' ', $aOptions));

        return true;
    }

    /**
     * Returns the for element output.
     * @return str
     */
    public function __toString() {
        $sTemplatePath  = (file_exists($this->_sTemplateDir . $this->_sType . '.tpl.html'))    ? $this->_sTemplateDir   : DOCUMENT_ROOT . Mjoelnir_Form::$_sDefaultTemplateDir;

        $this->_oView = new Mjoelnir_View ();
        $this->_oView->setTemplateDir($sTemplatePath);
        $this->_render();
        return $this->_oView->fetch(strtolower($this->_sType) . '.tpl.html');
    }
    
    #################
    ## GET METHODS ##
    #################
    
    /**
     * Returns the type of teh form element.
     * @return str
     */
    public function getType() {
        return $this->_sType;
    }
}
