<?php

/**
 * Tutorial Bab 1: Contoh Sederhana Skrip AI Agent Loop dengan PHP 8.3
 * 
 * Menjalankan Agent Loop (Observe -> Think -> Plan -> Act -> Reflect)
 * dengan Function Calling untuk mengecek stok barang di database.
 */

require_once __DIR__ . '/../src/Tools/ToolInterface.php';
require_once __DIR__ . '/../src/Tools/StockTool.php';
require_once __DIR__ . '/../src/Tools/CalculatorTool.php';
require_once __DIR__ . '/../src/Agent/Agent.php';

use App\Agent\Agent;
use App\Tools\StockTool;
use App\Tools\CalculatorTool;

// Ambil API Key dari environment atau ganti dengan API Key Anda
$apiKey = getenv('OPENAI_API_KEY') ?: 'sk-proj-YOUR_OPENAI_API_KEY';

if ($apiKey === 'sk-proj-YOUR_OPENAI_API_KEY') {
    echo "⚠️ Harap set OPENAI_API_KEY di environment atau di file .env terlebih dahulu.\n";
}

echo "=== DEMO AI AGENT LOOP (BAB 1) ===\n\n";

// Inisialisasi Agent
$agent = new Agent($apiKey);

// Registrasi Tools
$agent->addTool(new StockTool());
$agent->addTool(new CalculatorTool());

// Jalankan Agent dengan Goal Kompleks
$goal = "Tolong cek stok barang 'Laptop Asus', dan jika ada 3 unit, hitung berapa total harganya!";
echo "Goal: $goal\n\n";
echo "Menjalankan Agent Loop...\n";

$result = $agent->run($goal);

echo "\nJawaban Akhir Agent:\n";
echo "----------------------------------------\n";
echo $result . "\n";
echo "----------------------------------------\n";
