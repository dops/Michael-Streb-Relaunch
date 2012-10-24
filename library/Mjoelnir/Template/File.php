<?php

class Template_File extends Template_Abstract
{
    // Konstruktor, versucht das templatefile mit dem dateinamen $file zu laden
    public function __construct($file)
    {
        if (!$this->load($file)) { throw new exception("Templatedatei $file kann nicht gelesen werden."); }
        return true;
    }
}
