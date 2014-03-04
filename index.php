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

$start_time = microtime(TRUE);

$letter = new \Molly\library\out\messages\Letter(\Molly\library\out\messages\Letter::INFORMATION);

$letter->setHead('This Message');
$letter->setContents('has contents');
$letter->printLetter();

$end_time = microtime(TRUE);


echo "microsecs:  " . ($end_time - $start_time);
