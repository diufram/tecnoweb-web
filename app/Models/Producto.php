<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use SoftDeletes;

    protected $table = 'producto';

    protected $fillable = [
        'nombre_comercial',
        'stock_actual',
    ];

    protected function casts(): array
    {
        return [
            'stock_actual' => 'integer',
        ];
    }

    public function inventarios(): HasMany
    {
        return $this->hasMany(Inventario::class, 'id_producto');
    }

    public function detalleCompras(): HasMany
    {
        return $this->hasMany(DetalleCompra::class, 'id_producto');
    }
}
