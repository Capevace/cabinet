<?php

namespace Cabinet\Sources\Contracts;

use Cabinet\File;

interface CanGenerateUrls
{
    public function generateUrl(File $file, ?string $variant = null): ?string;
}
