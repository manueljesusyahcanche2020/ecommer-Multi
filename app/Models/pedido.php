<?php

namespace App\Models;
use App\Models\Direccion;
use App\Models\Vendedor\Local;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $table = 'pedidos';

    protected $fillable = [
        'codigo',
        'cliente_id',
        'local_id',
        'direccion_id',
        'tipo_entrega',
        'metodo_pago',
        'estado_pago',
        'estado_pedido',
        'subtotal',
        'costo_envio',
        'descuento',
        'total',
        'notas_cliente',
        'notas_vendedor',
        'fecha_aceptado',
        'fecha_entregado',
        'fecha_cancelado',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'costo_envio' => 'decimal:2',
        'descuento' => 'decimal:2',
        'total' => 'decimal:2',
        'fecha_aceptado' => 'datetime',
        'fecha_entregado' => 'datetime',
        'fecha_cancelado' => 'datetime',
    ];

    public function cliente()
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }

    public function local()
    {
        return $this->belongsTo(Local::class, 'local_id');
    }

    public function direccion()
    {
        return $this->belongsTo(Direccion::class, 'direccion_id');
    }

    public function detalles()
    {
        return $this->hasMany(PedidoDetalle::class, 'pedido_id');
    }
}