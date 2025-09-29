<?php

namespace App\Filament\Resources\Products\Widgets;

use App\Models\AvailabilityCheck;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class ProductPriceChart extends ChartWidget
{
    public $record = null;

    protected ?string $heading = 'Product Price Chart';

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '200px';

    protected function getData(): array
    {

        $data = Trend::query(
            AvailabilityCheck::query()
                ->where('product_id', $this->record->id)
        )
            ->between(
                start: now()->subWeek(),
                end: now(),
            )
            ->perDay()
            ->min('price');

        return [
            'datasets' => [
                [
                    'label' => 'Price',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => Carbon::parse($value->date)->format('jS M')),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
