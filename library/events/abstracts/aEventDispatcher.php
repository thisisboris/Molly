<?php
/**
 * This file is part of Molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * Molly CMS - Written by Boris Wintein
 */

namespace Molly\library\events\abstracts;

// "Imports"
use Molly\library\events\Event;
use Molly\library\events\interfaces\EventDispatcher as iEventDispatcher;

use Molly\library\events\exceptions\InvalidEventHandlerException as HandlerException;
use Molly\library\exceptions\IllegalArgumentException;
use Molly\library\utils\collection\MollyArray as MollyArray;

abstract class EventDispatcher implements iEventDispatcher
{
    /**
     * Multidimensional array containing all eventHandlers
     * @var array
     */
    protected $registeredHandlers = array();

    public function dispatchEvent(&$event)
    {
        if ($event instanceof Event) {
            $type = $event->getEventType();
            if (is_array($this->registeredHandlers[$type])) {
                foreach ($this->registeredHandlers[$type] as $handlerInfo) {
                    $handler = $handlerInfo['handler'];
                    $handler->handleEvent($type, $event);
                }
            }
        } else {
            throw new IllegalArgumentException();
        }
    }

    /**
     *  Function that adds eventhandlers to a list so that they can be called when an event is dispatched.
     *
     * @param $eventType
     * @param $eventHandler
     * @return mixed|void
     * @throws \Molly\library\events\exceptions\InvalidEventHandlerException
     */
    public function addEventListener($eventType, &$eventHandler) {
        $interfaces = class_implements($eventHandler);
        if (isset($interfaces['Molly\library\events\interfaces\EventHandler'])) {
            $temp = new MollyArray($this->registeredHandlers);
            if ($temp->search($eventHandler) === false) {
                $this->registeredHandlers[$eventType][] = array('handler' => $eventHandler, 'added' => time(), 'classname' => "");
                return true;
            }

        } else {
            throw new HandlerException("Class must implement EventHandler interface to be added to listener list.");
        }

        return false;
    }

    public function removeEventListener($eventType, &$eventHandler) {
        $interfaces = class_implements($eventHandler);
        if (isset($interfaces['Molly\library\events\interfaces\EventHandler'])) {
            $temp = new MollyArray($this->registeredHandlers);
            if ($temp->search($eventHandler) !== false) {
                $temp = array_keys($temp[$eventType]);
                $handlerLocation = $temp[0];
                unset($this->registeredHandlers[$eventType][$handlerLocation]);
                return true;
            }
        } else {
            throw new HandlerException("Class must implement EventHandler interface to be removed from the listener list.");
        }

        return false;
    }
}
