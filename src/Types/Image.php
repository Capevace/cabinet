<?php

namespace Cabinet\Types;

use Cabinet\Types\Concerns\StringableAsSlug;
use Cabinet\Types\Concerns\UsesDefaultIcon;
use Cabinet\Types\Concerns\WithMime;

class Image implements \Cabinet\FileType
{
    use StringableAsSlug;
    use UsesDefaultIcon;
    use WithMime;

    public function name(): string
    {
        return ($mime = $this->formattedMimeType())
            ? __('cabinet::files.image') . " ({$mime})"
            : __('cabinet::files.image');
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

    public function formattedMimeType(): ?string
    {
        return match ($this->mime) {
            'image/jpeg' => 'JPEG',
            'image/png' => 'PNG',
            'image/gif' => 'GIF',
            'image/svg+xml' => 'SVG',
            'image/webp' => 'WebP',
            'image/bmp' => 'BMP',
            'image/tiff' => 'TIFF',
            'image/x-icon' => 'ICO',

            default => $this->mime,
        };
    }
}
