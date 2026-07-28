<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

/**
 * Class AiStreamController
 * Controller CodeIgniter 4 untuk menangani Server-Sent Events (SSE) Streaming Token LLM secara real-time.
 */
class AiStreamController extends BaseController
{
    public function stream()
    {
        $prompt = esc($this->request->getGet('prompt'));
        if (empty($prompt)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Prompt wajib diisi']);
        }

        // 1. Set Header khusus Server-Sent Events (SSE) & matikan Nginx/Apache Buffering
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no'); // Penting untuk Web Server Nginx

        // 2. Bersihkan output buffer PHP bawaan
        while (ob_get_level() > 0) {
            ob_end_flush();
        }

        // 3. Panggil API OpenAI dengan opsi 'stream' => true
        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type: application/json',
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                'model'    => 'gpt-4o-mini',
                'messages' => [['role' => 'user', 'content' => $prompt]],
                'stream'   => true,
            ]),
            CURLOPT_WRITEFUNCTION => function ($ch, $data) {
                echo $data;
                flush(); // Dorong chunk data secara instan ke browser
                return strlen($data);
            }
        ]);

        curl_exec($ch);
        curl_close($ch);
        exit();
    }
}
