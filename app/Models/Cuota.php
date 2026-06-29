<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cuota extends Model
{
    use SoftDeletes;

    protected $table = 'cuota';

    protected $fillable = [
        'estado_cuota',
        'fecha_vencimiento',
        'id_transaccion_pago_facil',
        'monto',
        'nro_cuota',
        'id_plan_pago',
    ];

    protected function casts(): array
    {
        return [
            'fecha_vencimiento' => 'date',
            'monto' => 'decimal:2',
            'nro_cuota' => 'integer',
        ];
    }

    public function planPago(): BelongsTo
    {
        return $this->belongsTo(PlanPago::class, 'id_plan_pago');
    }
}
