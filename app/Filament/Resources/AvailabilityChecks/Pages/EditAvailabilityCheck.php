<?php

namespace App\Filament\Resources\AvailabilityChecks\Pages;

use App\Filament\Resources\AvailabilityChecks\AvailabilityCheckResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAvailabilityCheck extends EditRecord
{
    protected static string $resource = AvailabilityCheckResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
