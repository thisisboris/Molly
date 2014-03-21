<?php
/**
 * This file is part of molly, an open-source content manager.
 * 
 * This application is licensed under the Apache License, found in LICENSE.TXT
 * 
 * molly CMS - Written by Boris Wintein
 */


namespace Lucy\http\html\exceptions;


class HTMLoadException extends \InvalidArgumentException {
    public function __construct($argument) {
        parent::__construct($argument, 'DOM');
    }
} 