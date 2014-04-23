<?php
/**
 * This file is part of Molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * Molly CMS - Written by Boris Wintein
 */

namespace Lucy\io\dataloaders\abstracts;
use Lucy\io\dataloaders\interfaces\Writer;

abstract class AbstractWriter implements Writer
{
    protected static $singleton;

    public static function getInstance() {
        if (!isset($singleton)) {
            self::$singleton = new static();
        }

        return self::$singleton;
    }
}
