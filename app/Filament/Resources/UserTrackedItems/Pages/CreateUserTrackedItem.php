<?php

namespace App\Filament\Resources\UserTrackedItems\Pages;

use App\Filament\Resources\UserTrackedItems\UserTrackedItemResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateUserTrackedItem extends CreateRecord
{
    protected static string $resource = UserTrackedItemResource::class;

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()->visible(false);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
