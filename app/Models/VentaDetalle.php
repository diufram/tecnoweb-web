<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VentaDetalle extends Model
{
    use SoftDeletes;

    protected $table = 'venta_detalle';

    protected $fillable = [
        'cantidad',
        'precio_unitario',
        'subtotal',
        'id_venta',
        'id_inventario',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'integer',
            'precio_unitario' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'id_venta');
    }

    public function inventario(): BelongsTo
    {
        return $this->belongsTo(Inventario::class, 'id_inventario');
    }
}
