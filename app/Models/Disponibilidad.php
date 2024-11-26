<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disponibilidad extends Model
{
    use HasFactory;

    protected $fillable = ['doctor_id', 'dia_semana', 'hora_inicio', 'hora_fin'];

    protected $casts = [
        'dia_semana' => 'array',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'id');
    }

    protected $table = 'disponibilidades';

}
