<?php
/**
 * This file is part of Molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * Molly CMS - Written by Boris Wintein
 */
namespace Molly\library\events;

use Molly\library\events\interfaces\Event as iEvent;
use Molly\library\exceptions\InvalidConstructorException as ConstructException;

class Event implements iEvent
{
    private $name, $message, $target, $firedBy, $eventType, $extraData;

    public function __construct($name, $message, $target, $firedBy, $eventType, $extraData = null) {
        if (is_string($name) && is_string($message) && is_object($target) && is_object($firedBy) && is_string($eventType)) {
            $this->name = $name;
            $this->message = $message;
            $this->target = $target;
            $this->firedBy = $firedBy;
            $this->eventType = $eventType;

        } else {
            throw new ConstructException("Name, Message, Target, FiredBy and EventType must be filled in and valid type");
        }
    }

    public final function getMessage() {
        return $this->message;
    }

    public final function getName(){
        return $this->name;
    }

    public function getTarget() {
        return $this->target;
    }

    public function getFiredBy() {
        return $this->firedBy;
    }

    public function getEventType() {
        return $this->eventType;
    }

    public function getEventData() {
        return $this->extraData;
    }
}
