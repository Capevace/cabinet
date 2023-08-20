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
            $file->update([
                'name' => $name,
            ]);

            return $this;
        } else if ($file instanceof Folder && $file->isCabinetFolder()) {
            $file->findDirectoryOrFail()
                ->update([
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
            $file->delete();

            return $this;
        } else if ($file instanceof Folder && $file->isCabinetFolder()) {
            $file->findDirectoryOrFail()
                ->delete();

            return $this;
        }

        $source = $this->getSource($file->source);

        $source->delete($file);

        return $this;
    }
}
