<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carrito extends Model
{
    protected $table = 'carritos';
    protected $fillable = ['cliente_id', 'local_id', 'estado', 'total'];

    public function items()
    {
        return $this->hasMany(CarritoItem::class, 'carrito_id');
    }

    public function local()
    {
        return $this->belongsTo(\App\Models\Vendedor\Local::class, 'local_id');
    }
}