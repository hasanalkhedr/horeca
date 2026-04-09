<?php

namespace App\Filament\Widgets;

use App\Models\UserTarget;
use App\Models\Event;
use App\Models\User;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\Auth;

class DashboardUserTargetSponsorChartWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Sponsor Targets Progress';

    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 1;

    protected $listeners = ['filtersUpdated' => 'refreshWidget'];

    public function refreshWidget(): void
    {
        $this->dispatch('$refresh');
    }

    protected function getData(): array
    {
        try {
            // Get current user's targets or all targets if admin
            $user = Auth::user();
            $query = UserTarget::with(['user', 'event']);

            // If not admin, show only current user's targets
            if (!$user->hasRole('super_admin')) {
                $query->where('user_id', $user->id);
            }

            // Apply dashboard filters using the trait's built-in method
            $filters = $this->filters;

            // Ensure filters is an array
            if (!is_array($filters)) {
                $filters = [];
            }

            // Ensure filter values are properly handled
            $eventIds = $filters['event_id'] ?? [];
            $userIds = $filters['user_id'] ?? [];
            $startDate = $filters['startDate'] ?? null;
            $endDate = $filters['endDate'] ?? null;

            // Convert to arrays if they're not already
            if (!is_array($eventIds)) {
                $eventIds = empty($eventIds) ? [] : [$eventIds];
            }
            if (!is_array($userIds)) {
                $userIds = empty($userIds) ? [] : [$userIds];
            }

            if (!empty($eventIds)) {
                $query->whereIn('event_id', $eventIds);
            }

            if (!empty($userIds)) {
                $query->whereIn('user_id', $userIds);
            }

            // Apply date filters if needed (based on contract dates)
            if (!empty($startDate) || !empty($endDate)) {
                $query->whereHas('contracts', function ($q) use ($startDate, $endDate) {
                    if (!empty($startDate)) {
                        $q->whereDate('contract_date', '>=', $startDate);
                    }
                    if (!empty($endDate)) {
                        $q->whereDate('contract_date', '<=', $endDate);
                    }
                });
            }

            $targets = $query->get();

            // Update achieved values for all targets
            foreach ($targets as $target) {
                if ($target instanceof \App\Models\UserTarget) {
                    $target->updateAchievedValues();
                }
            }

            // Refresh the targets after updating to get the latest values
            $targets = $query->get();

            $labels = [];
            $targetData = [];
            $achievedData = [];
            $backgroundColor = [];

            foreach ($targets as $target) {
                // Ensure labels are strings
                $labels[] = (string) ($target->user->name . ' - ' . $target->event->name);

                $targetSponsor = (float) $target->target_sponsor_amount;
                $achievedSponsor = (float) $target->achieved_sponsor_amount;

                $targetData[] = $targetSponsor;
                $achievedData[] = $achievedSponsor;

                // Color based on completion percentage
                $completionPercentage = $targetSponsor > 0 ? ($achievedSponsor / $targetSponsor) * 100 : 0;
                if ($completionPercentage >= 100) {
                    $backgroundColor[] = '#10b981'; // green
                } elseif ($completionPercentage >= 75) {
                    $backgroundColor[] = '#f59e0b'; // yellow
                } else {
                    $backgroundColor[] = '#ef4444'; // red
                }
            }

            return [
                'datasets' => [
                    [
                        'label' => 'Target Sponsor',
                        'data' => $targetData,
                        'backgroundColor' => '#3b82f6',
                        'borderColor' => '#3b82f6',
                    ],
                    [
                        'label' => 'Achieved Sponsor',
                        'data' => $achievedData,
                        'backgroundColor' => $backgroundColor,
                        'borderColor' => $backgroundColor,
                    ],
                ],
                'labels' => $labels,
            ];

        } catch (\Exception $e) {
            \Log::error('DashboardUserTargetSponsorChartWidget error: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
