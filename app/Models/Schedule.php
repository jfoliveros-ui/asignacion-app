<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'salon_id',
        'area_id',
        'nombre',
        'email',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'status',
        'observacion',
    ];

    protected $casts = [
        'fecha' => 'date',
        // hora_inicio / hora_fin las deja como string tipo "HH:MM:SS" (ok)
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
