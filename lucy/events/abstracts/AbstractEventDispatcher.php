<?php
/**
 * Lucy Library
 * This file is part of the Lucy Library, an open source framework built for Molly.
 *
 * @author Boris Wintein
 * @website http://www.thisisboris.be
 *
 */

namespace Lucy\events\abstracts;


use Lucy\events\interfaces\Event;
use Lucy\events\interfaces\EventDispatcher;
use Lucy\events\interfaces\EventHandler;
use Lucy\events\exceptions\InvalidEventHandlerException;
use Lucy\exceptions\IllegalArgumentException;
use Lucy\utils\collection\MollyArray as MollyArray;

abstract class AbstractEventDispatcher implements EventDispatcher
{
    /**
     * Multidimensional array containing all eventHandlers
     * @var array
     */
    protected $registeredHandlers = array();

    public function dispatchEvent(Event &$event)
    {
        if ($event instanceof Event) {
            $type = $event->getEventType();
            if (array_key_exists($type, $this->registeredHandlers) && is_array($this->registeredHandlers[$type])) {
                foreach ($this->registeredHandlers[$type] as $handlerInfo) {
                    $handler = $handlerInfo['handler'];
                    $handler->handleEvent($type, $event);
                }
            }
        } else {
            throw new IllegalArgumentException($event, "Event");
        }
    }

    /**
     *  Function that adds eventhandlers to a list so that they can be called when an event is dispatched.
     *
     * @param $eventType
     * @param $eventHandler
     * @return mixed|void
     * @throws \Lucy\events\exceptions\InvalidEventHandlerException
     */
    public function addEventListener($eventType, EventHandler &$eventHandler) {
        $interfaces = class_implements($eventHandler);
        if (isset($interfaces['Lucy\events\interfaces\EventHandler'])) {
            $temp = new MollyArray($this->registeredHandlers);
            if ($temp->search($eventHandler) === false) {
                $this->registeredHandlers[$eventType][] = array('handler' => $eventHandler, 'added' => time(), 'classname' => "");
                return true;
            }

        } else {
            throw new InvalidEventHandlerException("Class must implement EventHandler interface to be added to listener list.");
        }

        return false;
    }

    public function removeEventListener($eventType, EventHandler &$eventHandler) {
        $interfaces = class_implements($eventHandler);
        if (isset($interfaces['Lucy\events\interfaces\EventHandler'])) {
            $temp = new MollyArray($this->registeredHandlers);
            if ($temp->search($eventHandler) !== false) {
                $temp = array_keys($temp[$eventType]);
                $handlerLocation = $temp[0];
                unset($this->registeredHandlers[$eventType][$handlerLocation]);
                return true;
            }
        } else {
            throw new InvalidEventHandlerException("Class must implement EventHandler interface to be removed from the listener list.");
        }

        return false;
    }
}
