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

readonly class File
{
    public function __construct(
        public string $id,
        public string $source,
        public FileType $type,
        public string $name,
        public string $slug,
        public string $mimeType,
        public int $size,
        public string $url,
    ) {}
}
