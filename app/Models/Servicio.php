<?php
// app/Models/Servicio.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'especialidad_id',
        'precio',
    ];

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class);
    }


    public function doctores()
    {
        return $this->hasMany(Doctor::class, 'especialidad_id', 'especialidad_id');
    }

    public function detalleFactura()
    {
        return $this->morphOne(DetalleFactura::class, 'detallable');
    }
}
