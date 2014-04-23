<?php
/**
 * This file is part of Molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * Molly CMS - Written by Boris Wintein
 */
 
namespace Lucy\io\cache;

use Lucy\events\Event;
use Lucy\io\buffer\Buffer as Buffer;
use Lucy\events\interfaces\EventHandler as EventHandler;

use Lucy\io\dataloaders\abstracts\AbstractHandler;

Class Archive extends AbstractHandler implements EventHandler {
    // Constants
    const CACHE_LOCATION = "cache";

    // Singleton
	public static $instance;



	public static function &getInstance() {
		if (!isset(self::$instance)) {
			self::$instance = &new Archive();
		} 

		return self::$instance;
	}

	/**
	 * Object
	 **/

	private $scribe, $scholar, $buffer;

	private function __construct() {
		$this->scribe = new Scribe();
		$this->scholar = new Scholar($this);

		$this->buffer = &Buffer::getInstance();
		$this->buffer->addEventListener(Buffer::EVENT_BUFFER_REGISTERED, $this);
		$this->buffer->addEventListener(Buffer::EVENT_BUFFER_UNREGISTERED, $this);
	}


	public function handleEvent(Event &$event, $eventdata) {
        switch ($event->getEventType()) {
            case Buffer::EVENT_BUFFER_REGISTERED:
                if ($this->scholar->knows($eventdata)) {
                    // Do an injection
                    $data = $this->scholar->load($eventdata);
                    $this->buffer->inject($eventdata['classname'], $eventdata['buffername'], $data);
                }
                break;

            case Buffer::EVENT_BUFFER_UNREGISTERED:
                $this->scribe->createCache($event, $eventdata);
                break;
        }
	}

    function load($identifier)
    {
        // Uses the Scholar to load an object
        return $this->scholar->load($identifier);
    }

    function locate($identifier)
    {
        return $this->scholar->locate($identifier);
    }

    function write(&$file, $overwrite = true)
    {
        return $this->scribe->write($file, $overwrite);
    }

    function append(&$file, $data)
    {
        $this->scribe->append($file, $data);
    }


    function __destruct()
    {
        die("ondestruct");
    }

    function __call($name, $arguments)
    {
        echo "<pre>";
        print_r($name);
        echo "<br/>";
        print_r($arguments);
        die("__call");
    }

    public static function __callStatic($name, $arguments)
    {
        echo "<pre>";
        print_r($name);
        echo "<br/>";
        print_r($arguments);
        die("__callStatic");
    }

    function __get($name)
    {
        echo "<pre>";
        print_r($name);
        die("__get");
    }

    function __set($name, $value)
    {
        echo "<pre>";
        print_r($name);
        echo "<br/>";
        print_r($value);
        die("__set");
    }

    function __isset($name)
    {
        echo "<pre>";
        print_r($name);
        die("__isset");
    }

    function __unset($name)
    {
        echo "<pre>";
        print_r($name);
        die("__unset");
    }

    function __sleep()
    {
        die("__sleep");
    }

    function __wakeup()
    {
        die("__wakeup");
    }

    function __invoke()
    {
        die("__invoke");
    }
}
