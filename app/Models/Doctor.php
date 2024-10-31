<?php
// app/Models/Doctor.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre', 'apellido', 'especialidad_id', 'telefono', 'email', 'cip', 'numero_junta_tecnica', 'direccion', 'sexo'
    ];
    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class);
    }
    
    protected $table = 'doctores';
}
