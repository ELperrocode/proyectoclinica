<?php

namespace App\Filament\Resources\DisponibilidadResource\Pages;

use App\Filament\Resources\DisponibilidadResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDisponibilidad extends EditRecord
{
    protected static string $resource = DisponibilidadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
