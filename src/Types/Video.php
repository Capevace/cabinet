<?php

namespace Cabinet\Types;

use Cabinet\Types\Concerns\StringableAsSlug;
use Cabinet\Types\Concerns\UsesDefaultIcon;

class Video implements \Cabinet\FileType
{
    use StringableAsSlug;
    use UsesDefaultIcon;

    public function name(): string
    {
        return __('cabinet::files.video');
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
