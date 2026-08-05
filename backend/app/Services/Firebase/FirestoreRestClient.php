<?php

namespace App\Services\Firebase;

use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class FirestoreRestClient
{
    private readonly string $baseUri;

    public function __construct(private readonly FirebaseService $firebase)
    {
        $this->baseUri = sprintf(
            'https://firestore.googleapis.com/v1/projects/%s/databases/(default)/documents',
            $this->firebase->projectId(),
        );
    }

    public function find(string $collection, string $documentId): ?array
    {
        try {
            $response = $this->firebase->apiClient()->get("{$this->baseUri}/{$collection}/{$documentId}");
        } catch (ClientException $exception) {
            if ($exception->getResponse()?->getStatusCode() === 404) {
                return null;
            }

            throw $exception;
        }

        return $this->mapDocument($this->decodeResponse($response));
    }

    public function findOneByField(string $collection, string $field, string $value): ?array
    {
        $response = $this->firebase->apiClient()->post("{$this->baseUri}:runQuery", [
            'json' => [
                'structuredQuery' => [
                    'from' => [['collectionId' => $collection]],
                    'where' => [
                        'fieldFilter' => [
                            'field' => ['fieldPath' => $field],
                            'op' => 'EQUAL',
                            'value' => ['stringValue' => $value],
                        ],
                    ],
                    'limit' => 1,
                ],
            ],
        ]);

        foreach ($this->decodeResponse($response) as $result) {
            if (isset($result['document'])) {
                return $this->mapDocument($result['document']);
            }
        }

        return null;
    }

    public function create(string $collection, array $fields, ?string $documentId = null): array
    {
        $uri = "{$this->baseUri}/{$collection}";

        if ($documentId !== null) {
            $uri .= '?documentId='.urlencode($documentId);
        }

        $response = $this->firebase->apiClient()->post($uri, [
            'json' => ['fields' => $this->encodeFields($fields)],
        ]);

        return $this->mapDocument($this->decodeResponse($response));
    }

    public function update(string $collection, string $documentId, array $fields): void
    {
        $mask = collect(array_keys($fields))
            ->map(fn (string $field) => 'updateMask.fieldPaths='.urlencode($field))
            ->implode('&');

        $this->firebase->apiClient()->patch("{$this->baseUri}/{$collection}/{$documentId}?{$mask}", [
            'json' => ['fields' => $this->encodeFields($fields)],
        ]);
    }

    public function delete(string $collection, string $documentId): void
    {
        try {
            $this->firebase->apiClient()->delete("{$this->baseUri}/{$collection}/{$documentId}");
        } catch (ClientException $exception) {
            if ($exception->getResponse()?->getStatusCode() !== 404) {
                throw $exception;
            }
        }
    }

    public function any(string $collection): bool
    {
        $response = $this->firebase->apiClient()->post("{$this->baseUri}:runQuery", [
            'json' => [
                'structuredQuery' => [
                    'from' => [['collectionId' => $collection]],
                    'limit' => 1,
                ],
            ],
        ]);

        foreach ($this->decodeResponse($response) as $result) {
            if (isset($result['document'])) {
                return true;
            }
        }

        return false;
    }

    public function all(string $collection): array
    {
        $documents = [];
        $pageToken = null;

        do {
            $query = ['pageSize' => 300];

            if ($pageToken !== null) {
                $query['pageToken'] = $pageToken;
            }

            $response = $this->firebase->apiClient()->get("{$this->baseUri}/{$collection}", [
                'query' => $query,
            ]);

            $payload = $this->decodeResponse($response);

            foreach ($payload['documents'] ?? [] as $document) {
                $documents[] = $this->mapDocument($document);
            }

            $pageToken = $payload['nextPageToken'] ?? null;
        } while ($pageToken !== null);

        return $documents;
    }

    private function decodeResponse(\Psr\Http\Message\ResponseInterface $response): array
    {
        return json_decode($response->getBody()->getContents(), true) ?? [];
    }

    private function mapDocument(array $document): array
    {
        $id = Str::afterLast($document['name'], '/');

        return ['id' => $id, ...$this->decodeFields($document['fields'] ?? [])];
    }

    private function encodeFields(array $fields): array
    {
        $encoded = [];

        foreach ($fields as $key => $value) {
            $encoded[$key] = $this->encodeValue($value);
        }

        return $encoded;
    }

    private function encodeValue(mixed $value): array
    {
        return match (true) {
            $value === null => ['nullValue' => null],
            is_bool($value) => ['booleanValue' => $value],
            $value instanceof Carbon => ['timestampValue' => $value->clone()->utc()->format('Y-m-d\TH:i:s.v\Z')],
            is_int($value) => ['integerValue' => (string) $value],
            is_array($value) && array_is_list($value) => [
                'arrayValue' => ['values' => array_map($this->encodeValue(...), $value)],
            ],
            is_array($value) => ['mapValue' => ['fields' => $this->encodeFields($value)]],
            default => ['stringValue' => (string) $value],
        };
    }

    private function decodeFields(array $fields): array
    {
        $decoded = [];

        foreach ($fields as $key => $value) {
            $decoded[$key] = $this->decodeValue($value);
        }

        return $decoded;
    }

    private function decodeValue(array $value): mixed
    {
        return match (true) {
            array_key_exists('stringValue', $value) => $value['stringValue'],
            array_key_exists('booleanValue', $value) => $value['booleanValue'],
            array_key_exists('integerValue', $value) => (int) $value['integerValue'],
            array_key_exists('timestampValue', $value) => Carbon::parse($value['timestampValue']),
            array_key_exists('arrayValue', $value) => array_map(
                fn (array $item) => $this->decodeValue($item),
                $value['arrayValue']['values'] ?? []
            ),
            array_key_exists('mapValue', $value) => $this->decodeFields($value['mapValue']['fields'] ?? []),
            default => null,
        };
    }
}
