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
use Illuminate\Support\Str;
use Closure;

trait References
{
    /**
     * Reference descriptors, keyed by model class.
     *
     * @var array<class-string, array<string, mixed>>
     */
    protected array $referenceDescriptors = [];

    /**
     * Usage labels that apply to every model, keyed by `attached_as`.
     *
     * @var array<string, Closure|string>
     */
    protected array $referenceUsageLabels = [];

    /**
     * Describe how references to a model are shown in the finder.
     *
     * Host applications register this centrally, e.g. in a service provider,
     * so the finder can tell the user what a file is used for:
     *
     *     Cabinet::describeReferences(
     *         model: Estate::class,
     *         label: fn (Estate $estate) => $estate->name,
     *         typeLabel: fn () => __('files.references.types.estate'),
     *         icon: 'heroicon-o-building-office',
     *         url: fn (Estate $estate) => $estate->getAdminUrl(),
     *         usages: ['thumbnail' => fn () => __('files.references.usages.thumbnail')],
     *     );
     *
     * Every value may be a closure (called with the attached model and the
     * reference) or a fixed value. Everything is optional and falls back to the
     * `getCabinetReference*()` methods of the model, then to a generic default.
     * Repeated calls for the same model are merged, so labels and usages can be
     * added separately (e.g. one call per domain).
     *
     * @template TModel of Model
     *
     * @param  class-string<TModel>  $model
     * @param  (Closure(TModel, FileRef): string)|string|null  $label
     * @param  (Closure(TModel, FileRef): string)|string|null  $typeLabel
     * @param  (Closure(TModel, FileRef): string)|string|null  $icon
     * @param  (Closure(TModel, FileRef): string)|string|null  $url
     * @param  (Closure(TModel, FileRef): string)|string|null  $thumbnail
     * @param  array<string, (Closure(TModel, FileRef): string)|string>  $usages
     */
    public function describeReferences(
        string $model,
        Closure|string|null $label = null,
        Closure|string|null $typeLabel = null,
        Closure|string|null $icon = null,
        Closure|string|null $url = null,
        Closure|string|null $thumbnail = null,
        array $usages = [],
    ): static {
        $existing = $this->referenceDescriptors[$model] ?? [];

        $descriptor = array_filter([
            'label' => $label,
            'type_label' => $typeLabel,
            'icon' => $icon,
            'url' => $url,
            'thumbnail' => $thumbnail,
        ], fn (mixed $value) => $value !== null);

        $this->referenceDescriptors[$model] = [
            ...$existing,
            ...$descriptor,
            'usages' => [
                ...($existing['usages'] ?? []),
                ...$usages,
            ],
        ];

        return $this;
    }

    /**
     * Label the `attached_as` values that mean the same thing on every model.
     *
     * @param  array<string, (Closure(Model, FileRef): string)|string>  $usages
     */
    public function describeReferenceUsages(array $usages): static
    {
        $this->referenceUsageLabels = [...$this->referenceUsageLabels, ...$usages];

        return $this;
    }

    /**
     * The descriptor registered for a model class, if any.
     *
     * @return array<string, mixed>|null
     */
    public function referenceDescriptor(string $model): ?array
    {
        return $this->referenceDescriptors[$model] ?? null;
    }

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
            ->unique(fn (FileRef $ref) => $ref->getKey())
            ->map(function (FileRef $ref) {
                $model = $ref->attachedTo;

                if ($model === null) {
                    return null;
                }

                return $this->resolveReference($ref, $model);
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * The descriptor registered for the model behind a morph type, if any.
     *
     * @return array<string, mixed>|null
     */
    protected function referenceDescriptorFor(?string $morphType): ?array
    {
        if (blank($morphType)) {
            return null;
        }

        return $this->referenceDescriptors[Model::getActualClassNameForMorph($morphType)] ?? null;
    }

    /**
     * Everything the finder needs to show one reference: the record it belongs
     * to, how it can be opened, and what the file is used for there.
     *
     * Values are looked up in the registered descriptor first, then in the
     * `getCabinetReference*()` methods of the model, then in generic defaults,
     * so undescribed models still resolve to something readable.
     *
     * @param  array<string, mixed>|null  $descriptor
     * @return array{
     *     label: string,
     *     url: string|null,
     *     icon: string|null,
     *     typeLabel: string|null,
     *     thumbnailUrl: string|null,
     *     usage: string|null,
     *     usageLabel: string|null,
     *     modelType: string|null,
     *     isDescribed: bool,
     * }
     */
    protected function resolveReference(FileRef $ref, Model $model): array
    {
        $descriptor = $this->referenceDescriptorFor($ref->attached_to_type);
        $usage = filled($ref->attached_as) ? $ref->attached_as : null;

        // Model specific usage labels win over the ones every model shares.
        $usageLabels = [...$this->referenceUsageLabels, ...($descriptor['usages'] ?? [])];

        $label = $this->referenceValue($descriptor['label'] ?? null, $model, $ref);

        if ($label === null && method_exists($model, 'getCabinetReferenceLabel')) {
            $label = $model->getCabinetReferenceLabel();
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

        $typeLabel = $this->referenceValue($descriptor['type_label'] ?? null, $model, $ref)
            ?? (method_exists($model, 'getCabinetReferenceTypeLabel') ? $model->getCabinetReferenceTypeLabel() : null)
            ?? Str::headline(class_basename($model));

        $usageLabel = $usage === null
            ? null
            : ($this->referenceValue($usageLabels[$usage] ?? null, $model, $ref) ?? Str::headline($usage));

        return [
            'label'        => (string) $label,
            'url'          => $this->referenceValue($descriptor['url'] ?? null, $model, $ref)
                ?? (method_exists($model, 'getCabinetReferenceUrl') ? $model->getCabinetReferenceUrl() : null),
            'icon'         => $this->referenceValue($descriptor['icon'] ?? null, $model, $ref)
                ?? (method_exists($model, 'getCabinetReferenceIcon') ? $model->getCabinetReferenceIcon() : null),
            'typeLabel'    => $typeLabel,
            'thumbnailUrl' => $this->referenceValue($descriptor['thumbnail'] ?? null, $model, $ref)
                ?? (method_exists($model, 'getCabinetReferenceThumbnailUrl') ? $model->getCabinetReferenceThumbnailUrl() : null),
            'usage'        => $usage,
            'usageLabel'   => $usageLabel,
            'modelType'    => $ref->attached_to_type,
            'isDescribed'  => $descriptor !== null,
        ];
    }

    /**
     * Evaluate a descriptor value against the attached model and reference.
     */
    protected function referenceValue(mixed $value, Model $model, FileRef $ref): mixed
    {
        if ($value === null) {
            return null;
        }

        return $value instanceof Closure ? $value($model, $ref) : $value;
    }
}
