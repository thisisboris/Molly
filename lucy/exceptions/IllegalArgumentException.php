<?php
/**
 * This file is part of molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * molly CMS - Written by Boris Wintein
 */
namespace Lucy\exceptions;

class IllegalArgumentException extends \Exception
{
    public function __construct($argument, $expected_class) {
        parent::__construct("Expected instance of " . $expected_class . ", got " . gettype($argument));
    }
}