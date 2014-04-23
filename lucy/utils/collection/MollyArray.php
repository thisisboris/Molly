<?php
/**
 * This file is part of Molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * Molly CMS - Written by Boris Wintein
 */

namespace Lucy\utils\collection;
use Lucy\exceptions\InvalidConstructorException as ConstructException;

class MollyArray
{
    public $_array;

    function __construct(&$array)
    {
        if (is_array($array)) {
            $this->_array = &$array;
        } else {
            throw new ConstructException();
        }

        return $this;
    }

    function __destroy() {
        unset($this->_array);
    }

    function __get($argument) {
        return false;
    }

    function search($needle) {
        foreach($this->_array as $key => $value) {
            if (is_array($value) && !is_array($needle)) {
                $tempArray = new MollyArray($value);
                $arrayFind = $tempArray->search($needle);

                if ($arrayFind) return array($key => $arrayFind);

            } elseif ($value === $needle) {
                return array ($key => $value);
            }
        }
        return false;
    }

    function contains_key($needle) {
        foreach ($this->_array as $key => $value) {
            if ($key == $needle) {
                return true;
            } elseif (is_array($value)) {
                $tempArray = new MollyArray($value);
                if ($tempArray->contains_key($needle)) {
                    return true;
                }
            }
        }

        return false;
    }

    
    function extract($needle) {
        if (!$this->contains_key($needle)) {
            return false;
        } else {
            foreach ($this->_array as $key => $value) {
                if ($key == $needle) {
                    return $value;
                } elseif (is_array($value)) {
                    $tempArray = new MollyArray($value);
                    if (($result = $tempArray->extract($needle)) != false) {
                        return $result;
                    }
                }
            }
        }

        return false;
    }

    function add($value, $key = null, $overwrite = true) {
        if (is_null($key)) {
            $this->_array[] = $value;
        } else if ($overwrite) {
            $this->_array[$key] = $value;
        } else {
            if (!isset($this->_array[$key])) {
                $this->_array[$key] = $value;
            } else {
                throw new \Exception("Array key has already been set. To overwrite set overwrite flag to true");
            }
        }
    }

    function remove($value, $iskey = false) {
        /**
         * @TODO make this a correct multidimensional remove.
         */

        if ($iskey) {
            if (isset($this->_array[$value])) {
                unset($this->_array[$value]);
                return true;
            }
        } else {
            foreach($this->_array as $key => $content) {
                if ($value == $content) {
                    unset($this->_array[$key]);
                    return true;
                } else if (is_array($content)) {
                    $content = new MollyArray($content);
                    if ($content->remove($value)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    function is_assoc() {
        $keys = array_keys($this->_array);
        foreach ($keys as $key) {
            if (is_string($key)) {
                return true;
            }
        }
        return false;
    }

    function flatten() {
        return $this->flattenPiece($this->_array);
    }

    private function flattenPiece($array) {
        $return = '';
        foreach ($array as $value) {
            if (is_array($value)) {
                $return .= $this->flattenPiece($value);
            } else {
                $return .= $value . ' ';
            }
        }
        return rtrim($return);
    }

    function __toString() {
        return $this->dumpArray($this->_array);
    }

    private function dumpArray($array, $depth = 0) {
        $string = str_repeat('   ', $depth);
        foreach ($array as $key => $value) {
            $string .= "[$key] => ";
            if (is_array($value)) {
                $string .= '(';
                $string .= '\n';
                $string .= $this->dumpArray($array, ++$depth);
                $string .= '\n';
                $string .= ')';
            } else {
                $string .= $value;
                $string .= '\n';
            }
        }
        return $string;
    }
}
