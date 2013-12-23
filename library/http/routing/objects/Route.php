<?php
/**
 * This file is part of Molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * Molly CMS - Written by Boris Wintein
 */

namespace Molly\library\http\routing\objects;

class Route
{
    /**
     * @var string $path
     * @description contains the path in the website
     */
    private $path;

    /**
     * @var Controller $controller
     * @description contains the controller for this path
     */
    private $controller = "controller";

    /**
     * @return string - Path of the website
     */
    public function getPath() {
        return $this->path;
    }

    public function &getController() {
        return $this->controller;
    }


    public function __get($name) {
    return $this->controller;
}
}
