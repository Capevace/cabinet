<?php

namespace Cabinet\Types;

class Image implements \Cabinet\FileType
{
    use \Cabinet\Types\Concerns\StringableAsSlug;

    public function name(): string
    {
        return __('cabinet::image');
    }

    public function slug(): string
    {
        return 'image';
    }

    public static function supportedMimeTypes(): array
    {
        return [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/svg+xml',
            'image/webp',
            'image/bmp',
            'image/tiff',
            'image/x-icon',
        ];
    }
}
