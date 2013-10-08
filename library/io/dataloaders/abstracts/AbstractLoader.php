<?php
/**
 * This file is part of Molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * Molly CMS - Written by Boris Wintein
 */

namespace Molly\library\io\dataloaders\abstracts;
use \Molly\library\io\dataloaders\interfaces\Loader;

abstract class AbstractLoader implements Loader
{
    protected static $singleton;

    public static function getInstance() {
        if (!isset($singleton)) {
            self::$singleton = new static();
        }

        return self::$singleton;
    }
}
