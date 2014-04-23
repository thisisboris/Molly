<?php
/**
 * This file is part of Molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * Molly CMS - Written by Boris Wintein
 */

namespace Lucy\events\interfaces;

interface Event
{
    function getName();
    function getMessage();

    public function getTarget();
    public function getFiredBy();
    public function getEventType();
}
