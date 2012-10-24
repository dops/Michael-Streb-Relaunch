<?php

/**
 * Description of Text
 *
 * @author Michael Streb <kontakt@michael-streb.de>
 */
class Mjoelnir_Form_Element_Datepicker extends Mjoelnir_Form_Element_Abstract
{

    public function __construct($name, $value, $options = array(), $templateDir = null, $prefix = '', $suffix = '') {
        parent::__construct($name, $value, $options, $templateDir, $prefix, $suffix);

        if (isset($this->_options['class']))    { $this->_options['class']    .= ' datepicker'; }
        else                                    { $this->_options['class']    = ' datepicker'; }
    }

    /**
     * Type definition needed in abstract class.
     * @var str
     */
    protected $_type    = 'datepicker';
}