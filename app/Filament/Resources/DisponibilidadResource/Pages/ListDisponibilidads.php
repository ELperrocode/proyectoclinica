<?php

namespace App\Filament\Resources\DisponibilidadResource\Pages;

use App\Filament\Resources\DisponibilidadResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDisponibilidads extends ListRecords
{
    protected static string $resource = DisponibilidadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
