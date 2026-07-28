<?php

namespace App\Controllers;

use App\Services\AiAgent\AiAgentEngine;
use App\Services\AiAgent\Tools\DatabaseTool;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Class AiAgentController
 * Controller API CodeIgniter 4 untuk memproses request AI Agent secara aman.
 */
class AiAgentController extends BaseController
{
    public function index(): string
    {
        return view('ai_agent/index', [
            'title' => 'Demo AI Agent CodeIgniter 4'
        ]);
    }

    public function process(): ResponseInterface
    {
        // 1. Validasi Input Strict & Filter XSS (CI4 Validation Service)
        $rules = [
            'prompt' => 'required|min_length[3]|max_length[1000]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'errors' => $this->validator->getErrors()
            ])->setStatusCode(400);
        }

        $prompt = esc($this->request->getPost('prompt'));

        // 2. Inisialisasi Engine & Registrasi Tools
        $engine = new AiAgentEngine();
        $engine->registerTool(new DatabaseTool());

        // 3. Eksekusi Agent
        $answer = $engine->run($prompt);

        return $this->response->setJSON([
            'status' => 'success',
            'answer' => $answer
        ]);
    }
}
