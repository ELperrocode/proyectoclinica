<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PacienteResource\Pages;
use App\Filament\Resources\PacienteResource\RelationManagers\CitasRelationManager;
use App\Models\Paciente;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Forms;


class PacienteResource extends Resource
{
    protected static ?string $model = Paciente::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Gestión Pacientes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Personal')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('nombre')->required(),
                                Forms\Components\TextInput::make('apellido')->required(),
                                Forms\Components\DatePicker::make('fecha_nacimiento')->required(),
                                Forms\Components\Select::make('sexo')
                                    ->options([
                                        'masculino' => 'Masculino',
                                        'femenino' => 'Femenino',
                                    ])
                                    ->required(),
                                Forms\Components\TextInput::make('nacionalidad')->required(),
                                Forms\Components\TextInput::make('cip')->label('CIP')->required(),
                            ]),
                    ]),
                Forms\Components\Section::make('Información de Contacto')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('direccion')->required(),
                                Forms\Components\TextInput::make('telefono')->required(),
                                Forms\Components\TextInput::make('email')->email()->required(),
                            ]),
                    ]),
                Forms\Components\Section::make('Información Médica')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('grupo_sanguineo')
                                    ->options([
                                        'A+' => 'A+',
                                        'A-' => 'A-',
                                        'B+' => 'B+',
                                        'B-' => 'B-',
                                        'AB+' => 'AB+',
                                        'AB-' => 'AB-',
                                        'O+' => 'O+',
                                        'O-' => 'O-',
                                    ]),
                                Forms\Components\Textarea::make('alergias'),
                                Forms\Components\Textarea::make('condiciones_medicas'),
                                Forms\Components\Textarea::make('medicamentos'),
                            ]),
                    ]),
                Forms\Components\Section::make('Información de Seguro')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('nombre_aseguradora'),
                                Forms\Components\TextInput::make('numero_poliza'),
                                Forms\Components\DatePicker::make('fecha_vencimiento_poliza'),
                            ]),
                    ]),
                Forms\Components\Section::make('Información de Emergencia')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('contacto_emergencia_nombre')->required(),
                                Forms\Components\TextInput::make('contacto_emergencia_relacion')->required(),
                                Forms\Components\TextInput::make('contacto_emergencia_telefono')->required(),
                            ]),
                    ]),
                Forms\Components\Section::make('Información Adicional')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('ocupacion'),
                                Forms\Components\Select::make('estado_civil')
                                    ->options([
                                        'soltero' => 'Soltero',
                                        'casado' => 'Casado',
                                        'divorciado' => 'Divorciado',
                                        'viudo' => 'Viudo',
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')->searchable(),
                Tables\Columns\TextColumn::make('apellido')->searchable(),
                Tables\Columns\TextColumn::make('fecha_nacimiento'),
                Tables\Columns\TextColumn::make('sexo'),
                Tables\Columns\TextColumn::make('nacionalidad'),
                Tables\Columns\TextColumn::make('cip')->label('CIP')->searchable(),
                Tables\Columns\TextColumn::make('direccion'),
                Tables\Columns\TextColumn::make('telefono'),
                Tables\Columns\TextColumn::make('email'),
                Tables\Columns\TextColumn::make('grupo_sanguineo'),
                Tables\Columns\TextColumn::make('alergias'),
                Tables\Columns\TextColumn::make('condiciones_medicas'),
                Tables\Columns\TextColumn::make('medicamentos'),
                Tables\Columns\TextColumn::make('historial_medico_familiar'),
                Tables\Columns\TextColumn::make('nombre_aseguradora'),
                Tables\Columns\TextColumn::make('numero_poliza'),
                Tables\Columns\TextColumn::make('fecha_vencimiento_poliza'),
                Tables\Columns\TextColumn::make('contacto_emergencia_nombre'),
                Tables\Columns\TextColumn::make('contacto_emergencia_relacion'),
                Tables\Columns\TextColumn::make('contacto_emergencia_telefono'),
                Tables\Columns\TextColumn::make('ocupacion'),
                Tables\Columns\TextColumn::make('estado_civil'),
            ])
            ->filters([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            CitasRelationManager::class, // Registrar el RelationManager aquí
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPacientes::route('/'),
            'create' => Pages\CreatePaciente::route('/create'),
            'edit' => Pages\EditPaciente::route('/{record}/edit'),
        ];
    }
}
