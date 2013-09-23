<?php
/**
 * This file is part of Molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * Molly CMS - Written by Boris Wintein
 */
 
namespace Molly\library\io\cache;

use Molly\library\events\Event;
use Molly\library\io\buffer\Buffer as Buffer;
use Molly\library\events\interfaces\EventHandler as EventHandler;

Class Archive implements EventHandler {	
	/**
	 * Singleton
	 **/
	private static $instance;

	public static function &getInstance() {
		if (!isset(self::$instance)) {
			self::$instance = new Archive();
		} 

		return self::$instance;
	}

	/**
	 * Object
	 **/

	private $scribe, $scholar, $buffer;

	private function __construct() {
		$this->scribe = new Scribe();
		$this->scholar = new Scholar();

		$this->buffer = Buffer::getInstance();
		$this->buffer->addEventListener(Buffer::BUFFER_REGISTERED, $this);
		$this->buffer->addEventListener(Buffer::BUFFER_UNREGISTERED, $this);
	}


	public function handleEvent(&$event, $eventdata) {
        if ($event instanceof Event) {
            switch ($event->getEventType()) {
                case Buffer::BUFFER_REGISTERED:
                    if ($this->scholar->knows($eventdata)) {
                        // Do an injection
                        $data = $this->scholar->load($eventdata);
                        $this->buffer->inject($eventdata['classname'], $eventdata['buffername'], $data);
                    }
                    break;

                case Buffer::BUFFER_UNREGISTERED:
                    $this->scribe->write($eventdata);
                    break;
            }
        } else {
            throw new \InvalidArgumentException("Expected an event to handle of type event, got " . gettype($event) . " - " . get_class($event));
        }
	}
}