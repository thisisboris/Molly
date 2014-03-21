<?php
/**
 * This file is part of molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * molly CMS - Written by Boris Wintein
 */

namespace Lucy\http\routing\abstracts;
use Lucy\exceptions\IllegalArgumentException;
use Lucy\http\routing\exceptions\RoutingException;
use Lucy\http\routing\interfaces\Controller;
use Lucy\http\routing\interfaces\Route;

abstract class AbstractRoute implements Route
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
    public $controller;

    /**
     * @param $path
     * @throws \Lucy\exceptions\IllegalArgumentException
     * @throws \Lucy\http\routing\exceptions\RoutingException
     */
    public function setPath($path) {
        if (isset($this->path) && !is_null($this->path)) throw new RoutingException('The path was already set to ' . $path . ' for this Route. Remove it first to change it');
        if (!is_string($path)) throw new IllegalArgumentException($path, 'String');
        $this->path = $path;
    }

    /**
     * @description Unsets the path from this route.
     */
    public function removePath() {
        $this->path = null;
    }

    /**
     * @return string - Path of the website
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * @param Controller $controller
     * @throws \Lucy\http\routing\exceptions\RoutingException
     */
    public function setController(Controller &$controller) {
        if (isset($this->controller) && !is_null($this->controller)) throw new RoutingException('The controller was already set for this route. Remove it first to change it.');
        $this->controller = $controller;
    }

    /**
     *
     */
    public function removeController() {
        $this->controller = null;
    }

    /**
     * @return Controller
     */
    public function &getController() {
        return $this->controller;
    }
}
