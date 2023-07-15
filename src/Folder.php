<?php

namespace Cabinet;

//interface File
//{
//    public function id(): string;
//
//    public function source(): string;
//
//    public function type(): FileType;
//
//    public function name(): string;
//
//    public function slug(): string;
//
//    public function mimeType(): string;
//
//    public function size(): int;
//
//    public function url(): string;
//}

use Cabinet\Models\Directory;

readonly class Folder
{
    public function __construct(
        public string $id,
        public string $source,
        public string $name,
    ) {
    }

    public function isCabinetFolder(): bool
    {
        return $this->source === 'cabinet';
    }
}
