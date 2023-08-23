<?php

namespace Cabinet\Services;

use Cabinet\Exceptions\WrongSource;
use Cabinet\File;
use Cabinet\Folder;
use Cabinet\Models\Directory;
use Cabinet\Source;
use Illuminate\Support\Collection;

trait Directories
{
    public function folder(Directory|string $directory): ?Folder
    {
        $id = $directory instanceof Directory
            ? $directory->id
            : $directory;

        return static::directory($id)
            ?->asFolder();
    }

    public function directory(Folder|string $directory): ?Directory
    {
        $className = config('cabinet.directory_model');

        $id = is_string($directory)
            ? $directory
            : $directory->id;

        return $className::find($id);
    }

    public function createDirectory(string $name, Folder|Directory|null $parent = null, bool $protected = false, ?string $translationKey = null): Directory
    {
        if ($parent instanceof Folder && !$parent->isCabinetFolder()) {
            throw new WrongSource('Cannot create directory in non-cabinet parent folder');
        }

        $className = config('cabinet.directory_model');

        $directory = $className::make([
            'name' => $name,
            'parent_directory_id' => $parent?->id
        ]);
        $directory->is_protected = $protected;

        if ($translationKey) {
            $directory->translation_key = $translationKey;
        }

        $directory->save();

        return $directory;
    }

    public function findOrCreateDirectory(string $id, string $name, Folder|Directory|null $parent = null, bool $protected = false, ?string $translationKey = null): Directory
    {
        if ($parent instanceof Folder && !$parent->isCabinetFolder()) {
            throw new WrongSource('Cannot create directory in non-cabinet parent folder');
        }

        if (!uuid_is_valid($id)) {
            throw new \InvalidArgumentException('Fixed id must be a valid uuid');
        }

        $className = config('cabinet.directory_model');

        $directory = $className::firstOrNew(
            ['id' => $id],
            [
                'name' => $name,
                'parent_directory_id' => $parent?->id
            ]
        );
        $directory->is_protected = $protected;

        if ($id) {
            $directory->id = $id;
        }

        if ($translationKey) {
            $directory->translation_key = $translationKey;
        }

        $directory->save();

        return $directory;
    }

    public function getDirectoryMorphClass(): string
    {
        $className = config('cabinet.directory_model');
        $dir = new $className;

        return $dir->getMorphClass();
    }

    /**
     * @return Collection<File|Folder>
     */
    public function files(Directory|Folder $directory, ?array $sourceNames = null): Collection
    {
        $folder = $directory instanceof Directory
            ? $directory->asFolder()
            : $directory;

        $files = $this->mapSources($sourceNames)
            ->map(fn(Source $source) => $source->all($folder))
            ->flatten();

        if ($folder->isCabinetFolder()) {
            $subdirs = $this->findCabinetDirectory($folder->id)
                ->directories
                ->map(fn(Directory $directory) => new Folder(
                    id: $directory->id,
                    source: 'cabinet',
                    name: $directory->name,
                ));

            $files = $files
                ->merge($subdirs);
        }


        return $files;
    }

    public function findCabinetDirectory(string $id)
    {
        $className = config('cabinet.directory_model');

        return $className::find($id);
    }
}
