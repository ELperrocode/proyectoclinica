<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\DetalleFactura;

class Factura extends Model
{
    use HasFactory;

    protected $fillable = ['cliente', 'total', 'estado'];

    public function detalles()
    {
        return $this->hasMany(DetalleFactura::class);
    }


    public function getTotalAttribute()
    {
        return $this->detalles->sum(function ($detalle) {
            return ($detalle->precio ?? 0) * ($detalle->cantidad ?? 1);
        });
    }

 
    protected static function booted()
    {
        static::creating(function ($factura) {
            $factura->total = $factura->detalles->sum(function ($detalle) {
                return ($detalle->precio ?? 0) * ($detalle->cantidad ?? 1);
            });
        });

        static::updating(function ($factura) {
            $factura->total = $factura->detalles->sum(function ($detalle) {
                return ($detalle->precio ?? 0) * ($detalle->cantidad ?? 1);
            });
        });
    }
}
