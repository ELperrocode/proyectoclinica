<?php
namespace App\Filament\Resources\InsumoResource\Api;

use Rupadana\ApiService\ApiService;
use App\Filament\Resources\InsumoResource;
use Illuminate\Routing\Router;


class InsumoApiService extends ApiService
{
    protected static string | null $resource = InsumoResource::class;

    public static function handlers() : array
    {
        return [
            Handlers\CreateHandler::class,
            Handlers\UpdateHandler::class,
            Handlers\DeleteHandler::class,
            Handlers\PaginationHandler::class,
            Handlers\DetailHandler::class
        ];

    }
}
