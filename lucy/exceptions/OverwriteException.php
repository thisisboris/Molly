<?php
/**
 * This file is part of molly, an open-source content manager.
 * 
 * This application is licensed under the Apache License, found in LICENSE.TXT
 * 
 * molly CMS - Written by Boris Wintein
 */


namespace Lucy\exceptions;


class OverwriteException extends \Exception {
    function __construct($variable, $oldvalue, $newvalue) {
        parent::__construct("Overwrite Protection, variable " . $variable . " (" . $oldvalue . ") was about to be overwritten with " . $newvalue . ". Set the overwrite value to true to force overwrite." );
    }
} 