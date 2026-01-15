<?php

namespace App\Filament\Widgets;

use App\Models\Contract;
use App\Models\Event;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;

class SpaceByEventChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Space Utilization by Event';
    protected static ?string $maxHeight = '450px';
    protected static ?string $pollingInterval = null;

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        $event_id = $this->filters['event_id'] ?? null;
        $user_id = $this->filters['user_id'] ?? null;

        // Get events with their contracts
        $events = Event::with(['contracts.Stand'])
            ->when($event_id, fn(Builder $query) => $query->whereIn('id', $event_id))
            ->get();

        $eventData = [];

        foreach ($events as $event) {
            $totalSpace = 0;
            $signedPaidSpace = 0;

            // Filter contracts based on date range and user
            $filteredContracts = $event->contracts
                ->when($user_id, fn($contracts) => $contracts->whereIn('seller', $user_id))
                ->when($startDate, fn($contracts) => $contracts->where('contract_date', '>=', $startDate))
                ->when($endDate, fn($contracts) => $contracts->where('contract_date', '<=', $endDate));

            foreach ($filteredContracts as $contract) {
                if ($contract->Stand && $contract->Stand->space) {
                    $space = (float) $contract->Stand->space;
                    $totalSpace += $space;

                    if ($contract->status === Contract::STATUS_SIGNED_PAID) {
                        $signedPaidSpace += $space;
                    }
                }
            }

            if ($totalSpace > 0) {
                $eventData[$event->name] = [
                    'total' => $totalSpace,
                    'signed_paid' => $signedPaidSpace,
                    'utilization_rate' => $totalSpace > 0 ? round(($signedPaidSpace / $totalSpace) * 100, 1) : 0,
                ];
            }
        }

        // Sort by total space (descending)
        arsort($eventData);

        // Prepare chart data
        $labels = array_keys($eventData);
        $totalSpaceData = array_column($eventData, 'total');
        $signedPaidData = array_column($eventData, 'signed_paid');

        return [
            'datasets' => [
                [
                    'label' => 'Total Space (m²)',
                    'data' => $totalSpaceData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.7)',
                    'borderColor' => 'rgb(29, 78, 216)',
                    'borderWidth' => 2,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Signed & Paid Space (m²)',
                    'data' => $signedPaidData,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.7)',
                    'borderColor' => 'rgb(5, 150, 105)',
                    'borderWidth' => 2,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Utilization Rate (%)',
                    'data' => array_column($eventData, 'utilization_rate'),
                    'borderColor' => 'rgb(245, 158, 11)',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'type' => 'line',
                    'yAxisID' => 'y1',
                    'tension' => 0.4,
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
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
                'x' => [
                    'stacked' => true,
                    'ticks' => [
                        'maxRotation' => 45,
                        'minRotation' => 45,
                    ],
                ],
                'y' => [
                    'stacked' => false,
                    'beginAtZero' => true,
                    'position' => 'left',
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
                ],
                'y1' => [
                    'beginAtZero' => true,
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => 'Utilization Rate (%)',
                        'font' => [
                            'weight' => 'bold',
                        ],
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                    'ticks' => [
                        'callback' => 'function(value) {
                            return value + "%";
                        }'
                    ],
                    'max' => 100,
                ],
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => [
                        'boxWidth' => 12,
                        'padding' => 15,
                    ],
                ],
                // 'tooltip' => [
                //     'mode' => 'index',
                //     'intersect' => false,
                //     'callbacks' => [
                //         'label' => 'function(context) {
                //             let label = context.dataset.label || "";
                //             let value = context.raw;

                //             if (context.datasetIndex === 2) {
                //                 // Utilization rate
                //                 return label + ": " + value.toFixed(1) + "%";
                //             } else {
                //                 // Space values
                //                 return label + ": " + value.toLocaleString("en-US", {
                //                     minimumFractionDigits: 1,
                //                     maximumFractionDigits: 1
                //                 }) + " m²";
                //             }
                //         }',
                //         'afterLabel' => 'function(context) {
                //             if (context.datasetIndex === 0) {
                //                 let eventName = context.label;
                //                 let totalSpace = context.raw;
                //                 let signedPaidSpace = context.chart.data.datasets[1].data[context.dataIndex];
                //                 let rate = context.chart.data.datasets[2].data[context.dataIndex];

                //                 return "Utilization: " + rate.toFixed(1) + "%\n" +
                //                        "Paid: " + signedPaidSpace.toLocaleString("en-US") + " m²\n" +
                //                        "Total: " + totalSpace.toLocaleString("en-US") + " m²";
                //             }
                //             return "";
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
