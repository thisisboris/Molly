<?php
/**
 * This file is part of Molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * Molly CMS - Written by Boris Wintein
 */

// Require our libary autoloader.
require_once("library/toolbelt/Classloader.php");


$template = &\Molly\library\out\templating\Theme::getInstance();
$fileloader = \Molly\library\io\dataloaders\files\FileLoader::getInstance();

$file = new \Molly\library\io\dataloaders\files\File("index.tpl");
$file->setLocation("themes/default/frontend/");
$template->setFile($file);
$template->setFileloader($fileloader);
$template->render();
