<?php

namespace App\Filament\Resources\EventResource\Widgets;

use App\Models\Event;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EventStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalEvents = Event::count();
        $activeEvents = Event::where('end_date', '>=', now())->count();
        $pastEvents = Event::where('end_date', '<', now())->count();

        $totalSpace = Event::sum('total_space');
        $spaceToSell = Event::sum('space_to_sell');
        $remainingToSell = Event::sum('remaining_space_to_sell');

        $totalStands = Event::withCount('Stands')->get()->sum('stands_count');
        $soldStands = Event::all()->sum(fn ($event) => $event->soldStands()->count());

        return [
            Stat::make('Total Events', $totalEvents)
                ->description($activeEvents . ' active, ' . $pastEvents . ' past')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('primary'),

            Stat::make('Total Space', number_format($totalSpace, 2) . ' sqm')
                ->description(number_format($spaceToSell, 2) . ' sqm to sell')
                ->descriptionIcon('heroicon-o-square-3-stack-3d')
                ->color('info'),

            Stat::make('Remaining to Sell', number_format($remainingToSell, 2) . ' sqm')
                ->description(number_format(($remainingToSell / max($spaceToSell, 1)) * 100, 1) . '% remaining')
                ->descriptionIcon('heroicon-o-clock')
                ->color($remainingToSell > 0 ? 'warning' : 'success'),

            Stat::make('Total Stands', $totalStands)
                ->description($soldStands . ' sold stands')
                ->descriptionIcon('heroicon-o-map-pin')
                ->color('primary'),
        ];
    }
}
