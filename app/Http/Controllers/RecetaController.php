<?php

namespace App\Http\Controllers;

use App\Models\Receta;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Illuminate\Http\Request;
use PDF;

class RecetaController extends Controller
{
    public function generarPDF($id)
    {
        $receta = Receta::findOrFail($id);
        $paciente = $receta->paciente;
        $fecha = now()->format('d/m/Y');

        $pdf = FacadePdf::loadView('receta', compact('receta', 'paciente', 'fecha'));

        return $pdf->download('receta.pdf');
    }
}
