<?php

namespace Cabinet\Sources\Contracts;

interface HasFilamentForm extends AcceptsData
{
    public function getFormSchema(): array;
}
