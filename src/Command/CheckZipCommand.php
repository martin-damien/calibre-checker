<?php

namespace App\Command;

final class CheckZipCommand
{
    public $file;

    public function __construct(\SplFileInfo $file)
    {
        $this->file = $file;
    }
}