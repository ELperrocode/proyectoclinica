<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CitaResource\Pages;
use App\Filament\Resources\CitaResource\RelationManagers;
use App\Models\Cita;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CitaResource\Widgets\CalendarWidget;
use Carbon\Carbon;
use Filament\Notifications\Notification;

class CitaResource extends Resource
{
    protected static ?string $model = Cita::class;
    protected static ?string $navigationGroup = 'Gestión Pacientes';
    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('paciente_id')
                    ->relationship('paciente', 'nombre')
                    ->required(),
                Forms\Components\Select::make('doctor_id')
                    ->relationship('doctor', 'nombre')
                    ->required(),
                Forms\Components\DatePicker::make('fecha')
                    ->required()
                    ->minDate(now()->toDateString()),
                Forms\Components\TimePicker::make('hora_inicio')
                    ->required()
                    ->seconds(false)
                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                        $doctorId = $get('doctor_id');
                        $fecha = $get('fecha');
                        $horaFin = $get('hora_fin');

                        if ($doctorId && $fecha && $horaFin) {
                            $exists = Cita::where('doctor_id', $doctorId)
                                ->where('fecha', $fecha)
                                ->where(function ($query) use ($state, $horaFin) {
                                    $query->whereBetween('hora_inicio', [$state, $horaFin])
                                          ->orWhereBetween('hora_fin', [$state, $horaFin]);
                                })
                                ->exists();

                            if ($exists) {
                                $set('hora_inicio', null);
                                Notification::make()
                                    ->title('Conflicto de Horario')
                                    ->body('Ya existe una cita con el mismo doctor en el mismo horario.')
                                    ->danger()
                                    ->send();
                            }
                        }
                    }),
                Forms\Components\TimePicker::make('hora_fin')
                    ->required()
                    ->seconds(false)
                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                        $doctorId = $get('doctor_id');
                        $fecha = $get('fecha');
                        $horaInicio = $get('hora_inicio');

                        if ($doctorId && $fecha && $horaInicio) {
                            $exists = Cita::where('doctor_id', $doctorId)
                                ->where('fecha', $fecha)
                                ->where(function ($query) use ($horaInicio, $state) {
                                    $query->whereBetween('hora_inicio', [$horaInicio, $state])
                                          ->orWhereBetween('hora_fin', [$horaInicio, $state]);
                                })
                                ->exists();

                            if ($exists) {
                                $set('hora_fin', null);
                                Notification::make()
                                    ->title('Conflicto de Horario')
                                    ->body('Ya existe una cita con el mismo doctor en el mismo horario.')
                                    ->danger()
                                    ->send();
                            }
                        }
                    }),
                Forms\Components\Select::make('motivo_id')
                    ->relationship('motivo', 'nombre')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('paciente.nombre')->label('Paciente'),
                Tables\Columns\TextColumn::make('doctor.nombre')->label('Doctor'),
                Tables\Columns\TextColumn::make('fecha')->label('Fecha'),
                Tables\Columns\TextColumn::make('hora_inicio')->label('Hora de Inicio'),
                Tables\Columns\TextColumn::make('hora_fin')->label('Hora de Fin'),
                Tables\Columns\TextColumn::make('motivo.nombre')->label('Motivo'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCitas::route('/'),
            'create' => Pages\CreateCita::route('/create'),
            'edit' => Pages\EditCita::route('/{record}/edit'),
            'view' => Pages\ViewEvent::route('/{record}'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            CalendarWidget::class,
        ];
    }
}