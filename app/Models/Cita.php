<?php
// app/Models/Cita.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    use HasFactory;

    protected $fillable = [
        'paciente_id', 'doctor_id', 'fecha', 'hora_inicio', 'hora_fin', 'motivo_id', 'status'
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function motivo()
    {
        return $this->belongsTo(Servicio::class);
    }
    public function recetas()
    {
        return $this->hasMany(Receta::class);
    }

    public function diagnosticos()
    {
        return $this->hasMany(Diagnostico::class);
    }
    public function especialidad()
    {
        return $this->hasOneThrough(Especialidad::class, Servicio::class, 'id', 'id', 'motivo_id', 'especialidad_id');
    }
}
