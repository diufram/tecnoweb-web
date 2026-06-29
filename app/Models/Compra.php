<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Compra extends Model
{
    use SoftDeletes;

    protected $table = 'compra';

    protected $fillable = [
        'estado',
        'fecha_emision',
        'monto_total',
        'observaciones',
        'id_propietario',
        'id_proveedor',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
            'monto_total' => 'decimal:2',
        ];
    }

    public function propietario(): BelongsTo
    {
        return $this->belongsTo(Propietario::class, 'id_propietario');
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleCompra::class, 'id_compra');
    }
}
