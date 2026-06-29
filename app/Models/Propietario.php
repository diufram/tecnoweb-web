<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Propietario extends Model
{
    use SoftDeletes;

    protected $table = 'propietario';

    protected $primaryKey = 'id_usuario';

    public $incrementing = false;

    protected $fillable = [
        'id_usuario',
        'login',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'id_propietario');
    }

    public function compras(): HasMany
    {
        return $this->hasMany(Compra::class, 'id_propietario');
    }
}
