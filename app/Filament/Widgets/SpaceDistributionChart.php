<?php

namespace App\Filament\Widgets;

use App\Models\Contract;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;

class SpaceDistributionChart extends ChartWidget
{
    use InteractsWithPageFilters;
use HasWidgetShield;
    protected static ?string $heading = 'Space Distribution by Size Range';
    protected static ?string $maxHeight = '400px';
    protected static ?string $pollingInterval = null;

    protected function getData(): array
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

        // Define size ranges in square meters
        $sizeRanges = [
            '0-10 m²' => [0, 10],
            '11-25 m²' => [11, 25],
            '26-50 m²' => [26, 50],
            '51-100 m²' => [51, 100],
            '101-200 m²' => [101, 200],
            '201+ m²' => [201, PHP_FLOAT_MAX],
        ];

        $countByRange = array_fill_keys(array_keys($sizeRanges), 0);

        foreach ($contracts as $contract) {
            if ($contract->Stand && $contract->Stand->space) {
                $space = (float) $contract->Stand->space;

                foreach ($sizeRanges as $rangeLabel => $range) {
                    if ($space >= $range[0] && $space <= $range[1]) {
                        $countByRange[$rangeLabel]++;
                        break;
                    }
                }
            }
        }

        // Remove empty ranges for cleaner chart
        $filteredData = array_filter($countByRange, fn($count) => $count > 0);

        return [
            'datasets' => [
                [
                    'label' => 'Number of Contracts',
                    'data' => array_values($filteredData),
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.8)',   // Blue
                        'rgba(16, 185, 129, 0.8)',   // Green
                        'rgba(245, 158, 11, 0.8)',   // Amber
                        'rgba(239, 68, 68, 0.8)',    // Red
                        'rgba(139, 92, 246, 0.8)',   // Purple
                        'rgba(6, 182, 212, 0.8)',    // Cyan
                        'rgba(251, 191, 36, 0.8)',   // Yellow
                        'rgba(234, 88, 12, 0.8)',    // Orange
                    ],
                    'borderColor' => [
                        'rgb(29, 78, 216)',
                        'rgb(5, 150, 105)',
                        'rgb(217, 119, 6)',
                        'rgb(185, 28, 28)',
                        'rgb(109, 40, 217)',
                        'rgb(8, 145, 178)',
                        'rgb(202, 138, 4)',
                        'rgb(194, 65, 12)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => array_keys($filteredData),
        ];
    }

    protected function getType(): string
    {
        return 'pie'; // Pie chart works well for distribution
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'right',
                    'labels' => [
                        'boxWidth' => 12,
                        'padding' => 15,
                        'usePointStyle' => true,
                    ],
                ],
                // 'tooltip' => [
                //     'backgroundColor' => 'rgba(0, 0, 0, 0.8)',
                //     'titleColor' => '#fff',
                //     'bodyColor' => '#fff',
                //     'padding' => 12,
                //     'cornerRadius' => 6,
                //     'callbacks' => [
                //         'label' => 'function(context) {
                //             let label = context.label || "";
                //             let value = context.raw;
                //             let total = context.dataset.data.reduce((a, b) => a + b, 0);
                //             let percentage = Math.round((value / total) * 100);
                //             return label + ": " + value + " contracts (" + percentage + "%)";
                //         }'
                //     ]
                // ],
            ],
        ];
    }
}
