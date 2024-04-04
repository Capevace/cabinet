<?php

namespace Cabinet\Facades;

use Closure;

/**
 * @method static self registerSource(string $name, string $className)
 * @method static \Cabinet\Source getSource(string $source)
 * @method static \Illuminate\Support\Collection validSources()
 * @method static \Illuminate\Support\Collection mapSources(?array $sourceNames = NULL)
 * @method static array getSourceOptions()
 * @method static array|string|null getSourceForm(string $sourceName, ?Closure $fileUploadComponent = NULL)
 * @method static self setSourceLabel(string $source, string $label)
 * @method static \Illuminate\Support\Collection validFileTypes()
 * @method static self registerFileType(string $classPath)
 * @method static \Cabinet\FileType determineFileTypeFromMime(string $mime)
 * @method static ?\Cabinet\FileType makeFileType(string $slug, ?string $mime = NULL)
 * @method static self configureMediaConversionsUsing(Closure $callback)
 * @method static void callConfigureMediaConversions(\Spatie\MediaLibrary\HasMedia $directory, ?\Spatie\MediaLibrary\MediaCollections\Models\Media $media = NULL)
 * @method static self move(\Cabinet\File|\Cabinet\Models\Directory|\Cabinet\Folder $file, \Cabinet\Folder|\Cabinet\Models\Directory $directory)
 * @method static self rename(\Cabinet\File|\Cabinet\Models\Directory|\Cabinet\Folder $file, string $name)
 * @method static self delete(\Cabinet\File|\Cabinet\Folder|\Cabinet\Models\Directory $file)
 * @method static ?\Cabinet\Folder folder(\Cabinet\Models\Directory|string $directory)
 * @method static ?\Cabinet\Models\Directory directory(\Cabinet\Folder|string $directory)
 * @method static \Cabinet\Models\Directory createDirectory(string $name, \Cabinet\Folder|\Cabinet\Models\Directory|null $parent = NULL, bool $protected = false, ?string $translationKey = NULL)
 * @method static \Cabinet\Models\Directory findOrCreateDirectory(string $id, string $name, \Cabinet\Folder|\Cabinet\Models\Directory|null $parent = NULL, bool $protected = false, ?string $translationKey = NULL)
 * @method static string getDirectoryMorphClass()
 * @method static \Illuminate\Support\Collection files(\Cabinet\Models\Directory|\Cabinet\Folder $directory, ?array $sourceNames = NULL)
 * @method static findCabinetDirectory(string $id)
 * @method static ?\Cabinet\File file(string $sourceName, string $idOrPath, ?string $disk = NULL)
 * @method static string contents(\Cabinet\File $file)
 * @method static ?string generateFileUrl(\Cabinet\File $file, ?string $variant = NULL, ?DateTimeInterface $expiresAt = NULL)
 * @method static ?string generateFilePath(\Cabinet\File $file)
 * @method static ?Illuminate\Database\Eloquent\Model getFileModel(\Cabinet\File $file)
 * @method static \Symfony\Component\HttpFoundation\Response download(\Cabinet\File $file)
 * @method static \Illuminate\Support\Collection references(\Cabinet\File $file, ?int $limit = NULL, ?int $offset = NULL, ?array $sourceNames = NULL)
 * @method static ?\Cabinet\Models\FileRef findReference(string $id)
 * @method static createReference(\Cabinet\File $file)
 * @method static \Cabinet\Models\FileRef attach(\Cabinet\File $file, ?\Illuminate\Database\Eloquent\Model $to = NULL, ?string $as = NULL, ?int $order = NULL)
 * @method static static syncMany(\Cabinet\HasFiles $record, string $relationship, \Illuminate\Support\Collection|array $files)
 * @method static static syncOne(\Cabinet\HasFiles $record, string $relationship, ?\Cabinet\File $file)
 * @method static static reorder(\Cabinet\HasFiles $record, string $relationship, int $from, int $to)
 *
 */
class Cabinet extends \Illuminate\Support\Facades\Facade
{
    protected static function getFacadeAccessor()
    {
        return 'cabinet';
    }
}
