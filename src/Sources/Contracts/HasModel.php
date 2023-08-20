<?php

namespace Cabinet\Sources\Contracts;

use Cabinet\File;
use Illuminate\Database\Eloquent\Model;

interface HasModel
{
    public function getFileModel(File $file): Model;
}
