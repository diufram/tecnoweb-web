<?php

namespace Database\Seeders;

use App\Models\Lote;
use Illuminate\Database\Seeder;

class LoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lotes = [
            ['codigo_lote' => 'LOTE-001', 'fecha_vencimiento' => now()->addYears(2)],
            ['codigo_lote' => 'LOTE-002', 'fecha_vencimiento' => now()->addYear()],
            ['codigo_lote' => 'LOTE-003', 'fecha_vencimiento' => now()->addMonths(18)],
            ['codigo_lote' => 'LOTE-004', 'fecha_vencimiento' => now()->addMonths(6)],
            ['codigo_lote' => 'LOTE-005', 'fecha_vencimiento' => now()->addYears(3)],
            ['codigo_lote' => 'LOTE-006', 'fecha_vencimiento' => now()->addDays(20)],
            ['codigo_lote' => 'LOTE-007', 'fecha_vencimiento' => now()->addDays(45)],
        ];

        foreach ($lotes as $lote) {
            Lote::updateOrCreate(
                ['codigo_lote' => $lote['codigo_lote']],
                [
                    'fecha_ingreso' => now()->toDateString(),
                    'fecha_vencimiento' => $lote['fecha_vencimiento']->toDateString(),
                ]
            );
        }
    }
}
