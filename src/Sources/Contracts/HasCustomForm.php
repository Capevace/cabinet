<?php

namespace Cabinet\Sources\Contracts;

use Illuminate\View\View;

interface HasCustomForm
{
    public function getFormView(): View;

    public function getSubmitFn(): \Closure;
}
