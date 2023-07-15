<?php

namespace Cabinet\Sources;

use Cabinet\Facades\Cabinet;
use Cabinet\File;
use Cabinet\Folder;
use Cabinet\Models\Directory;
use Cabinet\Models\FileRef;
use Cabinet\Query;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class DiskSource implements \Cabinet\Source
{
    public const TYPE = 'local-disk';

    public string $disk = 'local';

    public string $prefix = 'cabinet/';

    protected function getDisk(?string $disk = null): \Illuminate\Contracts\Filesystem\Filesystem
    {
        return Storage::disk($disk ?? $this->disk);
    }

    protected function determineBasePath(Directory $directory): string
    {
        return $this->getDisk()
            ->path("cabinet/{$directory->id}");
    }

    protected function transformPath(string $path): File
    {
        $filename = basename($path);
        $pathHash = md5($path);
        $mimeType = $this->getDisk()->mimeType($path);

        return new File(
            id: "{$this->disk}:{$pathHash}",
            source: static::TYPE,
            type: Cabinet::determineFileTypeFromMime($mimeType),
            name: str($filename)->beforeLast('.')->toString(),
            slug: $filename,
            mimeType: $mimeType,
            size: $this->getDisk()->size($path),
            url: $this->getDisk()->url($path),
        );
    }

    public function all(Folder $folder, Query $options = new Query): Collection
    {
        $basePath = $this->determineBasePath($folder);

        return collect($this->getDisk()->allFiles($basePath))
            ->filter(fn (string $path) => $options->search === null || str($path)->afterLast('/')->contains($options->search))
            ->map(fn (string $path) => $this->transformPath($path));
    }

    public function transform(FileRef $ref): File
    {
        $ref->validateSource(static::TYPE);

        $path = $this->getDisk()->path($ref->path);

        return $this->transformPath($path);
    }

    public function rename(File $file, string $name): File
    {
        // TODO: Implement rename() method.
    }

    public function move(File $file, Directory $directory): File
    {
        // TODO: Implement move() method.
    }

    public function delete(File $file): void
    {
        // TODO: Implement delete() method.
    }
}
