<?php

namespace Cabinet\Models;

use Cabinet\Folder;
use Cabinet\Models\Concerns\WithUuid;
use Illuminate\Database\Eloquent\Model;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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
            name: $this->name,
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
                ->keepOriginalImageFormat();
        }

        $this->addMediaConversion('thumbnail')
              ->width(256)
              ->height(256)
              ->format('jpg')
              ->quality(80)
              ->pdfPageNumber(1);

        $this->addMediaConversion('medium')
            ->fit(Manipulations::FIT_CONTAIN, width: 640, height: 360) // 16:9
            ->format('jpg');

        $this->addMediaConversion('large')
            ->height(1200)
            ->quality(80)
            ->format('jpg');

        $this->addMediaConversion('mini-square')
            ->crop(Manipulations::CROP_CENTER, 256, 256)
            ->border(20, '#fff', 'shrink')
            ->format('jpg');
    }
}
