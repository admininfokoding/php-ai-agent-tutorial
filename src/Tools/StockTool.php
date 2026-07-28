<?php

namespace App\Tools;

use PDO;

class StockTool implements ToolInterface
{
    public function __construct(private readonly ?PDO $pdo = null) {}

    public function getDefinition(): array
    {
        return [
            "type" => "function",
            "function" => [
                "name" => "check_stock",
                "description" => "Mengecek stok dan harga barang di database MySQL berdasarkan nama produk.",
                "parameters" => [
                    "type" => "object",
                    "properties" => [
                        "product_name" => [
                            "type" => "string",
                            "description" => "Nama produk yang akan dicari"
                        ]
                    ],
                    "required" => ["product_name"]
                ]
            ]
        ];
    }

    public function execute(array $args): mixed
    {
        $productName = $args['product_name'] ?? '';
        if (empty($productName)) {
            return ["error" => "Parameter product_name wajib diisi."];
        }

        if (!$this->pdo) {
            // Mock data jika database belum terkoneksi
            return [
                "product_name" => $productName,
                "stock" => 42,
                "price" => 150000,
                "status" => "Tersedia (Mock Data)"
            ];
        }

        $stmt = $this->pdo->prepare("SELECT name, stock, price FROM products WHERE name LIKE ? LIMIT 1");
        $stmt->execute(["%" . $productName . "%"]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: ["error" => "Produk '$productName' tidak ditemukan di database."];
    }
}
