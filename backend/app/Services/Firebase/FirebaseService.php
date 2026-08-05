<?php

namespace App\Services\Firebase;

use Google\Cloud\Firestore\FirestoreClient;
use Google\Cloud\Storage\Bucket;
use GuzzleHttp\Client;
use Kreait\Firebase\Factory;

class FirebaseService
{
    protected Factory $factory;

    public function __construct()
    {
        $this->factory = new Factory();

        if ($credentials = config('firebase.credentials')) {
            $this->factory = $this->factory->withServiceAccount($credentials);
        }

        if ($projectId = config('firebase.project_id')) {
            $this->factory = $this->factory->withProjectId($projectId);
        }
    }

    public function firestore(): FirestoreClient
    {
        return $this->factory->createFirestore()->database();
    }

    public function apiClient(): Client
    {
        return $this->factory->createApiClient();
    }

    public function projectId(): string
    {
        return (string) config('firebase.project_id');
    }

    public function storageBucket(): Bucket
    {
        $bucketName = config('firebase.storage_bucket');

        return $this->factory->createStorage()->getBucket($bucketName ?: null);
    }
}
