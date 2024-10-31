<?php
// app/Filament/Resources/DoctorResource.php
namespace App\Filament\Resources;

use App\Filament\Resources\DoctorResource\Pages;
use App\Models\Doctor;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Forms;

class DoctorResource extends Resource
{
    protected static ?string $model = Doctor::class;
    protected static ?string $navigationLabel = 'Doctores';
    protected static ?string $navigationIcon = 'heroicon-o-plus-circle';
    protected static ?string $navigationGroup = 'Gestión Médica';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('nombre')->required(),
                        Forms\Components\TextInput::make('apellido')->required(),
                        Forms\Components\TextInput::make('cip')->required(),
                        Forms\Components\TextInput::make('numero_junta_tecnica')->label('Número de Junta Técnica')->required(),
                        Forms\Components\Select::make('sexo')
                            ->options([
                                'masculino' => 'Masculino',
                                'femenino' => 'Femenino',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('telefono')->required(),
                        Forms\Components\TextInput::make('email')->email()->required(),
                        Forms\Components\TextInput::make('direccion')->required(),
                        Forms\Components\Select::make('especialidad_id')
                            ->relationship('especialidad', 'nombre')
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre'),
                Tables\Columns\TextColumn::make('apellido'),
                Tables\Columns\TextColumn::make('cip'),
                Tables\Columns\TextColumn::make('numero_junta_tecnica')->label('Número de Junta Técnica'),
                Tables\Columns\TextColumn::make('sexo'),
                Tables\Columns\TextColumn::make('telefono'),
                Tables\Columns\TextColumn::make('email'),
                Tables\Columns\TextColumn::make('direccion'),
                Tables\Columns\TextColumn::make('especialidad.nombre')->label('Especialidad'),
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
            'index' => Pages\ListDoctors::route('/'),
            'create' => Pages\CreateDoctor::route('/create'),
            'edit' => Pages\EditDoctor::route('/{record}/edit'),
        ];
    }
}
