<?php

namespace Cabinet\Types;

use Cabinet\Types\Concerns\StringableAsSlug;
use Cabinet\Types\Concerns\UsesDefaultIcon;
use Cabinet\Types\Concerns\WithMime;
use Cabinet\Types\Contracts\HasMime;

class DWG implements \Cabinet\FileType, HasMime
{
    use StringableAsSlug;
    use UsesDefaultIcon;
    use WithMime;

    public function name(): string
    {
        return __('cabinet::files.dwg');
    }

    public function slug(): string
    {
        return 'dwg';
    }

    public static function supportedMimeTypes(): array
    {
        return [
            'image/vnd.dwg',
            'application/vnd.dwg',
            'application/acad',
            'application/x-acad',
            'application/autocad',
            'application/dwg',
            'drawing/x-dwg',
        ];
    }

    public function formattedMimeType(): ?string
    {
        return match ($this->mime) {
            'image/vnd.dwg' => 'DWG',
            'application/vnd.dwg' => 'DWG',
            'application/acad' => 'AutoCAD',
            'application/x-acad' => 'AutoCAD',
            'application/autocad' => 'AutoCAD',
            'application/dwg' => 'DWG',
            'drawing/x-dwg' => 'DWG',
            default => $this->mime,
        };
    }
}
