<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lote extends Model
{
    use SoftDeletes;

    protected $table = 'lote';

    protected $fillable = [
        'codigo_lote',
        'fecha_ingreso',
        'fecha_vencimiento',
    ];

    protected function casts(): array
    {
        return [
            'fecha_ingreso' => 'date',
            'fecha_vencimiento' => 'date',
        ];
    }

    public function inventarios(): HasMany
    {
        return $this->hasMany(Inventario::class, 'id_lote');
    }
}
