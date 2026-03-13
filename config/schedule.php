<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'salon_id',
        'area_id',
        'nombre',
        'email',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'observacion',
        'status',
    ];

    public function salon()
    {
        return $this->belongsTo(Parameter::class, 'salon_id');
    }

    public function area()
    {
        return $this->belongsTo(Parameter::class, 'area_id');
    }
}