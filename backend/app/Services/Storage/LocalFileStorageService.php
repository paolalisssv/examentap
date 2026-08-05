<?php

namespace App\Services\Storage;

use App\Interfaces\FileStorageInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class LocalFileStorageService implements FileStorageInterface
{
    public function upload(UploadedFile $file, string $path): string
    {
        $directory = dirname($path);
        $filename = basename($path);

        Storage::disk('public')->putFileAs($directory === '.' ? '' : $directory, $file, $filename);

        return Storage::disk('public')->url($path);
    }
}
