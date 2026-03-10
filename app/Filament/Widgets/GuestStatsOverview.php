<?php

namespace App\Filament\Widgets;

use App\Models\WrittenGuest;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class GuestStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Toti invitatiii', WrittenGuest::count()),

            Stat::make('Confirmati', WrittenGuest::where('answer', true)->count()),

            Stat::make('Refuzat', WrittenGuest::where('answer', false)->count()),
        ];
    }
}
