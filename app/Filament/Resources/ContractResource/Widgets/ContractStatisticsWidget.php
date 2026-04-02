<?php

namespace App\Filament\Resources\ContractResource\Widgets;

use App\Models\Contract;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class ContractStatisticsWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?string $pollingInterval = null;
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 4;

    protected $listeners = ['refresh-widget' => 'onRefreshWidget'];

     protected function getColumns(): int
    {
        return 3;
    }

    // Store filter data from page
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
        // Get filtered query using filters from page
        $query = $this->getFilteredQuery();

        $contracts = $query->get();

        // Calculate total statistics
        $totalContracts = $contracts->count();
        $totalSpace = $contracts->sum(function ($contract) {
            return $contract->Stand ? $contract->Stand->space : 0;
        });

        // Calculate status statistics
        $interestedContracts = $contracts->where('status', Contract::STATUS_INTERESTED)->count();
        $interestedSpace = $contracts->where('status', Contract::STATUS_INTERESTED)->sum(function ($contract) {
            return $contract->Stand ? $contract->Stand->space : 0;
        });

        $signedNotPaidContracts = $contracts->where('status', Contract::STATUS_SIGNED_NOT_PAID)->count();
        $signedNotPaidSpace = $contracts->where('status', Contract::STATUS_SIGNED_NOT_PAID)->sum(function ($contract) {
            return $contract->Stand ? $contract->Stand->space : 0;
        });

        $signedPaidContracts = $contracts->where('status', Contract::STATUS_SIGNED_PAID)->count();
        $signedPaidSpace = $contracts->where('status', Contract::STATUS_SIGNED_PAID)->sum(function ($contract) {
            return $contract->Stand ? $contract->Stand->space : 0;
        });

        $finalizedContracts = $contracts->whereIn('status', [
            Contract::STATUS_SIGNED_PAID,
            Contract::STATUS_PAID_TROC,
            Contract::STATUS_SPONSOR,
        ])->count();

        $activeContracts = $contracts->whereIn('status', [
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

        // Calculate amount statistics
        $totalAmount = $contracts->sum('net_total');
        $spaceAmount = $contracts->sum('space_net');
        $sponsorAmount = $contracts->sum('sponsor_net');

        // Calculate contracts with amounts
        $contractsWithSpaceAmount = $contracts->whereNotNull('space_net')->where('space_net', '>', 0)->count();
        $contractsWithSponsorAmount = $contracts->whereNotNull('sponsor_net')->where('sponsor_net', '>', 0)->count();

        return [
            // Contract Count Statistics
            Stat::make('Total Contracts', $totalContracts)
                ->description("{$finalizedContracts} finalized • {$activeContracts} active")
                ->descriptionIcon('heroicon-o-document-text')
                ->color('primary')
                ->chart($this->getContractTrend()),

            // Stat::make('Finalized', $finalizedContracts)
            //     ->description(number_format($totalContracts > 0 ? round(($finalizedContracts / $totalContracts) * 100, 1) : 0, 1) . '% of total')
            //     ->descriptionIcon('heroicon-o-check-circle')
            //     ->color('success')
            //     ->chart($this->getFinalizedTrend()),

            // Stat::make('Active', $activeContracts)
            //     ->description(number_format($totalContracts > 0 ? round(($activeContracts / $totalContracts) * 100, 1) : 0, 1) . '% of total')
            //     ->descriptionIcon('heroicon-o-clipboard-document-list')
            //     ->color('info')
            //     ->chart($this->getActiveTrend()),

            // // Status Breakdown
            // Stat::make('Interested', $interestedContracts)
            //     ->description(number_format($totalSpace > 0 ? round(($interestedSpace / $totalSpace) * 100, 1) : 0, 1) . '% of space')
            //     ->descriptionIcon('heroicon-o-eye')
            //     ->color('info')
            //     ->chart($this->getStatusTrend(Contract::STATUS_INTERESTED)),

            // Stat::make('Signed (Not Paid)', $signedNotPaidContracts)
            //     ->description(number_format($totalSpace > 0 ? round(($signedNotPaidSpace / $totalSpace) * 100, 1) : 0, 1) . '% of space')
            //     ->descriptionIcon('heroicon-o-document-check')
            //     ->color('warning')
            //     ->chart($this->getStatusTrend(Contract::STATUS_SIGNED_NOT_PAID)),

            Stat::make('Signed (Paid)', $signedPaidContracts)
                ->description(number_format($totalSpace > 0 ? round(($signedPaidSpace / $totalSpace) * 100, 1) : 0, 1) . '% of space')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success')
                ->chart($this->getStatusTrend(Contract::STATUS_SIGNED_PAID)),

            // Space Statistics
            Stat::make('Total Space', number_format($totalSpace, 2) . ' sqm')
                ->description("{$totalContracts} contracts")
                ->descriptionIcon('heroicon-o-square-3-stack-3d')
                ->color('primary')
                ->chart($this->getTotalSpaceTrend()),

            // Financial Statistics
            Stat::make('Total Amount', '$' . number_format($totalAmount, 2))
                ->description("{$contractsWithSpaceAmount} with space amount")
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('primary')
                ->chart($this->getAmountTrend()),

            Stat::make('Space Amount', '$' . number_format($spaceAmount, 2))
                ->description("{$contractsWithSpaceAmount} contracts")
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success')
                ->chart($this->getSpaceAmountTrend()),

            Stat::make('Sponsor Amount', '$' . number_format($sponsorAmount, 2))
                ->description("{$contractsWithSponsorAmount} contracts")
                ->descriptionIcon('heroicon-o-star')
                ->color('warning')
                ->chart($this->getSponsorAmountTrend()),
        ];
    }

    /**
     * Get trend data for contract status
     */
    protected function getStatusTrend(string $status): array
    {
        $data = [];

        // Get last 7 days of data
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $value = $this->getFilteredQuery()
                ->where('status', $status)
                ->whereDate('contract_date', '<=', $date)
                ->count();
            $data[] = $value;
        }

        return $data;
    }

    /**
     * Get trend data for finalized contracts
     */
    protected function getFinalizedTrend(): array
    {
        $data = [];

        // Get last 7 days of data
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $value = $this->getFilteredQuery()
                ->whereIn('status', [
                    Contract::STATUS_SIGNED_PAID,
                    Contract::STATUS_PAID_TROC,
                    Contract::STATUS_SPONSOR,
                ])
                ->whereDate('contract_date', '<=', $date)
                ->count();
            $data[] = $value;
        }

        return $data;
    }

    /**
     * Get trend data for active contracts
     */
    protected function getActiveTrend(): array
    {
        $data = [];

        // Get last 7 days of data
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $value = $this->getFilteredQuery()
                ->whereIn('status', [
                    Contract::STATUS_INTERESTED,
                    Contract::STATUS_SIGNED_NOT_PAID,
                    Contract::STATUS_SIGNED_PAID,
                    Contract::STATUS_FREE_FROM_HS,
                    Contract::STATUS_PAID_TROC,
                    Contract::STATUS_ON_HOLD,
                    Contract::STATUS_ON_SITE_FREE,
                    Contract::STATUS_ANIMATION,
                    Contract::STATUS_SPONSOR,
                ])
                ->whereDate('contract_date', '<=', $date)
                ->count();
            $data[] = $value;
        }

        return $data;
    }

    /**
     * Get trend data for total contracts
     */
    protected function getContractTrend(): array
    {
        $data = [];

        // Get last 7 days of data
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $value = $this->getFilteredQuery()
                ->whereDate('contract_date', '<=', $date)
                ->count();
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

        // Get last 7 days of data
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $value = $this->getFilteredQuery()
                ->whereDate('contract_date', '<=', $date)
                ->get()
                ->sum(function ($contract) {
                    return $contract->Stand ? $contract->Stand->space : 0;
                });
            $data[] = $value;
        }

        return $data;
    }

    /**
     * Get trend data for amounts
     */
    protected function getAmountTrend(): array
    {
        $data = [];

        // Get last 7 days of data
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $value = $this->getFilteredQuery()
                ->whereDate('contract_date', '<=', $date)
                ->sum('net_total');
            $data[] = $value;
        }

        return $data;
    }

    /**
     * Get trend data for space amounts
     */
    protected function getSpaceAmountTrend(): array
    {
        $data = [];

        // Get last 7 days of data
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $value = $this->getFilteredQuery()
                ->whereDate('contract_date', '<=', $date)
                ->sum('space_net');
            $data[] = $value;
        }

        return $data;
    }

    /**
     * Get trend data for sponsor amounts
     */
    protected function getSponsorAmountTrend(): array
    {
        $data = [];

        // Get last 7 days of data
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $value = $this->getFilteredQuery()
                ->whereDate('contract_date', '<=', $date)
                ->sum('sponsor_net');
            $data[] = $value;
        }
        return $data;
    }

    /**
     * Get filtered query based on applied filters
     */
    protected function getFilteredQuery(): Builder
    {
        // Start with the base query (same as in the resource)
        $query = Contract::query()->with(['Stand', 'Report.Currency']);

        // Use the stored page filters if available, otherwise try to get them from the parent Livewire component / request.
        // This mirrors the StandStatisticsWidget behavior.
        $filters = $this->pageFilters;

        if (!empty($filters)) {
            // Filters are being applied
        }

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
                // Error accessing Livewire filters
            }
        }

        // Normalize Filament v3 snapshot tableFilters structure:
        // Each filter is usually [ { value|from|isActive... }, { s: 'arr' } ]
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

        if (isset($filters['status'])) {
            // Status filter normalized
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
            $eventIdsValue = $getValue($filters['event_id']);
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
