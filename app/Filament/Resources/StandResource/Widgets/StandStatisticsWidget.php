<?php

namespace App\Filament\Resources\StandResource\Widgets;

use App\Models\Stand;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StandStatisticsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {

    $stands = Stand::where('is_merged', false)->orWhere('parent_stand_id', null)->get();
        // Calculate total statistics
        $totalStands = $stands->count();
        $totalSpace = $stands->sum('space');

        // Calculate sold statistics
        $soldStands = $stands->where('status', 'Sold')->count();
        $soldSpace = $stands->where('status', 'Sold')->sum('space');
        $soldPercentage = $totalStands > 0 ? round(($soldStands / $totalStands) * 100, 1) : 0;
        $soldSpacePercentage = $totalSpace > 0 ? round(($soldSpace / $totalSpace) * 100, 1) : 0;

        // Calculate available statistics
        $availableStands = $stands->where('status', 'Available')->count();
        $availableSpace = $stands->where('status', 'Available')->sum('space');
        $availablePercentage = $totalStands > 0 ? round(($availableStands / $totalStands) * 100, 1) : 0;
        $availableSpacePercentage = $totalSpace > 0 ? round(($availableSpace / $totalSpace) * 100, 1) : 0;

        // Calculate reserved statistics
        $reservedStands = $stands->where('status', 'Reserved')->count();
        $reservedSpace = $stands->where('status', 'Reserved')->sum('space');

        // Calculate deductible statistics
        $deductibleStands = $stands->where('deductable', true)->count();
        $deductiblePercentage = $totalStands > 0 ? round(($deductibleStands / $totalStands) * 100, 1) : 0;

        return [
            // Sold Statistics
            Stat::make('Sold Space', number_format($soldSpace, 2) . ' sqm')
                ->description("{$soldStands} stands • {$soldSpacePercentage}% of total space")
                ->descriptionIcon('heroicon-o-check-badge')
                ->color('danger')
                ->chart($this->getStatusTrend('Sold')),

            // Stat::make('Sold Stands', $soldStands)
            //     ->description("{$soldPercentage}% of total stands")
            //     ->descriptionIcon('heroicon-o-map-pin')
            //     ->color('danger')
            //     ->chart($this->getStatusTrend('Sold', 'count')),

            // Available Statistics
            Stat::make('Available Space', number_format($availableSpace, 2) . ' sqm')
                ->description("{$availableStands} stands • {$availableSpacePercentage}% of total space")
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success')
                ->chart($this->getStatusTrend('Available')),

            // Stat::make('Available Stands', $availableStands)
            //     ->description("{$availablePercentage}% of total stands")
            //     ->descriptionIcon('heroicon-o-map-pin')
            //     ->color('success')
            //     ->chart($this->getStatusTrend('Available', 'count')),

            // Total Statistics
            Stat::make('Total Space', number_format($totalSpace, 2) . ' sqm')
                ->description("{$totalStands} total stands")
                ->descriptionIcon('heroicon-o-square-3-stack-3d')
                ->color('primary')
                ->chart($this->getTotalSpaceTrend()),

            // Other Statuses
            Stat::make('Reserved', $reservedStands)
                ->description(number_format($reservedSpace, 2) . ' sqm')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),

            // Deductible
            // Stat::make('Deductible Stands', $deductibleStands)
            //     ->description("{$deductiblePercentage}% of total")
            //     ->descriptionIcon('heroicon-o-receipt-percent')
            //     ->color('warning'),
        ];
    }

    /**
     * Get trend data for a specific status
     */
    protected function getStatusTrend(string $status, string $type = 'space'): array
    {
        $data = [];

        // Get last 7 days of data
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);

            if ($type === 'space') {
                $value = Stand::where('status', $status)
                    ->whereDate('created_at', '<=', $date)
                    ->sum('space');
            } else {
                $value = Stand::where('status', $status)
                    ->whereDate('created_at', '<=', $date)
                    ->count();
            }

            $data[] = $value;
        }

        return $data;
    }

    /**
     * Get trend data for total space
     */
    protected function getTotalSpaceTrend(): array
    {
        $data = [];

        // Get last 7 days of total space
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $value = Stand::where('is_merged', false)->orWhere('parent_stand_id', null)->whereDate('created_at', '<=', $date)->sum('space');
            $data[] = $value;
        }

        return $data;
    }
}
