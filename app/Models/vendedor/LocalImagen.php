<?php

namespace App\Models\Vendedor;

use Illuminate\Database\Eloquent\Model;

class LocalImagen extends Model
{
    protected $table = 'local_imagenes';

    protected $fillable = [
        'local_id',
        'imagen',
        'orden',
    ];

    public function local()
    {
        return $this->belongsTo(Local::class, 'local_id');
    }
}