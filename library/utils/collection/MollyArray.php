<?php
/**
 * This file is part of Molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * Molly CMS - Written by Boris Wintein
 */

namespace Molly\library\utils\collection;
use Molly\library\exceptions\InvalidConstructorException as ConstructException;

class MollyArray
{
    private $array;

    function __construct(&$array)
    {
        if (is_array($array)) {
            $this->array = $array;
        } else {
            throw new ConstructException();
        }
    }

    function search($needle) {
        foreach($this->array as $key => $value) {
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
        foreach ($this->array as $key => $value) {
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
            foreach ($this->array as $key => $value) {
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
            $this->array[] = $value;
        } else if ($overwrite) {
            $this->array[$key] = $value;
        } else {
            if (!isset($this->array[$key])) {
                $this->array[$key] = $value;
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
            if (isset($this->array[$value])) {
                unset($this->array[$value]);
                return true;
            }
        } else {
            foreach($this->array as $key => $content) {
                if ($value == $content) {
                    unset($this->array[$key]);
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
}
