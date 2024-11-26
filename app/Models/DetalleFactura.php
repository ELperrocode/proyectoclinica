<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;

class DetalleFactura extends Model
{
    use HasFactory;

    protected $fillable = ['factura_id', 'detallable_id', 'detallable_type', 'precio', 'cantidad'];

    public function detallable()
    {
        return $this->morphTo();
    }

    public function factura()
    {
        return $this->belongsTo(Factura::class);
    }

    public function detalleFactura()
    {
        return $this->morphOne(DetalleFactura::class, 'detallable');
    }
    public function getTotalAttribute()
    {
        if ($this->detallable_type === Cita::class) {
            return $this->precio;
        } elseif ($this->detallable_type === Servicio::class) {
            return $this->precio;
        } elseif ($this->detallable_type === Insumo::class) {
            return $this->cantidad * $this->precio;
        }
        return 0;
    }

    protected static function booted()
    {
        static::creating(function ($detalle) {
            if ($detalle->detallable_type === Insumo::class) {
                $insumo = $detalle->detallable;
                if ($detalle->cantidad > $insumo->cantidad) {
                    Notification::make()
                        ->title('Stock insuficiente')
                        ->danger()
                        ->body('No hay suficiente stock para facturar este insumo.')
                        ->send();

                   return false;
                }
            }
        });

        static::created(function ($detalle) {
            if ($detalle->detallable_type === Insumo::class) {
                MovimientoInventario::create([
                    'insumo_id' => $detalle->detallable_id,
                    'tipo' => 'salida',
                    'cantidad' => $detalle->cantidad,
                    'motivo' => 'Facturación',
                ]);
            }
        });

        static::created(function ($detalle) {
            $detalle->factura->touch(); 
        });

        static::updated(function ($detalle) {
            $detalle->factura->touch();
        });

        static::deleted(function ($detalle) {
            $detalle->factura->touch();
        });
    }

}
