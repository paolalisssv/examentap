<?php

namespace Tests\Support\Fakes;

use App\Interfaces\FileStorageInterface;
use Illuminate\Http\UploadedFile;

class FakeFileStorage implements FileStorageInterface
{
    public array $uploaded = [];

    public function upload(UploadedFile $file, string $path): string
    {
        $this->uploaded[] = $path;

        return "https://fake-storage.test/{$path}";
    }
}
