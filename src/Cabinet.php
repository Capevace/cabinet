<?php

namespace Cabinet;

use Cabinet\Models\Directory;
use Cabinet\Services\Actions;
use Cabinet\Services\Directories;
use Cabinet\Services\Files;
use Cabinet\Services\References;
use Cabinet\Sources\Contracts\AcceptsUploads;
use Cabinet\Sources\Contracts\HasFilamentForm;
use Cabinet\Sources\SpatieMediaSource;
use Cabinet\Types\Audio;
use Cabinet\Types\Document;
use Cabinet\Types\Image;
use Cabinet\Types\Other;
use Cabinet\Types\PDF;
use Cabinet\Types\Video;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Cabinet
{
    use Actions, Directories, Files, References;

    protected $sources = [
        SpatieMediaSource::TYPE => SpatieMediaSource::class,
    ];

    protected $sourceLabels = [];

    protected $fileTypes = [
        Image::class,
        Video::class,
        Audio::class,
        Document::class,
        PDF::class,
        Other::class
    ];

    protected Closure|null $configureMediaConversions = null;

    public function __construct()
    {
        $this->sourceLabels = [
            SpatieMediaSource::TYPE => __('cabinet::files.files'),
        ];
    }

    public function registerSource(string $name, string $className): self
    {
        // Check if class implements Source interface
        if (!in_array(Source::class, class_implements($className))) {
            throw new \Exception("{$className} must implement " . Source::class);
        }

        $this->sources[$name] = $className;

        app()->singleton($className);

        return $this;
    }

    public function getSource(string $source): Source
    {
        if (!isset($this->sources[$source])) {
            throw new \Exception("Source {$source} is not registered");
        }

        return app($this->sources[$source]);
    }

    public function validSources(): Collection
    {
        return collect($this->sources)->keys();
    }

    protected function mapSources(?array $sourceNames = null): Collection
    {
        $sourceNames = $sourceNames ?? array_keys($this->sources);

        return collect($sourceNames)
            ->map(fn (string $source) => $this->getSource($source));
    }

    public function getSourceOptions(): array
    {
        return $this->mapSources()
            ->filter(fn (Source $source) => $source instanceof HasFilamentForm || $source instanceof AcceptsUploads)
            ->mapWithKeys(fn (Source $source) => [$source::type() => $this->sourceLabels[$source::type()] ?? $source->label()])
            ->toArray();
    }


    public function getSourceForm(string $sourceName, ?Closure $fileUploadComponent = null): array|string|null
    {
        $source = $this->getSource($sourceName);

        if ($source instanceof HasFilamentForm) {
            /**
             * @var HasFilamentForm $source
             */

            return $source->getFormSchema($fileUploadComponent);
        } else if ($source instanceof AcceptsUploads) {
            return $fileUploadComponent
				? [$fileUploadComponent()]
				: null;
        }

        return null;
    }

    public function setSourceLabel(string $source, string $label): self
    {
        $this->sourceLabels[$source] = $label;

        return $this;
    }

    /**
     * @return Collection<FileType>
     */
    public function validFileTypes(): Collection
    {
        return collect($this->fileTypes)
            ->map(fn (string $classPath) => app($classPath));
    }

    public function registerFileType(string $classPath): self
    {
        if (!in_array(FileType::class, class_implements($classPath))) {
            throw new \Exception("{$classPath} must implement " . FileType::class);
        }

        $this->fileTypes[] = $classPath;

        return $this;
    }

    public function determineFileTypeFromMime(string $mime): FileType
    {
        foreach ($this->fileTypes as $fileType) {
            if (array_search($mime, $fileType::supportedMimeTypes()) !== false) {
                return app($fileType, ['mime' => $mime]);
            }
        }

        return app(Other::class, ['mime' => $mime]);
    }

    public function makeFileType(string $slug, ?string $mime = null): ?FileType
    {
        foreach ($this->fileTypes as $fileTypeClass) {
            $type = app($fileTypeClass, ['mime' => $mime]);

            if ($type->slug() === $slug) {
                return $type;
            }
        }

        return null;
    }


    public function configureMediaConversionsUsing(Closure $callback): self
    {
        $this->configureMediaConversions = $callback;

        return $this;
    }

    public function callConfigureMediaConversions(HasMedia $directory, ?Media $media = null): void
    {
        if ($this->configureMediaConversions !== null) {
            $this->configureMediaConversions->call($directory, $directory, $media);
        }
    }
}
