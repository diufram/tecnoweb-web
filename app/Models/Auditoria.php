<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Auditoria extends Model
{
    use SoftDeletes;

    protected $table = 'auditoria';

    protected $fillable = [
        'accion',
        'datos_anteriores',
        'datos_nuevos',
        'fecha_hora',
        'id_usuario',
    ];

    protected function casts(): array
    {
        return [
            'fecha_hora' => 'datetime',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }
}
