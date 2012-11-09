<?php

/**
 * Select element
 *
 * @author Michael Streb <kontakt@michael-streb.de>
 */
class Mjoelnir_Form_Element_Select extends Mjoelnir_Form_Element_Abstract
{
    /**
     * Type definition needed in abstract class.
     * @var str
     */
    protected $_sType    = 'select';

    /**
     * Selectable values.
     * @var array
     */
    protected $_aValueList   = array();


    /**
     * Constructor.
     * @param   str     $sName          The name of the element. Because form element values are delivered by their name, teh element will be registered under his name. This means that further elements will override a first one with the same name.
     * @param   mixed   $mValue         The value of the element. Can be a string or an array, depending on the requested element type.
     * @param   array   $aValueList     The list of selectable values.
     * @param   array   $aOptions       The options array can name additional attributes to set in the element output. label = displays a label; description = displays a discription under the input element; html attributes
     * @param   str     $sTemplateDir   The path to the templates to use.
     * @param   str     $sPrefix        The prefix will be place dirct before the element output.
     * @param   str     $sSuffix        The suffix will be place dirct behind the element output.
     */
    public function __construct($sName, $mValue, $aValueList, $aOptions = array(), $sTemplateDir = null, $sPrefix = '', $sSuffix = '') {
        $this->_sName        = $sName;
        $this->_mValue       = $mValue;
        $this->_aValueList   = $aValueList;
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
        $aTmpOptions    = $this->_aOptions;

        // Check for special options and delete them from tem option array.
        if (isset($aTmpOptions['label'])) {
            $this->_oView->assign('label', $aTmpOptions['label']);
        }

        if (isset($aTmpOptions['description'])) {
            $this->_oView->assign('description', $aTmpOptions['description']);
        }

        $aClasses   = array();
        if (isset($aTmpOptions['error']) && $aTmpOptions['error'] === true) {
            $aClasses[] = 'error';
        }
        unset($aTmpOptions['label'], $aTmpOptions['description'], $aTmpOptions['error']);

        if (isset($aTmpOptions['required'])) { $this->_oView->assign('required', true); }
        else                                { $this->_oView->assign('required', false); }

        $aOptions   = array();
        foreach ($aTmpOptions as $sParam => $mValue) {
            $aOptions[] .= $sParam . '="' . $mValue . '"';
        }

        $this->_oView->assign('wrapperId', 'formElementWrapper' . ucfirst(strtolower($this->_sName)));
        $this->_oView->assign('elementId', 'formElement' . ucfirst(strtolower($this->_sName)));
        $this->_oView->assign('classes', implode(' ', $aClasses));
        $this->_oView->assign('name', $this->_sName);
        $this->_oView->assign('value', $this->_mValue);
        $this->_oView->assign('valueList', $this->_aValueList);
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
        $sTemplatePath   = (file_exists($this->_sTemplateDir . '/' . $this->_sType . '.tpl.html'))    ? $this->_sTemplateDir   : DOCUMENT_ROOT . Mjoelnir_Form::$_sDefaultTemplateDir;

        $this->_oView = new Mjoelnir_View ();
        $this->_oView->setTemplateDir($sTemplatePath);
        $this->_render();
        return $this->_oView->fetch(strtolower($this->_sType) . '.tpl.html');
    }
}

?>
