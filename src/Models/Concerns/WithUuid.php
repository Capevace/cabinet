<?php

namespace Cabinet\Models\Concerns;

use Illuminate\Support\Str;

trait WithUuid
{
    public function getIncrementing()
	{
		return false;
	}

	public function getKeyType()
	{
		return 'string';
	}

    protected static function bootWithUuid()
	{
		static::creating(function ($model) {
			if (!$model->getKey()) {
				$model->{$model->getKeyName()} = (string) Str::uuid();
			}
		});
	}


}
