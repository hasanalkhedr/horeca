<?php

namespace App\Filament\Widgets;

use App\Models\Contract;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;

class ContractCountsChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Contracts by Status';
    protected static ?string $maxHeight = '400px';
    protected static ?string $pollingInterval = null;

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        $event_id = $this->filters['event_id'] ?? null;
        $user_id = $this->filters['user_id'] ?? null;

        $contracts = Contract::query()
            ->when($event_id, fn(Builder $query) => $query->whereIn('event_id', $event_id))
            ->when($user_id, fn(Builder $query) => $query->whereIn('seller', $user_id))
            ->when($startDate, fn(Builder $query) => $query->whereDate('contract_date', '>=', $startDate))
            ->when($endDate, fn(Builder $query) => $query->whereDate('contract_date', '<=', $endDate))
            ->get();

        $counts = [
            $contracts->where('status', Contract::STATUS_DRAFT)->count(),
            $contracts->where('status', Contract::STATUS_INTERESTED)->count(),
            $contracts->where('status', Contract::STATUS_SIGNED_NOT_PAID)->count(),
            $contracts->where('status', Contract::STATUS_SIGNED_PAID)->count(),
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Number of Contracts',
                    'data' => $counts,
                    'backgroundColor' => [
                        'rgb(107, 114, 128)', // Gray - Draft
                        'rgb(14, 165, 233)',  // Blue - Interested
                        'rgb(245, 158, 11)',  // Amber - Signed Not Paid
                        'rgb(16, 185, 129)',  // Green - Signed Paid
                    ],
                    'borderColor' => [
                        'rgb(75, 85, 99)',
                        'rgb(2, 132, 199)',
                        'rgb(217, 119, 6)',
                        'rgb(5, 150, 105)',
                    ],
                    'borderWidth' => 2,
                    'borderRadius' => 4,
                ],
            ],
            'labels' => ['Draft', 'Interested', 'Signed (Not Paid)', 'Signed (Paid)'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                        'stepSize' => 1,
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'Number of Contracts',
                        'font' => [
                            'weight' => 'bold',
                        ],
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                // 'tooltip' => [
                //     'backgroundColor' => 'rgba(0, 0, 0, 0.8)',
                //     'titleColor' => '#fff',
                //     'bodyColor' => '#fff',
                //     'padding' => 12,
                //     'cornerRadius' => 6,
                //     'callbacks' => [
                //         'label' => 'function(context) {
                //             let value = context.raw;
                //             let label = context.dataset.label || "";
                //             return label + ": " + value + " contract" + (value !== 1 ? "s" : "");
                //         }'
                //     ]
                // ],
            ],
        ];
    }

    public static function canView(): bool
    {
        return true; // Or add your authorization logic
    }
}
