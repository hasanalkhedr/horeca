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
        \Log::info('Widget received refresh-widget event, refreshing...');
        if ($filters) {
            $this->pageFilters = $filters;
            \Log::info('Widget received filter data:', $filters);
        } else {
            // Clear stored filters when null is passed (filters cleared)
            $this->pageFilters = [];
            \Log::info('Widget cleared all filters');
        }
        // Trigger widget re-render
        $this->dispatch('$refresh');
    }

    protected function getStats(): array
    {
        \Log::info('Widget getStats called, building statistics...');

        // Get the filtered query using the filters from the page
        $query = $this->getFilteredQuery();

        $stands = $query->get();
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

        return [
            // Sold Statistics
            Stat::make('Sold Space', number_format($soldSpace, 2) . ' sqm')
                ->description("{$soldStands} stands • {$soldSpacePercentage}% of total space")
                ->descriptionIcon('heroicon-o-check-badge')
                ->color('danger')
                ->chart($this->getStatusTrend('Sold')),

            // Stat::make('Sold Stands', $soldStands)
            //     ->description("{$soldPercentage}% of total stands")
            //     ->descriptionIcon('heroicon-o-map-pin')
            //     ->color('danger')
            //     ->chart($this->getStatusTrend('Sold', 'count')),

            // Available Statistics
            Stat::make('Available Space to Sell', number_format($availableSpace, 2) . ' sqm')
                ->description("{$availableStands} stands • {$availableSpacePercentage}% of total space")
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success')
                ->chart($this->getStatusTrend('Available')),

            // Stat::make('Available Stands', $availableStands)
            //     ->description("{$availablePercentage}% of total stands")
            //     ->descriptionIcon('heroicon-o-map-pin')
            //     ->color('success')
            //     ->chart($this->getStatusTrend('Available', 'count')),

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

            // Deductible
            // Stat::make('Deductible Stands', $deductibleStands)
            //     ->description("{$deductiblePercentage}% of total")
            //     ->descriptionIcon('heroicon-o-receipt-percent')
            //     ->color('warning'),
        ];
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
                $value = Stand::where('status', $status)
                    ->whereDate('created_at', '<=', $date)
                    ->sum('space');
            } else {
                $value = Stand::where('status', $status)
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
            $value = $this->getFilteredQuery()->whereDate('created_at', '<=', $date)->sum('space');
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
\Log::info('FILTERS:',$filters);
        if (empty($filters)) {
            \Log::info('Widget using request-based filtering as fallback');
            // Fallback to request-based filtering (existing logic)
            try {
                // Use the correct method to access the parent Livewire component
                if (method_exists($this, 'getLivewire')) {
                    $livewire = $this->getLivewire();
                    if ($livewire && isset($livewire->tableFilters)) {
                        $filters = $livewire->tableFilters;
                        \Log::info('Widget retrieved filters from Livewire:', $filters);
                    } else {
                        \Log::info('Widget could not retrieve filters - Livewire component not found or no tableFilters property');
                    }
                } else {
                    \Log::info('Widget getLivewire method does not exist, trying alternative approach');

                    // Alternative: try to access the Livewire component through the widget's internal properties
                    if (property_exists($this, 'livewire') && $this->livewire) {
                        $livewire = $this->livewire;
                        if (isset($livewire->tableFilters)) {
                            $filters = $livewire->tableFilters;
                            \Log::info('Widget retrieved filters from livewire property:', $filters);
                        } else {
                            \Log::info('Widget livewire property exists but no tableFilters found');
                        }
                    } else {
                        \Log::info('Widget has no livewire property, falling back to request-based filtering');

                        // Parse filter data from Livewire components in the request
                        $request = request();
                        $requestData = $request->all();

                        \Log::info('Request data keys:', array_keys($requestData));

                        if (isset($requestData['components']) && is_array($requestData['components'])) {
                            foreach ($requestData['components'] as $component) {
                                if (isset($component['snapshot']['data']['tableFilters'])) {
                                    $tableFiltersJson = $component['snapshot']['data']['tableFilters'];
                                    \Log::info('Raw tableFilters JSON:', $tableFiltersJson);

                                    // Decode the JSON string to get the actual filter array
                                    $decodedFilters = json_decode($tableFiltersJson, true);
                                    if ($decodedFilters !== null) {
                                        $filters = $decodedFilters;
                                        \Log::info('Widget retrieved and decoded filters from Livewire component:', $filters);
                                        break;
                                    } else {
                                        \Log::info('Failed to decode tableFilters JSON');
                                    }
                                }
                            }
                        }

                        if (empty($filters)) {
                            \Log::info('No tableFilters found in request components');
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Widget error accessing Livewire filters: ' . $e->getMessage());
            }
         } else {
             \Log::info('Widget using stored page filters:', $filters);
         }

        // Apply event filter
        if (isset($filters['event_id']) && !empty($filters['event_id'])) {
            $eventIdFilter = $filters['event_id'];
            // Extract the actual values from the nested structure
            if (isset($eventIdFilter['values']) && is_array($eventIdFilter['values'])) {
                $eventIds = $eventIdFilter['values'];
                // Remove empty values and flatten the array
                $eventIds = array_filter($eventIds, function($value) {
                    return is_array($value) ? !empty($value) : !empty($value);
                });
                if (!empty($eventIds)) {
                    $query->whereIn('event_id', $eventIds);
                    \Log::info('Applied event filter:', $eventIds);
                }
            }
        }

        // Apply category filter
        if (isset($filters['category_id']) && !empty($filters['category_id'])) {
            $categoryIdFilter = $filters['category_id'];
            if (isset($categoryIdFilter['values']) && is_array($categoryIdFilter['values'])) {
                $categoryIds = $categoryIdFilter['values'];
                $categoryIds = array_filter($categoryIds, function($value) {
                    return is_array($value) ? !empty($value) : !empty($value);
                });
                if (!empty($categoryIds)) {
                    $query->whereIn('category_id', $categoryIds);
                    \Log::info('Applied category filter:', $categoryIds);
                }
            }
        }

        // Apply status filter
        if (isset($filters['status']) && !empty($filters['status'])) {
            $statusFilter = $filters['status'];
            if (isset($statusFilter['value']) && !empty($statusFilter['value'])) {
                $statuses = is_array($statusFilter['value']) ? $statusFilter['value'] : [$statusFilter['value']];
                $query->whereIn('status', $statuses);
                \Log::info('Applied status filter:', $statuses);
            }
        }

        // Apply deductible filter
        if (isset($filters['deductable']) && !empty($filters['deductable'])) {
            $deductableFilter = $filters['deductable'];
            if (isset($deductableFilter['value']) && $deductableFilter['value'] !== null) {
                $deductable = $deductableFilter['value'];
                if ($deductable === true || $deductable === 'yes' || $deductable === '1') {
                    $query->where('deductable', true);
                    \Log::info('Applied deductible filter: true');
                } elseif ($deductable === false || $deductable === 'no' || $deductable === '0') {
                    $query->where('deductable', false);
                    \Log::info('Applied deductible filter: false');
                }
            }
        }

        return $query;
    }
}
