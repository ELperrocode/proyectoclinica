<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MovimientoInventarioResource\Pages;
use App\Models\MovimientoInventario;
use Filament\Resources\Resource;
use Filament\Forms;
use Filament\Tables;
use Filament\Notifications\Notification;

class MovimientoInventarioResource extends Resource
{
    protected static ?string $model = MovimientoInventario::class;
    protected static ?string $navigationLabel = 'Movimientos de Inventario';
    protected static ?string $navigationIcon = 'heroicon-o-archive-box-arrow-down';
    protected static ?string $navigationGroup = 'Inventario';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('insumo_id')
                    ->relationship('insumo', 'nombre')
                    ->required(),
                Forms\Components\Select::make('tipo')
                    ->options([
                        'entrada' => 'Entrada',
                        'salida' => 'Salida',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('cantidad')
                    ->required()
                    ->numeric()
                    ->afterStateUpdated(function ($state, callable $set, $get) {
                        $insumo = \App\Models\Insumo::find($get('insumo_id'));
                        if ($get('tipo') === 'salida' && $insumo && $state > $insumo->cantidad) {
                            Notification::make()
                                ->title('Stock insuficiente')
                                ->danger()
                                ->body('No hay suficiente stock para realizar esta salida.')
                                ->send();
                            $set('cantidad', null); // Resetea el campo cantidad para evitar el envío del formulario
                        }
                    }),
                Forms\Components\Textarea::make('motivo')->nullable(),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('insumo.nombre')->label('Insumo'),
                Tables\Columns\TextColumn::make('tipo'),
                Tables\Columns\TextColumn::make('cantidad'),
                Tables\Columns\TextColumn::make('motivo')->limit(50),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListMovimientoInventarios::route('/'),
            'create' => Pages\CreateMovimientoInventario::route('/create'),
            'edit' => Pages\EditMovimientoInventario::route('/{record}/edit'),
        ];
    }
}
