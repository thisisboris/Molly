<?php
/**
 * This file is part of molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * molly CMS - Written by Boris Wintein
 */

namespace Molly\abstracts;
use \Molly\rulesets\Module;
use \Lucy\events\abstracts\AbstractEventDispatcher;

abstract class AbstractModule extends AbstractEventDispatcher implements Module
{

}
