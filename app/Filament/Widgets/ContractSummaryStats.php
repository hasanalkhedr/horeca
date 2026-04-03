<?php

namespace App\Filament\Widgets;

use App\Models\Contract;
use App\Models\Event;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;

class ContractSummaryStats extends BaseWidget
{
    use InteractsWithPageFilters;
use HasWidgetShield;
    protected static ?string $pollingInterval = null;

    protected $listeners = ['filtersUpdated' => 'refreshWidget'];

    public function refreshWidget(): void
    {
        $this->dispatch('$refresh');
    }

    // Override the filters property to get them from the page
    public function getFilters(): array
    {
        // Try to get filters from the parent page
        if (method_exists($this, 'getLivewire')) {
            $livewire = $this->getLivewire();
            if ($livewire && method_exists($livewire, 'filters')) {
                return $livewire->filters;
            }
        }

        return $this->filters ?? [];
    }

    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        $event_id = $this->filters['event_id'] ?? null;
        $user_id = $this->filters['user_id'] ?? null;

        $query = Contract::query()
            ->when($event_id, fn(Builder $query) => $query->whereIn('event_id', $event_id))
            ->when($user_id, fn(Builder $query) => $query->whereIn('seller', $user_id))
            ->when($startDate, fn(Builder $query) => $query->whereDate('contract_date', '>=', $startDate))
            ->when($endDate, fn(Builder $query) => $query->whereDate('contract_date', '<=', $endDate));

        // Get counts
        $totalContracts = $query->count();
        $interestedCount = $query->clone()->where('status', Contract::STATUS_INTERESTED)->count();
        $signedCount = $query->clone()->whereIn('status', [
            Contract::STATUS_SIGNED_NOT_PAID,
            Contract::STATUS_SIGNED_PAID,
        ])->count();
        $finalizedCount = $query->clone()->whereIn('status', [
            Contract::STATUS_SIGNED_PAID,
            Contract::STATUS_PAID_TROC,
            Contract::STATUS_SPONSOR,
        ])->count();
        $activeCount = $query->clone()->whereIn('status', [
            Contract::STATUS_INTERESTED,
            Contract::STATUS_SIGNED_NOT_PAID,
            Contract::STATUS_SIGNED_PAID,
            Contract::STATUS_FREE_FROM_HS,
            Contract::STATUS_PAID_TROC,
            Contract::STATUS_ON_HOLD,
            Contract::STATUS_ON_SITE_FREE,
            Contract::STATUS_ANIMATION,
            Contract::STATUS_SPONSOR,
        ])->count();

        // Get total amount in USD
        $totalAmount = Contract::with(['Report.Currency'])
            ->when($event_id, fn(Builder $query) => $query->whereIn('event_id', $event_id))
            ->when($user_id, fn(Builder $query) => $query->whereIn('seller', $user_id))
            ->when($startDate, fn(Builder $query) => $query->whereDate('contract_date', '>=', $startDate))
            ->when($endDate, fn(Builder $query) => $query->whereDate('contract_date', '<=', $endDate))
            ->whereNotNull('net_total')
            ->get()
            ->sum(function($contract) {
                $rateToUSD = $contract->Report->Currency->rate_to_usd ?? 1;
                return $contract->net_total * $rateToUSD;
            });

        // Get space and sponsor amounts
        $spaceAmount = Contract::with(['Report.Currency'])
            ->when($event_id, fn(Builder $query) => $query->whereIn('event_id', $event_id))
            ->when($user_id, fn(Builder $query) => $query->whereIn('seller', $user_id))
            ->when($startDate, fn(Builder $query) => $query->whereDate('contract_date', '>=', $startDate))
            ->when($endDate, fn(Builder $query) => $query->whereDate('contract_date', '<=', $endDate))
            ->whereNotNull('space_net')
            ->get()
            ->sum(function($contract) {
                $rateToUSD = $contract->Report->Currency->rate_to_usd ?? 1;
                return $contract->space_net * $rateToUSD;
            });

        $sponsorAmount = Contract::with(['Report.Currency'])
            ->when($event_id, fn(Builder $query) => $query->whereIn('event_id', $event_id))
            ->when($user_id, fn(Builder $query) => $query->whereIn('seller', $user_id))
            ->when($startDate, fn(Builder $query) => $query->whereDate('contract_date', '>=', $startDate))
            ->when($endDate, fn(Builder $query) => $query->whereDate('contract_date', '<=', $endDate))
            ->whereNotNull('sponsor_net')
            ->get()
            ->sum(function($contract) {
                $rateToUSD = $contract->Report->Currency->rate_to_usd ?? 1;
                return $contract->sponsor_net * $rateToUSD;
            });

        // Get total space
        $totalSpace = Contract::with('Stand')
            ->when($event_id, fn(Builder $query) => $query->whereIn('event_id', $event_id))
            ->when($user_id, fn(Builder $query) => $query->whereIn('seller', $user_id))
            ->when($startDate, fn(Builder $query) => $query->whereDate('contract_date', '>=', $startDate))
            ->when($endDate, fn(Builder $query) => $query->whereDate('contract_date', '<=', $endDate))
            ->whereHas('Stand', fn($q) => $q->whereNotNull('space'))
            ->get()
            ->sum(function($contract) {
                return $contract->Stand ? $contract->Stand->space : 0;
            });

        // Get event targets if filtered by single event
        $eventTargets = $this->getEventTargets($event_id);

        // Calculate target comparisons
        $targetComparisons = $this->calculateTargetComparisons($totalSpace, $spaceAmount, $sponsorAmount, $eventTargets);

        return [
            Stat::make('Total Contracts', $totalContracts)
                ->description('All contracts')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('primary')
                ->chart([7, 3, 5, 8, 10, 12, 15])
                ->extraAttributes(['class' => 'cursor-pointer']),

            Stat::make('Active Contracts', $activeCount)
                ->description($totalContracts > 0 ? round(($activeCount / $totalContracts) * 100, 1) . '% of total' : '0%')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color($activeCount > 0 ? 'success' : 'gray')
                ->chart([1, 2, 3, 4, 5, 6, 7]),

            Stat::make('Finalized Contracts', $finalizedCount)
                ->description($activeCount > 0 ? round(($finalizedCount / $activeCount) * 100, 1) . '% of active' : '0%')
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color($finalizedCount > 0 ? 'success' : 'gray')
                ->chart([1, 2, 2, 3, 4, 5, 6]),

            Stat::make('Total Amount (USD)', '$' . number_format($totalAmount, 2))
                ->description($targetComparisons['total_amount_description'])
                ->descriptionIcon('heroicon-o-banknotes')
                ->color($targetComparisons['total_amount_color'])
                ->chart([1000, 2000, 3000, 4000, 5000, 6000, 7000]),

            Stat::make('Space Achieved', number_format($totalSpace, 0) . ' sqm')
                ->description($targetComparisons['space_description'])
                ->descriptionIcon('heroicon-o-square-3-stack-3d')
                ->color($targetComparisons['space_color'])
                ->chart([50, 100, 150, 200, 250, 300, 350]),

            Stat::make('Space Amount (USD)', '$' . number_format($spaceAmount, 2))
                ->description($targetComparisons['space_amount_description'])
                ->descriptionIcon('heroicon-o-banknotes')
                ->color($targetComparisons['space_amount_color'])
                ->chart([5000, 10000, 15000, 20000, 25000, 30000, 35000]),

            Stat::make('Sponsor Amount (USD)', '$' . number_format($sponsorAmount, 2))
                ->description($targetComparisons['sponsor_amount_description'])
                ->descriptionIcon('heroicon-o-star')
                ->color($targetComparisons['sponsor_amount_color'])
                ->chart([1000, 2000, 3000, 4000, 5000, 6000, 7000]),
        ];
    }

    /**
     * Get event targets based on current filters
     */
    protected function getEventTargets($event_id): ?array
    {
        if ($event_id && is_array($event_id) && !empty($event_id)) {
            // Get sum of targets for selected events
            $events = Event::whereIn('id', $event_id)->get();
            return [
                'target_space' => $events->sum('target_space'),
                'target_space_amount' => $events->sum('target_space_amount'),
                'target_sponsor_amount' => $events->sum('target_sponsor_amount'),
            ];
        }

        // No event filter - sum all targets
        return [
            'target_space' => Event::sum('target_space'),
            'target_space_amount' => Event::sum('target_space_amount'),
            'target_sponsor_amount' => Event::sum('target_sponsor_amount'),
        ];
    }

    /**
     * Calculate target comparisons
     */
    protected function calculateTargetComparisons(float $achievedSpace, float $achievedSpaceAmount, float $achievedSponsorAmount, ?array $targets): array
    {
        $comparisons = [
            'total_amount_description' => 'Converted to USD',
            'total_amount_color' => 'warning',
            'space_description' => 'All contracts with space',
            'space_color' => 'primary',
            'space_amount_description' => 'Space amount achieved',
            'space_amount_color' => 'success',
            'sponsor_amount_description' => 'Sponsor amount achieved',
            'sponsor_amount_color' => 'warning',
        ];

        if ($targets) {
            // Calculate total target amount
            $totalTargetAmount = ($targets['target_space_amount'] ?? 0) + ($targets['target_sponsor_amount'] ?? 0);
            $totalAchievedAmount = $achievedSpaceAmount + $achievedSponsorAmount;

            // Total amount comparison
            if ($totalTargetAmount > 0) {
                $percentage = ($totalAchievedAmount / $totalTargetAmount) * 100;
                $comparisons['total_amount_description'] = number_format($percentage, 1) . '% of $' . number_format($totalTargetAmount, 0) . ' target';
                $comparisons['total_amount_color'] = $percentage >= 100 ? 'success' : ($percentage >= 75 ? 'warning' : 'danger');
            }

            // Space comparison
            if ($targets['target_space'] && $targets['target_space'] > 0) {
                $percentage = ($achievedSpace / $targets['target_space']) * 100;
                $comparisons['space_description'] = number_format($percentage, 1) . '% of ' . number_format($targets['target_space'], 0) . ' sqm target';
                $comparisons['space_color'] = $percentage >= 100 ? 'success' : ($percentage >= 75 ? 'warning' : 'danger');
            }

            // Space amount comparison
            if ($targets['target_space_amount'] && $targets['target_space_amount'] > 0) {
                $percentage = ($achievedSpaceAmount / $targets['target_space_amount']) * 100;
                $comparisons['space_amount_description'] = number_format($percentage, 1) . '% of $' . number_format($targets['target_space_amount'], 0) . ' target';
                $comparisons['space_amount_color'] = $percentage >= 100 ? 'success' : ($percentage >= 75 ? 'warning' : 'danger');
            }

            // Sponsor amount comparison
            if ($targets['target_sponsor_amount'] && $targets['target_sponsor_amount'] > 0) {
                $percentage = ($achievedSponsorAmount / $targets['target_sponsor_amount']) * 100;
                $comparisons['sponsor_amount_description'] = number_format($percentage, 1) . '% of $' . number_format($targets['target_sponsor_amount'], 0) . ' target';
                $comparisons['sponsor_amount_color'] = $percentage >= 100 ? 'success' : ($percentage >= 75 ? 'warning' : 'danger');
            }
        }

        return $comparisons;
    }
}
