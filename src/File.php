<?php

namespace Cabinet;

//interface File
//{
//    public function id(): string;
//
//    public function source(): string;
//
//    public function type(): FileType;
//
//    public function name(): string;
//
//    public function slug(): string;
//
//    public function mimeType(): string;
//
//    public function size(): int;
//
//    public function url(): string;
//}

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
        public ?string  $icon = null
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

    public function toIdentifier(): array
    {
        return [
            'type' => $this->type->slug(),
            'id' => $this->id,
            'source' => $this->source,
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
