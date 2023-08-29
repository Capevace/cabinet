<?php

namespace Cabinet;

use Cabinet\Models\FileRef;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @implements HasFiles
 */
trait InteractsWithFiles
{
    public function fileRef(string $as, ?array $types = null): MorphOne
    {
        $morphOne = $this->morphOne(FileRef::class, 'attached_to')
            ->where('attached_as', $as);

        if (filled($types)) {
            $slugs = array_map(fn ($classPath) => (new $classPath)->slug(), $types);

            $morphOne = $morphOne->whereIn('type', $slugs);
        }

        return $morphOne;
    }

    public function fileRefs(string $as, ?array $types = null): MorphMany
    {
        $morphMany = $this->morphMany(FileRef::class, 'attached_to')
            ->where('attached_as', $as);

        if (filled($types)) {
            $slugs = array_map(fn ($classPath) => (new $classPath)->slug(), $types);

            $morphMany = $morphMany->whereIn('type', $slugs);
        }

        return $morphMany
            ->orderBy('attached_order');
    }

    public function all_file_refs(): MorphMany
	{
		return $this->morphMany(FileRef::class, 'attached_to')
			->orderBy('attached_order');
	}
}
