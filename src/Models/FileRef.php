<?php

namespace Cabinet\Models;

use Cabinet\Cabinet;
use Cabinet\File;
use Cabinet\Models\Concerns\WithUuid;
use Cabinet\Types\Image;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FileRef extends Model
{
    use WithUuid;

    protected $table = 'cabinet:file_refs';

    protected $fillable = [
        'source',

        'attached_to_type',
        'attached_to_id',
        'attached_as',
        'attached_order',

        'model_type',
        'model_id',

        'disk',
        'path',
    ];

    protected $with = [
        'model',
    ];

    public function file(): File
    {
        $cabinet = app(Cabinet::class);
        $source = $cabinet->getSource($this->source);


        return new File(
            id: 'wat',
            source: 'spatie-media',
            type: new Image(),
            name: 'test.png',
            slug: 'test.png',
            mimeType: 'image/png',
            size: 6566715,
            previewUrl: null,
            model: $this->model,
        );

        return $source->transform($this);
    }

    public function validateSource(string $source)
    {
        if ($this->source !== $source) {
            throw new \LogicException('Source type mismatch');
        }
    }

    public function references(?File $file): bool
    {
        if (!$file) {
            return false;
        }

        return $this->file()->uniqueId() === $file->uniqueId();
    }

    public function model(): MorphTo
    {
        return $this->morphTo('model');
    }
}
