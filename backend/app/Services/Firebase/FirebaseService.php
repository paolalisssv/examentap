<?php

namespace App\Services\Firebase;

use Google\Cloud\Firestore\FirestoreClient;
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
}
