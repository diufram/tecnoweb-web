<?php

namespace Database\Seeders;

use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Producto;
use App\Models\Propietario;
use App\Models\Proveedor;
use Illuminate\Database\Seeder;

class CompraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $propietario = Propietario::query()->orderBy('id_usuario')->first();
        $proveedores = Proveedor::query()->orderBy('id_usuario')->get();
        $productos = Producto::query()->orderBy('id')->get();

        if (! $propietario || $proveedores->isEmpty() || $productos->isEmpty()) {
            return;
        }

        $compras = [
            ['estado' => 'SOLICITUD', 'fecha_emision' => now()->subDays(3), 'observaciones' => 'Solicitud inicial de reposicion', 'proveedor' => 0, 'producto' => 0, 'cantidad' => 50, 'precio_unitario' => 4.80],
            ['estado' => 'CONTRA_OFERTA', 'fecha_emision' => now()->subDays(2), 'observaciones' => 'Proveedor envio contra oferta', 'proveedor' => 1, 'producto' => 1, 'cantidad' => 35, 'precio_unitario' => 7.90],
            ['estado' => 'APROBADO', 'fecha_emision' => now()->subDays(10), 'observaciones' => 'Compra aprobada para inventario', 'proveedor' => 2, 'producto' => 2, 'cantidad' => 80, 'precio_unitario' => 11.50],
            ['estado' => 'RECHAZADO', 'fecha_emision' => now()->subDays(7), 'observaciones' => 'Precio fuera del presupuesto', 'proveedor' => 0, 'producto' => 3, 'cantidad' => 25, 'precio_unitario' => 6.20],
        ];

        foreach ($compras as $compraData) {
            $proveedor = $proveedores->get(min($compraData['proveedor'], $proveedores->count() - 1));
            $producto = $productos->get(min($compraData['producto'], $productos->count() - 1));
            $subtotal = $compraData['cantidad'] * $compraData['precio_unitario'];

            $compra = Compra::updateOrCreate(
                [
                    'estado' => $compraData['estado'],
                    'observaciones' => $compraData['observaciones'],
                    'id_propietario' => $propietario->id_usuario,
                    'id_proveedor' => $proveedor->id_usuario,
                ],
                [
                    'fecha_emision' => $compraData['fecha_emision']->toDateString(),
                    'monto_total' => $subtotal,
                ]
            );

            DetalleCompra::updateOrCreate(
                [
                    'id_compra' => $compra->id,
                    'id_producto' => $producto->id,
                ],
                [
                    'cantidad' => $compraData['cantidad'],
                    'precio_unitario' => $compraData['precio_unitario'],
                    'subtotal' => $subtotal,
                ]
            );
        }
    }
}
