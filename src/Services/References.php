<?php

namespace Cabinet\Services;

use Cabinet\Exceptions\WrongSource;
use Cabinet\File;
use Cabinet\Folder;
use Cabinet\HasFiles;
use Cabinet\Models\Directory;
use Cabinet\Models\FileRef;
use Cabinet\Source;
use Cabinet\Sources\Contracts\AcceptsUploads;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
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

    /**
     * @throws WrongSource
     * @throws Exception
     */
    public function uploadAndAttachFromPath(string $source, string $path, Directory|Folder $folder, Model $to = null, string $as = null, ?int $order = null): FileRef
    {
        /** @var AcceptsUploads $uploadableSource */
        $uploadableSource = $this->getSource($source);

        if (!($uploadableSource instanceof AcceptsUploads)) {
            throw new WrongSource("{$source} source does not implement " . AcceptsUploads::class);
        }

        $uploadedFile = new UploadedFile(
            path: $path,
            originalName: basename($path),
            mimeType: mime_content_type($path)
        );

        if ($folder instanceof Directory) {
            $folder = $folder->asFolder();
        }

        $file = $uploadableSource->upload($folder, $uploadedFile);

        return $this->attach(
            file: $file,
            to: $to,
            as: $as,
            order: $order
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

//    public function reorder(HasFiles $record, string $relationship, int $from, int $to): static
//    {
//        DB::transaction(function () use ($record, $relationship, $from, $to) {
//            $fileRefs = $record->{$relationship}()
//                ->orderBy('attached_order')
//                ->get();
//
//            // Explained:
//            // 1. Remove the file from the from index
//            // 2. Insert the file to the to index
//            $fileRefs->splice($to, 0, $fileRefs->splice($from, 1));
//
//            $fileRefs->each(fn (FileRef $ref, int $index) => $ref->update(['attached_order' => $index]));
//        });
//
//        return $this;
//    }
}
