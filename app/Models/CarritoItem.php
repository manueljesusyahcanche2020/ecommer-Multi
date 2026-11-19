<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarritoItem extends Model
{
    protected $table = 'carrito_items';
    protected $fillable = [
        'carrito_id', 'producto_id', 'variante_id', 
        'cantidad', 'precio_unitario', 'subtotal', 'notas'
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function variante()
    {
        return $this->belongsTo(ProductoVariante::class, 'variante_id');
    }
    public function carrito()
{
    return $this->belongsTo(Carrito::class, 'carrito_id');
}
}