<?php
/**
 * This file is part of Molly, an open-source content manager.
 * 
 * This application is licensed under the Apache License, found in LICENSE.TXT
 * 
 * Molly CMS - Written by Boris Wintein
 */


namespace Lucy\out\messages\interfaces;


interface Message {
    public function getMessage();
    public function printMessage();

    public function setOrigin(&$origin);
    public function removeOrigin();
    public function &getOrigin();

    public function setHead($head);
    public function removeHead();
    public function getHead();

    public function setContents($contents);
    public function removeContents();
    public function getContents();

} 