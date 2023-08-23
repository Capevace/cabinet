<?php

namespace Cabinet\Types\Concerns;

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
}
