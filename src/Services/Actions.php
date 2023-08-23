<?php

namespace Cabinet\Services;

use Cabinet\File;
use Cabinet\Folder;
use Cabinet\Models\Directory;

trait Actions
{
    public function move(File|Directory|Folder $file, Folder|Directory $directory): self
    {
        if ($file instanceof Directory && $file->isCabinetDirectory()) {
            $file->update([
                'parent_directory_id' => $directory->id,
            ]);

            return $this;
        } else if ($file instanceof Folder && $file->isCabinetFolder()) {
            $file->findDirectoryOrFail()
                ->update([
                    'parent_directory_id' => $directory->id,
                ]);

            return $this;
        }

        $source = $this->getSource($file->source);

        $folder = $directory instanceof Directory
            ? $directory->asFolder()
            : $directory;

        $source->move($file, $folder);

        return $this;
    }

    public function rename(File|Directory|Folder $file, string $name): self
    {
        if ($file instanceof Directory && $file->isCabinetDirectory()) {
            abort_if($file->is_protected, 403);

            $file->update([
                'name' => $name,
            ]);

            return $this;
        } else if ($file instanceof Folder && $file->isCabinetFolder()) {
            $directory = $file->findDirectoryOrFail();

            abort_if($directory->is_protected, 403);

            $directory->update([
                'name' => $name,
            ]);

            return $this;
        }

        $source = $this->getSource($file->source);

        $source->rename($file, $name);

        return $this;
    }

    public function delete(File|Folder|Directory $file): self
    {
        if ($file instanceof Directory && $file->isCabinetDirectory()) {
            abort_if($file->is_protected, 403);

            $file->delete();

            return $this;
        } else if ($file instanceof Folder && $file->isCabinetFolder()) {
            $directory = $file->findDirectoryOrFail();

            abort_if($directory->is_protected, 403);

            $directory->delete();

            return $this;
        }

        $source = $this->getSource($file->source);

        $source->delete($file);

        return $this;
    }
}
