<?php

namespace Cabinet;

interface CanBeCabinetFile
{
    public function asCabinetFile(): File;
}
