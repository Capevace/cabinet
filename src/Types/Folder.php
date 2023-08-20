<?php

namespace Cabinet\Types;

use Cabinet\Types\Concerns\StringableAsSlug;

class Folder implements \Cabinet\FileType
{
    use StringableAsSlug;

    public function name(): string
    {
        return __('cabinet::files.folder');
    }

    public function slug(): string
    {
        return 'folder';
    }

    public function icon(): string
    {
        return 'heroicon-o-folder';
    }

    public static function supportedMimeTypes(): array
    {
        return ['folder'];
    }
}
