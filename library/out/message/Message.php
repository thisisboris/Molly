<?php
/**
 * This file is part of Molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * Molly CMS - Written by Boris Wintein
 */

namespace Molly\library\out\message;

use \Molly\library\exceptions\IllegalArgumentException as IllegalArgumentException;
use \Molly\library\exceptions\InvalidConstructorException as InvalidConstructorException;

class Message
{
    private $message;
    private $level;
    const INFORMATION = 0;
    const WARNING = 1;
    const ERROR = 2;

    public function __construct($message, $level = Message::INFORMATION) {
        try {
            $this->setMessage($message);
            $this->setLevel($level);
        } catch (IllegalArgumentException $iae) {
            throw new InvalidConstructorException($iae);
        }
    }

    private function setMessage($message) {
        if (is_string($message)) {
            $this->message = $message;
        } else {
            throw new IllegalArgumentException("Expected var message to be a string, got " . gettype($message) . " :: " . get_class($message));
        }
    }

    private function setLevel($level) {
        if (is_int($level) && ($level == self::INFORMATION || $level == self::WARNING || $level == self::ERROR)) {
            $this->level = $level;
        } else {
            throw new IllegalArgumentException("Expected var level to be an int, got " . gettype($level) . " :: " . get_class($level));
        }
    }

    public function getStyledMessage() {
        $html = '<div class="message ' . $this->getLevel(true) . '">';
        $html .= '<p>' . $this->getMessage() . '</p>';
        $html .= '</div>';
        return $html;
    }

    public function getPlainMessage() {
        return $this->getLevel(true) . " :: " . $this->getMessage();
    }

    private function getMessage() {
        return $this->message;
    }

    private function getLevel($asString = false) {
        if ($asString) {
            switch ($this->level) {
                case self::INFORMATION:
                    return "information";
                break;
                case self::WARNING:
                    return "warning";
                break;
                case self::ERROR:
                    return "error";
                break;
            }
        } else {
            return $this->level;
        }
    }
}
