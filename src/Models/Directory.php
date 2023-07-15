<?php

namespace Cabinet\Models;

use Cabinet\Folder;
use Cabinet\Models\Concerns\WithUuid;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

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
}
