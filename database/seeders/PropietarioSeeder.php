<?php

namespace Database\Seeders;

use App\Models\Propietario;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class PropietarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usuario = Usuario::updateOrCreate(
            ['ci_nit' => '100000001'],
            [
                'nombre' => 'Matias Franco Ramos Limachi',
                'email' => 'diufram007@gmail.com',
                'contrasena' => '123123123',
                'direccion' => 'Av. SanaMed Central 123',
                'telefono' => '+591 70000001',
                'email_verified_at' => now(),
            ]
        );

        Propietario::updateOrCreate(
            ['id_usuario' => $usuario->id],
            ['login' => 'matias.admin']
        );
    }
}
