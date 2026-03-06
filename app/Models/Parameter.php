<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Parameter extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'name',
        'meta',
    ];

    // Helpers opcionales
    public const TYPE_SALON = 'SALON';
    public const TYPE_AREA  = 'AREA';
}
