<?php

namespace App\Filament\Widgets;

use App\Models\Contract;
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
        $draftCount = $query->clone()->where('status', Contract::STATUS_DRAFT)->count();
        $interestedCount = $query->clone()->where('status', Contract::STATUS_INTERESTED)->count();
        $signedCount = $query->clone()->whereIn('status', [
            Contract::STATUS_SIGNED_NOT_PAID,
            Contract::STATUS_SIGNED_PAID,
        ])->count();
        $paidCount = $query->clone()->where('status', Contract::STATUS_SIGNED_PAID)->count();

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

        return [
            Stat::make('Total Contracts', $totalContracts)
                ->description('All contracts')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('primary')
                ->chart([7, 3, 5, 8, 10, 12, 15])
                ->extraAttributes(['class' => 'cursor-pointer']),

            Stat::make('Signed Contracts', $signedCount)
                ->description($totalContracts > 0 ? round(($signedCount / $totalContracts) * 100, 1) . '% of total' : '0%')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color($signedCount > 0 ? 'success' : 'gray')
                ->chart([1, 2, 3, 4, 5, 6, 7]),

            Stat::make('Paid Contracts', $paidCount)
                ->description($signedCount > 0 ? round(($paidCount / $signedCount) * 100, 1) . '% of signed' : '0%')
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color($paidCount > 0 ? 'success' : 'gray')
                ->chart([1, 2, 2, 3, 4, 5, 6]),

            Stat::make('Total Amount (USD)', '$' . number_format($totalAmount, 2))
                ->description('Converted to USD')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('warning')
                ->chart([1000, 2000, 3000, 4000, 5000, 6000, 7000]),
        ];
    }
}
