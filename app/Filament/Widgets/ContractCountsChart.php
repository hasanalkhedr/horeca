<?php

namespace App\Filament\Widgets;

use App\Models\Contract;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;

class ContractCountsChart extends ChartWidget
{
    use InteractsWithPageFilters;
use HasWidgetShield;
    protected static ?string $heading = 'Contracts by Status';
    protected static ?string $maxHeight = '400px';
    protected static ?string $pollingInterval = null;

    protected $listeners = ['filtersUpdated' => 'refreshWidget'];

    public function refreshWidget(): void
    {
        $this->dispatch('$refresh');
    }

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
            $contracts->where('status', Contract::STATUS_INTERESTED)->count(),
            $contracts->where('status', Contract::STATUS_SIGNED_NOT_PAID)->count(),
            $contracts->where('status', Contract::STATUS_SIGNED_PAID)->count(),
            $contracts->where('status', Contract::STATUS_CLOSED)->count(),
            $contracts->where('status', Contract::STATUS_FREE_FROM_HS)->count(),
            $contracts->where('status', Contract::STATUS_PAID_TROC)->count(),
            $contracts->where('status', Contract::STATUS_ON_HOLD)->count(),
            $contracts->where('status', Contract::STATUS_ON_SITE_FREE)->count(),
            $contracts->where('status', Contract::STATUS_ANIMATION)->count(),
            $contracts->where('status', Contract::STATUS_SPONSOR)->count(),
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Number of Contracts',
                    'data' => $counts,
                    'backgroundColor' => [
                        'rgb(14, 165, 233)',  // Blue - Interested
                        'rgb(245, 158, 11)',  // Amber - Signed Not Paid
                        'rgb(16, 185, 129)',  // Green - Signed Paid
                        'rgb(239, 68, 68)',   // Red - Closed
                        'rgb(59, 130, 246)',  // Indigo - Free From HS
                        'rgb(16, 185, 129)',  // Green - Paid Troc
                        'rgb(245, 158, 11)',  // Amber - On Hold
                        'rgb(14, 165, 233)',  // Blue - On Site Free
                        'rgb(168, 85, 247)',  // Purple - Animation
                        'rgb(16, 185, 129)',  // Green - Sponsor
                    ],
                    'borderColor' => [
                        'rgb(2, 132, 199)',
                        'rgb(217, 119, 6)',
                        'rgb(5, 150, 105)',
                        'rgb(220, 38, 38)',
                        'rgb(37, 99, 235)',
                        'rgb(5, 150, 105)',
                        'rgb(217, 119, 6)',
                        'rgb(2, 132, 199)',
                        'rgb(147, 51, 234)',
                        'rgb(5, 150, 105)',
                    ],
                    'borderWidth' => 2,
                    'borderRadius' => 4,
                ],
            ],
            'labels' => ['Interested', 'Signed (Not Paid)', 'Signed (Paid)', 'Closed', 'Free From HS', 'Paid Troc', 'On Hold', 'On Site Free', 'Animation', 'Sponsor'],
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
}
