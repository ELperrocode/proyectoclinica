<?php
// app/Filament/Resources/ServicioResource.php
namespace App\Filament\Resources;

use App\Filament\Resources\ServicioResource\Pages;
use App\Models\Servicio;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Forms;

class ServicioResource extends Resource
{
    protected static ?string $model = Servicio::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Gestión Médica';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')->required(),
                Forms\Components\Textarea::make('descripcion')->nullable(),
                Forms\Components\Select::make('especialidad_id')
                    ->relationship('especialidad', 'nombre')
                    ->required(),
                Forms\Components\TextInput::make('precio')
                    ->numeric()
                    ->required()
                    ->label('Precio'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre'),
                Tables\Columns\TextColumn::make('descripcion'),
                Tables\Columns\TextColumn::make('especialidad.nombre')->label('Especialidad'),
                Tables\Columns\TextColumn::make('precio')->label('Precio')->money('USD', true),
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
            'index' => Pages\ListServicios::route('/'),
            'create' => Pages\CreateServicio::route('/create'),
            'edit' => Pages\EditServicio::route('/{record}/edit'),
        ];
    }
}
