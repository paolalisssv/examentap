<?php

namespace App\Services\Firebase;

use App\Interfaces\FileStorageInterface;
use Illuminate\Http\UploadedFile;

class FirebaseStorageService implements FileStorageInterface
{
    public function __construct(private readonly FirebaseService $firebase)
    {
    }

    public function upload(UploadedFile $file, string $path): string
    {
        $bucket = $this->firebase->storageBucket();

        $stream = fopen($file->getRealPath(), 'r');

        $bucket->upload($stream, [
            'name' => $path,
            'predefinedAcl' => 'publicRead',
        ]);

        return sprintf('https://storage.googleapis.com/%s/%s', $bucket->name(), $path);
    }
}
