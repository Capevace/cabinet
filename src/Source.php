<?php

namespace Cabinet;

use Cabinet\Models\Directory;
use Cabinet\Models\FileRef;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface Source
{
    /**
     * @return Collection<File|Folder>
     */
    public function all(Folder $folder, Query $options = new Query): Collection;

    public function transform(FileRef $ref): File;

    public function rename(File $file, string $name): File;

    public function move(File $file, Folder $folder): File;

    public function delete(File $file): void;

    /**
     * @return Collection<FileRef>
     */
    public function references(File $file, ?int $limit = null, ?int $offset = null): Collection;

    public function reference(File $file, array $attached_to = []): FileRef;
}
