<?php

namespace Cabinet\Facades;

/**
 * @method static \Cabinet\File|null file(string $source, string $id)
 */
class Cabinet extends \Illuminate\Support\Facades\Facade
{
    protected static function getFacadeAccessor()
    {
        return 'cabinet';
    }
}
