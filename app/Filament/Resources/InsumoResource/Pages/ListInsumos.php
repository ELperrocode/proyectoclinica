<?php

namespace App\Filament\Resources\InsumoResource\Pages;

use App\Filament\Resources\InsumoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use App\Models\Insumo;

class ListInsumos extends ListRecords
{
    protected static string $resource = InsumoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    public function mount(): void
    {
        parent::mount();

        $lowStockItems = Insumo::where('cantidad', '<', 100)->get();
        if ($lowStockItems->isNotEmpty()) {
            Notification::make()
                ->title('Insumos bajos de stock')
                ->body('Hay insumos con menos de 100 unidades en stock.')
                ->warning()
                ->send();
        }
    }
}
