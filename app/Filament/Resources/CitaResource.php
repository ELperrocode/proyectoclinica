<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CitaResource\Pages;
use App\Filament\Resources\CitaResource\RelationManagers;
use App\Models\Cita;
use App\Models\Servicio;
use App\Models\Doctor;
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
                Forms\Components\Select::make('motivo_id')
                    ->relationship('motivo', 'nombre')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function (callable $set, $state) {
                        $especialidadId = Servicio::find($state)?->especialidad_id;
                        $set('doctor_id', null);
                        $set('especialidad_id', $especialidadId);
                    }),
                Forms\Components\Select::make('especialidad_id')
                    ->relationship('especialidad', 'nombre')
                    ->disabled(),
                Forms\Components\Select::make('doctor_id')
                    ->options(function (callable $get) {
                        $especialidadId = $get('especialidad_id');
                        return Doctor::where('especialidad_id', $especialidadId)->pluck('nombre', 'id');
                    })
                    ->required(),
                Forms\Components\DatePicker::make('fecha')
                    ->required()
                    ->minDate(now()->toDateString()),
                Forms\Components\TimePicker::make('hora_inicio')
                    ->required()
                    ->seconds(false),
                Forms\Components\TimePicker::make('hora_fin')
                    ->required()
                    ->seconds(false),
                Forms\Components\Select::make('status')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'confirmada' => 'Confirmada',
                        'cancelada' => 'Cancelada',
                        'completada' => 'Completada',
                    ])
                    ->default('pendiente')
                    ->required(),
                Forms\Components\Grid::make()
                    ->schema([
                        Forms\Components\Repeater::make('recetas')
                            ->relationship('recetas')
                            ->schema([
                                Forms\Components\Textarea::make('descripcion')
                                    ->label('Descripción de la Receta')
                                    ->required(),
                            ])
                            ->columnSpan(6),

                        Forms\Components\Repeater::make('diagnosticos')
                            ->relationship('diagnosticos')
                            ->schema([
                                Forms\Components\Textarea::make('descripcion')
                                    ->label('Descripción del Diagnóstico')
                                    ->required(),
                            ])
                            ->columnSpan(6),
                    ])
                    ->columns(12),
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
                Tables\Columns\BadgeColumn::make('status')->label('Estado')
                    ->colors([
                        'primary' => 'pendiente',
                        'success' => 'confirmada',
                        'danger' => 'cancelada',
                        'secondary' => 'completada',
                    ]),
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
