<?php

namespace Cabinet\Types;

use Cabinet\Types\Concerns\StringableAsSlug;
use Cabinet\Types\Concerns\UsesDefaultIcon;
use Cabinet\Types\Concerns\WithMime;
use Cabinet\Types\Contracts\HasMime;

class Audio implements \Cabinet\FileType, HasMime
{
    use StringableAsSlug;
    use UsesDefaultIcon;
    use WithMime;

    public function name(): string
    {
        return ($mime = $this->formattedMimeType())
            ? __('cabinet::files.audio') . " ({$mime})"
            : __('cabinet::files.audio');
    }

    public function slug(): string
    {
        return 'audio';
    }

    public static function supportedMimeTypes(): array
    {
        return [
            'audio/mpeg',
            'audio/ogg',
            'audio/wav',
            'audio/webm',
            'audio/x-m4a',
            'audio/x-aac',
            'audio/x-aiff',
            'audio/x-flac',
            'audio/x-matroska',
            'audio/x-ms-wma',
            'audio/x-wav',
        ];
    }

    public function formattedMimeType(): ?string
    {
        return match ($this->mime) {
            'audio/mpeg' => 'MPEG',
            'audio/ogg' => 'OGG',
            'audio/wav' => 'WAV',
            'audio/webm' => 'WebM',
            'audio/x-m4a' => 'M4A',
            'audio/x-aac' => 'AAC',
            'audio/x-aiff' => 'AIFF',
            'audio/x-flac' => 'FLAC',
            'audio/x-matroska' => 'MKV',
            'audio/x-ms-wma' => 'WMA',
            'audio/x-wav' => 'WAV',

            default => $this->mime,
        };
    }
}
