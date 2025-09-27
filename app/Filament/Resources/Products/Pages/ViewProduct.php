<?php

namespace App\Filament\Resources\Products\Pages;

use App\Actions\AvailabilityCheck\CheckAvailabilityForProduct;
use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Filament\Notifications\Notification;

class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('checkAvailability')
                ->label('Check Availability')
                ->icon(Heroicon::ArrowRightCircle)
                ->action(function () {
                    CheckAvailabilityForProduct::run($this->record);

                    // Refresh the AvailabilityChecks relation manager
                    $this->dispatch('$refresh', to: 'availabilityChecks');

                    Notification::make()
                        ->title('Availability check completed')
                        ->success()
                        ->send();
                })
                ->after(function ($livewire) {
                    $livewire->dispatch('refreshRelation');
                })
                ->color('success'),
        ];
    }
}
