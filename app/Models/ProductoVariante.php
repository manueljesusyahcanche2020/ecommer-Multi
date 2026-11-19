<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoVariante extends Model
{
    protected $table = 'producto_variantes';

    protected $fillable = [
        'producto_id',
        'nombre',
        'valor',
        'precio_venta',
        'precio_costo',
        'precio_oferta',
        'sku',
        'imagen',
        'stock',
        'activo',
    ];

    protected $casts = [
        'precio_extra' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function stock()
    {
        return $this->hasOne(ProductoStock::class, 'variante_id');
    }

}