<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AverageSpacePerContractChart;
use App\Filament\Widgets\ContractAmountsByCurrencyChart;
use App\Filament\Widgets\ContractAmountsUSDChart;
use App\Filament\Widgets\ContractCountsChart;
use App\Filament\Widgets\ContractSpaceByStatusChart;
use App\Filament\Widgets\ContractSummaryStats;
use App\Filament\Widgets\SpaceByEventChart;
use App\Filament\Widgets\SpaceDistributionChart;
use App\Filament\Widgets\SpaceRevenueCorrelationChart;
use App\Filament\Widgets\SpaceStatsOverview;
use App\Models\Event;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Pages\Page;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('event_id')
                            ->label('Filter By Event(s)')
                            ->options(Event::all()->pluck('name', 'id'))
                            ->multiple(),

                        Select::make('user_id')
                            ->label('Filter By User(s)')
                            ->options(User::all()->pluck('name', 'id'))
                            ->multiple(),


                        DatePicker::make('startDate'),
                        DatePicker::make('endDate'),
                        // ...
                    ])
                    ->columns(4),
            ]);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ContractSummaryStats::class,
            SpaceStatsOverview::class,
        ];
    }

    public function getWidgets(): array
    {
        return [
    ContractCountsChart::class,
    ContractAmountsByCurrencyChart::class,
    ContractAmountsUSDChart::class,
    ContractSpaceByStatusChart::class,
    SpaceByEventChart::class,
    SpaceDistributionChart::class,
    SpaceRevenueCorrelationChart::class,
    AverageSpacePerContractChart::class,
        ];
    }

}
