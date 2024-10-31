<?php

use App\Filament\Resources\PacienteResource\RelationManagers\CitasRelationManager;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RecetaController;

Route::get('/receta/{id}/pdf', [RecetaController::class, 'generarPDF'])->name('receta.pdf');
Route::view('/', 'welcome');
Route::get('/citas/{id}/download-receta-pdf', [CitasRelationManager::class, 'downloadRecetaPDF'])->name('citas.downloadRecetaPDF');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');


require __DIR__ . '/auth.php';
