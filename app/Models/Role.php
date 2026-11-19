<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }

    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'role_menu_permissions', 'role_id', 'menu_id')
            ->withPivot('permitido')
            ->withTimestamps();
    }
}