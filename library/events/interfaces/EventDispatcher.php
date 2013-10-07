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

interface EventDispatcher
{
    /**
     * Function that adds eventhandlers to a list so that they can be called when an event is dispatched.
     *
     * @param $eventType
     * @param $eventHandler
     * @return mixed
     */
    function addEventListener($eventType, &$eventHandler);

    /**
     * Function to remove eventhandlers from handler's list.
     *
     * @param $eventType
     * @param $eventHandler
     * @return mixed
     */
    function removeEventListener($eventType, &$eventHandler);

    /**
     * Function to actually launch the event.
     *
     * @param $event
     * @return void
     **/
    function dispatchEvent(Event &$event);

}
