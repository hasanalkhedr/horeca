<?php

namespace App\Filament\Widgets;

use App\Models\Contract;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;

class SpaceStatsOverview extends BaseWidget
{
    use InteractsWithPageFilters;
use HasWidgetShield;
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        $event_id = $this->filters['event_id'] ?? null;
        $user_id = $this->filters['user_id'] ?? null;

        $contracts = Contract::with('Stand')
            ->when($event_id, fn(Builder $query) => $query->whereIn('event_id', $event_id))
            ->when($user_id, fn(Builder $query) => $query->whereIn('seller', $user_id))
            ->when($startDate, fn(Builder $query) => $query->whereDate('contract_date', '>=', $startDate))
            ->when($endDate, fn(Builder $query) => $query->whereDate('contract_date', '<=', $endDate))
            ->whereHas('Stand', fn($q) => $q->whereNotNull('space'))
            ->get();

        // Calculate statistics
        $totalSpace = 0;
        $signedPaidSpace = 0;
        $spaceValues = [];
        $contractCount = 0;

        foreach ($contracts as $contract) {
            if ($contract->Stand && $contract->Stand->space) {
                $space = (float) $contract->Stand->space;
                $totalSpace += $space;

                if ($contract->status === Contract::STATUS_SIGNED_PAID) {
                    $signedPaidSpace += $space;
                }

                $spaceValues[] = $space;
                $contractCount++;
            }
        }

        // Calculate statistics
        $averageSpace = $contractCount > 0 ? $totalSpace / $contractCount : 0;
        $maxSpace = !empty($spaceValues) ? max($spaceValues) : 0;
        $minSpace = !empty($spaceValues) ? min($spaceValues) : 0;
        $utilizationRate = $totalSpace > 0 ? ($signedPaidSpace / $totalSpace) * 100 : 0;

        return [
            Stat::make('Total Space', number_format($totalSpace, 1) . ' m²')
                ->description('All contracts with space')
                ->descriptionIcon('heroicon-o-square-3-stack-3d')
                ->color('primary')
                ->chart($this->generateSpaceTrend()),

            Stat::make('Average Space', number_format($averageSpace, 1) . ' m²')
                ->description('Per contract')
                ->descriptionIcon('heroicon-o-calculator')
                ->color($averageSpace > 0 ? 'success' : 'gray')
                ->chart([10, 15, 20, 18, 22, 25, 23]),

            Stat::make('Utilization Rate', number_format($utilizationRate, 1) . '%')
                ->description('Signed & Paid / Total')
                ->descriptionIcon($utilizationRate >= 50 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
                ->color($utilizationRate >= 75 ? 'success' : ($utilizationRate >= 50 ? 'warning' : 'danger'))
                ->chart([30, 40, 50, 60, 70, 80, 75]),

            Stat::make('Space Range', number_format($minSpace, 0) . ' - ' . number_format($maxSpace, 0) . ' m²')
                ->description('Min - Max')
                ->descriptionIcon('heroicon-o-arrows-pointing-out')
                ->color('info')
                ->chart([$minSpace, $averageSpace, $maxSpace]),
        ];
    }

    private function generateSpaceTrend(): array
    {
        // Generate sample trend data
        return array_map(fn($i) => rand(50, 200), range(1, 7));
    }
}
