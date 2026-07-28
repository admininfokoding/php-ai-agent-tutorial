<?php

namespace App\Tools;

interface ToolInterface
{
    /**
     * Mengembalikan definisi tool dalam format OpenAI Function Calling JSON Schema.
     */
    public function getDefinition(): array;

    /**
     * Mengeksekusi logika tool berdasarkan argumen yang dikembalikan oleh AI.
     */
    public function execute(array $args): mixed;
}
