<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Venta extends Model
{
    use SoftDeletes;

    protected $table = 'venta';

    protected $fillable = [
        'estado_venta',
        'fecha',
        'total',
        'id_cliente',
        'id_propietario',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'total' => 'decimal:2',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    public function propietario(): BelongsTo
    {
        return $this->belongsTo(Propietario::class, 'id_propietario');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(VentaDetalle::class, 'id_venta');
    }

    public function planPago(): HasOne
    {
        return $this->hasOne(PlanPago::class, 'id_venta');
    }
}
