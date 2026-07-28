<?php

namespace App\Services\AiAgent\Tools;

use App\Services\AiAgent\ToolInterface;
use Config\Database;

/**
 * Class VectorSearchTool
 * Concrete implementation tool untuk melakukan pencarian semantik (Semantic Search / RAG)
 * menggunakan PostgreSQL Pgvector atau Qdrant di CodeIgniter 4.
 */
class VectorSearchTool implements ToolInterface
{
    public function getName(): string
    {
        return 'search_knowledge_base';
    }

    public function getDescription(): string
    {
        return 'Mencari informasi dokumen internal perusahaan berdasarkan pencarian semantik (Vector Embedding).';
    }

    public function getParameters(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'query' => [
                    'type'        => 'string',
                    'description' => 'Kata kunci atau pertanyaan topik yang dicari dalam dokumen'
                ]
            ],
            'required'   => ['query']
        ];
    }

    public function execute(array $args): array
    {
        $queryText = trim($args['query'] ?? '');
        if (empty($queryText)) {
            return ['error' => 'Query tidak boleh kosong'];
        }

        // 1. Generate Vector Embedding via API OpenAI (model text-embedding-3-small)
        $vectorArray = $this->generateEmbedding($queryText);
        $vectorString = '[' . implode(',', $vectorArray) . ']';

        // 2. Query Pencarian Cosine Similarity ke PostgreSQL (pgvector operator <=>)
        $db = Database::connect('pgsql'); // Koneksi ke database PostgreSQL
        $sql = "SELECT id, title, content, 1 - (embedding <=> ?::vector) AS similarity 
                FROM document_embeddings 
                ORDER BY similarity DESC 
                LIMIT 3";

        $results = $db->query($sql, [$vectorString])->getResultArray();

        return [
            'status'  => 'success',
            'query'   => $queryText,
            'matches' => $results
        ];
    }

    private function generateEmbedding(string $text): array
    {
        $client = \Config\Services::curlrequest();
        $response = $client->post('https://api.openai.com/v1/embeddings', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type'  => 'application/json'
            ],
            'json' => [
                'model' => 'text-embedding-3-small',
                'input' => $text
            ]
        ]);

        $json = json_decode($response->getBody(), true);
        return $json['data'][0]['embedding'] ?? [];
    }
}
