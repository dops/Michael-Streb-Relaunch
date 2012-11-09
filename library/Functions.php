<?php

/*
 * This is a function library with objectless functions.
 */

function array_diff_recursive($aArray1, $aArray2) {
    $aReturn = array();

    foreach ($aArray1 as $mKey => $mValue) {
        if (array_key_exists($mKey, $aArray2)) {
            if (is_array($mValue)) {
                $aRecursiveDiff = array_diff_recursive($mValue, $aArray2[$mKey]);
                if (count($aRecursiveDiff)) { $aReturn[$mKey] = $aRecursiveDiff; }
            }
            else {
                if ($mValue != $aArray2[$mKey]) {
                    $aReturn[$mKey] = $mValue;
                }
            }
        }
        else {
            $aReturn[$mKey] = $mValue;
        }
    }
   
    return $aReturn;
}


function renameArrayIndex(&$aArray, $mOldIndex, $mNewIndex) {
    // If the old or the new index is an array...
    if (is_array($mOldIndex) || is_array($mNewIndex)) {
        // ... the other one has to be an array too.
        if (!is_array($mOldIndex) || !is_array($mNewIndex)) {
            return false;
        }
        
        // If the number of indexes is not equal.
        if (count($mOldIndex) !== count($mNewIndex)) {
            return false;
        }
        
        foreach ($mOldIndex as $iKey => $mIndex) {
            $aArray[$mNewIndex[$iKey]] = $aArray[$mOldIndex[$iKey]];
            unset($aArray[$mOldIndex[$iKey]]);
        }
    }
    else {
        $aArray[$mNewIndex] = $aArray[$mOldIndex];
        unset($aArray[$mOldIndex]);
    }
    
    return $aArray;
}