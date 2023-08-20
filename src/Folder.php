<?php

namespace Cabinet;

//interface File
//{
//    public function id(): string;
//
//    public function source(): string;
//
//    public function type(): FileType;
//
//    public function name(): string;
//
//    public function slug(): string;
//
//    public function mimeType(): string;
//
//    public function size(): int;
//
//    public function url(): string;
//}

use Cabinet\Exceptions\FileNotFound;
use Cabinet\Exceptions\WrongSource;
use Cabinet\Models\Directory;
use Illuminate\Support\Collection;
use Livewire\Wireable;

readonly class Folder implements Wireable
{
    public function __construct(
        public string $id,
        public string $source,
        public string $name,
        public FileType $type = new \Cabinet\Types\Folder,
    ) {
    }

    public function files(): Collection
    {
        return $this->getCabinet()->files($this);
    }

    public function isCabinetFolder(): bool
    {
        return $this->source === 'cabinet';
    }

    public function findDirectoryOrFail(): Directory
    {
        if (!$this->isCabinetFolder()) {
            throw new WrongSource("This Folder is managed by {$this->source}, and can not be used as a Cabinet directory");
        }

        $directory = $this->getCabinet()->directory($this->id);

        if ($directory === null) {
            throw new FileNotFound("Directory with ID {$this->id} could not be found");
        }

        return $directory;
    }

    protected function getCabinet(): Cabinet
    {
        return app(Cabinet::class);
    }

    public function uniqueId(): string
    {
        return "D-{$this->source}-{$this->id}";
    }

    public function toLivewire()
    {
        return [
            'id' => $this->id,
            'source' => $this->source,
            'name' => $this->name,
        ];
    }

    public static function fromLivewire($value)
    {
        return new static(
            id: $value['id'],
            source: $value['source'],
            name: $value['name'],
        );
    }

    public function toIdentifier(): array
    {
        return [
            'type' => $this->type->slug(),
            'id' => $this->id,
            'source' => $this->source,
        ];
    }
}
