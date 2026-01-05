<?php

namespace App\Filament\Widgets;

use App\Models\Stand;
use Filament\Widgets\ChartWidget;

class StandsChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Stands Distribution';
    protected static ?string $maxHeight = '400px';

    protected function getData(): array
    {
        $stands = Stand::all();

        return [
            'datasets' => [
                [
                    'label' => 'Stands by Status',
                    'data' => [
                        $stands->where('status', 'Available')->count(),
                        $stands->where('status', 'Sold')->count(),
                        $stands->where('status', 'Reserved')->count(),
                        $stands->where('is_merged', true)->count(),
                    ],
                    'backgroundColor' => [
                        'rgb(16, 185, 129)', // Green - Available
                        'rgb(239, 68, 68)',  // Red - Sold
                        'rgb(245, 158, 11)', // Amber - Reserved
                        'rgb(139, 92, 246)', // Purple - Merged
                    ],
                ],
            ],
            'labels' => ['Available', 'Sold', 'Reserved', 'Merged'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
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
