<?php

namespace Cabinet;

use Cabinet\Models\Directory;
use Cabinet\Sources\SpatieMediaSource;
use Cabinet\Types\Image;
use Cabinet\Types\Video;
use Illuminate\Support\Collection;

class Cabinet
{
    protected $sources = [
        SpatieMediaSource::TYPE => SpatieMediaSource::class,
    ];

    protected $fileTypes = [
        Image::class,
        Video::class,
    ];

    public function registerSource(string $name, string $className): self
    {
        // Check if class implements Source interface
        if (!in_array(Source::class, class_implements($className))) {
            throw new \Exception("{$className} must implement " . Source::class);
        }

        $this->sources[$name] = $className;

        return $this;
    }

    public function getSource(string $source): Source
    {
        if (!isset($this->sources[$source])) {
            throw new \Exception("Source {$source} is not registered");
        }

        return app($this->sources[$source]);
    }

    public function determineFileTypeFromMime(string $mime): FileType
    {
        foreach ($this->fileTypes as $fileType) {
            if (array_search($mime, $fileType::supportedMimeTypes()) !== false) {
                return app($fileType);
            }
        }

        throw new \Exception("Could not determine file type for mime {$mime}");
    }

    /**
     * @return Collection<File|Folder>
     */
    public function all(Folder $folder, ?array $sourceNames = null): Collection
    {
        $sourceNames = $sourceNames ?? array_keys($this->sources);

        $files = collect($sourceNames)
            ->map(fn (string $source) => $this->getSource($source))
            ->map(fn (Source $source) => $source->all($folder))
            ->flatten();

        if ($folder->isCabinetFolder()) {
            $subdirs = $this->findCabinetDirectory($folder->id)
                ->directories
                ->map(fn (Directory $directory) => new Folder(
                    id: $directory->id,
                    source: 'cabinet',
                    name: $directory->name,
                ));

            $files = $files
                ->merge($subdirs);
        }


        return $files;
    }

    public function move(File $file, Directory $directory): self
    {
        $source = $this->getSource($file->source);

        $source->move($file, $directory);

        return $this;
    }

    public function rename(File $file, string $name): self
    {
        $source = $this->getSource($file->source);

        $source->rename($file, $name);

        return $this;
    }

    public function delete(File $file): self
    {
        $source = $this->getSource($file->source);

        $source->delete($file);

        return $this;
    }

    public function getDirectoryMorphClass(): string
    {
        $className = config('cabinet.directory_model');
        $dir = new $className;

        return $dir->getMorphClass();
    }

    public function findCabinetDirectory(string $id)
    {
        $className = config('cabinet.directory_model');

        return $className::find($id);
    }
}
