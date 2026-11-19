<?php

namespace App\Models;

use App\Models\Vendedor\Local;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $table = 'categorias';

    protected $fillable = [
        'local_id',
        'nombre',
        'descripcion',
        'imagen',
        'orden',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function local()
    {
        return $this->belongsTo(Local::class, 'local_id');
    }

    public function productos()
    {
        return $this->hasMany(Producto::class, 'categoria_id');
    }
}