<?php

namespace App\Filament\Pages\Reports;

use App\Models\Event;
use App\Models\Contract;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Barryvdh\DomPDF\Facade\Pdf;

class EventsReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Reports';
    protected static string $view = 'filament.pages.reports.events-report';
    protected static ?int $navigationSort = 1;

    public ?array $data = [];
    public Collection $events;
    public array $summaryData = [];
    public array $chartData = [];

    public function mount(): void
    {
        $this->form->fill();
        $this->loadReportData();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Report Filters')
                    ->description('Filter the events to include in the report')
                    ->schema([
                        Select::make('event_ids')
                            ->label('Events')
                            ->options(Event::pluck('name', 'id'))
                            ->multiple()
                            ->live()
                            ->afterStateUpdated(fn () => $this->loadReportData())
                            ->helperText('Leave empty to include all events'),

                        DatePicker::make('start_date')
                            ->label('Start Date')
                            ->live()
                            ->afterStateUpdated(fn () => $this->loadReportData())
                            ->helperText('Filter contracts from this date'),

                        DatePicker::make('end_date')
                            ->label('End Date')
                            ->live()
                            ->afterStateUpdated(fn () => $this->loadReportData())
                            ->helperText('Filter contracts to this date'),
                    ])
                    ->columns(3),
            ])
            ->statePath('data');
    }

    protected function getActions(): array
    {
        return [
            // Action::make('print')
            //     ->label('Print')
            //     ->icon('heroicon-o-printer')
            //     ->action('printReport')
            //     ->color('primary'),

            Action::make('export_pdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->action('exportPDF')
                ->color('danger'),
        ];
    }

    public function loadReportData(): void
    {
        $this->events = $this->getFilteredEvents();
        $this->summaryData = $this->calculateSummaryData();
        $this->chartData = $this->prepareChartData();
    }

    private function getFilteredEvents(): Collection
    {
        $query = Event::with(['contracts.Stand', 'contracts.Report.Currency']);

        if (!empty($this->data['event_ids'])) {
            $query->whereIn('id', $this->data['event_ids']);
        }

        return $query->get();
    }

    private function calculateSummaryData(): array
    {
        $totalEvents = $this->events->count();
        $totalSpace = $this->events->sum('total_space');
        $totalTargetSpace = $this->events->sum('target_space');
        $totalTargetSpaceAmount = $this->events->sum('target_space_amount');
        $totalTargetSponsorAmount = $this->events->sum('target_sponsor_amount');

        // Calculate actual achievements
        $totalSoldSpace = 0;
        $totalSpaceAmount = 0;
        $totalSponsorAmount = 0;
        $totalContracts = 0;
        $totalAmount = 0;

        foreach ($this->events as $event) {
            foreach ($event->contracts as $contract) {
                $totalContracts++;
                $rateToUSD = $contract->Report->Currency->rate_to_usd ?? 1;

                if ($contract->Stand && $contract->Stand->space) {
                    $totalSoldSpace += $contract->Stand->space;
                }

                if ($contract->space_net) {
                    $totalSpaceAmount += $contract->space_net * $rateToUSD;
                }

                if ($contract->sponsor_net) {
                    $totalSponsorAmount += $contract->sponsor_net * $rateToUSD;
                }

                if ($contract->net_total) {
                    $totalAmount += $contract->net_total * $rateToUSD;
                }
            }
        }

        return [
            'total_events' => $totalEvents,
            'total_space' => $totalSpace,
            'total_sold_space' => $totalSoldSpace,
            'total_target_space' => $totalTargetSpace,
            'space_achievement_percent' => $totalTargetSpace > 0 ? round(($totalSoldSpace / $totalTargetSpace) * 100, 1) : 0,
            'total_contracts' => $totalContracts,
            'total_amount' => $totalAmount,
            'total_space_amount' => $totalSpaceAmount,
            'total_target_space_amount' => $totalTargetSpaceAmount,
            'space_amount_achievement_percent' => $totalTargetSpaceAmount > 0 ? round(($totalSpaceAmount / $totalTargetSpaceAmount) * 100, 1) : 0,
            'total_sponsor_amount' => $totalSponsorAmount,
            'total_target_sponsor_amount' => $totalTargetSponsorAmount,
            'sponsor_amount_achievement_percent' => $totalTargetSponsorAmount > 0 ? round(($totalSponsorAmount / $totalTargetSponsorAmount) * 100, 1) : 0,
        ];
    }

    private function prepareChartData(): array
    {
        $eventNames = [];
        $spaceAchievement = [];
        $spaceAmountAchievement = [];
        $sponsorAmountAchievement = [];

        foreach ($this->events as $event) {
            $eventNames[] = $event->name;

            $soldSpace = $event->contracts->sum(function ($contract) {
                return $contract->Stand ? $contract->Stand->space : 0;
            });
            $spaceAchievement[] = $event->target_space > 0 ? round(($soldSpace / $event->target_space) * 100, 1) : 0;

            $spaceAmount = $event->contracts->sum(function ($contract) {
                $rateToUSD = $contract->Report->Currency->rate_to_usd ?? 1;
                return ($contract->space_net ?? 0) * $rateToUSD;
            });
            $spaceAmountAchievement[] = $event->target_space_amount > 0 ? round(($spaceAmount / $event->target_space_amount) * 100, 1) : 0;

            $sponsorAmount = $event->contracts->sum(function ($contract) {
                $rateToUSD = $contract->Report->Currency->rate_to_usd ?? 1;
                return ($contract->sponsor_net ?? 0) * $rateToUSD;
            });
            $sponsorAmountAchievement[] = $event->target_sponsor_amount > 0 ? round(($sponsorAmount / $event->target_sponsor_amount) * 100, 1) : 0;
        }

        return [
            'event_names' => $eventNames,
            'space_achievement' => $spaceAchievement,
            'space_amount_achievement' => $spaceAmountAchievement,
            'sponsor_amount_achievement' => $sponsorAmountAchievement,
        ];
    }

    // public function printReport(): void
    // {
    //     $this->dispatch('print-window');
    // }

    public function exportPDF()
    {
        $pdf = Pdf::loadView('reports.events-pdf', [
            'events' => $this->events,
            'summaryData' => $this->summaryData,
            'chartData' => $this->chartData,
            'filters' => $this->data,
        ])->setOptions(['defaultFont' => 'Arial']);

        Notification::make()
            ->title('Events Report Exported')
            ->body('The events report has been exported as PDF.')
            ->success()
            ->send();

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'events-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function getAchievementColor(float $percentage): string
    {
        if ($percentage >= 100) return 'success';
        if ($percentage >= 75) return 'warning';
        return 'danger';
    }

    public function getAchievementIcon(float $percentage): string
    {
        if ($percentage >= 100) return 'heroicon-o-check-circle';
        if ($percentage >= 75) return 'heroicon-o-clock';
        return 'heroicon-o-exclamation-triangle';
    }
}
