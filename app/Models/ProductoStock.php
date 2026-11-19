<?php

namespace App\Models;

use App\Models\Vendedor\Local;
use Illuminate\Database\Eloquent\Model;

class ProductoStock extends Model
{
    protected $table = 'producto_stocks';

    protected $fillable = [
        'producto_id',
        'variante_id',
        'local_id',
        'stock',
        'stock_minimo',
        'maneja_stock',
    ];

    protected $casts = [
        'maneja_stock' => 'boolean',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function local()
    {
        return $this->belongsTo(Local::class, 'local_id');
    }
        public function variante()
    {
        return $this->belongsTo(ProductoVariante::class, 'variante_id');
    }
}