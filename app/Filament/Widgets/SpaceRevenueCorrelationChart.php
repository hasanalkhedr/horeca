<?php

namespace App\Filament\Widgets;

use App\Models\Contract;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;

class SpaceRevenueCorrelationChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Space vs Revenue Correlation';
    protected static ?string $maxHeight = '450px';
    protected static ?string $pollingInterval = null;

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        $event_id = $this->filters['event_id'] ?? null;
        $user_id = $this->filters['user_id'] ?? null;

        // Get contracts with space and revenue
        $contracts = Contract::with(['Stand', 'Report.Currency'])
            ->when($event_id, fn(Builder $query) => $query->whereIn('event_id', $event_id))
            ->when($user_id, fn(Builder $query) => $query->whereIn('seller', $user_id))
            ->when($startDate, fn(Builder $query) => $query->whereDate('contract_date', '>=', $startDate))
            ->when($endDate, fn(Builder $query) => $query->whereDate('contract_date', '<=', $endDate))
            ->whereNotNull('net_total')
            ->where('net_total', '>', 0)
            ->whereHas('Stand', fn($q) => $q->whereNotNull('space'))
            ->get();

        // Prepare data points for scatter plot
        $dataPoints = [];
        $statusLabels = [];

        $statusColorMap = [
            Contract::STATUS_DRAFT => 'rgba(107, 114, 128, 0.8)',
            Contract::STATUS_INTERESTED => 'rgba(14, 165, 233, 0.8)',
            Contract::STATUS_SIGNED_NOT_PAID => 'rgba(245, 158, 11, 0.8)',
            Contract::STATUS_SIGNED_PAID => 'rgba(16, 185, 129, 0.8)',
        ];

        foreach ($contracts as $contract) {
            if ($contract->Stand && $contract->Stand->space) {
                $space = (float) $contract->Stand->space;
                $revenue = $contract->net_total;

                // Convert to USD if needed
                $rateToUSD = $contract->Report->Currency->rate_to_usd ?? 1;
                $revenueUSD = $revenue * $rateToUSD;

                if ($space > 0 && $revenueUSD > 0) {
                    $dataPoints[] = [
                        'x' => $space,
                        'y' => $revenueUSD,
                        'status' => $contract->status,
                        'contract_no' => $contract->contract_no,
                        'status_display' => $contract->getStatusDisplayAttribute(),
                    ];

                    $statusLabels[] = $contract->getStatusDisplayAttribute();
                }
            }
        }

        // Group by status for better visualization
        $statusGroups = [];
        foreach ($dataPoints as $point) {
            $status = $point['status'];
            if (!isset($statusGroups[$status])) {
                $statusGroups[$status] = [
                    'label' => $point['status_display'],
                    'data' => [],
                    'backgroundColor' => $statusColorMap[$status] ?? 'rgba(156, 163, 175, 0.8)',
                    'borderColor' => str_replace('0.8', '1', $statusColorMap[$status] ?? 'rgb(156, 163, 175)'),
                    'borderWidth' => 1,
                    'pointRadius' => 6,
                    'pointHoverRadius' => 8,
                ];
            }
            $statusGroups[$status]['data'][] = [
                'x' => $point['x'],
                'y' => $point['y'],
                'contract_no' => $point['contract_no'],
            ];
        }

        return [
            'datasets' => array_values($statusGroups),
        ];
    }

    protected function getType(): string
    {
        return 'scatter';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'scales' => [
                'x' => [
                    'type' => 'linear',
                    'position' => 'bottom',
                    'title' => [
                        'display' => true,
                        'text' => 'Space (m²)',
                        'font' => [
                            'weight' => 'bold',
                        ],
                    ],
                    'ticks' => [
                        'callback' => 'function(value) {
                            return value.toLocaleString("en-US", {
                                minimumFractionDigits: 0,
                                maximumFractionDigits: 0
                            }) + " m²";
                        }'
                    ],
                    'grid' => [
                        'color' => 'rgba(0, 0, 0, 0.1)',
                    ],
                ],
                'y' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Revenue (USD)',
                        'font' => [
                            'weight' => 'bold',
                        ],
                    ],
                    'ticks' => [
                        'callback' => 'function(value) {
                            if (value >= 1000000) {
                                return "$" + (value / 1000000).toFixed(1) + "M";
                            } else if (value >= 1000) {
                                return "$" + (value / 1000).toFixed(1) + "K";
                            }
                            return "$" + value.toLocaleString("en-US", {
                                minimumFractionDigits: 0,
                                maximumFractionDigits: 0
                            });
                        }'
                    ],
                    'grid' => [
                        'color' => 'rgba(0, 0, 0, 0.1)',
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'top',
                    'labels' => [
                        'boxWidth' => 12,
                        'padding' => 15,
                        'usePointStyle' => true,
                    ],
                ],
                // 'tooltip' => [
                //     'callbacks' => [
                //         'label' => 'function(context) {
                //             let point = context.raw;
                //             return [
                //                 "Contract: " + (point.contract_no || "N/A"),
                //                 "Space: " + point.x.toLocaleString("en-US", {
                //                     minimumFractionDigits: 1,
                //                     maximumFractionDigits: 1
                //                 }) + " m²",
                //                 "Revenue: $" + point.y.toLocaleString("en-US", {
                //                     minimumFractionDigits: 2,
                //                     maximumFractionDigits: 2
                //                 }),
                //                 "Status: " + context.dataset.label
                //             ];
                //         }',
                //         'title' => 'function(tooltipItems) {
                //             // Show dataset label as title
                //             if (tooltipItems.length > 0) {
                //                 return tooltipItems[0].dataset.label;
                //             }
                //             return "";
                //         }'
                //     ],
                //     'backgroundColor' => 'rgba(0, 0, 0, 0.8)',
                //     'titleColor' => '#fff',
                //     'bodyColor' => '#fff',
                //     'padding' => 12,
                //     'cornerRadius' => 6,
                // ],
            ],
        ];
    }

    public static function canView(): bool
    {
        return true;
    }
}
