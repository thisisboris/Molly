<?php
/**
 * This file is part of Molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * Molly CMS - Written by Boris Wintein
 */

namespace Molly\library\http\routing;
use Molly\library\http\routing\objects\Route;

class Router
{
    private $routes;

    private function addRoute(Route $route) {
        $this->routes[$route->getPath()] = $route;
    }
}
