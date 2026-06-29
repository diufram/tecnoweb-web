<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'usuario';

    protected $fillable = [
        'ci_nit',
        'nombre',
        'name',
        'email',
        'email_verified_at',
        'password',
        'direccion',
        'telefono',
        'remember_token',
    ];

    protected $appends = [
        'name',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getNameAttribute(): string
    {
        return $this->nombre;
    }

    public function setNameAttribute(string $value): void
    {
        $this->attributes['nombre'] = $value;
    }

    public function cliente(): HasOne
    {
        return $this->hasOne(Cliente::class, 'id_usuario');
    }

    public function proveedor(): HasOne
    {
        return $this->hasOne(Proveedor::class, 'id_usuario');
    }

    public function propietario(): HasOne
    {
        return $this->hasOne(Propietario::class, 'id_usuario');
    }

    public function auditorias(): HasMany
    {
        return $this->hasMany(Auditoria::class, 'id_usuario');
    }
}
