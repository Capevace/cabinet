<?php

namespace Cabinet;

interface FileType
{
    public function name(): string;

    public function slug(): string;

    public function __toString(): string;

    public static function supportedMimeTypes(): array;
}
