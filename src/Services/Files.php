<?php

namespace Cabinet\Services;

use Cabinet\Exceptions\FileNotFound;
use Cabinet\Exceptions\WrongSource;
use Cabinet\File;
use Cabinet\Sources\Contracts\CanBeDownloaded;
use Cabinet\Sources\Contracts\CanGenerateUrls;
use Cabinet\Sources\Contracts\FindWithId;
use Cabinet\Sources\Contracts\FindWithPath;
use Cabinet\Sources\Contracts\HasModel;
use Cabinet\Sources\Contracts\HasPath;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;

trait Files
{
    public function file(string $sourceName, string $idOrPath, ?string $disk = null): ?File
    {
        try {
            $source = $this->getSource($sourceName);
            $interfaces = class_implements($source::class);

            $supportsIdLookup = in_array(FindWithId::class, $interfaces);
            $supportsPathLookup = in_array(FindWithPath::class, $interfaces);

            if (!$supportsIdLookup && !$supportsPathLookup) {
                throw new WrongSource("{$sourceName} source does not implement file lookup");
            } else if ($disk === null && !$supportsIdLookup) {
                throw new WrongSource("{$sourceName} source does not support id lookup");
            } else if ($disk !== null && !$supportsPathLookup) {
                throw new WrongSource("{$sourceName} source does not support path lookup");
            }

            if ($supportsIdLookup) {
                /** @var FindWithId $source */

                return $source->findWithId($idOrPath);
            } else if ($supportsPathLookup) {
                /** @var FindWithPath $source */

                return $source->findWithPath($idOrPath, $disk);
            }
        } catch (FileNotFound $e) {
            report($e);
        }

        return null;
    }

    public function contents(File $file): string
    {
        $source = $this->getSource($file->source);

        if (!class_implements($source::class, HasPath::class)) {
            throw new \Exception("Source {$file->source} does not implement " . HasPath::class);
        }

        return $source->path($file);
    }

    public function generateFileUrl(File $file, ?string $variant = null): ?string
    {
        $source = $this->getSource($file->source);

        if (!class_implements($source::class, CanGenerateUrls::class)) {
            return null;
        }

        /** @var CanGenerateUrls $source */
        return $source->generateUrl($file, $variant);
    }

    public function generateFilePath(File $file): ?string
    {
        $source = $this->getSource($file->source);

        if (!class_implements($source::class, HasPath::class)) {
            return null;
        }

        /** @var HasPath $source */
        return $source->path($file);
    }

    public function getFileModel(File $file): ?Model
    {
        $source = $this->getSource($file->source);

        if (!class_implements($source::class, HasModel::class)) {
            return null;
        }

        /** @var HasModel $source */
        return $source->getFileModel($file);
    }

    public function download(File $file): Response
    {
        $source = $this->getSource($file->source);

        if (!class_implements($source::class, CanBeDownloaded::class)) {
            throw new \Exception("Source {$file->source} does not implement " . CanBeDownloaded::class);
        }

        return $source->download($file);
    }


}
