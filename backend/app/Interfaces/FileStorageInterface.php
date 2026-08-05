<?php

namespace App\Interfaces;

use Illuminate\Http\UploadedFile;

interface FileStorageInterface
{
    public function upload(UploadedFile $file, string $path): string;
}
