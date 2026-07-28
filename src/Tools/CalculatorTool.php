<?php

namespace App\Tools;

class CalculatorTool implements ToolInterface
{
    public function getDefinition(): array
    {
        return [
            "type" => "function",
            "function" => [
                "name" => "calculator",
                "description" => "Melakukan operasi matematika dasar (penjumlahan, pengurangan, perkalian, pembagian).",
                "parameters" => [
                    "type" => "object",
                    "properties" => [
                        "expression" => [
                            "type" => "string",
                            "description" => "Ekspresi matematika yang aman, misal: '150000 * 3'"
                        ]
                    ],
                    "required" => ["expression"]
                ]
            ]
        ];
    }

    public function execute(array $args): mixed
    {
        $expr = $args['expression'] ?? '';
        // Sanitasi hanya mengizinkan angka dan operator matematika dasar
        if (preg_match('/^[0-9\+\-\*\/\(\)\.\s]+$/', $expr)) {
            try {
                // Evaluation aman tanpa eval() kaku
                $result = eval("return ($expr);");
                return ["expression" => $expr, "result" => $result];
            } catch (\Throwable $e) {
                return ["error" => "Format ekspresi matematika tidak valid."];
            }
        }

        return ["error" => "Ekspresi mengandung karakter terlarang."];
    }
}
