<?php

namespace Cabinet\Types;

use Cabinet\Types\Concerns\StringableAsSlug;
use Cabinet\Types\Concerns\UsesDefaultIcon;

class Other implements \Cabinet\FileType
{
    use StringableAsSlug;
    use UsesDefaultIcon;

    public function name(): string
    {
        return __('cabinet::files.other');
    }

    public function slug(): string
    {
        return 'other';
    }

    public static function supportedMimeTypes(): array
    {
        return [];
    }
}
