<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Form
 *
 * @author td-office
 */
class Mjoelnir_Form
{
    /**
     * The forms name.
     * @var str
     */
    protected $_sName    = '';

    /**
     * The URL to send the from to.
     * @var str
     */
    protected $_sAction  = '/';

    /**
     * Defines if the form will be sent via https or not. False is default.
     * @var bool
     */
    protected $_bUseSsl  = false;

    /**
     * The method of form submition.
     * @var str
     */
    protected $_sMethod  = 'post';

    /**
     * Additional form options.
     * @var array
     */
    protected $_aOptions = array();

    /**
     * Contanis all elements belonging to the form.
     * @var type
     */
    protected $_aElements    = array();

    /**
     * Contains a fiedlsets an the corresponding elements.
     * @var array
     */
    protected $_aFieldsets   = array();

    /**
     * A template dir given by the application.
     * @var str
     */
    protected $_sTemplateDir = null;

    /**
     * The default template dir.
     * @var str
     */
    public static $_sDefaultTemplateDir  = '../library/Mjoelnir/Form/Templates/';
    
    /**
     * Multipart flag. If an element is added that needs multipart form handling, this flag is set to true.
     * @var bool
     */
    protected $_bIsMultipart = false;

    /**
     * The form view.
     * @var Smarty
     */
    protected $_oView    = null;


    public function __construct($sName, $sAction = null, $sMethod = 'post', $aOptions = array(), $sTemplateDir = null) {
        $this->_sName           = $sName;
        $this->_sAction         = (is_null($sAction) || empty ($sAction)) ? $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . $_SERVER['QUERY_STRING']    : $_SERVER['SERVER_NAME'] . $sAction;
        $this->_sMethod         = $sMethod;
        $this->_aOptions        = $aOptions;
        $this->_sTemplateDir    = (is_null($sTemplateDir)) ? DOCUMENT_ROOT . self::$_sDefaultTemplateDir : $sTemplateDir;
    }

    /**
     * Adds an element to the form.
     * @param   str     $sType      The element type. Must be equal to a html input type.
     * @param   str     $sName      The name of the element. Because form element values are delivered by their name, teh element will be registered under his name. This means that further elements will override a first one with the same name.
     * @param   mixed   $mValue     The value of the element. Can be a string or an array, depending on the requested element type.
     * @param   array   $aOptions   The options array can name additional attributes to set in the element output.
     * @param   str     $sPrefix    The prefix will be place dirct before the element output.
     * @param   str     $sSuffix    The suffix will be place dirct behind the element output.
     * @return Topdeals_Form_Element_Abstract
     */
    public function addElement($sType, $sName, $mValue, $aOptions = array(), $sPrefix = '', $sSuffix = '') {
        if ($sType == 'select') {
            $sTypeClassName = 'Mjoelnir_Form_Element_' . ucfirst(strtolower($sType));
            $oElement       = new $sTypeClassName($sName, (isset($mValue['selected'])) ? $mValue['selected'] : '', $mValue['list'], $aOptions, $this->_sTemplateDir, $sPrefix, $sSuffix);
        }
        else {
            if ($sType === 'file') {
                $this->_bIsMultipart = true;
            }
            
            $sTypeClassName = 'Mjoelnir_Form_Element_' . ucfirst(strtolower($sType));
            $oElement       = new $sTypeClassName($sName, $mValue, $aOptions, $this->_sTemplateDir, $sPrefix, $sSuffix);
        }

        $this->_aElements[$sName]    = $oElement;

        return $oElement;
    }

    /**
     * Removes an element from the form.
     * @param   str     $sName  The nam of the element.
     * @return  bool
     */
    public function removeElement($sName) {
        if (isset($this->_aElements[$sName])) {
            unset($this->_aElements[$sName]);
        }

        return true;
    }

    /**
     * Adds a fieldset to with the given elements to the list of elements. The elements itself will be moved from the element list into the fieldset list entry. Named but not
     * existing elements will be skipped.
     * @param   array   $aElements  An array with one or more already existing form elements.
     * @param   str     $sName      The legend of the fieldset.
     * @return  bool
     */
    public function addElementsToFieldset($aElements, $sName) {
        if (!isset($this->_aFieldsets[$sName])) {
            $this->_aFieldsets[$sName]   = array();
            $this->_aElements[$sName]    = 'fieldset';
        }
        foreach ($aElements as $sElementName) {
            if (isset($this->_aElements[$sElementName])) {
                $this->_aFieldsets[$sName][] = $this->_aElements[$sElementName];
                unset($this->_aElements[$sElementName]);
            }
        }

        return true;
    }

    /**
     * Renders the form.
     * @return bool
     */
    protected function _render() {
        $sOutput    = '';
        foreach ($this->_aElements as $sElementName => $oElement) {
            if ($oElement == 'fieldset') {
                $sFieldsetElements  = '';
                foreach ($this->_aFieldsets[$sElementName] as $sFieldsetElement) {
                    $sFieldsetElements  .= $sFieldsetElement;
                }
                $oFieldset  = new Mjoelnir_Form_Element_Fieldset($sElementName, $sFieldsetElements, array(), $this->_sTemplateDir);
                $sOutput    .= $oFieldset;
            }
            else {
                if ($oElement->getType() === 'file') {
                    $this->_bIsMultipart    = true;
                }
                $sOutput    .= $oElement;
            }
        }

        $aTmpOptions    = $this->_aOptions;
        $aElements      = array();
        if ($this->_bIsMultipart) { $aElements[] = 'enctype="multipart/form-data"'; }
        foreach ($aTmpOptions as $sParam => $mValue) {
            $aElements[]    .= $sParam . '="' . $mValue . '"';
        }

        $this->_oView->assign('name', $this->_sName);
        $this->_oView->assign('action', ($this->_bUseSsl) ? 'https://' . $this->_sAction : 'http://' . $this->_sAction);
        $this->_oView->assign('method', $this->_sMethod);
        $this->_oView->assign('options', implode(' ', $aElements));
        $this->_oView->assign('elements', $sOutput);

        return true;
    }

    /**
     * Returns the form output.
     * @return str
     */
    public function __toString() {
        $sTemplatePath  = (file_exists($this->_sTemplateDir . '/form.tpl.html'))    ? $this->_sTemplateDir   : DOCUMENT_ROOT . Mjoelnir_Form::$_sDefaultTemplateDir;

        $this->_oView = new Mjoelnir_View ();
        $this->_oView->setTemplateDir($sTemplatePath);
        $this->_render();

        return  $this->_oView->fetch('form.tpl.html');
    }

    /**
     * Configures the form to use https or not. If you call the function without a parameter given, https will be activated. To deactivate it, $value has to be false.
     * If an non-boolean value is given, false will be returned.
     * @param   bool    $value  Activate or deactivate the usage of https.
     * @return  bool
     */
    public function useSsl($value = true) {
        if (is_bool($value)) {
            $this->_bUseSsl  = $value;
            return true;
        }

        return false;
    }
}

?>
