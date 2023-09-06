<?php

namespace Cabinet\Sources\Contracts;

use Closure;

interface HasFilamentForm
{
    public function getFormSchema(Closure $fileUploadComponent): array;


}
