<?php

namespace Cabinet\Types\Concerns;

trait UsesDefaultIcon
{
    public function icon(): string
    {
        return 'heroicon-o-document';
    }
}
