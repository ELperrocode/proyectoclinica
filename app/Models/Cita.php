<?php
// app/Models/Cita.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class Cita extends Model
{
    use HasFactory;

    protected $fillable = [
        'paciente_id',
        'doctor_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'motivo_id',
        'status'
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
    public function detalleFactura()
    {
        return $this->morphOne(DetalleFactura::class, 'detallable');
    }
    /**
     * Valida si el doctor está disponible el día seleccionado.
     */

    public function validarDiaDisponible($doctorId, $fecha)
    {
        // Buscar la disponibilidad del doctor usando el modelo Disponibilidad
        $disponibilidad = Disponibilidad::where('doctor_id', $doctorId)->first();

        // Verificar que el doctor tenga disponibilidad configurada
        if (!$disponibilidad) {
            throw ValidationException::withMessages([
                'fecha' => 'El doctor no tiene configurada su disponibilidad.',
            ]);
        }

        // Verificar que el campo `dia_semana` no sea nulo o vacío
        $diasDisponibles = $disponibilidad->dia_semana ?? [];
        if (empty($diasDisponibles)) {
            throw ValidationException::withMessages([
                'fecha' => 'El doctor no tiene configurados los días disponibles.',
            ]);
        }

        // Validar si el día de la cita está dentro de los días disponibles
        $diaSemana = Carbon::parse($fecha)->locale('es')->dayName; // Ejemplo: "lunes"
        if (!in_array($diaSemana, $diasDisponibles)) {
            throw ValidationException::withMessages([
                'fecha' => "El doctor no trabaja los días $diaSemana.",
            ]);
        }
    }



    /**
     * Valida si hay conflicto de horario con otras citas.
     */
    public function validarHorarioDisponible($doctorId, $fecha, $horaInicio, $horaFin)
    {
        // Buscar la disponibilidad del doctor usando el modelo Disponibilidad
        $disponibilidad = Disponibilidad::where('doctor_id', $doctorId)->first();

        // Verificar que el doctor tenga disponibilidad configurada
        if (!$disponibilidad) {
            throw ValidationException::withMessages([
                'fecha' => 'El doctor no tiene configurada su disponibilidad.',
            ]);
        }

        // Verificar que la cita esté dentro del horario de atención del doctor
        if ($horaInicio < $disponibilidad->hora_inicio || $horaFin > $disponibilidad->hora_fin) {
            throw ValidationException::withMessages([
                'fecha' => 'El horario de la cita está fuera del horario de atención del doctor.',
            ]);
        }

        // Validar conflictos con otras citas
        $conflicto = self::where('doctor_id', $doctorId)
            ->where('fecha', $fecha)
            ->where(function ($query) use ($horaInicio, $horaFin) {
                $query->whereBetween('hora_inicio', [$horaInicio, $horaFin])
                    ->orWhereBetween('hora_fin', [$horaInicio, $horaFin])
                    ->orWhere(function ($subQuery) use ($horaInicio, $horaFin) {
                        $subQuery->where('hora_inicio', '<=', $horaInicio)
                            ->where('hora_fin', '>=', $horaFin);
                    })
                    ->orWhere(function ($subQuery) use ($horaInicio, $horaFin) {
                        $subQuery->where('hora_fin', '=', $horaInicio)
                            ->orWhere('hora_inicio', '=', $horaFin);
                    })
                    ->orWhere(function ($subQuery) use ($horaInicio, $horaFin) {
                        $subQuery->where('hora_inicio', '=', $horaFin)
                            ->orWhere('hora_fin', '=', $horaInicio);
                    });
            })
            ->exists();

        if ($conflicto) {
            throw ValidationException::withMessages([
                'fecha' => 'El horario seleccionado está ocupado para este doctor.',
            ]);
        }
    }

    /**
     * Método de validación principal.
     */
    public function validarDisponibilidad($data)
    {
        $this->validarDiaDisponible($data['doctor_id'], $data['fecha']);
        $this->validarHorarioDisponible($data['doctor_id'], $data['fecha'], $data['hora_inicio'], $data['hora_fin']);
    }
}
