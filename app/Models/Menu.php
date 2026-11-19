<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menus';

    protected $fillable = [
        'nombre',
        'icono',
        'ruta',
        'orden',
        'estado',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_menu_permissions', 'menu_id', 'role_id')
            ->withPivot('permitido')
            ->withTimestamps();
    }
}