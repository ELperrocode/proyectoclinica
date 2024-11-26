<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FacturaResource\Pages;
use App\Models\Factura;
use App\Models\Servicio;
use App\Models\Cita;
use App\Models\Insumo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class FacturaResource extends Resource
{
    protected static ?string $model = Factura::class;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Facturación';
    public static function getNavigationBadge(): ?string
    {
        $pendientes = Factura::where('estado', 'pendiente')->count();
        return $pendientes > 0 ? (string) $pendientes : null;
    }
    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Información General')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('cliente')
                        ->required()
                        ->label('Cliente'),

                    Forms\Components\Select::make('estado')
                        ->options([
                            'pendiente' => 'Pendiente',
                            'pagada' => 'Pagada',
                            'cancelada' => 'Cancelada',
                        ])
                        ->default('pendiente')
                        ->label('Estado')
                        ->required(),

                    Forms\Components\Checkbox::make('es_jubilado')
                        ->label('¿Es Jubilado?')
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            self::recalculateTotal($set, $get);
                        }),
                ]),

            Forms\Components\Section::make('Items')
                ->columns(1)
                ->schema([
                    Forms\Components\Repeater::make('detalles')->label('Detalles')
                        ->relationship()
                        ->columns(3) // Configuración de columnas para reducir altura
                        ->schema([
                            Forms\Components\Grid::make(3)->schema([
                                Forms\Components\Select::make('detallable_type')
                                    ->options([
                                        Cita::class => 'Cita',
                                        Servicio::class => 'Servicio',
                                        Insumo::class => 'Insumo',
                                    ])
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $set('detallable_id', null);
                                        if ($state === Cita::class || $state === Servicio::class) {
                                            $set('cantidad', 1);
                                        }
                                        self::recalculateTotal($set, $get);
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
                                    ->label('Detalle')
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
                                        self::recalculateTotal($set, $get);
                                    }),

                                Forms\Components\TextInput::make('precio')
                                    ->numeric()
                                    ->required()
                                    ->readOnly(true)
                                    ->label('Precio')
                                    ->reactive()
                                    ->afterStateUpdated(fn($state, callable $set, callable $get) => self::recalculateTotal($set, $get)),

                                Forms\Components\TextInput::make('cantidad')
                                    ->numeric()
                                    ->label('Cantidad')
                                    ->default(fn(callable $get) => $get('detallable_type') === Insumo::class ? 1 : null)
                                    ->required(fn(callable $get) => $get('detallable_type') === Insumo::class)
                                    ->visible(fn(callable $get) => $get('detallable_type') === Insumo::class)
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $type = $get('detallable_type');
                                        $id = $get('detallable_id');
                                        if ($type === Insumo::class && $id) {
                                            $insumo = Insumo::find($id);
                                            if ($insumo && $state > $insumo->cantidad) {
                                                Notification::make()
                                                    ->title('Stock insuficiente')
                                                    ->danger()
                                                    ->body('No hay suficiente stock para facturar este insumo.')
                                                    ->send();
                                                $set('cantidad', null);
                                            }
                                        }
                                        self::recalculateTotal($set, $get);
                                    }),
                            ]),
                        ])
                        ->columns(4)
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            self::recalculateTotal($set, $get);
                        }),
                ]),

            Forms\Components\Section::make('Resumen')
                ->columns(1)
                ->schema([
                    Forms\Components\TextInput::make('total')
                        ->numeric()
                        ->disabled()
                        ->default(0)
                        ->label('Total')
                        ->reactive()
                        ->formatStateUsing(fn($state) => number_format($state, 2)),
                ]),
        ]);
    }

    private static function recalculateTotal(callable $set, callable $get)
    {
        $detalles = $get('detalles') ?? [];
        $total = 0;

        foreach ($detalles as $detalle) {
            $type = $detalle['detallable_type'] ?? null;
            $id = $detalle['detallable_id'] ?? null;
            $cantidad = $detalle['cantidad'] ?? 1;
            $precio = $detalle['precio'] ?? 0;

            if ($type === Insumo::class && $id) {
                $insumo = Insumo::find($id);
                if ($insumo && $cantidad > $insumo->cantidad) {
                    Notification::make()
                        ->title('Stock insuficiente')
                        ->danger()
                        ->body('No hay suficiente stock para facturar el insumo "' . $insumo->nombre . '".')
                        ->send();

                    continue;
                }
            }

            $total += $precio * $cantidad;
        }

        if ($get('es_jubilado')) {
            $total *= 0.85;
        }

        $set('total', $total);
    }



    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->searchable(),
                Tables\Columns\TextColumn::make('cliente')->searchable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('total')->numeric()->sortable()->formatStateUsing(fn($state) => number_format($state, 2)),
                Tables\Columns\TextColumn::make('estado')->searchable()->label('Estado')
                    ->sortable()
                    ->badge()
                    ->color(function (string $state): string {
                        if ($state === 'pendiente') {
                            return 'warning';
                        } elseif ($state === 'pagada') {
                            return 'success';
                        } elseif ($state === 'cancelada') {
                            return 'danger';
                        }
                        return 'default';
                    }),
                Tables\Columns\TextColumn::make('created_at')->label("Fecha")->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('detalles')
                    ->label('Detalles')
                    ->searchable()
                    ->formatStateUsing(function ($record) {
                        return $record->detalles->map(function ($detalle) {
                            $tipo = class_basename($detalle->detallable_type);
                            $nombre = $detalle->detallable->nombre ?? $detalle->detallable->motivo->nombre ?? 'N/A';
                            $cantidad = $detalle->cantidad ?? 1;
                            $lote = $detalle->detallable_type === Insumo::class ? ' - Lote: ' . ($detalle->detallable->lote ?? 'N/A') : '';
                            return "{$tipo}: {$nombre} (Cantidad: {$cantidad}{$lote})";
                        })->join(', ');
                    }),
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
        return [];
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
