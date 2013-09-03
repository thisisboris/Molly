<?php
/**
 * This file is part of Molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * Molly CMS - Written by Boris Wintein
 */

namespace Molly\library\utils;
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

    function array_search($needle) {
        foreach($this->array as $key => $value) {
            if (is_array($value) && !is_array($needle)) {
                $tempArray = new MollyArray($value);
                $arrayFind = $tempArray->array_search($needle);

                if (!$arrayFind) {
                    continue;
                } else {
                    return array($key => $arrayFind);
                }

            } elseif ($value === $needle) {
                return array ($key => $value);
            }
        }
        return false;
    }

    function array_key_contains($needle) {
        foreach ($this->array as $key => $value) {
            if ($key == $needle) {
                return true;
            } elseif (is_array($value)) {
                $tempArray = new MollyArray($value);
                if ($tempArray->array_key_contains($needle)) {
                    return true;
                }
            }
        }

        return false;
    }

    
    function array_extract($needle) {
        if (!$this->array_key_contains($needle)) {
            return false;
        } else {
            foreach ($this->array as $key => $value) {
                if ($key == $needle) {
                    return $value;
                } elseif (is_array($value)) {
                    $tempArray = new MollyArray($value);
                    if (($result = $tempArray->array_extract($needle)) != false) {
                        return $result;
                    }
                }
            }
        }

        return false;
    }
}
