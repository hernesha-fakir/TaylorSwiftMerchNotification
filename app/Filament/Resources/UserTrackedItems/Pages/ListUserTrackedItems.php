<?php

namespace App\Filament\Resources\UserTrackedItems\Pages;

use App\Filament\Resources\UserTrackedItems\UserTrackedItemResource;
use App\Services\ProductImportService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListUserTrackedItems extends ListRecords
{
    protected static string $resource = UserTrackedItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('quickTrack')
                ->label('Quick Track URL')
                ->icon('heroicon-o-plus')
                ->color('success')
                ->form([
                    TextInput::make('url')
                        ->label('Taylor Swift Product URL')
                        ->url()
                        ->required()
                        ->placeholder('https://storeau.taylorswift.com/products/...')
                        ->helperText('Paste any Taylor Swift product URL and we\'ll automatically track it for you!')
                        ->columnSpanFull(),
                ])
                ->action(function (array $data) {
                    try {
                        $importService = new ProductImportService();
                        $trackedItem = $importService->importFromUrl($data['url'], auth()->id());

                        Notification::make()
                            ->success()
                            ->title('Product Tracked Successfully!')
                            ->body("Now tracking: {$trackedItem->productVariant->product->name}" .
                                   ($trackedItem->productVariant->size ? " - {$trackedItem->productVariant->size}" : ''))
                            ->send();

                        $this->redirect($this->getResource()::getUrl('index'));

                    } catch (\Exception $e) {
                        Notification::make()
                            ->danger()
                            ->title('Failed to Track Product')
                            ->body($e->getMessage())
                            ->send();
                    }
                }),
            CreateAction::make(),
        ];
    }
}
