<?php

namespace Cabinet\Services;

use Cabinet\File;
use Cabinet\HasFiles;
use Cabinet\Models\FileRef;
use Cabinet\Source;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait References
{
    public function references(File $file, ?int $limit = null, ?int $offset = null, ?array $sourceNames = null): Collection
    {
        return $this->mapSources($sourceNames)
            ->map(fn (Source $source) => $source->references($file, $limit, $offset))
            ->flatten();
    }

    public function findReference(string $id): ?FileRef
    {
        $className = config('cabinet.file_ref_model');

        return $className::find($id);
    }

    public function createReference(File $file)
    {
        $source = $this->getSource($file->source);

        return $source->reference($file);
    }

    public function attach(File $file, Model $to = null, string $as = null, ?int $order = null): FileRef
    {
        $source = $this->getSource($file->source);

        return $source->reference(
            file: $file,
            attached_to: [
                'attached_to_type' => $to->getMorphClass(),
                'attached_to_id' => $to->getKey(),
                'attached_as' => $as,
                'attached_order' => $order
            ]
        );
    }

    public function syncMany(HasFiles $record, string $relationship, array|Collection $files): static
    {
        DB::transaction(function () use ($record, $relationship, $files) {
           $files = collect($files);

            $oldFileRefs = $record->{$relationship}()
                ->get();

            // Sync order
            $files
                ->each(fn (File $file, int $index) => ($ref = $oldFileRefs->first(fn (FileRef $ref) => $ref->references($file)))
                    ? $ref->update(['attached_order' => $index])
                    : $this->attach($file, to: $record, as: $relationship, order: $index)
                );

            // Delete removed files
            $oldFileRefs
                ->filter(fn (FileRef $ref) => !$files->contains(fn (File $file) => $ref->references($file)))
                ->each(fn (FileRef $ref) => $ref->delete());
        });

        return $this;
    }

    public function syncOne(HasFiles $record, string $relationship, ?File $file): static
    {
        DB::transaction(function () use ($record, $relationship, $file) {
            $oldFileRef = $record->{$relationship}()->first();

            if ($oldFileRef && $oldFileRef->references($file)) {
                return;
            }

            $oldFileRef?->delete();

            if ($file)
                $this->attach($file, to: $record, as: $relationship);
        });

        return $this;
    }
}
