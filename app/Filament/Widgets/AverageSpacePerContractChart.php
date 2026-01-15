<?php

namespace App\Filament\Widgets;

use App\Models\Contract;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;

class AverageSpacePerContractChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Average Space per Contract';
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
            ->get();

        // Group contracts by status
        $statusGroups = [
            Contract::STATUS_DRAFT => ['total_space' => 0, 'count' => 0],
            Contract::STATUS_INTERESTED => ['total_space' => 0, 'count' => 0],
            Contract::STATUS_SIGNED_NOT_PAID => ['total_space' => 0, 'count' => 0],
            Contract::STATUS_SIGNED_PAID => ['total_space' => 0, 'count' => 0],
        ];

        foreach ($contracts as $contract) {
            if ($contract->Stand && $contract->Stand->space) {
                $space = (float) $contract->Stand->space;
                $status = $contract->status;

                if (isset($statusGroups[$status])) {
                    $statusGroups[$status]['total_space'] += $space;
                    $statusGroups[$status]['count']++;
                }
            }
        }

        // Calculate averages
        $averages = [];
        foreach ($statusGroups as $status => $data) {
            $averages[] = $data['count'] > 0 ? $data['total_space'] / $data['count'] : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Average Space (m²)',
                    'data' => $averages,
                    'backgroundColor' => [
                        'rgba(107, 114, 128, 0.8)',     // Gray - Draft
                        'rgba(14, 165, 233, 0.8)',      // Blue - Interested
                        'rgba(245, 158, 11, 0.8)',      // Amber - Signed Not Paid
                        'rgba(16, 185, 129, 0.8)',      // Green - Signed Paid
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
        return 'bar';
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
                        'callback' => 'function(value) {
                            return value.toLocaleString("en-US", {
                                minimumFractionDigits: 1,
                                maximumFractionDigits: 1
                            }) + " m²";
                        }'
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'Average Space (m²)',
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
                //             return label + ": " + value.toLocaleString("en-US", {
                //                 minimumFractionDigits: 2,
                //                 maximumFractionDigits: 2
                //             }) + " m²";
                //         }',
                //         'afterLabel' => 'function(context) {
                //             let statusLabels = ["Draft", "Interested", "Signed (Not Paid)", "Signed (Paid)"];
                //             let status = statusLabels[context.dataIndex];
                //             return "Average per " + status + " contract";
                //         }'
                //     ]
                // ],
            ],
        ];
    }

    public static function canView(): bool
    {
        return true;
    }
}
