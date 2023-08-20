<?php

namespace Cabinet\Sources\Contracts;

use Cabinet\File;

interface CanBeDownloaded
{
    public function download(File $file): \Symfony\Component\HttpFoundation\Response;
}
