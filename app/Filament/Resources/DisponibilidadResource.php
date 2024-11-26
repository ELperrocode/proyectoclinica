<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DisponibilidadResource\Pages;
use App\Models\Disponibilidad;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Forms;
use Filament\Resources\Components;
use App\Models\Doctor;

class DisponibilidadResource extends Resource
{
    protected static ?string $model = Disponibilidad::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationGroup = 'Gestión Médica';
    protected static ?string $navigationLabel = 'Horarios ';
    protected static ?string $label = 'Horarios ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('doctor_id')
                    ->options(function () {
                        return Doctor::all()->mapWithKeys(function ($doctor) {
                            return [$doctor->id => $doctor->nombre . ' ' . $doctor->apellido . ' (CIP: ' . $doctor->cip . ')'];
                        });
                    })
                    ->required()
                    ->label('Doctor'),

                Forms\Components\CheckboxList::make('dia_semana')
                    ->options([
                        'lunes' => 'Lunes',
                        'martes' => 'Martes',
                        'miércoles' => 'Miércoles',
                        'jueves' => 'Jueves',
                        'viernes' => 'Viernes',
                        'sábado' => 'Sábado',
                        'domingo' => 'Domingo',
                    ])
                    ->columns(3)
                    ->required()
                    ->label('Día de la Semana'),

                Forms\Components\TimePicker::make('hora_inicio')
                    ->required()
                    ->label('Hora de Inicio')
                    ->seconds(false),

                Forms\Components\TimePicker::make('hora_fin')
                    ->required()
                    ->label('Hora de Fin')
                    ->seconds(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('doctor.nombre')->label('Doctor'),
                Tables\Columns\TextColumn::make('dia_semana')->label('Día de la Semana'),
                Tables\Columns\TextColumn::make('hora_inicio')->label('Hora de Inicio'),
                Tables\Columns\TextColumn::make('hora_fin')->label('Hora de Fin'),
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
            'index' => Pages\ListDisponibilidads::route('/'),
            'create' => Pages\CreateDisponibilidad::route('/create'),
            'edit' => Pages\EditDisponibilidad::route('/{record}/edit'),
        ];
    }
}
