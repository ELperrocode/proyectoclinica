<?php
// app/Models/Paciente.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre', 'apellido', 'fecha_nacimiento', 'sexo', 'nacionalidad', 'cip',
        'direccion', 'telefono', 'email', 'grupo_sanguineo', 'alergias', 'condiciones_medicas',
        'medicamentos', 'historial_medico_familiar', 'nombre_aseguradora', 'numero_poliza',
        'fecha_vencimiento_poliza', 'contacto_emergencia_nombre', 'contacto_emergencia_relacion',
        'contacto_emergencia_telefono', 'ocupacion', 'estado_civil'
    ];

    public function citas()
    {
        return $this->hasMany(Cita::class);
    }
}
