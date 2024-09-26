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
use Cabinet\Sources\Contracts\HasModel;
use Cabinet\Sources\Contracts\HasPath;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Closure;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SpatieMediaSource implements \Cabinet\Source, AcceptsUploads, CanBeDownloaded, CanGenerateUrls, FindWithId, HasContents, HasModel, HasPath
{
    public const TYPE = 'spatie-media';

    public static function type(): string
    {
        return static::TYPE;
    }

    public static function label(): string
    {
        return __('cabinet::files.files');
    }

    protected function getThumbnailConversion(): string
    {
        return config('cabinet.spatie_media_library.preview_conversion') ?? '';
    }

    protected function getDefaultConversion(): string
    {
        return config('cabinet.spatie_media_library.default_conversion') ?? '';
    }

    protected function getDefaultExpiration(): CarbonImmutable
    {
        return CarbonImmutable::now()->addMinutes(config('cabinet.spatie_media_library.default_expiration_minutes') ?? 60);
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

    public function transformMedia(Media $media, ?DateTimeInterface $expiresAt = null): File
    {
        $thumbnailConversion = $this->getThumbnailConversion();
        $type = Cabinet::determineFileTypeFromMime($media->mime_type);

        return new File(
            id: (string) $media->getKey(),
            source: self::TYPE,
            type: $type,
            name: $media->name,
            slug: $media->file_name,
            mimeType: $media->mime_type,
            size: $media->size,
            // For non-image files, we HAVE to try the thumbnail, as displaying them in img won't work (video, pdf, etc.).
            // For images, we can use the thumbnail if it exists, but if it doesn't, we can just use display the full image itself.
            previewUrl: $media->hasGeneratedConversion($thumbnailConversion) || $type->slug() !== 'image'
                ? $media->getTemporaryUrl(expiration: $expiresAt ?? $this->getDefaultExpiration(), conversionName: $thumbnailConversion)
                : $media->getTemporaryUrl(expiration: $expiresAt ?? $this->getDefaultExpiration()),
            model: $media
        );
    }

    public function all(Folder $folder, Query $options = new Query): Collection
    {
        if (! $folder->isCabinetFolder()) {
            return collect();
        }

        $mimeTypes = $options->types !== null
            ? collect($options->types)
                ->map(fn (FileType $type) => $type::supportedMimeTypes())
                ->flatten()
            : null;

        $mediaModel = $this->getMediaModel();
        $query = $mediaModel::query()
            ->whereHas('model', fn ($query) => $query
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
        $media = $this->getFileModel($file);

        $media->name = $name;

        $media->save();

        return $file;
    }

    public function move(File $file, Folder $folder): File
    {
        if (! $folder->isCabinetFolder()) {
            throw new WrongSource('Media files can only be moved to Cabinet folders');
        }

        $media = $this->getFileModel($file);
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

        if ($refs->isNotEmpty() && ! $deleteRefs) {
            throw new FileStillReferenced('Cannot delete media file that is still referenced');
        } elseif ($refs->isNotEmpty() && $deleteRefs) {
            $refs->each->delete();
        }

        $media = $this->findMediaOrFail($file);
        $media->delete();
    }

    public function upload(Folder $folder, UploadedFile $file, array $data = [], ?string $collection = null, ?bool $preserveOriginal = null): File
    {
        if (! $folder->isCabinetFolder()) {
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

    public function references(File $file, ?int $limit = null, ?int $offset = null): Collection
    {
        $mediaModel = $this->getMediaModel();
        $morphClass = (new $mediaModel)->getMorphClass();

        $query = FileRef::where('model_type', $morphClass)
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
        $media = $this->getFileModel($file);

        return FileRef::create([
            'source' => static::TYPE,
            'model_type' => $media->getMorphClass(),
            'model_id' => $media->getKey(),
            ...$attached_to,
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

    public function generateUrl(File $file, ?string $variant = null, ?DateTimeInterface $expiresAt = null): ?string
    {
        $media = $this->getFileModel($file);

        return $media->getTemporaryUrl($expiresAt ?? $this->getDefaultExpiration(), $variant ?? $this->getDefaultConversion());
    }

	/**
	 * @return Media
	 */
    public function getFileModel(File $file): Model
    {
        return $file->model ?? $this->findMediaOrFail($file);
    }

    public function path(File $file, ?string $variant = null): string
    {
        $media = $this->getFileModel($file);

        return $media->getPath($variant ?? $this->getDefaultConversion());
    }

    public function contents(File $file): string
    {
        return file_get_contents($this->path($file));
    }

    public function download(File $file): \Symfony\Component\HttpFoundation\Response
    {
        $media = $this->getFileModel($file);

        $url = $media->getTemporaryUrl(Carbon::now()->addHours(2), $this->getDefaultConversion());
        $filename = $media->file_name;

        return response()->redirectTo($url);
    }

    public function getFormSchema(Closure $fileUploadComponent): array
    {
        $source = $this;

        return [

        ];
    }
}
