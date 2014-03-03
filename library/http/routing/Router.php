<?php
/**
 * This file is part of Molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * Molly CMS - Written by Boris Wintein
 */

namespace Molly\library\http\routing;
use Molly\library\events\abstracts\AbstractEventDispatcher;
use Molly\library\http\routing\exceptions\RoutingException;
use Molly\library\http\routing\interfaces\Route;

class Router extends AbstractEventDispatcher
{
    public static $singleton = null;

    private $GET, $SERVER;
    private $routes;

    public static function &getInstance() {
        if (self::$singleton == null) { self::$singleton = new self();}
        return self::$singleton;
    }

    private function __construct() {
        $this->$SERVER = $_SERVER;
        $this->$GET = $_GET;
    }

    public function addRoute(Route &$route) {
        if (in_array($route, $this->routes)) throw new RoutingException('This route was already added to the router.');

        $this->routes[$route->getPath()] = $route;
    }

    public function removeRoute(Route &$route) {
        if (($key = array_search($route, $this->routes)) !== false) {
            unset($this->routes[$key]);
        }
    }

    /**
     *
     */
    public function travel() {

    }
}
