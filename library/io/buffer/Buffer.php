<?php
/**
 * This file is part of Molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * Molly CMS - Written by Boris Wintein
 */
 
namespace Molly\library\io\buffer;

use Molly\library\events\EventDispatcher;
use Molly\library\events\Event;

use Molly\library\io\buffer\exceptions\BufferAlreadyRegisteredException;
use Molly\library\io\buffer\exceptions\BufferNeverRegisteredException;
use Molly\library\io\buffer\exceptions\InvalidBufferCallbackException;
use Molly\library\io\buffer\exceptions\InvalidBufferOrderException;

/**
 * Buffer singleton
 **/
class Buffer extends EventDispatcher {
	/** Singleton Instance **/
	private static $instance;

	public static function &getInstance() {
		if (!isset(self::$instance) || is_null(self::$instance)) self::$instance = new Buffer();
		return self::$instance;
	}

	/** INSTANCE **/
	private $encoding , $encoded;
    private $stack = array();

    private $injections;

    /** EVENT CONSTANTS **/
    const BUFFER_REGISTERED = "buffer was succesfully registered";
    const BUFFER_UNREGISTERED = "buffer was succesfully unregistered";

	private function __construct() {
	 	if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') && ($ext_zlib_loaded = extension_loaded('zlib')) && (PHP_VERSION >= '4')){
            $this->encoded = true;
            $this->encoding = "gzip";
         }
	}

	public function start($classname, $buffername, $callback = "") {
		$bufferinfo = array('classname' => $classname, 
							'buffername' => $buffername, 
							'callback' => $callback, 
							'timestamp' => time());
		
		if (in_array($bufferinfo, $this->stack)) {
			throw new BufferAlreadyRegisteredException();
		} else {
			if ($callback != null && $callback != "" && !is_callable($callback)) {
		        throw new InvalidBufferCallbackException();
			}

			array_push($this->stack, $bufferinfo);

			$event = new Event("BUFFER_REGISTERED", "A buffer was added to the stack", $bufferinfo, $this, self::BUFFER_REGISTERED);
			$this->dispatchEvent($event);

			if ($this->isEncoded() && !headers_sent()) {
		            ob_start("ob_gzhandler");
	        } else {
		            ob_start();
	        }
		}
	}

	public function check($classname, $buffername, $injection = false) {
		if (!$injection) {
			$bufferinfo = $this->stack[count($this->stack) - 1];
			return ($bufferinfo['classname'] == $classname && $bufferinfo['buffername'] == $buffername);
		} else {
			foreach ($this->injections as $key => $injectioninfo) {
				if ($injectioninfo['classname'] == $classname && $injectioninfo['buffername'] == $buffername) {
					return $key;
				}
			}

			return false;
		}		
	}

	public function stop($classname, $buffername) {

		// Check whether the buffer we want to stop is in fact this top most buffer
		if ($this->check($classname, $buffername)) {
			// Get top most buffer info
			$bufferinfo = $this->stack[count($this->stack) - 1];

			$data = ob_get_contents();
            ob_end_clean();
            
            if (isset($bufferinfo['callback']) && $bufferinfo['callback'] != "" && is_callable($bufferinfo['callback'])) {
           		call_user_func($bufferinfo['callback'], $data);
            }

			$event = new Event("BUFFER_UNREGISTERED", "A buffer was closed and removed from the stack", $bufferinfo, $this, self::BUFFER_UNREGISTERED, $data);
			$this->dispatchEvent($event);

            return $data;

        // Check if the buffer we want to stop has been injected with data.
		} else if (($injectionId = $this->check($classname, $buffername, true)) !== false) {

			$bufferinfo = $this->injections[$injectionId];
			$data = $this->injections[$injectionId]['data'];

			if (isset($bufferinfo['callback']) && $bufferinfo['callback'] != "" && is_callable($bufferinfo['callback'])) {
           		call_user_func($bufferinfo['callback'], $data);
            }

			$event = new Event("BUFFER_UNREGISTERED", "A buffer was closed and removed from the stack", $bufferinfo, $this, self::BUFFER_UNREGISTERED, $data);
			$this->dispatchEvent($event);

            return $data;

		} else {
			throw new InvalidBufferOrderException();
		}
	}

	/**
	 * Stops a buffer without letting anybody know. Useful for injections.
	 **/
	private function silent_stop($classname, $buffername, $stackId) {
		if (isset($this->stack[$stackId]) && $this->stack[$stackId]['classname'] == $classname && $this->stack[$stackId]['buffername'] == $buffername) {
			if ($stackId == (count($this->stack) - 1)) {
				$data = ob_get_contents();
				ob_end_clean();
				unset($this->stack[$stackId]);
				return $data;
			} else {
				$temparray = array();

				// Stop running buffers in correct order and save the data they have captured so far.
				for ($i = (count($this->stack) - 1); $i > $stackId; $i--) {
					$temparray[$i]['data'] = ob_get_contents();
					ob_end_clean();
					$temparray[$i]['bufferinfo'] = $this->stack[$i];
					unset($this->stack[$i]);
				}	

				// The buffer that needs to be stopped silently is now the topmost buffer. We can safely close it.
				$data = ob_get_contents();
				ob_end_clean();
				unset($this->stack[$stackId]);

				// Reverse the array, so that the buffer that has to be started first is the first element.
				$temparray = array_reverse($temparray, true);

				// Restart the stopped buffers in the correct order, echo'ing the data they previously captured.
				foreach ($temparray as $info) {
					$bufferinfo = $info['bufferinfo'];
					$this->stack[] = $bufferinfo;

					if ($this->isEncoded() && !headers_sent()) {
				            ob_start("ob_gzhandler");
			        } else {
				            ob_start();
			        }

		        	// Print the captured data to the buffer.
			        echo $info['data'];
				}

				// Return the data of the stopped buffer
				return $data;

			}
		} else {
			throw new InvalidBufferOrderException();
		}
	}

	public function inject($classname, $buffername, $data) {
		foreach ($this->stack as $id => $bufferinfo) {
			if ($bufferinfo['classname'] == $classname && $bufferinfo['buffername'] == $buffername) {
				// Get the injection info
				$injectionInfo = array_merge($bufferinfo, array('data' => $data));

				// End the buffer silently
				$this->silent_stop($classname, $buffername, $id);

				// Return the new bufferinfo
				return $this->injections[] = $injectionInfo;
			} elseif ($id == count($this->stack) - 1) {
				throw new BufferNeverRegisteredException();
			}
		}

        return false;
	}

	public function isEncoded() {
		return $this->encoded;
	}

	public function getStack() {
		return $this->stack;
	}
}