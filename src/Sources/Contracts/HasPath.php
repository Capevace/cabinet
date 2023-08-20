<?php

namespace Cabinet\Sources\Contracts;

use Cabinet\File;

interface HasPath
{
    public function path(File $file): string;
}
