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

class IllegalArgumentException extends AbstractException
{
    public function __construct($argument, $expected_class) {
        parent::__construct("Expected instance of " . $expected_class . ", got " . gettype($argument));
    }
}