<?php

namespace App\Models\Vendedor;

use Illuminate\Database\Eloquent\Model;

class LocalZonaEntrega extends Model
{
    protected $table = 'local_zonas_entrega';

    protected $fillable = [
        'local_id',
        'ciudad',
        'estado',
        'costo_envio',
        'activo',
    ];

    protected $casts = [
        'costo_envio' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function local()
    {
        return $this->belongsTo(Local::class, 'local_id');
    }
}