<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Adiciona produtos iniciais à base de dados.
     */
    public function run(): void
    {
        Product::create([
            'name' => 'Teclado Mecânico',
            'price' => 79.99,
            'stock' => 15,
        ]);

        Product::create([
            'name' => 'Rato Gaming',
            'price' => 49.99,
            'stock' => 20,
        ]);

        Product::create([
            'name' => 'Monitor 24"',
            'price' => 149.99,
            'stock' => 10,
        ]);

        Product::create([
            'name' => 'Auscultadores',
            'price' => 59.99,
            'stock' => 12,
        ]);

        Product::create([
            'name' => 'Webcam Full HD',
            'price' => 39.99,
            'stock' => 8,
        ]);
    }
}