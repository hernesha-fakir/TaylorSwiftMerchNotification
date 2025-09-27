<?php

namespace App\Filament\Resources\AvailabilityChecks\Pages;

use App\Filament\Resources\AvailabilityChecks\AvailabilityCheckResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAvailabilityChecks extends ListRecords
{
    protected static string $resource = AvailabilityCheckResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
