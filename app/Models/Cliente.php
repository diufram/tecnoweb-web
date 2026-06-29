<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use SoftDeletes;

    protected $table = 'cliente';

    protected $primaryKey = 'id_usuario';

    public $incrementing = false;

    protected $fillable = [
        'id_usuario',
        'linea_credito',
        'nit_facturacion',
        'saldo_actual',
    ];

    protected function casts(): array
    {
        return [
            'linea_credito' => 'decimal:2',
            'saldo_actual' => 'decimal:2',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'id_cliente');
    }
}
