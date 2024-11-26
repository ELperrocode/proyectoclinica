<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FacturaResource\Pages;
use App\Filament\Resources\FacturaResource\RelationManagers;
use App\Models\Factura;
use App\Models\Servicio;
use App\Models\Cita;
use App\Models\Insumo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;

class FacturaResource extends Resource
{
    protected static ?string $model = Factura::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Facturación';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('cliente')->required(),
                Forms\Components\Repeater::make('detalles')
                    ->relationship()
                    ->schema([

                        Forms\Components\Select::make('detallable_type')
                        ->options([
                            Cita::class => 'Cita',
                            Servicio::class => 'Servicio',
                            Insumo::class => 'Insumo',
                        ])
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            $set('detallable_id', null); // Resetear el ID seleccionable
                            if ($state === Cita::class || $state === Servicio::class) {
                                $set('cantidad', 1); // Asignar cantidad implícita 1
                            }
                            self::recalculateTotal($set, $get); // Recalcular el total
                        }),

                        Forms\Components\Select::make('detallable_id')
                        ->options(function (callable $get) {
                            $type = $get('detallable_type');
                            if ($type === Cita::class) {
                                return Cita::where('status', 'confirmada')
                                    ->get()
                                    ->mapWithKeys(fn($cita) => [$cita->id => $cita->paciente->nombre . ' - ' . $cita->motivo->nombre]);
                            } elseif ($type === Servicio::class) {
                                return Servicio::all()->pluck('nombre', 'id');
                            } elseif ($type === Insumo::class) {
                                return Insumo::all()->pluck('nombre', 'id');
                            }
                            return [];
                        })
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            $type = $get('detallable_type');
                            if ($type && $state) {
                                $item = $type::find($state);
                                if ($item) {
                                    $set('precio', match ($type) {
                                        Cita::class => $item->motivo->precio,
                                        Servicio::class => $item->precio,
                                        Insumo::class => $item->precio_unitario,
                                        default => 0,
                                    });
                                }
                            }
                            self::recalculateTotal($set, $get); // Recalcular el total
                        }),



                        Forms\Components\TextInput::make('precio')
                            ->numeric()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn($state, callable $set, callable $get) => self::recalculateTotal($set, $get)),
                            Forms\Components\TextInput::make('cantidad')
                            ->numeric()
                            ->default(fn (callable $get) => $get('detallable_type') === Insumo::class ? 1 : null) // Por defecto 1 solo si es Insumo
                            ->required(fn (callable $get) => $get('detallable_type') === Insumo::class) // Requerido solo para Insumos
                            ->visible(fn (callable $get) => $get('detallable_type') === Insumo::class) // Visible solo para Insumos
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $type = $get('detallable_type');
                                $id = $get('detallable_id');

                                // Validación específica para Insumos
                                if ($type === Insumo::class && $id) {
                                    $insumo = Insumo::find($id);
                                    if ($insumo && $state > $insumo->cantidad) {
                                        Notification::make()
                                            ->title('Stock insuficiente')
                                            ->danger()
                                            ->body('No hay suficiente stock para facturar este insumo.')
                                            ->send();
                                        $set('cantidad', null); // Restablecer la cantidad si supera el stock
                                    }
                                }

                                // Asignar cantidad predeterminada para Citas o Servicios
                                if (in_array($type, [Cita::class, Servicio::class])) {
                                    $set('cantidad', 1); // Cantidad fija
                                }

                                // Recalcular el total
                                self::recalculateTotal($set, $get);
                            }),

                    ])
                    ->columns(3)
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        self::recalculateTotal($set, $get);
                    }),

                Forms\Components\TextInput::make('total')
                    ->numeric()
                    ->disabled()
                    ->default(0)
                    ->reactive(),
                Forms\Components\Select::make('estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'pagada' => 'Pagada',
                        'cancelada' => 'Cancelada',
                    ])
                    ->default('pendiente')
                    ->required(),
            ]);
    }

    private static function recalculateTotal(callable $set, callable $get)
    {
        $detalles = $get('detalles') ?? [];
        $total = collect($detalles)->sum(function ($detalle) {
            $precio = $detalle['precio'] ?? 0;
            $cantidad = $detalle['cantidad'] ?? 1; // Si cantidad es null, se asume 1
            return (float) $precio * (int) $cantidad;
        });

        $set('total', $total);
    }





    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('cliente')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('estado')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListFacturas::route('/'),
            'create' => Pages\CreateFactura::route('/create'),
            'edit' => Pages\EditFactura::route('/{record}/edit'),
        ];
    }
}
