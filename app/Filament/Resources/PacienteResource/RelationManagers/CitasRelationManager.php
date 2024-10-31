<?php

namespace App\Filament\Resources\PacienteResource\RelationManagers;

use App\Models\Cita;
use App\Models\Doctor;
use App\Models\Paciente;
use App\Models\Servicio;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Barryvdh\DomPDF\Facade\Pdf;

class CitasRelationManager extends RelationManager
{
    protected static string $relationship = 'citas';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información del Paciente')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('paciente_id')
                                    ->label('Paciente')
                                    ->options(Paciente::all()->mapWithKeys(function ($paciente) {
                                        return [$paciente->id => $paciente->nombre . ' ' . $paciente->apellido . ' (CIP: ' . $paciente->cip . ')'];
                                    })),
                            ]),
                    ]),

                Section::make('Detalles de la Cita')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('motivo_id')
                                    ->label('Motivo')
                                    ->options(Servicio::all()->pluck('nombre', 'id'))
                                    ->reactive(),

                                Forms\Components\Select::make('doctor_id')
                                    ->label('Doctor')
                                    ->options(function (callable $get) {
                                        $servicioId = $get('motivo_id');
                                        $servicio = Servicio::find($servicioId);
                                        return $servicio ? $servicio->doctores->mapWithKeys(function ($doctor) {
                                            return [$doctor->id => $doctor->nombre . ' ' . $doctor->apellido . ' (CIP: ' . $doctor->cip . ')'];
                                        }) : [];
                                    })
                                    ->required(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('fecha')
                                    ->label('Fecha'),

                                Forms\Components\TimePicker::make('hora_inicio')
                                    ->label('Hora de Inicio')
                                    ->seconds(false)
                                    ->required(),

                                Forms\Components\TimePicker::make('hora_fin')
                                    ->label('Hora de Fin')
                                    ->seconds(false)
                                    ->required(),
                            ]),
                    ]),

                Section::make('Diagnósticos y Recetas')
                    ->schema([
                        Forms\Components\Repeater::make('diagnosticos')
                            ->relationship('diagnosticos')
                            ->schema([
                                Forms\Components\Textarea::make('descripcion')
                                    ->label('Descripción del Diagnóstico')
                                    ->required(),
                            ])
                            ->columns(1),

                        Forms\Components\Repeater::make('recetas')
                            ->relationship('recetas')
                            ->schema([
                                Forms\Components\Textarea::make('descripcion')
                                    ->label('Descripción de la Receta')
                                    ->required(),
                            ])
                            ->columns(1),
                    ]),

                Section::make('Estado de la Cita')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'pendiente' => 'Pendiente',
                                'confirmada' => 'Confirmada',
                                'cancelada' => 'Cancelada',
                                'completada' => 'Completada',
                            ])
                            ->default('pendiente')
                            ->required(),
                    ]),
            ]);
    }


    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('paciente_info')
                    ->label('Paciente')
                    ->getStateUsing(function (Cita $record) {
                        return "{$record->paciente->cip} {$record->paciente->nombre} {$record->paciente->apellido}";
                    }),
                Tables\Columns\TextColumn::make('doctor_info')
                    ->label('Doctor')
                    ->getStateUsing(function (Cita $record) {
                        return "{$record->doctor->nombre} {$record->doctor->apellido} ({$record->doctor->especialidad->nombre})";
                    }),
                Tables\Columns\TextColumn::make('fecha')->label('Fecha'),
                Tables\Columns\TextColumn::make('hora_inicio')->label('Hora de Inicio'),
                Tables\Columns\TextColumn::make('hora_fin')->label('Hora de Fin'),
                Tables\Columns\TextColumn::make('motivo.nombre')->label('Motivo'),
                Tables\Columns\TextColumn::make('status')->label('Estado')
                    ->badge()
                    ->color(function (string $state): string {
                        if ($state === 'pendiente') {
                            return 'warning';
                        } elseif ($state === 'confirmada') {
                            return 'primary';
                        } elseif ($state === 'cancelada') {
                            return 'danger';
                        } elseif ($state === 'completada') {
                            return 'success';
                        }
                        return 'default';
                    }),
            ])
            ->filters([
                SelectFilter::make('doctor_id')
                    ->label('Doctor')
                    ->relationship('doctor', 'nombre')
                    ->placeholder('Todos los Doctores'),
                SelectFilter::make('fecha')
                    ->label('Ver citas')
                    ->options([
                        'todas' => 'Todas las citas',
                        'hoy' => 'Citas de hoy',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['value'] === 'hoy') {
                            return $query->whereDate('fecha', Carbon::today());
                        }

                        return $query;
                    })
                    ->default('todas'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('downloadRecetaPDF')
                ->label('Descargar Receta PDF')
                ->url(fn (Cita $record) => route('citas.downloadRecetaPDF', $record->id))

            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }


public function downloadRecetaPDF($recordId)
{
    $cita = Cita::findOrFail($recordId);
    $receta = $cita->recetas;
    $paciente = $cita->paciente;
    $doctor = $cita->doctor;
    $fecha = now()->format('d/m/Y');

    $pdf = Pdf::loadView('receta', compact('receta', 'paciente', 'doctor', 'fecha'));

    return $pdf->download('receta.pdf');
}
}
