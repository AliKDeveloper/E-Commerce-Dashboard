<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class ProductChart extends ChartWidget
{
    protected static ?string $heading = 'Chart';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = $this->getProductsPerMonth();

        return [
            'datasets' => [
                [
                    'label' => 'Products per month',
                    'data' => $data['productsPerMonth'],
                ]
            ],

            'labels' => $data['months'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getProductsPerMonth(): array
    {
        $now = now();
        $productsPerMonth = [];

        $months = collect(range(1,12))->map(function ($month) use ($now, &$productsPerMonth) {
            $count = Product::whereMonth('created_at', Carbon::parse($now->month($month)->format('Y-m')))->count();
            $productsPerMonth[] = $count;

            return $now->month($month)->format('M');
        })->toArray();

        return [
            'productsPerMonth' => $productsPerMonth,
            'months' => $months,
        ];
    }
}
