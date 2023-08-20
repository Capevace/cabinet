<?php

namespace Cabinet\Types\Concerns;

use Cabinet\Facades\Cabinet;

trait StringableAsSlug
{
    public function __toString(): string
    {
        return $this->slug();
    }
}
