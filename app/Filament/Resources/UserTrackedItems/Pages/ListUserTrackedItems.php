<?php

namespace App\Filament\Resources\UserTrackedItems\Pages;

use App\Filament\Resources\UserTrackedItems\UserTrackedItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUserTrackedItems extends ListRecords
{
    protected static string $resource = UserTrackedItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
