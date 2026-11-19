<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Role;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'role_id',
        'imagen',
        'name',
        'email',
        'telefono',
        'password',
        'estado',
        'email_verified_at',
        'remember_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // =========================
    // RELACIONES
    // =========================

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    // Si el usuario es vendedor, puede tener varios locales
    public function locales()
    {
        return $this->hasMany(Local::class, 'vendedor_id');
    }

    // Si el usuario es cliente, puede tener varias direcciones
    public function direcciones()
    {
        return $this->hasMany(Direccion::class, 'user_id');
    }

    // Si el usuario es cliente, puede tener carritos
    public function carritos()
    {
        return $this->hasMany(Carrito::class, 'cliente_id');
    }

    // Si el usuario es cliente, puede tener pedidos
    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'cliente_id');
    }

    public function favoritos()
    {
        return $this->hasMany(Favorito::class, 'cliente_id');
    }

    public function resenas()
    {
        return $this->hasMany(Resena::class, 'cliente_id');
    }

    // =========================
    // AYUDAS DE ROL
    // =========================

    public function esAdmin(): bool
    {
        return $this->role_id == 1;
    }

    public function esVendedor(): bool
    {
        return $this->role_id == 2;
    }

    public function esCliente(): bool
    {
        return $this->role_id == 3;
    }

    public function estaActivo(): bool
    {
        return $this->estado === 'activo';
    }
}