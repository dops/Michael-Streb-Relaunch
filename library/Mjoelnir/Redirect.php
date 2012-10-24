<?php

class Mjoelnir_Redirect
{
    public static function redirect($sTarget, $iCode) {
        /**
         * TODO: Validate if the target is reachable. If not, redirect to "the
         * requested site is not reachable"-site.
         */
        
        header('Location: ' . $sTarget);
        exit();
    }
}