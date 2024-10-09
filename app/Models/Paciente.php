<?php
// app/Models/Paciente.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre', 'apellido', 'fecha_nacimiento', 'direccion', 'telefono', 'email'
    ];
}