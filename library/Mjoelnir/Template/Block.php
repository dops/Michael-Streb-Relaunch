<?php

class Template_Block extends Template_Abstract
{
    // Konstruktor, f�llt den blockk�rper mit code aus $body
    public function __construct($body)
    {
        $this->body = $body;
        return true;
    }
}
