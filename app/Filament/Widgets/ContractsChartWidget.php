<?php

namespace App\Filament\Widgets;

use App\Models\Contract;
use Filament\Widgets\ChartWidget;

class ContractsChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Contracts by Status';
    protected static ?string $maxHeight = '400px';

    protected function getData(): array
    {
        $contracts = Contract::all();

        return [
            'datasets' => [
                [
                    'label' => 'Contracts',
                    'data' => [
                        $contracts->where('status', 'draft')->count(),
                        $contracts->where('status', 'INT')->count(),
                        $contracts->where('status', 'S&NP')->count(),
                        $contracts->where('status', 'S&P')->count(),
                    ],
                    'backgroundColor' => [
                        'rgb(107, 114, 128)', // Gray - Draft
                        'rgb(14, 165, 233)',  // Blue - Interested
                        'rgb(245, 158, 11)',  // Amber - Signed Not Paid
                        'rgb(16, 185, 129)',  // Green - Signed Paid
                    ],
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
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
