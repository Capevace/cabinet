<?php

namespace Cabinet\Types\Concerns;

trait StringableAsSlug
{
    public function __toString(): string
    {
        return $this->slug();
    }
}
