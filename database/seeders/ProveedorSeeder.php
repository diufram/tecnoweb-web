<?php

namespace Database\Seeders;

use App\Models\Proveedor;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class ProveedorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $proveedores = [
            ['nombre' => 'Distribuidora FarmaBol', 'email' => 'roberthuarachi27@gmail.com', 'ci_nit' => '200000001', 'direccion' => 'Av. Industrial 100', 'telefono' => '+591 71000001', 'empresa' => 'FarmaBol SRL'],
            ['nombre' => 'Laboratorios Vida', 'email' => 'proveedor.vida@sanamed.com', 'ci_nit' => '200000002', 'direccion' => 'Calle Salud 220', 'telefono' => '+591 71000002', 'empresa' => 'Laboratorios Vida SA'],
            ['nombre' => 'Importadora MedPlus', 'email' => 'proveedor.medplus@sanamed.com', 'ci_nit' => '200000003', 'direccion' => 'Av. Comercio 450', 'telefono' => '+591 71000003', 'empresa' => 'MedPlus Importaciones'],
        ];

        foreach ($proveedores as $proveedor) {
            $usuario = Usuario::updateOrCreate(
                ['ci_nit' => $proveedor['ci_nit']],
                [
                    'nombre' => $proveedor['nombre'],
                    'email' => $proveedor['email'],
                    'contrasena' => '123123123',
                    'direccion' => $proveedor['direccion'],
                    'telefono' => $proveedor['telefono'],
                    'email_verified_at' => now(),
                ]
            );

            Proveedor::updateOrCreate(
                ['id_usuario' => $usuario->id],
                ['empresa' => $proveedor['empresa']]
            );
        }
    }
}
