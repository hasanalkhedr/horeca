<?php

namespace App\Filament\Resources\ContractResource\Widgets;

use App\Models\Contract;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;

class ContractTypeChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Contracts by Type';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 3;

    protected $listeners = ['refresh-widget' => 'onRefreshWidget'];

    public $pageFilters = [];

    public function onRefreshWidget($filters = null): void
    {
        if ($filters) {
            $this->pageFilters = $filters;
        } else {
            $this->pageFilters = [];
        }
        $this->dispatch('$refresh');
    }

    protected function getData(): array
    {
        $query = $this->getFilteredQuery();

        // Get contracts by type
        $contractsByType = $query->selectRaw('status, COUNT(*) as count')
            ->whereNotNull('status')
            ->groupBy('status')
            ->orderBy('count', 'desc')
            ->pluck('count', 'status')
            ->toArray();

        // Define colors for different types
        $colors = [
            'Space' => '#ef4444',
            'Sponsor' => '#f59e0b',
            'Space + Sponsor' => '#10b981',
            'Troc' => '#8b5cf6',
            'Other' => '#6b7280',
        ];

        $labels = array_keys($contractsByType);
        $data = array_values($contractsByType);
        $backgroundColor = [];

        foreach ($labels as $label) {
            $backgroundColor[] = $colors[$label] ?? '#6b7280';
        }

        return [
            'datasets' => [
                [
                    'label' => 'Number of Contracts',
                    'data' => $data,
                    'backgroundColor' => $backgroundColor,
                    'borderColor' => $backgroundColor,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getFilteredQuery(): Builder
    {
        // Start with base query
        $query = Contract::query()->with(['Stand', 'Report.Currency']);

        // Use the stored page filters if available, otherwise try to get them from the parent Livewire component / request.
        $filters = $this->pageFilters;

        if (empty($filters)) {
            try {
                // Use the correct method to access the parent Livewire component
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

                                // In this project, snapshot is often a JSON string.
                                $snapshot = is_array($component['snapshot'])
                                    ? $component['snapshot']
                                    : json_decode($component['snapshot'], true);

                                if (!is_array($snapshot) || !isset($snapshot['data']['tableFilters'])) {
                                    continue;
                                }

                                $tableFilters = $snapshot['data']['tableFilters'];
                                if (is_array($tableFilters) && isset($tableFilters[0]) && is_array($tableFilters[0])) {
                                    $filters = $tableFilters[0];
                                }

                                break;
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Contract type chart widget error accessing Livewire filters: ' . $e->getMessage());
            }
        }

        // Normalize Filament v3 snapshot tableFilters structure:
        $normalizeFilterPayload = function (mixed $payload): mixed {
            if (is_array($payload) && isset($payload[0]) && is_array($payload[0])) {
                return $payload[0];
            }

            return $payload;
        };

        if (is_array($filters)) {
            foreach ($filters as $key => $payload) {
                $filters[$key] = $normalizeFilterPayload($payload);
            }
        }

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

            if (isset($filter[0]) && is_array($filter[0]) && array_key_exists('value', $filter[0])) {
                return $filter[0]['value'];
            }

            return null;
        };

        // Apply event filter
        if (isset($filters['event_id']) && !empty($filters['event_id'])) {
            $eventIdFilter = $filters['event_id'];
            $eventIdsValue = $getValue($eventIdFilter);
            $eventIds = is_array($eventIdsValue) ? $eventIdsValue : (empty($eventIdsValue) ? [] : [$eventIdsValue]);
            $eventIds = array_filter($eventIds, fn ($v) => !empty($v));

            if (!empty($eventIds)) {
                $query->whereIn('event_id', $eventIds);
            }
        }

        // Apply status filter
        if (isset($filters['status']) && !empty($filters['status'])) {
            $statusValue = $getValue($filters['status']);
            $statuses = is_array($statusValue) ? $statusValue : (empty($statusValue) ? [] : [$statusValue]);
            $statuses = array_filter($statuses, fn ($v) => !empty($v));

            if (!empty($statuses)) {
                $query->whereIn('status', $statuses);
            }
        }

        // Apply seller filter
        if (isset($filters['seller']) && !empty($filters['seller'])) {
            $sellerValue = $getValue($filters['seller']);
            $selleres = is_array($sellerValue) ? $sellerValue : (empty($sellerValue) ? [] : [$sellerValue]);
            $selleres = array_filter($selleres, fn ($v) => !empty($v));

            if (!empty($selleres)) {
                $query->whereIn('seller', $selleres);
            }
        }

        // Apply contract date filter
        if (isset($filters['contract_date']) && !empty($filters['contract_date'])) {
            $dateFilter = $filters['contract_date'];

            if (isset($dateFilter['from']) && !empty($dateFilter['from'])) {
                $query->whereDate('contract_date', '>=', $dateFilter['from']);
            }

            if (isset($dateFilter['until']) && !empty($dateFilter['until'])) {
                $query->whereDate('contract_date', '<=', $dateFilter['until']);
            }
        }

        // Apply space amount filter
        if (isset($filters['has_space_net']) && !empty($filters['has_space_net'])) {
            $spaceNetFilter = $filters['has_space_net'];
            if (isset($spaceNetFilter['isActive']) && $spaceNetFilter['isActive'] === true) {
                $query->whereNotNull('space_net')->where('space_net', '>', 0);
            }
        }

        // Apply sponsor amount filter
        if (isset($filters['has_sponsor_net']) && !empty($filters['has_sponsor_net'])) {
            $sponsorNetFilter = $filters['has_sponsor_net'];
            if (isset($sponsorNetFilter['isActive']) && $sponsorNetFilter['isActive'] === true) {
                $query->whereNotNull('sponsor_net')->where('sponsor_net', '>', 0);
            }
        }

        return $query;
    }
}
