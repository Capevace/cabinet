<?php

namespace Cabinet\Sources\Contracts;

use Cabinet\File;
use Cabinet\Folder;
use Illuminate\Http\UploadedFile;

interface AcceptsUploads
{
    public function upload(Folder $folder, UploadedFile $file, array $data = []): File;
}
