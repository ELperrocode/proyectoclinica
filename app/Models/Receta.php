<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receta extends Model
{
    use HasFactory;

    protected $fillable = ['cita_id', 'descripcion'];

    public function cita()
    {
        return $this->belongsTo(Cita::class);
    }
}
