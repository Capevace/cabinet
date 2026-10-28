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

    public function attachedTo(): MorphTo
    {
        // The relation name (first argument) has to be camelCase like the
        // method, while the columns stay snake_case. Naming it 'attached_to'
        // made eager loading with `with('attachedTo')` store the model under a
        // different key than `$ref->attachedTo` reads, so every reference
        // resolved to null.
        return $this->morphTo(__FUNCTION__, 'attached_to_type', 'attached_to_id');
    }
}
