<?php

namespace App\Services\AiAgent;

/**
 * Interface ToolInterface
 * Contract standar untuk setiap Tool yang dapat dieksekusi oleh AI Agent di CodeIgniter 4.
 */
interface ToolInterface
{
    /**
     * Nama unik tool yang dikenali LLM (misal: 'get_user_stats')
     */
    public function getName(): string;

    /**
     * Deskripsi fungsi tool untuk panduan reasoning LLM
     */
    public function getDescription(): string;

    /**
     * Skema parameter input (JSON Schema)
     */
    public function getParameters(): array;

    /**
     * Eksekusi logika bisnis tool
     */
    public function execute(array $args): array;
}
