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
    protected $_name    = '';

    /**
     * The URL to send the from to.
     * @var str
     */
    protected $_action  = '/';

    /**
     * Defines if the form will be sent via https or not. False is default.
     * @var bool
     */
    protected $_useSsl  = false;

    /**
     * The method of form submition.
     * @var str
     */
    protected $_method  = 'post';

    /**
     * Additional form options.
     * @var array
     */
    protected $_options = array();

    /**
     * Contanis all elements belonging to the form.
     * @var type
     */
    protected $_elements    = array();

    /**
     * Contains a fiedlsets an the corresponding elements.
     * @var array
     */
    protected $_fieldsets   = array();

    /**
     * A template dir given by the application.
     * @var str
     */
    protected $_templateDir = null;

    /**
     * The default template dir.
     * @var str
     */
    public static $_defaultTemplateDir  = '../library/Mjoelnir/Form/Templates/';

    /**
     * The form view.
     * @var Smarty
     */
    protected $_view    = null;


    public function __construct($name, $action = null, $method = 'post', $options = array(), $templateDir = null) {
        $this->_name        = $name;
        $this->_action      = (is_null($action) || empty ($action)) ? $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . $_SERVER['QUERY_STRING']    : $_SERVER['SERVER_NAME'] . $action;
        $this->_method      = $method;
        $this->_options     = $options;
        $this->_templateDir = (is_null($templateDir)) ? DOCUMENT_ROOT . self::$_defaultTemplateDir : $templateDir;
    }

    /**
     * Adds an element to the form.
     * @param   str     $type       The element type. Must be equal to a html input type.
     * @param   str     $name       The name of the element. Because form element values are delivered by their name, teh element will be registered under his name. This means that further elements will override a first one with the same name.
     * @param   mixed   $value      The value of the element. Can be a string or an array, depending on the requested element type.
     * @param   array   $options    The options array can name additional attributes to set in the element output.
     * @param   str     $prefix     The prefix will be place dirct before the element output.
     * @param   str     $suffix     The suffix will be place dirct behind the element output.
     * @return Topdeals_Form_Element_Abstract
     */
    public function addElement($type, $name, $value, $options = array(), $prefix = '', $suffix = '') {
        if ($type == 'select') {
            $typeClassName          = 'Mjoelnir_Form_Element_' . ucfirst(strtolower($type));
            $element                = new $typeClassName($name, (isset($value['selected'])) ? $value['selected'] : '', $value['list'], $options, $this->_templateDir, $prefix, $suffix);
        }
        else {
            $typeClassName          = 'Mjoelnir_Form_Element_' . ucfirst(strtolower($type));
            $element                = new $typeClassName($name, $value, $options, $this->_templateDir, $prefix, $suffix);
        }

        $this->_elements[$name] = $element;

        return $element;
    }

    /**
     * Removes an element from the form.
     * @param   str     $name   The nam of the element.
     * @return  bool
     */
    public function removeElement($name) {
        if (isset($this->_elements[$name])) {
            unset($this->_elements[$name]);
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
        if (!isset($this->_fieldsets[$sName])) {
            $this->_fieldsets[$sName]   = array();
            $this->_elements[$sName]    = 'fieldset';
        }
        foreach ($aElements as $sElementName) {
            if (isset($this->_elements[$sElementName])) {
                $this->_fieldsets[$sName][] = $this->_elements[$sElementName];
                unset($this->_elements[$sElementName]);
            }
        }

        return true;
    }

    /**
     * Renders the form.
     * @return bool
     */
    protected function _render() {
        $output = '';
        foreach ($this->_elements as $sElementName => $element) {
            if ($element == 'fieldset') {
                $sFieldsetElements  = '';
                foreach ($this->_fieldsets[$sElementName] as $sFieldsetElement) {
                    $sFieldsetElements  .= $sFieldsetElement;
                }
                $oFieldset  = new Mjoelnir_Form_Element_Fieldset($sElementName, $sFieldsetElements, array(), $this->_templateDir);
                $output     .= $oFieldset;
            }
            else {
                $output .= $element;
            }
        }

        $tmpOptions = $this->_options;
        $elements    = array();
        foreach ($tmpOptions as $param => $value) {
            $elements[]    .= $param . '="' . $value . '"';
        }

        $this->_view->assign('name', $this->_name);
        $this->_view->assign('action', ($this->_useSsl) ? 'https://' . $this->_action : 'http://' . $this->_action);
        $this->_view->assign('method', $this->_method);
        $this->_view->assign('options', implode(' ', $elements));
        $this->_view->assign('elements', $output);

        return true;
    }

    /**
     * Returns the form output.
     * @return str
     */
    public function __toString() {
        $defaultTemplateFile    = DOCUMENT_ROOT . Mjoelnir_Form::$_defaultTemplateDir . '/form.tpl.html';
        $customTemplateFile     = $this->_templateDir . '/form.tpl.html';

        $templatePath   = (file_exists($this->_templateDir . '/form.tpl.html'))    ? $this->_templateDir   : DOCUMENT_ROOT . Mjoelnir_Form::$_defaultTemplateDir;

        $this->_view = new Mjoelnir_View ();
        $this->_view->setTemplateDir($templatePath);
        $this->_render();

        return  $this->_view->fetch('form.tpl.html');
    }

    /**
     * Configures the form to use https or not. If you call the function without a parameter given, https will be activated. To deactivate it, $value has to be false.
     * If an non-boolean value is given, false will be returned.
     * @param   bool    $value  Activate or deactivate the usage of https.
     * @return  bool
     */
    public function useSsl($value = true) {
        if (is_bool($value)) {
            $this->_useSsl  = $value;
            return true;
        }

        return false;
    }
}

?>
