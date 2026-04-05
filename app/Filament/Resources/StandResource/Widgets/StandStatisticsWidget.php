<?php

namespace App\Filament\Resources\StandResource\Widgets;

use App\Models\Stand;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class StandStatisticsWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?string $pollingInterval = null;

    // Add this property to make the widget reactive to filter changes
    protected $listeners = ['refresh-widget' => 'onRefreshWidget'];

    // Store filter data from the page
    public $pageFilters = [];

    public function onRefreshWidget($filters = null): void
    {
        if ($filters) {
            $this->pageFilters = $filters;
        } else {
            // Clear stored filters when null is passed (filters cleared)
            $this->pageFilters = [];
        }
        // Trigger widget re-render
        $this->dispatch('$refresh');
    }

    protected function getStats(): array
    {
        // If no filters are set initially, try to get them from the request
        if (empty($this->pageFilters)) {
            $this->pageFilters = $this->getFiltersFromRequest();
        }

        // Get the filtered query using the filters from the page
        $query = $this->getFilteredQuery();

        $stands = $query->get();

        // Get event targets if filtered by single event
        $eventTargets = $this->getEventTargets();

        // Calculate total statistics
        $totalStands = $stands->count();
        $totalSpace = $stands->sum('space');

        // Calculate sold statistics
        $soldStands = $stands->where('status', 'Sold')->count();
        $soldSpace = $stands->where('status', 'Sold')->sum('space');
        $soldPercentage = $totalStands > 0 ? round(($soldStands / $totalStands) * 100, 1) : 0;
        $soldSpacePercentage = $totalSpace > 0 ? round(($soldSpace / $totalSpace) * 100, 1) : 0;

        // Calculate available statistics
        $availableStands = $stands->where('status', 'Available')->count();
        $availableSpace = $stands->where('status', 'Available')->sum('space') - $stands->where('deductable', false)->sum('space');
        $availablePercentage = $totalStands > 0 ? round(($availableStands / $totalStands) * 100, 1) : 0;
        $availableSpacePercentage = $totalSpace > 0 ? round(($availableSpace / $totalSpace) * 100, 1) : 0;

        // Calculate reserved statistics
        $reservedStands = $stands->where('status', 'Reserved')->count();
        $reservedSpace = $stands->where('status', 'Reserved')->sum('space');

        // Calculate deductible statistics
        $deductibleStands = $stands->where('deductable', true)->count();
        $deductiblePercentage = $totalStands > 0 ? round(($deductibleStands / $totalStands) * 100, 1) : 0;

        // Calculate target comparisons
        $targetComparisons = $this->calculateTargetComparisons($soldSpace, $eventTargets);

        return [
            // Sold Statistics
            Stat::make('Sold Space', number_format($soldSpace, 2) . ' sqm')
                ->description($targetComparisons['description'])
                ->descriptionIcon('heroicon-o-check-badge')
                ->color($targetComparisons['color'])
                ->chart($this->getStatusTrend('Sold')),

            // Available Statistics
            Stat::make('Available Space to Sell', number_format($availableSpace, 2) . ' sqm')
                ->description("{$availableStands} stands • {$availableSpacePercentage}% of total space")
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success')
                ->chart($this->getStatusTrend('Available')),

            // Total Statistics
            Stat::make('Total Area', number_format($totalSpace, 2) . ' sqm')
                ->description("{$totalStands} total stands")
                ->descriptionIcon('heroicon-o-square-3-stack-3d')
                ->color('primary')
                ->chart($this->getTotalSpaceTrend()),

            // Other Statuses
            Stat::make('Reserved', $reservedStands)
                ->description(number_format($reservedSpace, 2) . ' sqm')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),
        ];
    }

    /**
     * Get filters from request as fallback
     */
    protected function getFiltersFromRequest(): array
    {
        try {
            $requestData = request()->all();

            if (isset($requestData['components']) && is_array($requestData['components'])) {
                foreach ($requestData['components'] as $component) {
                    if (!isset($component['snapshot'])) {
                        continue;
                    }

                    $snapshot = is_array($component['snapshot'])
                        ? $component['snapshot']
                        : json_decode($component['snapshot'], true);

                    if (!is_array($snapshot)) {
                        continue;
                    }

                    // Check for table filters (primary)
                    if (isset($snapshot['data']['tableFilters'])) {
                        return $snapshot['data']['tableFilters'];
                    }

                    // Check for form data (filtersForm) as fallback
                    if (isset($snapshot['data']['filters'])) {
                        return $snapshot['data']['filters'];
                    }
                }
            }
        } catch (\Exception $e) {
            // Error accessing request filters
        }

        return [];
    }

    /**
     * Get trend data for a specific status
     */
    protected function getStatusTrend(string $status, string $type = 'space'): array
    {
        $data = [];

        // Get last 7 days of data
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);

            if ($type === 'space') {
                $value = $this->getFilteredQuery()
                    ->where('status', $status)
                    ->whereDate('created_at', '<=', $date)
                    ->sum('space');
            } else {
                $value = $this->getFilteredQuery()
                    ->where('status', $status)
                    ->whereDate('created_at', '<=', $date)
                    ->count();
            }

            $data[] = $value;
        }

        return $data;
    }

    /**
     * Get trend data for total space
     */
    protected function getTotalSpaceTrend(): array
    {
        $data = [];

        // Get last 7 days of total space
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $value = $this->getFilteredQuery()
                ->whereDate('created_at', '<=', $date)
                ->sum('space');
            $data[] = $value;
        }

        return $data;
    }

    /**
     * Get the filtered query based on applied filters
     */
    protected function getFilteredQuery(): Builder
    {
        // Start with the base query (same as in the resource)
        $query = Stand::query()->where(function ($q) {
            $q->where('is_merged', false)
                ->orWhereNull('parent_stand_id');
        });

        // Use the stored page filters if available, otherwise try to get them from request
        $filters = $this->pageFilters;

        if (empty($filters)) {
            // Try to get from Livewire component
            try {
                if (method_exists($this, 'getLivewire')) {
                    $livewire = $this->getLivewire();
                    if ($livewire && isset($livewire->tableFilters)) {
                        $filters = $livewire->tableFilters;
                    }
                } else {
                    // Alternative: try to access the Livewire component through the widget's internal properties
                    if (property_exists($this, 'livewire') && $this->livewire) {
                        $livewire = $this->livewire;
                        if (isset($livewire->tableFilters)) {
                            $filters = $livewire->tableFilters;
                        }
                    } else {
                        // Parse filter data from Livewire components in the request
                        $requestData = request()->all();

                        if (isset($requestData['components']) && is_array($requestData['components'])) {
                            foreach ($requestData['components'] as $component) {
                                if (!isset($component['snapshot'])) {
                                    continue;
                                }

                                $snapshot = is_array($component['snapshot'])
                                    ? $component['snapshot']
                                    : json_decode($component['snapshot'], true);

                                if (!is_array($snapshot)) {
                                    continue;
                                }

                                // Check for table filters (primary)
                                if (isset($snapshot['data']['tableFilters'])) {
                                    $filters = $snapshot['data']['tableFilters'];
                                    break;
                                }

                                // Check for form data (filtersForm) as fallback
                                if (isset($snapshot['data']['filters'])) {
                                    $filters = $snapshot['data']['filters'];
                                    break;
                                }
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                // Error accessing Livewire filters
            }
        }

        // Normalize table filter structure (same as ContractResource)
        $normalizeFilterPayload = function (mixed $payload): mixed {
            if (is_array($payload) && isset($payload[0]) && is_array($payload[0])) {
                return $payload[0];
            }

            return $payload;
        };

        $getValue = function (mixed $filter): mixed {
            if (!is_array($filter)) {
                return null;
            }

            if (array_key_exists('value', $filter)) {
                return $filter['value'];
            }

            if (array_key_exists('values', $filter)) {
                return $filter['values'];
            }

            return null;
        };

        // Apply table filters
        if (is_array($filters)) {
            foreach ($filters as $key => $filter) {
                $filter = $normalizeFilterPayload($filter);

                if (!is_array($filter)) {
                    continue;
                }

                // Apply event filter
                if ($key === 'event_id') {
                    $eventIdsValue = $getValue($filter);
                    $eventIds = is_array($eventIdsValue) ? $eventIdsValue : (empty($eventIdsValue) ? [] : [$eventIdsValue]);
                    $eventIds = array_filter($eventIds, fn ($v) => !empty($v));
                    if (!empty($eventIds)) {
                        $query->whereIn('event_id', $eventIds);
                    }
                }

                // Apply category filter
                if ($key === 'category_id') {
                    $categoryIdsValue = $getValue($filter);
                    $categoryIds = is_array($categoryIdsValue) ? $categoryIdsValue : (empty($categoryIdsValue) ? [] : [$categoryIdsValue]);
                    $categoryIds = array_filter($categoryIds, fn ($v) => !empty($v));
                    if (!empty($categoryIds)) {
                        $query->whereIn('category_id', $categoryIds);
                    }
                }

                // Apply status filter
                if ($key === 'status') {
                    $statusValue = $getValue($filter);
                    $statuses = is_array($statusValue) ? $statusValue : (empty($statusValue) ? [] : [$statusValue]);
                    $statuses = array_filter($statuses, fn ($v) => !empty($v));
                    if (!empty($statuses)) {
                        $query->whereIn('status', $statuses);
                    }
                }

                // Apply deductible filter
                if ($key === 'deductable') {
                    if (isset($filter['value']) && $filter['value'] !== null) {
                        $deductable = $filter['value'];
                        if ($deductable === true || $deductable === 'yes' || $deductable === '1') {
                            $query->where('deductable', true);
                        } elseif ($deductable === false || $deductable === 'no' || $deductable === '0') {
                            $query->where('deductable', false);
                        }
                    }
                }
            }
        }

        return $query;
    }

    /**
     * Get event targets based on current filters
     */
    protected function getEventTargets(): ?array
    {
        $filters = $this->pageFilters;

        if (empty($filters)) {
            // Try to get from Livewire component
            try {
                if (method_exists($this, 'getLivewire')) {
                    $livewire = $this->getLivewire();
                    if ($livewire && isset($livewire->tableFilters)) {
                        $filters = $livewire->tableFilters;
                    }
                }
            } catch (\Exception $e) {
                // Error accessing Livewire filters
            }
        }

        // Normalize table filter structure (same as ContractResource)
        $normalizeFilterPayload = function (mixed $payload): mixed {
            if (is_array($payload) && isset($payload[0]) && is_array($payload[0])) {
                return $payload[0];
            }

            return $payload;
        };

        $getValue = function (mixed $filter): mixed {
            if (!is_array($filter)) {
                return null;
            }

            if (array_key_exists('value', $filter)) {
                return $filter['value'];
            }

            if (array_key_exists('values', $filter)) {
                return $filter['values'];
            }

            return null;
        };

        // Apply table filters to get event targets
        if (is_array($filters) && isset($filters['event_id'])) {
            $filter = $normalizeFilterPayload($filters['event_id']);
            $eventIdsValue = $getValue($filter);
            $eventIds = is_array($eventIdsValue) ? $eventIdsValue : (empty($eventIdsValue) ? [] : [$eventIdsValue]);
            $eventIds = array_filter($eventIds, fn ($v) => !empty($v));

            if (!empty($eventIds)) {
                // Get sum of targets for selected events
                $events = \App\Models\Event::whereIn('id', $eventIds)->get();
                return [
                    'target_space' => $events->sum('target_space'),
                ];
            }
        }

        // No event filter or empty event filter - sum all targets
        return [
            'target_space' => \App\Models\Event::sum('target_space'),
        ];
    }

    /**
     * Calculate target comparisons for stands
     */
    protected function calculateTargetComparisons(float $soldSpace, ?array $targets): array
    {
        $comparisons = [
            'description' => 'No target set',
            'color' => 'danger',
        ];

        if ($targets) {
            if ($targets['target_space'] && $targets['target_space'] > 0) {
                $percentage = ($soldSpace / $targets['target_space']) * 100;
                $comparisons['description'] = number_format($percentage, 1) . '% of ' . number_format($targets['target_space'], 0) . ' sqm target';
                $comparisons['color'] = $percentage >= 100 ? 'success' : ($percentage >= 75 ? 'warning' : 'danger');
            }
        }

        return $comparisons;
    }
}
