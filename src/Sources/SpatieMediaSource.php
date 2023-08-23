<?php

namespace Cabinet\Sources;

use Cabinet\Exceptions\FileNotFound;
use Cabinet\Exceptions\FileStillReferenced;
use Cabinet\Exceptions\WrongSource;
use Cabinet\Facades\Cabinet;
use Cabinet\File;
use Cabinet\FileType;
use Cabinet\Folder;
use Cabinet\Models\FileRef;
use Cabinet\Query;
use Cabinet\Sources\Contracts\AcceptsUploads;
use Cabinet\Sources\Contracts\CanBeDownloaded;
use Cabinet\Sources\Contracts\CanGenerateUrls;
use Cabinet\Sources\Contracts\FindWithId;
use Cabinet\Sources\Contracts\HasContents;
use Cabinet\Sources\Contracts\HasPath;
use Cabinet\Sources\Contracts\HasModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;


class SpatieMediaSource implements \Cabinet\Source, AcceptsUploads, FindWithId, CanGenerateUrls, HasModel, HasPath, HasContents, CanBeDownloaded
{
    public const TYPE = 'spatie-media';

    protected function getThumbnailConversion(): string
    {
        return config('cabinet.spatie_media_library.preview_conversion') ?? '';
    }

    protected function getDefaultConversion(): string
    {
        return config('cabinet.spatie_media_library.default_conversion') ?? '';
    }

    protected function findMediaOrFail(File $file): Media
    {
        $mediaModel = $this->getMediaModel();
        $media = $mediaModel::find($file->id);

        if ($media === null) {
            throw new FileNotFound("Media with ID {$file->id} could not be found");
        }

        return $media;
    }

    public function transformMedia(Media $media): File
    {
        $thumbnailConversion = $this->getThumbnailConversion();

        return new File(
            id: (string) $media->getKey(),
            source: self::TYPE,
            type: Cabinet::determineFileTypeFromMime($media->mime_type),
            name: $media->name,
            slug: $media->file_name,
            mimeType: $media->mime_type,
            size: $media->size,
            previewUrl: $media->hasGeneratedConversion($thumbnailConversion)
                ? $media->getFullUrl($thumbnailConversion)
                : $media->getFullUrl(),
        );
    }

    public function all(Folder $folder, Query $options = new Query): Collection
    {
        if (!$folder->isCabinetFolder()) {
            return collect();
        }

        $mimeTypes = $options->types !== null
            ? collect($options->types)
                ->map(fn (FileType $type) => $type::supportedMimeTypes())
                ->flatten()
            : null;

        $mediaModel = $this->getMediaModel();
        $query = $mediaModel::query()
            ->whereHas('model', fn ($query) =>
                $query
                    ->where('model_type', Cabinet::getDirectoryMorphClass())
                    ->where('model_id', $folder->id)
            );

        if ($options->search !== null) {
            $query = $query
                ->where('name', 'ilike', "%{$options->search}%")
                ->orWhere('file_name', 'ilike', "%{$options->search}%");
        }

        if ($mimeTypes !== null) {
            $query = $query->whereIn('mime_type', $mimeTypes);
        }

        return $query
            ->get()
            ->map(fn (Media $media) => $this->transformMedia($media));
    }

    public function transform(FileRef $ref): File
    {
        $ref->validateSource(static::TYPE);

        $media = $ref->model;

        if ($media === null) {
            throw new FileNotFound("Media with ID {$ref->model_id} could not be found");
        }

        return $this->transformMedia($media);
    }

    public function rename(File $file, string $name): File
    {
        $media = $this->findMediaOrFail($file);

        $media->name = $name;

        $media->save();

        return $file;
    }

    public function move(File $file, Folder $folder): File
    {
        if (!$folder->isCabinetFolder()) {
            throw new WrongSource('Media files can only be moved to Cabinet folders');
        }

        $media = $this->findMediaOrFail($file);
        $directory = $folder->findDirectoryOrFail();

        $media->model_id = $directory->getKey();
        $media->model_type = $directory->getMorphClass();
        $media->save();

        return $this->transformMedia($media);
    }

    public function delete(File $file): void
    {
        $refs = $this->references($file);
        $deleteRefs = config('cabinet.auto_delete_references', true);

        if ($refs->isNotEmpty() && !$deleteRefs) {
            throw new FileStillReferenced('Cannot delete media file that is still referenced');
        } else if ($refs->isNotEmpty() && $deleteRefs) {
            $refs->each->delete();
        }

        $media = $this->findMediaOrFail($file);
        $media->delete();
    }

    public function upload(Folder $folder, UploadedFile $file, ?string $collection = null, ?bool $preserveOriginal = null): File
    {
        if (!$folder->isCabinetFolder()) {
            throw new WrongSource('Media files can only be uploaded to Cabinet folders');
        }

        $collection = $collection ?? config('cabinet.spatie_media_library.collection_name');
        $preserveOriginal = $preserveOriginal ?? config('cabinet.spatie_media_library.preserve_original');

        $media = $folder->findDirectoryOrFail()
            ->addMedia($file)
            ->preservingOriginal($preserveOriginal)
            ->toMediaCollection($collection);

        return $this->transformMedia($media);
    }

    public function references(File $file, int $limit = null, int $offset = null): Collection
    {
        $mediaModel = $this->getMediaModel();
        $morphClass = (new $mediaModel)->getMorphClass();

        $query =  FileRef::where('model_type', $morphClass)
            ->where('model_id', $file->id);

        if ($limit !== null) {
            $query = $query->limit($limit);
        }

        if ($offset !== null) {
            $query = $query->offset($offset);
        }

        return $query->get();
    }

    protected function getMediaModel(): string
    {
        return config('media-library.media_model');
    }

    public function reference(File $file, array $attached_to = []): FileRef
    {
        $media = $this->findMediaOrFail($file);

        return FileRef::create([
            'source' => static::TYPE,
            'model_type' => $media->getMorphClass(),
            'model_id' => $media->getKey(),
            ...$attached_to
        ]);
    }

    public function findWithID(string $id): File
    {
        $mediaModel = $this->getMediaModel();

        $media = $mediaModel::find($id);

        if ($media === null) {
            throw new FileNotFound("Media model with ID {$id} could not be found");
        }

        return $this->transformMedia($media);
    }

    public function generateUrl(File $file, ?string $variant = null): ?string
    {
        $media = $this->findMediaOrFail($file);

        return $media->getFullUrl($variant ?? $this->getDefaultConversion());
    }

    public function getFileModel(File $file): Model
    {
        return $this->findMediaOrFail($file);
    }

    public function path(File $file): string
    {
        $media = $this->findMediaOrFail($file);

        return $media->getPath($this->getDefaultConversion());
    }

    public function contents(File $file): string
    {
        return file_get_contents($this->path($file));
    }

    public function download(File $file): \Symfony\Component\HttpFoundation\Response
    {
        $media = $this->findMediaOrFail($file);

        return response()->download($media->getPath($this->getDefaultConversion()), $media->file_name);
    }
}
