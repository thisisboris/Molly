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
use Lucy\exceptions\InvalidConstructorException;

abstract class AbstractEvent implements Event
{
    protected $name, $message, $target, $firedBy, $eventType, $extraData;

    public function __construct($name, $message, $target, $firedBy, $eventType, $extraData = null) {
        if (is_string($name) && is_string($message) && is_object($target) && is_object($firedBy) && is_string($eventType)) {
            $this->name = $name;
            $this->message = $message;
            $this->target = $target;
            $this->firedBy = $firedBy;
            $this->eventType = $eventType;

        } else {
            throw new InvalidConstructorException("Name, Message, Target, FiredBy and EventType must be filled in and valid type");
        }
    }
}
