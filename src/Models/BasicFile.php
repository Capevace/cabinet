<?php

namespace Cabinet\Models;

use Illuminate\Database\Eloquent\Model;

class BasicFile extends Model
{
    use \Cabinet\Models\Concerns\WithUuid;

    protected $table = 'cabinet:basic_files';

    protected $fillable = [
        'name',
        'mime_type',
        'disk',
        'path',
    ];

    public function directory()
    {
        return $this->belongsTo(Directory::class);
    }

    public function fileRef()
    {
        return $this->morphMany(FileRef::class, 'source');
    }
}
