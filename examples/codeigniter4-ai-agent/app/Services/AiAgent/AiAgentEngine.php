<?php

namespace App\Services\AiAgent;

use CodeIgniter\HTTP\CURLRequest;
use Config\Services;

/**
 * Class AiAgentEngine
 * Engine utama AI Agent di CodeIgniter 4 yang mengelola ReAct Loop (Reasoning + Acting)
 * dan komunikasi dengan REST API Provider LLM (OpenAI/Gemini) dilengkapi Exponential Backoff Retry.
 */
class AiAgentEngine
{
    private array $tools = [];
    private string $apiKey;
    private CURLRequest $client;

    public function __construct()
    {
        $this->apiKey = env('OPENAI_API_KEY', '');
        $this->client = Services::curlrequest();
    }

    public function registerTool(ToolInterface $tool): void
    {
        $this->tools[$tool->getName()] = $tool;
    }

    /**
     * Method helper HTTP Request dengan Exponential Backoff Retry Loop (Menangani HTTP 429 & 5xx)
     */
    private function requestWithRetry(string $url, array $payload, int $maxRetries = 3): ?array
    {
        $attempt = 0;
        $delay = 1; // Detik awal

        while ($attempt < $maxRetries) {
            $response = $this->client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type'  => 'application/json',
                ],
                'json'        => $payload,
                'http_errors' => false
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode === 200) {
                return json_decode($response->getBody(), true);
            }

            // Retry jika terkena Rate Limit Exceeded (HTTP 429) atau Server Error (5xx)
            if ($statusCode === 429 || $statusCode >= 500) {
                $attempt++;
                if ($attempt >= $maxRetries) {
                    break;
                }
                sleep($delay);
                $delay *= 2; // Exponential delay: 1s, 2s, 4s...
                continue;
            }

            // Error otentikasi / bad request (400, 401, 403) - Hentikan retry
            break;
        }

        return null;
    }

    public function run(string $userPrompt): string
    {
        if (empty($this->apiKey)) {
            return 'API Key belum dikonfigurasi pada file .env!';
        }

        $formattedTools = [];
        foreach ($this->tools as $tool) {
            $formattedTools[] = [
                'type'     => 'function',
                'function' => [
                    'name'        => $tool->getName(),
                    'description' => $tool->getDescription(),
                    'parameters'  => $tool->getParameters(),
                ]
            ];
        }

        $messages = [
            ['role' => 'system', 'content' => 'Anda adalah AI Agent berpengalaman di CodeIgniter 4. Gunakan tools jika membutuhkan data faktual.'],
            ['role' => 'user', 'content' => $userPrompt]
        ];

        // ReAct Loop: Maksimal 3 iterasi eksekusi tool
        for ($i = 0; $i < 3; $i++) {
            $payload = [
                'model'    => 'gpt-4o-mini',
                'messages' => $messages,
                'tools'    => $formattedTools,
            ];

            // Eksekusi API via helper Retry
            $result = $this->requestWithRetry('https://api.openai.com/v1/chat/completions', $payload);
            $choice = $result['choices'][0]['message'] ?? null;

            if (!$choice) {
                return 'Gagal menerima respon dari server LLM setelah retry.';
            }

            if (!empty($choice['tool_calls'])) {
                $messages[] = $choice; // Simpan konteks keputusan LLM

                foreach ($choice['tool_calls'] as $toolCall) {
                    $fnName = $toolCall['function']['name'];
                    $fnArgs = json_decode($toolCall['function']['arguments'], true) ?? [];

                    if (isset($this->tools[$fnName])) {
                        $toolOutput = $this->tools[$fnName]->execute($fnArgs);
                        
                        $messages[] = [
                            'role'         => 'tool',
                            'tool_call_id' => $toolCall['id'],
                            'content'      => json_encode($toolOutput)
                        ];
                    }
                }
            } else {
                return $choice['content'] ?? 'Tidak ada respon.';
            }
        }

        return 'Batas iterasi reasoning tercapai.';
    }
}
