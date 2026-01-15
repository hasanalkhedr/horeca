<?php

namespace App\Filament\Widgets;

use App\Models\Stand;
use App\Models\Contract;
use App\Models\Event;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class FilterableStandsContractsWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.widgets.filterable-stands-contracts-widget';

    protected int | string | array $columnSpan = 'full';

    public ?array $selectedEvents = [];
    public ?string $startDate = null;
    public ?string $endDate = null;

    public function mount(): void
    {
        $this->form->fill([
            'selectedEvents' => Event::limit(3)->pluck('id')->toArray(),
            'startDate' => now()->subMonths(3)->format('Y-m-d'),
            'endDate' => now()->format('Y-m-d'),
        ]);
    }

   protected function getFormSchema(): array
{
    return [
        Select::make('selectedEvents')
            ->label('Filter by Events')
            ->options(Event::pluck('name', 'id'))
            ->multiple()
            ->searchable()
            ->preload()
            ->placeholder('Select events...')
            ->live() // Add live() for real-time updates
            ->afterStateUpdated(fn() => $this->dispatch('filterUpdated'))
            ->columnSpan(2),

        DatePicker::make('startDate')
            ->label('From Date')
            ->live() // Add live() for real-time updates
            ->afterStateUpdated(fn() => $this->dispatch('filterUpdated'))
            ->columnSpan(1),

        DatePicker::make('endDate')
            ->label('To Date')
            ->live() // Add live() for real-time updates
            ->afterStateUpdated(fn() => $this->dispatch('filterUpdated'))
            ->columnSpan(1),
    ];
}

    public function getStandsData(): array
    {
        $query = Stand::query();

        // Apply event filter
        if (!empty($this->selectedEvents)) {
            $query->whereIn('event_id', $this->selectedEvents);
        }

        // Apply date filter
        if ($this->startDate) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }

        $stands = $query->get();

        $available = $stands->where('status', 'Available')->count();
        $sold = $stands->where('status', 'Sold')->count();
        $reserved = $stands->where('status', 'Reserved')->count();
        $merged = $stands->where('is_merged', true)->count();

        return [
            'labels' => ['Available', 'Sold', 'Reserved', 'Merged'],
            'data' => [$available, $sold, $reserved, $merged],
            'space' => [
                $stands->where('status', 'Available')->sum('space'),
                $stands->where('status', 'Sold')->sum('space'),
                $stands->where('status', 'Reserved')->sum('space'),
                $stands->where('is_merged', true)->sum('space'),
            ],
            'total' => $stands->count(),
            'totalSpace' => $stands->sum('space'),
            'colors' => [
                'rgb(16, 185, 129)', // Green - Available
                'rgb(239, 68, 68)',  // Red - Sold
                'rgb(245, 158, 11)', // Amber - Reserved
                'rgb(139, 92, 246)', // Purple - Merged
            ],
        ];
    }

    public function getContractsData(): array
    {
        $query = Contract::query();

        // Apply event filter
        if (!empty($this->selectedEvents)) {
            $query->whereIn('event_id', $this->selectedEvents);
        }

        // Apply date filter
        if ($this->startDate) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }

        $contracts = $query->get();

        $draft = $contracts->where('status', 'draft')->count();
        $interested = $contracts->where('status', 'INT')->count();
        $signedNotPaid = $contracts->where('status', 'S&NP')->count();
        $signedPaid = $contracts->where('status', 'S&P')->count();

        $draftAmount = $contracts->where('status', 'draft')->sum('net_total');
        $interestedAmount = $contracts->where('status', 'INT')->sum('net_total');
        $signedNotPaidAmount = $contracts->where('status', 'S&NP')->sum('net_total');
        $signedPaidAmount = $contracts->where('status', 'S&P')->sum('net_total');

        return [
            'labels' => ['Draft', 'Interested', 'Signed (Not Paid)', 'Signed (Paid)'],
            'counts' => [$draft, $interested, $signedNotPaid, $signedPaid],
            'amounts' => [$draftAmount, $interestedAmount, $signedNotPaidAmount, $signedPaidAmount],
            'total' => $contracts->count(),
            'totalAmount' => $contracts->sum('net_total'),
            'colors' => [
                'rgb(107, 114, 128)', // Gray - Draft
                'rgb(14, 165, 233)',  // Blue - Interested
                'rgb(245, 158, 11)',  // Amber - Signed Not Paid
                'rgb(16, 185, 129)',  // Green - Signed Paid
            ],
        ];
    }

    public function getSelectedEventNames(): array
    {
        if (empty($this->selectedEvents)) {
            return ['All Events'];
        }

        return Event::whereIn('id', $this->selectedEvents)
            ->pluck('name')
            ->toArray();
    }

    public function getFilterSummary(): string
    {
        $summary = [];

        if (!empty($this->selectedEvents)) {
            $summary[] = count($this->selectedEvents) . ' event(s) selected';
        } else {
            $summary[] = 'All events';
        }

        if ($this->startDate) {
            $summary[] = 'from ' . Carbon::parse($this->startDate)->format('M d, Y');
        }

        if ($this->endDate) {
            $summary[] = 'to ' . Carbon::parse($this->endDate)->format('M d, Y');
        }

        return implode(' • ', $summary);
    }
}
