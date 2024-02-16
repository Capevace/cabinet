<?php

namespace Cabinet;

interface FileType
{
    public function name(): string;

    public function slug(): string;

    public function icon(): string;

    public function extension(): ?string;

    public function __toString(): string;

    public static function supportedMimeTypes(): array;
}
