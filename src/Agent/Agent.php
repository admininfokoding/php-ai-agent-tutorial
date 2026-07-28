<?php

namespace App\Agent;

use App\Tools\ToolInterface;

class Agent
{
    /** @var array<string, ToolInterface> */
    private array $tools = [];

    public function __construct(
        private readonly string $apiKey,
        private readonly string $model = 'gpt-4o-mini',
        private readonly int $maxIterations = 5
    ) {}

    public function addTool(ToolInterface $tool): self
    {
        $def = $tool->getDefinition();
        $name = $def['function']['name'];
        $this->tools[$name] = $tool;
        return $this;
    }

    public function run(string $goal, string $systemPrompt = "Kamu adalah AI Agent cerdas yang dapat merencanakan dan menggunakan tools."): string
    {
        $messages = [
            ["role" => "system", "content" => $systemPrompt],
            ["role" => "user", "content" => $goal]
        ];

        $toolDefinitions = array_map(fn($t) => $t->getDefinition(), array_values($this->tools));

        for ($iteration = 1; $iteration <= $this->maxIterations; $iteration++) {
            $payload = [
                "model" => $this->model,
                "messages" => $messages,
                "temperature" => 0.2
            ];

            if (!empty($toolDefinitions)) {
                $payload["tools"] = $toolDefinitions;
                $payload["tool_choice"] = "auto";
            }

            $ch = curl_init("https://api.openai.com/v1/chat/completions");
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_HTTPHEADER => [
                    "Content-Type: application/json",
                    "Authorization: Bearer " . $this->apiKey
                ]
            ]);

            $rawResponse = curl_exec($ch);
            curl_close($ch);

            $response = json_decode($rawResponse, true);
            $message = $response["choices"][0]["message"] ?? [];

            if (empty($message)) {
                return "Gagal mendapatkan respon dari AI API.";
            }

            $messages[] = $message;

            // FASE ACT: Mengeksekusi Tool jika diminta oleh LLM
            if (!empty($message["tool_calls"])) {
                foreach ($message["tool_calls"] as $toolCall) {
                    $toolName = $toolCall["function"]["name"];
                    $args = json_decode($toolCall["function"]["arguments"] ?? "{}", true);

                    if (isset($this->tools[$toolName])) {
                        $result = $this->tools[$toolName]->execute($args);
                    } else {
                        $result = ["error" => "Tool '$toolName' tidak terdaftar."];
                    }

                    // FASE REFLECT: Mengirimkan hasil eksekusi tool kembali ke LLM
                    $messages[] = [
                        "role" => "tool",
                        "tool_call_id" => $toolCall["id"],
                        "content" => json_encode($result)
                    ];
                }
                continue; // Lanjutkan Agent Loop
            }

            // FASE FINAL: Jika tidak ada pemanggilan tool lagi, kembalikan teks hasil akhir
            return $message["content"] ?? "Selesai.";
        }

        return "Batas maksimum iterasi Agent Loop ($this->maxIterations) telah tercapai.";
    }
}
