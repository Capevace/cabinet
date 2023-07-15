<?php

namespace Cabinet\Types;

class Directory implements \Cabinet\FileType
{
    use \Cabinet\Types\Concerns\StringableAsSlug;

    public function name(): string
    {
        return __('cabinet::directory');
    }

    public function slug(): string
    {
        return 'directory';
    }

    public static function supportedMimeTypes(): array
    {
        return ['directory'];
    }
}
