<?php

namespace Database\Seeders;

use App\Models\Inventario;
use App\Models\Lote;
use App\Models\Producto;
use Illuminate\Database\Seeder;

class InventarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productos = Producto::query()->orderBy('id')->get();
        $lotes = Lote::query()->orderBy('id')->get();

        if ($productos->isEmpty() || $lotes->isEmpty()) {
            return;
        }

        foreach ($productos->take(7)->values() as $index => $producto) {
            $lote = $lotes->get($index);

            if (! $lote) {
                continue;
            }

            Inventario::updateOrCreate(
                [
                    'id_producto' => $producto->id,
                    'id_lote' => $lote->id,
                ],
                [
                    'cantidad_disponible' => 100,
                    'costo_unitario_lote' => 0.10,
                ]
            );
        }
    }
}
