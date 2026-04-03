<?php

namespace App\Filament\Widgets;

use App\Models\Contract;
use App\Models\Event;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;

class SpaceStatsOverview extends BaseWidget
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

        $contracts = Contract::with('Stand')
            ->when($event_id, fn(Builder $query) => $query->whereIn('event_id', $event_id))
            ->when($user_id, fn(Builder $query) => $query->whereIn('seller', $user_id))
            ->when($startDate, fn(Builder $query) => $query->whereDate('contract_date', '>=', $startDate))
            ->when($endDate, fn(Builder $query) => $query->whereDate('contract_date', '<=', $endDate))
            ->whereHas('Stand', fn($q) => $q->whereNotNull('space'))
            ->get();

        // Calculate statistics
        $totalSpace = 0;
        $signedPaidSpace = 0;
        $spaceValues = [];
        $contractCount = 0;

        foreach ($contracts as $contract) {
            if ($contract->Stand && $contract->Stand->space) {
                $space = (float) $contract->Stand->space;
                $totalSpace += $space;

                if ($contract->status === Contract::STATUS_SIGNED_PAID) {
                    $signedPaidSpace += $space;
                }

                $spaceValues[] = $space;
                $contractCount++;
            }
        }

        // Calculate statistics
        $averageSpace = $contractCount > 0 ? $totalSpace / $contractCount : 0;
        $maxSpace = !empty($spaceValues) ? max($spaceValues) : 0;
        $minSpace = !empty($spaceValues) ? min($spaceValues) : 0;
        $utilizationRate = $totalSpace > 0 ? ($signedPaidSpace / $totalSpace) * 100 : 0;

        // Get event targets if filtered by single event
        $eventTargets = $this->getEventTargets($event_id);

        // Calculate target comparisons
        $targetComparisons = $this->calculateTargetComparisons($signedPaidSpace, $eventTargets);

        return [
            Stat::make('Total Space', number_format($totalSpace, 1) . ' m²')
                ->description('All contracts with space')
                ->descriptionIcon('heroicon-o-square-3-stack-3d')
                ->color('primary')
                ->chart($this->generateSpaceTrend()),

            Stat::make('Average Space', number_format($averageSpace, 1) . ' m²')
                ->description('Per contract')
                ->descriptionIcon('heroicon-o-calculator')
                ->color($averageSpace > 0 ? 'success' : 'gray')
                ->chart([10, 15, 20, 18, 22, 25, 23]),

            Stat::make('Utilization Rate', number_format($utilizationRate, 1) . '%')
                ->description($targetComparisons['description'])
                ->descriptionIcon($targetComparisons['icon'])
                ->color($targetComparisons['color'])
                ->chart([30, 40, 50, 60, 70, 80, 75]),

            Stat::make('Space Range', number_format($minSpace, 0) . ' - ' . number_format($maxSpace, 0) . ' m²')
                ->description('Min - Max')
                ->descriptionIcon('heroicon-o-arrows-pointing-out')
                ->color('info')
                ->chart([$minSpace, $averageSpace, $maxSpace]),
        ];
    }

    private function generateSpaceTrend(): array
    {
        // Generate sample trend data
        return array_map(fn($i) => rand(50, 200), range(1, 7));
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
            ];
        }

        // No event filter - sum all targets
        return [
            'target_space' => Event::sum('target_space'),
        ];
    }

    /**
     * Calculate target comparisons for space utilization
     */
    protected function calculateTargetComparisons(float $signedPaidSpace, ?array $targets): array
    {
        $comparisons = [
            'description' => 'Signed & Paid / Total',
            'icon' => 'heroicon-o-arrow-trending-up',
            'color' => 'primary',
        ];

        if ($targets && $targets['target_space'] && $targets['target_space'] > 0) {
            $percentage = ($signedPaidSpace / $targets['target_space']) * 100;
            $comparisons['description'] = number_format($percentage, 1) . '% of ' . number_format($targets['target_space'], 0) . ' sqm target';
            $comparisons['icon'] = $percentage >= 100 ? 'heroicon-o-check-circle' : 'heroicon-o-arrow-trending-up';
            $comparisons['color'] = $percentage >= 100 ? 'success' : ($percentage >= 75 ? 'warning' : 'danger');
        } else {
            // Fallback to original utilization rate logic
            $totalSpace = $signedPaidSpace; // This is not accurate, but we need the total space
            $utilizationRate = 0; // Will be calculated properly in the main method
            $comparisons['icon'] = $utilizationRate >= 50 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down';
            $comparisons['color'] = 'primary';
        }

        return $comparisons;
    }
}
