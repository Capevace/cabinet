<?php

namespace Cabinet\Sources\Contracts;

use Cabinet\File;

interface HasContents
{
    public function contents(File $file): string;
}
