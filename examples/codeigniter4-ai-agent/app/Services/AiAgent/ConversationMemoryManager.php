<?php

namespace App\Services\AiAgent;

use Config\Services;

/**
 * Class ConversationMemoryManager
 * Kelas pengelola histori percakapan (Conversation Memory) berbasis CI4 Session & Redis dengan teknik Sliding Window.
 */
class ConversationMemoryManager
{
    private $session;

    public function __construct()
    {
        $this->session = Services::session();
    }

    /**
     * Mengambil riwayat percakapan dari Session / Redis
     */
    public function getHistory(string $sessionId): array
    {
        return $this->session->get('agent_chat_' . $sessionId) ?? [];
    }

    /**
     * Menambahkan pesan baru ke memori dengan limit sliding window
     */
    public function pushMessage(string $sessionId, string $role, string $content, int $maxHistory = 10): void
    {
        $key = 'agent_chat_' . $sessionId;
        $history = $this->session->get($key) ?? [];

        $history[] = [
            'role'    => $role, // 'user' atau 'assistant'
            'content' => $content,
            'time'    => date('Y-m-d H:i:s')
        ];

        // Pertahankan hanya N percakapan terakhir (Sliding Window Memory)
        if (count($history) > $maxHistory) {
            $history = array_slice($history, -$maxHistory);
        }

        $this->session->set($key, $history);
    }
}
