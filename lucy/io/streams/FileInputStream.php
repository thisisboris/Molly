<?php
/**
 * @author Boris Wintein
 * @project molly
 */

namespace Lucy\io\streams;


use Lucy\io\abstracts\streams\AbstractInputStream;
use Lucy\io\dataloaders\files\interfaces\File;

class FileInputStream extends AbstractInputStream {

    private $file;

    public function __construct(File &$file) {
        $this->file = $file;
        parent::__construct(fopen($this->file->getFilePath(), 'r'));
    }
} 