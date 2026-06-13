<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Qdrant Cloud Connection
    |--------------------------------------------------------------------------
    |
    | Supports QWDRANT_* (project convention) and QDRANT_* env variable names.
    |
    */

    'endpoint' => rtrim((string) env('QWDRANT_ENDPOINT', env('QDRANT_ENDPOINT', '')), '/'),

    'api_key' => env('QWDRANT_API_KEY', env('QDRANT_API_KEY')),

    'collection' => env('QWDRANT_COLLECTION', env('QDRANT_COLLECTION', 'hackathon_documents')),

    'vector_size' => (int) env('QWDRANT_VECTOR_SIZE', env('QDRANT_VECTOR_SIZE', 768)),

    'timeout' => (int) env('QWDRANT_TIMEOUT', env('QDRANT_TIMEOUT', 30)),

];
