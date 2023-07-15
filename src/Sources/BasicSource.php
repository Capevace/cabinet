<?php

namespace Cabinet\Sources;

use Cabinet\Facades\Cabinet;
use Cabinet\File;
use Cabinet\FileType;
use Cabinet\Folder;
use Cabinet\Models\Directory;
use Cabinet\Models\FileRef;
use Cabinet\Query;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class BasicSource implements \Cabinet\Source
{
    public const TYPE = 'basic';

    protected function transformMedia(Media $media): File
    {
        return new File(
            id: (string) $media->id,
            source: self::TYPE,
            type: Cabinet::determineFileTypeFromMime($media->mime_type),
            name: $media->name,
            slug: $media->slug,
            mimeType: $media->mime_type,
            size: $media->size,
            url: $media->getFullUrl(),
        );
    }

    public function all(Folder $folder, Query $options = new Query): Collection
    {
        $mimeTypes = $options->types !== null
            ? collect($options->types)
                ->map(fn (FileType $type) => $type::supportedMimeTypes())
                ->flatten()
            : null;

        $mediaModel = config('media-library.media_model');

        $options = $mediaModel::query()
            ->whereHas('model', fn ($query) =>
                $options
                    ->where('model_type', $folder->getMorphClass())
                    ->where('model_id', $folder->id)
            )
            ->whereIf($options->search !== null, fn ($query) =>
                $options
                    ->where('name', 'ilike', "%{$options->search}%")
                    ->orWhere('file_name', 'ilike', "%{$options->search}%")
            )
            ->whereIf($mimeTypes !== null, fn ($query) =>
                $options->whereIn('mime_type', $mimeTypes)
            );

        return $options
            ->get()
            ->map(fn (Media $media) => $this->transformMedia($media));
    }

    public function transform(FileRef $ref): File
    {
        $ref->validateSource(static::TYPE);

        $media = $ref->model;

        return $this->transformMedia($media);
    }

    public function rename(File $file, string $name): File
    {
        return $file;
    }

    public function move(File $file, Directory $directory): File
    {
        return $file;
    }

    public function delete(File $file): void
    {
    }
}
