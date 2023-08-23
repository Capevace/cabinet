<?php

namespace Cabinet\Types;

use Cabinet\Types\Concerns\StringableAsSlug;
use Cabinet\Types\Concerns\UsesDefaultIcon;
use Cabinet\Types\Concerns\WithMime;

class Video implements \Cabinet\FileType
{
    use StringableAsSlug;
    use UsesDefaultIcon;
    use WithMime;

    public function name(): string
    {
        return ($mime = $this->formattedMimeType())
            ? __('cabinet::files.video') . " ({$mime})"
            : __('cabinet::files.video');
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

    public function formattedMimeType(): ?string
    {
        return match ($this->mime) {
            'video/mp4' => 'MP4',
            'video/mpeg' => 'MPEG',
            'video/ogg' => 'OGG',
            'video/quicktime' => 'QuickTime',
            'video/webm' => 'WebM',
            'video/x-ms-wmv' => 'WMV',
            'video/x-flv' => 'FLV',
            'video/x-matroska' => 'Matroska',
            'video/3gpp' => '3GPP',
            'video/3gpp2' => '3GPP2',

            default => $this->mime,
        };
    }
}
