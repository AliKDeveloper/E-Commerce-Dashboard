<?php

namespace App\Filament\Widgets;

use App\Models\Brand;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static ?string $pollingInterval = '10s';
    protected function getStats(): array
    {
        return [
            Stat::make('Total Product', Product::count())
                ->description('Total products from all categories'),

            Stat::make('Total Customers', Customer::count()),

            Stat::make('Total Brands', Brand::count()),

            Stat::make('Total Orders', Order::count()),
        ];
    }
}
