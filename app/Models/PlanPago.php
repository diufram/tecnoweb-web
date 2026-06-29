<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanPago extends Model
{
    use SoftDeletes;

    protected $table = 'plan_pago';

    protected $fillable = [
        'estado_plan',
        'tipo_pago',
        'id_venta',
    ];

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'id_venta');
    }

    public function cuotas(): HasMany
    {
        return $this->hasMany(Cuota::class, 'id_plan_pago');
    }
}
