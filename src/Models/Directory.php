<?php

namespace Cabinet\Models;

use Cabinet\Facades\Cabinet;
use Cabinet\Folder;
use Cabinet\Models\Concerns\WithUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property-read string $id
 * @property string $name
 * @property string $parent_directory_id
 * @property Directory $parentDirectory
 * @property Collection<Directory> $directories
 * @property string $translation_key
 * @property bool $is_protected
 */
class Directory extends Model implements HasMedia
{
    use WithUuid, InteractsWithMedia;

    protected $table = 'cabinet:directories';

    protected $fillable = [
        'name',
        'parent_directory_id',
    ];

    public function parentDirectory()
    {
        return $this->belongsTo(Directory::class, 'parent_directory_id');
    }

    public function directories()
    {
        return $this->hasMany(Directory::class, 'parent_directory_id');
    }

    public function asFolder(): Folder
    {
        return new Folder(
            id: $this->id,
            source: 'cabinet',
            name: $this->translation_key
                ? trans_choice($this->translation_key, 9999)
                : $this->name,
        );
    }

    public function isCabinetDirectory(): bool
    {
        return true;
    }

    public function registerMediaConversions(Media $media = null): void
    {
        if ($conversion = config('cabinet.spatie_media_library.preview_conversion')) {
            $this->addMediaConversion($conversion)
                ->width(400)
                ->background('transparent')
                ->format('jpg')
                ->quality(85)
                ->pdfPageNumber(1);
        }

        Cabinet::callConfigureMediaConversions($this, $media);
    }
}
