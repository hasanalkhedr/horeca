<?php

namespace App\Filament\Widgets;

use App\Models\Contract;
use App\Models\Settings\Currency;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ContractAmountsByCurrencyChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Contract Amounts by Currency & Status';
    protected static ?string $maxHeight = '450px';
    protected static ?string $pollingInterval = null;

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        $event_id = $this->filters['event_id'] ?? null;
        $user_id = $this->filters['user_id'] ?? null;

        // Get contracts with their report currency information
        $contracts = Contract::with('Report.Currency')
            ->when($event_id, fn(Builder $query) => $query->whereIn('event_id', $event_id))
            ->when($user_id, fn(Builder $query) => $query->whereIn('seller', $user_id))
            ->when($startDate, fn(Builder $query) => $query->whereDate('contract_date', '>=', $startDate))
            ->when($endDate, fn(Builder $query) => $query->whereDate('contract_date', '<=', $endDate))
            ->whereNotNull('net_total')
            ->where('net_total', '>', 0)
            ->get();

        // Group contracts by currency and status
        $groupedContracts = [];
        $allCurrencies = [];
        $statuses = [
            Contract::STATUS_DRAFT,
            Contract::STATUS_INTERESTED,
            Contract::STATUS_SIGNED_NOT_PAID,
            Contract::STATUS_SIGNED_PAID,
        ];

        foreach ($contracts as $contract) {
            // Get currency from Report relationship
            $currencyCode = $contract->Report->Currency->CODE ?? 'USD'; // Default to USD if no currency

            if (!isset($groupedContracts[$currencyCode])) {
                $groupedContracts[$currencyCode] = [
                    Contract::STATUS_DRAFT => 0,
                    Contract::STATUS_INTERESTED => 0,
                    Contract::STATUS_SIGNED_NOT_PAID => 0,
                    Contract::STATUS_SIGNED_PAID => 0,
                ];
                $allCurrencies[$currencyCode] = $currencyCode;
            }

            // Sum net_total for the status and currency
            if (isset($groupedContracts[$currencyCode][$contract->status])) {
                $groupedContracts[$currencyCode][$contract->status] += $contract->net_total ?? 0;
            }
        }

        // Sort currencies alphabetically
        ksort($groupedContracts);
        ksort($allCurrencies);

        // Prepare datasets for each currency
        $datasets = [];
        $currencyColors = $this->getCurrencyColors();
        $colorIndex = 0;

        foreach ($groupedContracts as $currencyCode => $amountsByStatus) {
            $color = $currencyColors[$currencyCode] ?? $this->generateColor($colorIndex);

            $datasets[] = [
                'label' => $currencyCode,
                'data' => [
                    $amountsByStatus[Contract::STATUS_DRAFT],
                    $amountsByStatus[Contract::STATUS_INTERESTED],
                    $amountsByStatus[Contract::STATUS_SIGNED_NOT_PAID],
                    $amountsByStatus[Contract::STATUS_SIGNED_PAID],
                ],
                'backgroundColor' => $this->adjustOpacity($color, 0.7),
                'borderColor' => $color,
                'borderWidth' => 2,
                'borderRadius' => 4,
                'categoryPercentage' => 0.8,
                'barPercentage' => 0.9,
            ];

            $colorIndex++;
        }

        return [
            'datasets' => $datasets,
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
                'x' => [
                    'stacked' => true,
                    'grid' => [
                        'display' => false,
                    ],
                ],
                'y' => [
                    'stacked' => true,
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) {
                            if (value >= 1000000) {
                                return "$" + (value / 1000000).toFixed(1) + "M";
                            } else if (value >= 1000) {
                                return "$" + (value / 1000).toFixed(1) + "K";
                            }
                            return "$" + value;
                        }'
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'Total Amount',
                        'font' => [
                            'weight' => 'bold',
                        ],
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => [
                        'boxWidth' => 12,
                        'padding' => 15,
                        'usePointStyle' => true,
                    ],
                ],
                // 'tooltip' => [
                //     'mode' => 'index',
                //     'intersect' => false,
                //     'backgroundColor' => 'rgba(0, 0, 0, 0.8)',
                //     'titleColor' => '#fff',
                //     'bodyColor' => '#fff',
                //     'padding' => 12,
                //     'cornerRadius' => 6,
                //     'callbacks' => [
                //         'label' => 'function(context) {
                //             let datasetLabel = context.dataset.label || "";
                //             let value = context.raw;

                //             if (value === 0) return null;

                //             return datasetLabel + ": " +
                //                    new Intl.NumberFormat("en-US", {
                //                        style: "currency",
                //                        currency: datasetLabel,
                //                        minimumFractionDigits: 2,
                //                        maximumFractionDigits: 2
                //                    }).format(value);
                //         }',
                //         'footer' => 'function(tooltipItems) {
                //             let total = 0;
                //             tooltipItems.forEach(function(item) {
                //                 total += item.raw;
                //             });

                //             if (total > 0) {
                //                 return "Total: " + new Intl.NumberFormat("en-US", {
                //                     style: "currency",
                //                     currency: "USD",
                //                     minimumFractionDigits: 2,
                //                     maximumFractionDigits: 2
                //                 }).format(total);
                //             }
                //             return "";
                //         }'
                //     ]
                // ],
            ],
        ];
    }

    private function getCurrencyColors(): array
    {
        return [
            'USD' => 'rgb(16, 185, 129)',      // Green
            'EUR' => 'rgb(139, 92, 246)',      // Purple
            'GBP' => 'rgb(239, 68, 68)',       // Red
            'CAD' => 'rgb(245, 158, 11)',      // Amber
            'AED' => 'rgb(6, 182, 212)',       // Cyan (UAE Dirham)
            'SAR' => 'rgb(251, 191, 36)',      // Yellow (Saudi Riyal)
            'QAR' => 'rgb(249, 115, 22)',      // Orange (Qatari Riyal)
            'KWD' => 'rgb(168, 85, 247)',      // Deep Purple (Kuwaiti Dinar)
        ];
    }

    private function generateColor(int $index): string
    {
        $colors = [
            'rgb(59, 130, 246)',   // Blue
            'rgb(236, 72, 153)',   // Pink
            'rgb(8, 145, 178)',    // Teal
            'rgb(234, 88, 12)',    // Orange
            'rgb(101, 163, 13)',   // Lime
            'rgb(190, 24, 93)',    // Rose
        ];

        return $colors[$index % count($colors)];
    }

    private function adjustOpacity(string $rgb, float $opacity): string
    {
        // Convert rgb(r, g, b) to rgba(r, g, b, opacity)
        return str_replace('rgb(', 'rgba(', $rgb) . ', ' . $opacity . ')';
    }

    public static function canView(): bool
    {
        return true;
    }
}
