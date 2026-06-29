<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
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
        'contrasena',
        'direccion',
        'telefono',
        'remember_token',
    ];

    protected $appends = [
        'name',
        'actor',
    ];

    protected $hidden = [
        'contrasena',
    ];

    protected $authPasswordName = 'contrasena';

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'contrasena' => 'hashed',
        ];
    }

    public function getAuthPasswordName(): string
    {
        return 'contrasena';
    }

    public function getAuthPassword(): string
    {
        return $this->contrasena;
    }

    public function getNameAttribute(): string
    {
        return $this->nombre;
    }

    public function setNameAttribute(string $value): void
    {
        $this->attributes['nombre'] = $value;
    }

    public function getActorAttribute(): ?string
    {
        if ($this->relationLoaded('propietario') ? $this->propietario : $this->propietario()->exists()) {
            return 'propietario';
        }

        if ($this->relationLoaded('proveedor') ? $this->proveedor : $this->proveedor()->exists()) {
            return 'proveedor';
        }

        if ($this->relationLoaded('cliente') ? $this->cliente : $this->cliente()->exists()) {
            return 'cliente';
        }

        return null;
    }

    public function dashboardRouteName(): string
    {
        return match ($this->actor) {
            'propietario' => 'dashboard.propietario',
            'proveedor' => 'dashboard.proveedor',
            'cliente' => 'dashboard.cliente',
            default => 'dashboard.default',
        };
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
