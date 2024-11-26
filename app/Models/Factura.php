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
        return $this->detalles->sum('total');
    }
    protected static function booted()
    {
        static::creating(function ($factura) {
            $factura->total = $factura->detalles->sum('total');
        });

        static::updating(function ($factura) {
            $factura->total = $factura->detalles->sum('total');
        });
    }
}
