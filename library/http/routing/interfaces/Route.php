<?php
/**
 * This file is part of Molly, an open-source content manager.
 * 
 * This application is licensed under the Apache License, found in LICENSE.TXT
 * 
 * Molly CMS - Written by Boris Wintein
 */


namespace Molly\library\http\routing\interfaces;


interface Route {
    public function setPath($path);
    public function removePath();
    public function getPath();
    public function setController(Controller &$controller);
    public function removeController();
    public function &getController();
} 