<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    /** @use HasFactory<\Database\Factories\PersonaFactory> */
    use HasFactory;
    protected $fillable = [
        'nombres',
        'apellidos',
        'ci',
        'direccion',
        'telefono',
        'email',
    ];
}
