<?php

namespace App\Services\AiAgent;

use CodeIgniter\HTTP\CURLRequest;
use Config\Services;

/**
 * Class AiAgentEngine
 * Engine utama AI Agent di CodeIgniter 4 yang mengelola ReAct Loop (Reasoning + Acting)
 * dan komunikasi dengan REST API Provider LLM (OpenAI/Gemini).
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

            $response = $this->client->post('https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type'  => 'application/json',
                ],
                'json' => $payload,
                'http_errors' => false
            ]);

            $result = json_decode($response->getBody(), true);
            $choice = $result['choices'][0]['message'] ?? null;

            if (!$choice) {
                return 'Gagal menerima respon dari server LLM.';
            }

            // Jika LLM meminta pemanggilan Tool
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
                // Jawaban akhir dari AI Agent
                return $choice['content'] ?? 'Tidak ada respon.';
            }
        }

        return 'Batas iterasi reasoning tercapai.';
    }
}
