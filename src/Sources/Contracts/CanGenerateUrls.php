<?php

namespace Cabinet\Sources\Contracts;

use Cabinet\File;
use DateTimeInterface;

interface CanGenerateUrls
{
    public function generateUrl(File $file, ?string $variant = null, ?DateTimeInterface $expiresAt = null): ?string;
}
