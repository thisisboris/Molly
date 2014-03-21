<?php
/**
 * This file is part of molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * molly CMS - Written by Boris Wintein
 */

namespace Lucy\events\interfaces;

interface EventDispatcher
{
    /**
     * Function that adds eventhandlers to a list so that they can be called when an event is dispatched.
     *
     * @param $eventType
     * @param $eventHandler
     * @return mixed
     */
    function addEventListener($eventType, EventHandler &$eventHandler);

    /**
     * Function to remove eventhandlers from handler's list.
     *
     * @param $eventType
     * @param $eventHandler
     * @return mixed
     */
    function removeEventListener($eventType, EventHandler &$eventHandler);

    /**
     * Function to actually launch the event.
     *
     * @param $event
     * @return void
     **/
    function dispatchEvent(Event &$event);

}
