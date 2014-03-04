<?php
/**
 * This file is part of Molly, an open-source content manager.
 * 
 * This application is licensed under the Apache License, found in LICENSE.TXT
 * 
 * Molly CMS - Written by Boris Wintein
 */


namespace Molly\library\out\messages\abstracts;

use \Molly\library\exceptions\IllegalArgumentException as IllegalArgumentException;
use Molly\library\out\messages\exceptions\MessageException;
use Molly\library\out\messages\interfaces\Message;

class AbstractMessage implements Message {

    public $_origin;

    private $_head;
    private $_contents;
    private $_level;

    const INFORMATION = 0;
    const WARNING = 1;
    const ERROR = 2;

    public function __construct( $level = AbstractMessage::INFORMATION, $head = null, $contents = null, $origin = null) {
        $this->setLevel($level);

        if (!is_null($head)) $this->setHead($head);
        if (!is_null($contents)) $this->setContents($contents);
        if (!is_null($origin)) $this->setOrigin($origin);
    }


    private function setLevel($level) {
        if (is_int($level) && ($level == self::INFORMATION || $level == self::WARNING || $level == self::ERROR)) {
            $this->_level = $level;
        } elseif (is_int($level)) {
            throw new MessageException("Tried setting level to an undefined int. Please use the class constants INFORMATION, WARNING or ERROR");
        } else {
            throw new IllegalArgumentException('Integer', $level);
        }
    }

    public function getLevel($asString = false) {
        if ($asString) {
            switch ($this->_level) {
                case self::INFORMATION:
                    return "information";
                    break;
                case self::WARNING:
                    return "warning";
                    break;
                case self::ERROR:
                    return "error";
                    break;
                default:
                    return "messages";
            }
        }
        return $this->_level;
    }

    public function getMessage()
    {
        return array($this->getHead(), $this->getContents());
    }

    public function printMessage()
    {
        echo "HEAD :: " . $this->getHead() . '<br/><br/>';
        echo "CONTENT :: " . $this->getContents() . '<br/><br/>';
    }

    public function setOrigin(&$origin)
    {
        if (!is_null($this->_origin)) throw new MessageException('Origin of the message was already set to ' . get_class($origin));
        $this->_origin = $origin;
    }

    public function removeOrigin()
    {
        $this->_origin = null;
    }

    public function &getOrigin()
    {
        return $this->_origin;
    }

    public function setHead($head)
    {
        if (!is_null($this->_head)) throw new MessageException('Head of the message was already set to ' . $this->_head);
        $this->_head = $head;
    }

    public function removeHead()
    {
        $this->_head = null;
    }

    public function getHead()
    {
        return $this->_head;
    }

    public function setContents($contents)
    {
        if (!is_null($this->_contents)) throw new MessageException('Contents of the message was already set to ' . $this->_contents);
        $this->_contents = $contents;
    }

    public function removeContents()
    {
        $this->_contents = null;
    }

    public function getContents()
    {
        return $this->_contents;
    }
}