<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoInventario extends Model
{
    use HasFactory;

    protected $fillable = [
        'insumo_id',
        'tipo',
        'cantidad',
        'motivo'
    ];

    protected $table = 'movimientos_inventario';

    public function insumo()
    {
        return $this->belongsTo(Insumo::class);
    }

    protected static function booted()
    {
        static::created(function ($movimiento) {
            $insumo = $movimiento->insumo;
            if ($movimiento->tipo === 'entrada') {
                $insumo->cantidad += $movimiento->cantidad;
            } elseif ($movimiento->tipo === 'salida') {
                $insumo->cantidad -= $movimiento->cantidad;
            }
            $insumo->save();
        });
    }
}
