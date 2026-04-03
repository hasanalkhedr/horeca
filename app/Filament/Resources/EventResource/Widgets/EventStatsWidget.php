<?php

namespace App\Filament\Resources\EventResource\Widgets;

use App\Models\Event;
use App\Models\Stand;
use App\Models\Contract;
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

        // Calculate target statistics
        $targetSpace = Event::sum('target_space');
        $targetSpaceAmount = Event::sum('target_space_amount');
        $targetSponsorAmount = Event::sum('target_sponsor_amount');

        // Get actual achievements from contracts
        $achievements = $this->getContractAchievements();


        $stands = Stand::where('is_merged', false)->orWhere('parent_stand_id', null)->get();

        $totalStands = $stands->count();
        $soldStands = Event::all()->sum(fn ($event) => $event->soldStands()->count());

        // Calculate target comparisons
        $spaceComparison = $targetSpace > 0 ? round(($achievements['space'] / $targetSpace) * 100, 1) : 0;
        $spaceAmountComparison = $targetSpaceAmount > 0 ? round(($achievements['space_amount'] / $targetSpaceAmount) * 100, 1) : 0;
        $sponsorAmountComparison = $targetSponsorAmount > 0 ? round(($achievements['sponsor_amount'] / $targetSponsorAmount) * 100, 1) : 0;

        return [
            Stat::make('Total Events', $totalEvents)
                ->description($activeEvents . ' active, ' . $pastEvents . ' past')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('primary'),

            Stat::make('Total Space', number_format($totalSpace, 2) . ' sqm')
                ->description(number_format($spaceToSell, 2) . ' sqm to sell')
                ->descriptionIcon('heroicon-o-square-3-stack-3d')
                ->color('info'),

            Stat::make('Space Achieved', number_format($achievements['space'], 0) . ' sqm')
                ->description($spaceComparison . '% of ' . number_format($targetSpace, 0) . ' target')
                ->descriptionIcon($spaceComparison >= 100 ? 'heroicon-o-check-circle' : 'heroicon-o-clock')
                ->color($spaceComparison >= 100 ? 'success' : ($spaceComparison >= 75 ? 'warning' : 'danger')),

            Stat::make('Space Amount', '$' . number_format($achievements['space_amount'], 0))
                ->description($spaceAmountComparison . '% of $' . number_format($targetSpaceAmount, 0) . ' target')
                ->descriptionIcon($spaceAmountComparison >= 100 ? 'heroicon-o-check-circle' : 'heroicon-o-clock')
                ->color($spaceAmountComparison >= 100 ? 'success' : ($spaceAmountComparison >= 75 ? 'warning' : 'danger')),

            Stat::make('Sponsor Amount', '$' . number_format($achievements['sponsor_amount'], 0))
                ->description($sponsorAmountComparison . '% of $' . number_format($targetSponsorAmount, 0) . ' target')
                ->descriptionIcon($sponsorAmountComparison >= 100 ? 'heroicon-o-check-circle' : 'heroicon-o-clock')
                ->color($sponsorAmountComparison >= 100 ? 'success' : ($sponsorAmountComparison >= 75 ? 'warning' : 'danger')),

            Stat::make('Total Stands', $totalStands)
                ->description($soldStands . ' sold stands')
                ->descriptionIcon('heroicon-o-map-pin')
                ->color('primary'),
        ];
    }

    /**
     * Get contract achievements across all events
     */
    protected function getContractAchievements(): array
    {
        $contracts = Contract::with(['Stand', 'Report.Currency'])->get();

        $totalSpace = 0;
        $totalSpaceAmount = 0;
        $totalSponsorAmount = 0;

        foreach ($contracts as $contract) {
            // Add space if contract has stand
            if ($contract->Stand && $contract->Stand->space) {
                $totalSpace += $contract->Stand->space;
            }

            // Add space amount
            if ($contract->space_net) {
                $rateToUSD = $contract->Report->Currency->rate_to_usd ?? 1;
                $totalSpaceAmount += $contract->space_net * $rateToUSD;
            }

            // Add sponsor amount
            if ($contract->sponsor_net) {
                $rateToUSD = $contract->Report->Currency->rate_to_usd ?? 1;
                $totalSponsorAmount += $contract->sponsor_net * $rateToUSD;
            }
        }

        return [
            'space' => $totalSpace,
            'space_amount' => $totalSpaceAmount,
            'sponsor_amount' => $totalSponsorAmount,
        ];
    }
}
