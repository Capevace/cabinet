<?php

namespace Cabinet\Sources\Contracts;

use Illuminate\View\View;

interface HasCustomForm extends AcceptsData
{
    public function getFormView(): View;

    public function getSubmitFn(): \Closure;
}
