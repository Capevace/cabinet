<?php

namespace Cabinet\Sources\Contracts;

use Cabinet\File;
use Cabinet\Folder;

interface FindWithPath
{
    public function findWithPath(string $path, string $disk): File;
}
