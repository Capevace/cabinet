<?php

namespace Cabinet\Models;

use Cabinet\Cabinet;
use Cabinet\File;
use Cabinet\Models\Concerns\WithUuid;
use Illuminate\Database\Eloquent\Model;

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
}
