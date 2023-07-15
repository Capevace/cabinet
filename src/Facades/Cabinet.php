<?php

namespace Cabinet\Facades;

class Cabinet extends \Illuminate\Support\Facades\Facade
{
    protected static function getFacadeAccessor()
    {
        return 'cabinet';
    }
}
