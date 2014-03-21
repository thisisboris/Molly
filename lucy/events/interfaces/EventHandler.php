<?php
/**
 * This file is part of molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * molly CMS - Written by Boris Wintein
 */

namespace Lucy\events\interfaces;
use \Lucy\events\Event;

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
