<?php
/**
 * This file is part of molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * molly CMS - Written by Boris Wintein
 */
namespace Lucy\tracking\objects;

use Lucy\exceptions\IllegalArgumentException;

Class Cookie {

    /**
     * @var $name, $value
     * Name and value for the cookie. Name must be a string/int, value can be anything.
     */
    protected $name, $value;

    /**
     * @var $expire
     * Expire date in a unix timestamp. Standard is now + 1 day.
     */
    protected $expire;

    /**
     * @var $data
     * Additional data that can be saved along with the cookie.
     */
    protected $data;

	public function __construct($cookie) {

		if ($cookie instanceof Cookie) {
			$this->setName($cookie->getName());
			$this->setValue($cookie->getValue());			
			$this->setExpire($cookie->getExpire());
            $this->setData($cookie->getData());

		} else if (is_array($cookie)) {
			$this->setName($cookie['name']);
			$this->setValue($cookie['value']);
			$this->setExpire($cookie['expire']);
            $this->setData($cookie['data']);

		} else if (is_string($cookie)) {
			$this->setName($cookie);

			// Standard expire of 1 day.
			$this->setExpire(time() + 86400);
		}
	}

	public function setName($name) {
		if (is_string($name)) {
			$this->name = $name;
		} else {
			throw new IllegalArgumentException($name, 'String');
		}		
	}

	public function setValue($value) {
		// Setting cookies to false is a bad idea.
		if (is_bool($value)) {
			$value = $value ? 1 : 0;
		}

		$this->value = $value;
	}

	public function setExpire($expire) {
		if (is_int($expire)) {
			$this->expire = $expire;
		} else {
			throw new IllegalArgumentException($expire, "Integer");
		}
	}

    public function setData($data) {
        $this->data = $data;
    }

    public function getName() {
        return $this->name;
    }

    public function getValue() {
        return $this->value;
    }

    public function getBool() {
        if (is_string($this->value)) {
            return true;
        } else if (is_int($this->value)) {
            return ($this->value > 0);
        } else if (is_null($this->value)) {
            return false;
        } else {
            return false;
        }
    }

    public function getData() {
        return $this->data;
    }

    public function getExpire() {
        return $this->expire;
    }
}