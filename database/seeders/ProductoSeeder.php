<?php

namespace Database\Seeders;

use App\Models\Producto;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productos = [
            'Paracetamol 500mg',
            'Ibuprofeno 400mg',
            'Amoxicilina 250mg',
            'Vitamina C 1000mg',
            'Aspirina 500mg',
            'Suero Oral 500ml',
            'Omeprazol 20mg',
        ];

        foreach ($productos as $producto) {
            Producto::updateOrCreate(
                ['nombre_comercial' => $producto],
                ['stock_actual' => 100]
            );
        }
    }
}
