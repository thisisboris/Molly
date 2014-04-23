<?php
/**
 * This file is part of Molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * Molly CMS - Written by Boris Wintein
 */
namespace Lucy\tracking;

use Lucy\exceptions\IllegalArgumentException;
use Lucy\tracking\objects\Cookie;

Class CookieFactory {
	private static $singleton;

    public static function &getInstance() {
        if (!isset(self::$singleton)) {
            self::$singleton = new CookieFactory();
        }

        return self::$singleton;
    }

	private function __construct() {

	}

	public function bake($name, $value = null, $expire = null, $path = null) {
		if (is_null($value) && is_null($expire) && is_null($path)) {
			return new Cookie($name);
		} else {
			$cookie = array();

			return new Cookie($cookie);
		}
	}

    public function serve($cookie) {
        if ($cookie instanceof Cookie) {
            if (setcookie($cookie->getName(), $cookie->getValue(), $cookie->getExpire())) {
                /**
                 * @TODO database implementation of DATA attribute of a cookie.
                 */


                return true;
            } else {
                return false;
            }
        } else {
            throw new IllegalArgumentException($cookie, 'Cookie');
        }
    }
}