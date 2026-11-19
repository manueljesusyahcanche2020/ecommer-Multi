<?php

namespace App\Models\Vendedor;

use App\Models\User;
use App\Models\Producto\Categoria;
use App\Models\Producto;
use App\Models\Pedido\Pedido;
use App\Models\Vendedor\LocalZonaEntrega;

use Illuminate\Database\Eloquent\Model;

class Local extends Model
{
    protected $table = 'locales';

    protected $fillable = [
        'vendedor_id',
        'nombre',
        'descripcion',
        'logo',
        'portada',
        'telefono',
        'whatsapp',
        'correo',
        'direccion',
        'ciudad',
        'estado',
        'latitud',
        'longitud',
        'minimo_pedido',
        'costo_envio',
        'permite_delivery',
        'permite_recoger',
        'estado_local',
        'activo',
    ];

    protected $casts = [
        'latitud' => 'decimal:7',
        'longitud' => 'decimal:7',
        'minimo_pedido' => 'decimal:2',
        'costo_envio' => 'decimal:2',
        'permite_delivery' => 'boolean',
        'permite_recoger' => 'boolean',
        'activo' => 'boolean',
    ];

    public function vendedor()
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }

    public function categorias()
    {
        return $this->hasMany(Categoria::class, 'local_id');
    }

    public function productos()
    {
        return $this->hasMany(Producto::class, 'local_id');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'local_id');
    }

    public function imagenes()
    {
        return $this->hasMany(LocalImagen::class, 'local_id');
    }

    public function horarios()
    {
        return $this->hasMany(LocalHorario::class, 'local_id');
    }
    public function zonasEntrega()
{
    return $this->hasMany(LocalZonaEntrega::class, 'local_id');
}
}