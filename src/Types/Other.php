<?php

namespace Cabinet\Types;

use Cabinet\Types\Concerns\StringableAsSlug;
use Cabinet\Types\Concerns\UsesDefaultIcon;
use Cabinet\Types\Concerns\WithMime;
use Cabinet\Types\Contracts\HasMime;

class Other implements \Cabinet\FileType, HasMime
{
    use StringableAsSlug;
    use UsesDefaultIcon;
    use WithMime;

    public function name(): string
    {
        return $this->mime
            ? __('cabinet::files.other') . " ({$this->mime})"
            : __('cabinet::files.other');
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
