<?php

namespace App\Filament\Widgets;

use App\Models\Contract;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;

class ContractAmountsUSDChart extends ChartWidget
{
    use InteractsWithPageFilters;
use HasWidgetShield;
    protected static ?string $heading = 'Contract Amounts in USD Equivalent';
    protected static ?string $maxHeight = '400px';
    protected static ?string $pollingInterval = null;

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        $event_id = $this->filters['event_id'] ?? null;
        $user_id = $this->filters['user_id'] ?? null;

        // Get contracts with currency conversion
        $contracts = Contract::with(['Report.Currency'])
            ->when($event_id, fn(Builder $query) => $query->whereIn('event_id', $event_id))
            ->when($user_id, fn(Builder $query) => $query->whereIn('seller', $user_id))
            ->when($startDate, fn(Builder $query) => $query->whereDate('contract_date', '>=', $startDate))
            ->when($endDate, fn(Builder $query) => $query->whereDate('contract_date', '<=', $endDate))
            ->whereNotNull('net_total')
            ->where('net_total', '>', 0)
            ->get();

        // Calculate amounts in USD equivalent
        $amountsInUSD = [
            Contract::STATUS_DRAFT => 0,
            Contract::STATUS_INTERESTED => 0,
            Contract::STATUS_SIGNED_NOT_PAID => 0,
            Contract::STATUS_SIGNED_PAID => 0,
        ];

        foreach ($contracts as $contract) {
            $currency = $contract->Report->Currency ?? null;
            $rateToUSD = $currency ? $currency->rate_to_usd : 1;
            $amountInUSD = $contract->net_total * $rateToUSD;

            if (isset($amountsInUSD[$contract->status])) {
                $amountsInUSD[$contract->status] += $amountInUSD;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Amount in USD',
                    'data' => [
                        $amountsInUSD[Contract::STATUS_DRAFT],
                        $amountsInUSD[Contract::STATUS_INTERESTED],
                        $amountsInUSD[Contract::STATUS_SIGNED_NOT_PAID],
                        $amountsInUSD[Contract::STATUS_SIGNED_PAID],
                    ],
                    'backgroundColor' => [
                        'rgba(107, 114, 128, 0.8)',
                        'rgba(14, 165, 233, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
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
                    'title' => [
                        'display' => true,
                        'text' => 'Amount in USD',
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
                //             return "Amount: " + new Intl.NumberFormat("en-US", {
                //                 style: "currency",
                //                 currency: "USD",
                //                 minimumFractionDigits: 2,
                //                 maximumFractionDigits: 2
                //             }).format(value);
                //         }'
                //     ]
                // ],
            ],
        ];
    }
}
