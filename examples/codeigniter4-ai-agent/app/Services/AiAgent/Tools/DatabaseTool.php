<?php

namespace App\Services\AiAgent\Tools;

use App\Services\AiAgent\ToolInterface;
use Config\Database;

/**
 * Class DatabaseTool
 * Concrete implementation tool untuk mengecek data transaksi user di database MySQL secara aman.
 */
class DatabaseTool implements ToolInterface
{
    public function getName(): string
    {
        return 'get_user_stats';
    }

    public function getDescription(): string
    {
        return 'Mengambil statistik jumlah transaksi dan total belanja pengguna berdasarkan User ID.';
    }

    public function getParameters(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'user_id' => [
                    'type'        => 'integer',
                    'description' => 'ID unik pengguna di database'
                ]
            ],
            'required'   => ['user_id']
        ];
    }

    public function execute(array $args): array
    {
        $userId = (int) ($args['user_id'] ?? 0);
        if ($userId <= 0) {
            return ['status' => 'error', 'message' => 'User ID tidak valid'];
        }

        $db = Database::connect();
        
        // Menggunakan Prepared Statement / Query Builder bawaan CI4 untuk keamanan 100% dari SQL Injection
        $row = $db->table('transactions')
            ->select('COUNT(id) as total_trx, SUM(total_amount) as total_spent')
            ->where('user_id', $userId)
            ->get()
            ->getRowArray();

        return [
            'status'      => 'success',
            'user_id'     => $userId,
            'total_trx'   => (int) ($row['total_trx'] ?? 0),
            'total_spent' => (float) ($row['total_spent'] ?? 0)
        ];
    }
}
