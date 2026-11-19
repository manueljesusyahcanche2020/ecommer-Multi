<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Direccion extends Model
{
    protected $table = 'direcciones';

    protected $fillable = [
        'user_id',
        'nombre_recibe',
        'telefono',
        'calle',
        'numero',
        'cruzamientos',
        'colonia',
        'ciudad',
        'estado',
        'codigo_postal',
        'referencia',
        'latitud',
        'longitud',
        'principal',
    ];

    protected $casts = [
        'latitud' => 'decimal:7',
        'longitud' => 'decimal:7',
        'principal' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'direccion_id');
    }
}