<?php

namespace Cabinet\Types;

use Cabinet\Types\Concerns\StringableAsSlug;
use Cabinet\Types\Concerns\UsesDefaultIcon;

class PDF implements \Cabinet\FileType
{
    use StringableAsSlug;
    use UsesDefaultIcon;

    public function name(): string
    {
        return __('cabinet::files.pdf');
    }

    public function slug(): string
    {
        return 'pdf';
    }

    public static function supportedMimeTypes(): array
    {
        return [
            'application/pdf',
        ];
    }
}
