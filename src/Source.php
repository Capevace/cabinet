<?php

namespace Cabinet;

use Cabinet\Models\Directory;
use Cabinet\Models\FileRef;
use Illuminate\Support\Collection;

interface Source
{
    /**
     * @return Collection<File|Folder>
     */
    public function all(Folder $folder, Query $options = new Query): Collection;

    public function transform(FileRef $ref): File;

    public function rename(File $file, string $name): File;

    public function move(File $file, Directory $directory): File;

    public function delete(File $file): void;
}
