<?php
/**
 * This file is part of Molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * Molly CMS - Written by Boris Wintein
 */
namespace Molly\library\events\interfaces;
use \Molly\library\events\Event;

interface EventHandler {
    /**
     * Function that is called on eventhandlers by eventdispatchers
     *
     * @param &$event
     * @param $eventData
     * @return mixed
     */
    function handleEvent(Event &$event, $eventData);
}
