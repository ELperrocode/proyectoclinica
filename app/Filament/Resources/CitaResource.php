<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CitaResource\Pages;
use App\Models\Cita;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

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
                    ->disabled(),
                Forms\Components\Select::make('doctor_id')
                    ->relationship('doctor', 'nombre')
                    ->disabled(),
                Forms\Components\DatePicker::make('fecha')
                    ->disabled(),
                Forms\Components\TimePicker::make('hora_inicio')
                    ->disabled()
                    ->seconds(false),
                Forms\Components\TimePicker::make('hora_fin')
                    ->disabled()
                    ->seconds(false),
                Forms\Components\Select::make('motivo_id')
                    ->relationship('motivo', 'nombre')
                    ->disabled(),
                Forms\Components\Repeater::make('diagnosticos')
                    ->relationship('diagnosticos')
                    ->schema([
                        Forms\Components\Textarea::make('descripcion')
                            ->label('Descripción del Diagnóstico')
                            ->required(),
                    ]),
                Forms\Components\Repeater::make('recetas')
                    ->relationship('recetas')
                    ->schema([
                        Forms\Components\Textarea::make('descripcion')
                            ->label('Descripción de la Receta')
                            ->required(),
                    ]),
                Forms\Components\Select::make('status')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'confirmada' => 'Confirmada',
                        'cancelada' => 'Cancelada',
                        'completada' => 'Completada',
                    ])
                    ->default('pendiente')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
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
                Tables\Columns\BadgeColumn::make('status')->label('Estado')
                    ->colors([
                        'primary' => 'pendiente',
                        'success' => 'confirmada',
                        'danger' => 'cancelada',
                        'secondary' => 'completada',
                    ]),
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

    //public static function getWidgets(): array
    //{
    // return [
    //   CalendarWidget::class,
    //];
    //}
}
