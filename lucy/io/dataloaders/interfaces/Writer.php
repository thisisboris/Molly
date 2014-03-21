<?php
/**
 * This file is part of molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * molly CMS - Written by Boris Wintein
 */
namespace Lucy\io\dataloaders\interfaces;

interface Writer
{
    function write(&$file, $overwrite = true);
    function append(&$file, $data);
}
