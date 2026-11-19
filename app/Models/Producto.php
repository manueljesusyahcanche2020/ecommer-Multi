<?php

namespace App\Models;

use App\Models\Vendedor\Local;
use Illuminate\Database\Eloquent\Model;


class Producto extends Model
{
    protected $table = 'productos';

    protected $fillable = [
        'local_id',
        'categoria_id',
        'nombre',
        'descripcion',
        'precio',
        'activo',
        'destacado',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'precio_oferta' => 'decimal:2',
        'activo' => 'boolean',
        'destacado' => 'boolean',
    ];

    public function local()
    {
        return $this->belongsTo(Local::class, 'local_id');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function stock()
    {
        return $this->hasOne(ProductoStock::class, 'producto_id');
    }

    public function imagenes()
    {
        return $this->hasMany(ProductoImagen::class, 'producto_id');
    }

    public function imagenPrincipal()
    {
        return $this->hasOne(ProductoImagen::class, 'producto_id')
            ->where('principal', 1);
    }
    public function variantes()
    {
        return $this->hasMany(ProductoVariante::class, 'producto_id');
    }
    public function direccion()
    {
        return $this->belongsTo(Direccion::class, 'direccion_id');
    }
    public function productos()
{
    return $this->hasMany(Producto::class, 'local_id');
}
}