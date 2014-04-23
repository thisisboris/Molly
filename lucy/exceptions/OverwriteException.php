<?php
/**
 * This file is part of Molly, an open-source content manager.
 * 
 * This application is licensed under the Apache License, found in LICENSE.TXT
 * 
 * Molly CMS - Written by Boris Wintein
 */


namespace Lucy\exceptions;


use Lucy\exceptions\abstracts\AbstractException;

class OverwriteException extends AbstractException {
    function __construct($variable, $oldvalue, $newvalue) {
        parent::__construct("Overwrite Protection, variable " . $variable . " (" . $oldvalue . ") was about to be overwritten with " . $newvalue . ". Set the overwrite value to true to force overwrite." );
    }
} 