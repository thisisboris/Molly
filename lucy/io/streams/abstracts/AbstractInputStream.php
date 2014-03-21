<?php
/**
 * @author Boris Wintein
 * @project molly
 */

namespace Lucy\io\abstracts\streams;

use Lucy\io\streams\interfaces\InputStream;

abstract class AbstractInputStream implements InputStream {

    private $cursor = 0;
    private  $resource;
    private $char;

    public function __construct(&$resource)
    {
        $this->resource = &$resource;
    }

    public function read()
    {
        fseek($this->resource, $this->cursor++, SEEK_SET);
        return $this->char = fgetc($this->resource);
    }

    public function close()
    {
        fclose($this->resource);
    }

    function __destruct()
    {
        fclose($this->resource);
        $this->char = '';
        $this->cursor = 0;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        return fgetc($this->resource);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->cursor++;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        $this->cursor;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        return fseek($this->resource, $this->cursor, SEEK_SET) !== false;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->cursor = 0;
    }
}