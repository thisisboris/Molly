<?php
/**
 * @author Boris Wintein
 * @project molly
 */

namespace Lucy\io\streams\interfaces;


interface InputStream extends \Iterator {
    public function __destruct();
    public function read();
    public function close();
} 