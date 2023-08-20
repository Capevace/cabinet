<?php

namespace Cabinet;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

interface HasFiles
{
    /**
     * @param FileType[]|null $types
     */
    public function fileRef(string $as, ?array $types = null): MorphOne;

    public function fileRefs(string $as, ?array $types = null): MorphMany;
}
