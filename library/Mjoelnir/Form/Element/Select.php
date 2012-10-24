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
    protected $_type    = 'select';

    /**
     * Selectable values.
     * @var array
     */
    protected $_valueList   = array();


    /**
     * Constructor.
     * @param   str     $name           The name of the element. Because form element values are delivered by their name, teh element will be registered under his name. This means that further elements will override a first one with the same name.
     * @param   mixed   $value          The value of the element. Can be a string or an array, depending on the requested element type.
     * @param   array   $valueList      The list of selectable values.
     * @param   array   $options        The options array can name additional attributes to set in the element output. label = displays a label; description = displays a discription under the input element; html attributes
     * @param   str     $templateDir    The path to the templates to use.
     * @param   str     $prefix         The prefix will be place dirct before the element output.
     * @param   str     $suffix         The suffix will be place dirct behind the element output.
     */
    public function __construct($name, $value, $valueList, $options = array(), $templateDir = null, $prefix = '', $suffix = '') {
        $this->_name        = $name;
        $this->_value       = $value;
        $this->_valueList   = $valueList;
        $this->_options     = $options;
        $this->_templateDir = $templateDir;
        $this->_prefix      = $prefix;
        $this->_suffix      = $suffix;
    }

    /**
     * Inserts the element attributes into the template.
     * @return bool
     */
    protected function _render() {
        $tmpOptions    = $this->_options;

        // Check for special options and delete them from tem option array.
        if (isset($tmpOptions['label'])) {
            $this->_view->assign('label', $tmpOptions['label']);
        }

        if (isset($tmpOptions['description'])) {
            $this->_view->assign('description', $tmpOptions['description']);
        }

        $classes  = array();
        if (isset($tmpOptions['error']) && $tmpOptions['error'] === true) {
            $classes[]  = 'error';
        }
        unset($tmpOptions['label'], $tmpOptions['description'], $tmpOptions['error']);

        if (isset($tmpOptions['required'])) { $this->_view->assign('required', true); }
        else                                { $this->_view->assign('required', false); }

        $options    = array();
        foreach ($tmpOptions as $param => $value) {
            $options[]    .= $param . '="' . $value . '"';
        }

        $this->_view->assign('wrapperId', 'formElementWrapper' . ucfirst(strtolower($this->_name)));
        $this->_view->assign('elementId', 'formElement' . ucfirst(strtolower($this->_name)));
        $this->_view->assign('classes', implode(' ', $classes));
        $this->_view->assign('name', $this->_name);
        $this->_view->assign('value', $this->_value);
        $this->_view->assign('valueList', $this->_valueList);
        $this->_view->assign('prefix', $this->_prefix);
        $this->_view->assign('suffix', $this->_suffix);
        $this->_view->assign('options', implode(' ', $options));

        return true;
    }

    /**
     * Returns the for element output.
     * @return str
     */
    public function __toString() {
        $defaultTemplateFile    = DOCUMENT_ROOT . Mjoelnir_Form::$_defaultTemplateDir . '/' . $this->_type . '.tpl.html';
        $customTemplateFile     = $this->_templateDir . '/' . $this->_type . '.tpl.html';

        $templatePath   = (file_exists($customTemplateFile))    ? $this->_templateDir   : DOCUMENT_ROOT . Mjoelnir_Form::$_defaultTemplateDir;

        $this->_view = new Mjoelnir_View ();
        $this->_view->setTemplateDir($templatePath);
        $this->_render();
        return $this->_view->fetch(strtolower($this->_type) . '.tpl.html');
    }
}

?>
