<?php

return [

    'project_id' => env('FIREBASE_PROJECT_ID'),

    'credentials' => env('FIREBASE_CREDENTIALS'),

    'collections' => [
        'users' => env('FIRESTORE_USERS_COLLECTION', 'users'),
        'auth_tokens' => env('FIRESTORE_TOKENS_COLLECTION', 'auth_tokens'),
        'perfiles' => env('FIRESTORE_PERFILES_COLLECTION', 'perfiles'),
        'bitacora' => env('FIRESTORE_BITACORA_COLLECTION', 'bitacora'),
        'productos' => env('FIRESTORE_PRODUCTOS_COLLECTION', 'productos'),
    ],

    'auth_token_ttl_minutes' => env('AUTH_TOKEN_TTL_MINUTES', 1440),

    'storage_bucket' => env('FIREBASE_STORAGE_BUCKET'),

];
