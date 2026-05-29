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

    public function attach(File $file, Model $to, ?string $as = null, ?int $order = null): FileRef
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
    public function uploadAndAttachFromPath(string $source, string $path, Directory|Folder $folder, ?Model $to = null, ?string $as = null, ?int $order = null): FileRef
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

        if ($to === null) {
            return $this->createReference($file);
        }

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

    /**
     * Resolve human-readable references for a file.
     *
     * Returns an array of references that can be rendered in the Finder
     * detail sidebar. Each reference contains a label, optional URL, icon,
     * type label and thumbnail.
     *
     * Host applications can customise the output by implementing
     * getCabinetReferenceLabel(), getCabinetReferenceUrl(),
     * getCabinetReferenceIcon(), getCabinetReferenceTypeLabel()
     * and/or getCabinetReferenceThumbnailUrl() on the models that are
     * attached to the file.
     *
     * @return array<array{label: string, url: string|null, icon: string|null, typeLabel: string|null, thumbnailUrl: string|null}>
     */
    public function resolveFileReferences(File $file): array
    {
        $references = $this->references($file);

        return $references
            ->map(function (FileRef $ref) {
                $model = $ref->attachedTo;

                if ($model === null) {
                    return null;
                }

                $label = null;
                $url = null;
                $icon = null;
                $typeLabel = null;
                $thumbnailUrl = null;

                if (method_exists($model, 'getCabinetReferenceLabel')) {
                    $label = $model->getCabinetReferenceLabel();
                }

                if (method_exists($model, 'getCabinetReferenceUrl')) {
                    $url = $model->getCabinetReferenceUrl();
                }

                if (method_exists($model, 'getCabinetReferenceIcon')) {
                    $icon = $model->getCabinetReferenceIcon();
                }

                if (method_exists($model, 'getCabinetReferenceTypeLabel')) {
                    $typeLabel = $model->getCabinetReferenceTypeLabel();
                }

                if (method_exists($model, 'getCabinetReferenceThumbnailUrl')) {
                    $thumbnailUrl = $model->getCabinetReferenceThumbnailUrl();
                }

                if ($label === null) {
                    foreach (['name', 'title', 'label'] as $attribute) {
                        if (isset($model->{$attribute})) {
                            $label = $model->{$attribute};
                            break;
                        }
                    }
                }

                if ($label === null) {
                    $label = class_basename($model) . ' #' . $model->getKey();
                }

                return [
                    'label'        => (string) $label,
                    'url'          => $url,
                    'icon'         => $icon,
                    'typeLabel'    => $typeLabel,
                    'thumbnailUrl' => $thumbnailUrl,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }
}
