<?php

namespace Cabinet\Sources\Contracts;

use Cabinet\File;
use Cabinet\Folder;

interface AcceptsData
{
    public function add(Folder $folder, array $data): File;
}
