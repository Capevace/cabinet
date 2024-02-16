<?php

namespace Cabinet\Types\Concerns;

use Illuminate\Support\Str;

trait WithMime
{
    protected ?string $mime = null;

    public function __construct(
        ?string $mime = null
    )
    {
        $this->mime = $mime;
    }

    public function formattedMimeType(): ?string
    {
        return match ($this->mime) {
            default => $this->mime,
        };
    }

    public function getMime(): ?string
    {
        return $this->mime;
    }

    public function extension(): ?string
    {
        return Str::lower($this->formattedMimeType());
    }
}
