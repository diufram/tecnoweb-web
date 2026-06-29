<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class ClienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clientes = [
            ['nombre' => 'Juan Perez', 'email' => 'juan.perez@email.com', 'ci_nit' => '123456789', 'direccion' => 'Av. Principal 123', 'telefono' => '+591 70123456', 'linea_credito' => 10000, 'nit_facturacion' => '123456789'],
            ['nombre' => 'Maria Lopez', 'email' => 'maria.lopez@email.com', 'ci_nit' => '987654321', 'direccion' => 'Calle Secundary 456', 'telefono' => '+591 71234567', 'linea_credito' => 15000, 'nit_facturacion' => '987654321'],
            ['nombre' => 'Carlos Rodriguez', 'email' => 'carlos.rodriguez@email.com', 'ci_nit' => '456789123', 'direccion' => 'Av. Bolivia 789', 'telefono' => '+591 72345678', 'linea_credito' => 20000, 'nit_facturacion' => '456789123'],
            ['nombre' => 'Ana Martinez', 'email' => 'ana.martinez@email.com', 'ci_nit' => '789123456', 'direccion' => 'Calle Hoy 012', 'telefono' => '+591 73456789', 'linea_credito' => 8000, 'nit_facturacion' => '789123456'],
            ['nombre' => 'Luis Hernandez', 'email' => 'luis.hernandez@email.com', 'ci_nit' => '321654987', 'direccion' => 'Av. America 345', 'telefono' => '+591 74567890', 'linea_credito' => 12000, 'nit_facturacion' => '321654987'],
        ];

        foreach ($clientes as $cliente) {
            $usuario = Usuario::updateOrCreate(
                ['ci_nit' => $cliente['ci_nit']],
                [
                    'nombre' => $cliente['nombre'],
                    'email' => $cliente['email'],
                    'contrasena' => '123123123',
                    'direccion' => $cliente['direccion'],
                    'telefono' => $cliente['telefono'],
                    'email_verified_at' => now(),
                ]
            );

            Cliente::updateOrCreate(
                ['id_usuario' => $usuario->id],
                [
                    'linea_credito' => $cliente['linea_credito'],
                    'nit_facturacion' => $cliente['nit_facturacion'],
                    'saldo_actual' => 0,
                ]
            );
        }
    }
}
