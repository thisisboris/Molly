<?php
/**
 * This file is part of Molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * Molly CMS - Written by Boris Wintein
 */
namespace Molly\library\events;
use \Molly\library\events\abstracts\AbstractEvent;

class Event extends AbstractEvent
{
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
