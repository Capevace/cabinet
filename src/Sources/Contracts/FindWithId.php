<?php

namespace Cabinet\Sources\Contracts;

use Cabinet\File;
use Cabinet\Folder;

interface FindWithId
{
    public function findWithID(string $id): File;
}
