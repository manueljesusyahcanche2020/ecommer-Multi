<?php

namespace App\Models\Vendedor;

use Illuminate\Database\Eloquent\Model;

class LocalHorario extends Model
{
    protected $table = 'local_horarios';

    protected $fillable = [
        'local_id',
        'dia',
        'hora_apertura',
        'hora_cierre',
        'cerrado',
    ];

    protected $casts = [
        'cerrado' => 'boolean',
    ];

    public function local()
    {
        return $this->belongsTo(Local::class, 'local_id');
    }
}