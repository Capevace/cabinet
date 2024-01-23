<?php

namespace Cabinet;

use Illuminate\Database\Eloquent\Model;

readonly class File
{
    public function __construct(
        public string   $id,
        public string   $source,
        public FileType $type,
        public string   $name,
        public string   $slug,
        public string   $mimeType,
        public int      $size,
        public ?string  $previewUrl,
        public ?string  $icon = null,
        public ?array   $attributes = null,
    ) {}

    public function url(?string $variant = null): ?string
    {
        return \Cabinet\Facades\Cabinet::generateFileUrl($this, $variant);
    }

    public function path(): ?string
    {
        return \Cabinet\Facades\Cabinet::generateFilePath($this);
    }

    public function model(): ?Model
    {
        return \Cabinet\Facades\Cabinet::getFileModel($this);
    }

    public function uniqueId(): string
    {
        return "F-{$this->source}-{$this->id}";
    }

    public function formattedMimeType(): string
    {
        return match ($this->mimeType) {
            'image/jpeg' => 'JPEG',
            'image/png' => 'PNG',
            'image/gif' => 'GIF',
            'image/webp' => 'WebP',
            'image/svg+xml' => 'SVG',
            'image/bmp' => 'BMP',
            'image/tiff' => 'TIFF',
            'image/x-icon' => 'ICO',
            'image/vnd.adobe.photoshop' => 'Photoshop',
            'image/vnd.dwg' => 'AutoCAD',
            'image/vnd.dxf' => 'AutoCAD',
            'image/vnd.djvu' => 'DjVu',

            'audio/mpeg' => 'MP3',
            'audio/ogg' => 'OGG',
            'audio/wav' => 'WAV',
            'audio/x-m4a' => 'M4A',
            'audio/x-matroska' => 'MKA',
            'audio/x-ms-wma' => 'WMA',
            'audio/x-ms-wax' => 'WAX',
            'audio/vnd.rn-realaudio' => 'RealAudio',
            'audio/vnd.wave' => 'WAV',
            'audio/webm' => 'WebM',

            'video/mp4' => 'MP4',
            'video/mpeg' => 'MPEG',
            'video/ogg' => 'OGG',
            'video/quicktime' => 'QuickTime',
            'video/webm' => 'WebM',
            'video/x-flv' => 'FLV',
            'video/x-m4v' => 'M4V',
            'video/x-matroska' => 'MKV',
            'video/x-ms-asf' => 'ASF',
            'video/x-ms-wmv' => 'WMV',
            'video/x-msvideo' => 'AVI',
            'video/x-sgi-movie' => 'Movie',
            'video/3gpp' => '3GP',
            'video/3gpp2' => '3GP2',

            'application/pdf' => 'PDF',

            'application/vnd.ms-word' => 'Word',
            'application/vnd.ms-excel' => 'Excel',
            'application/vnd.ms-powerpoint' => 'PowerPoint',
            'application/vnd.oasis.opendocument.text' => 'OpenDocument Text',
            'application/vnd.oasis.opendocument.spreadsheet' => 'OpenDocument Spreadsheet',
            'application/vnd.oasis.opendocument.presentation' => 'OpenDocument Presentation',

            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'Word',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'Excel',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'PowerPoint',
            'application/zip' => 'ZIP',
            'application/x-rar-compressed' => 'RAR',
            'application/x-7z-compressed' => '7z',
            'application/x-tar' => 'TAR',
            'application/x-bzip2' => 'BZIP2',
            'application/x-gzip' => 'GZIP',
            'application/x-xz' => 'XZ',
            default => $this->mimeType,
        };
    }

    public function toIdentifier(): array
    {
        return [
            'type' => $this->type->slug(),
            'id' => $this->id,
            'source' => $this->source,
            'name' => $this->name
        ];
    }

    public function humanSize(): string
    {
        // convert bytes to human readable format (decimal, not binary)
        // e.g. 5383212 => 5.38 MB
        if ($this->size >= 1000000000000) {
            $bytes = number_format($this->size / 1000000000000, 2) . ' TB';
        } elseif ($this->size >= 1000000000) {
            $bytes = number_format($this->size / 1000000000, 2) . ' GB';
        } elseif ($this->size >= 1000000) {
            $bytes = number_format($this->size / 1000000, 2) . ' MB';
        } elseif ($this->size >= 1000) {
            $bytes = number_format($this->size / 1000, 2) . ' KB';
        } elseif ($this->size > 1) {
            $bytes = $this->size . ' bytes';
        } elseif ($this->size == 1) {
            $bytes = $this->size . ' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }
}
