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
                            ->multiple()
                            ->live()
                            ->afterStateUpdated(fn () => $this->dispatch('filtersUpdated')),

                        Select::make('user_id')
                            ->label('Filter By User(s)')
                            ->options(User::all()->pluck('name', 'id'))
                            ->multiple()
                            ->live()
                            ->afterStateUpdated(fn () => $this->dispatch('filtersUpdated')),

                        DatePicker::make('startDate')
                            ->live()
                            ->afterStateUpdated(fn () => $this->dispatch('filtersUpdated')),

                        DatePicker::make('endDate')
                            ->live()
                            ->afterStateUpdated(fn () => $this->dispatch('filtersUpdated')),
                        // ...
                    ])
                    ->columns(4),
            ]);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // Empty for now - widgets moved to main section
        ];
    }

    public function getWidgets(): array
    {
        return [
            // Statistics widgets at the top
            ContractSummaryStats::class,
            SpaceStatsOverview::class,

            // Chart widgets
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
