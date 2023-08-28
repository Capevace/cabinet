<?php

namespace Cabinet\Types\Contracts;

interface HasMime
{
    public function getMime(): ?string;
    public function formattedMimeType(): ?string;
}
