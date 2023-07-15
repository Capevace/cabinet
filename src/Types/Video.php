<?php

namespace Cabinet\Types;

class Video implements \Cabinet\FileType
{
    use \Cabinet\Types\Concerns\StringableAsSlug;

    public function name(): string
    {
        return __('cabinet::video');
    }

    public function slug(): string
    {
        return 'video';
    }

    public static function supportedMimeTypes(): array
    {
        return [
            'video/mp4',
            'video/mpeg',
            'video/ogg',
            'video/quicktime',
            'video/webm',
            'video/x-ms-wmv',
            'video/x-flv',
            'video/x-matroska',
            'video/3gpp',
            'video/3gpp2',
        ];
    }
}
