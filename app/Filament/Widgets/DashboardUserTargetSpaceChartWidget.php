<?php

namespace App\Filament\Widgets;

use App\Models\UserTarget;
use App\Models\Event;
use App\Models\User;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\Auth;

class DashboardUserTargetSpaceChartWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Space Targets Progress';

    protected static ?int $sort = 3;

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

            // Log filters for debugging
            \Log::info('DashboardUserTargetSpaceChartWidget filters:', [
                'filters' => $filters,
                'type' => gettype($filters),
                'is_array' => is_array($filters)
            ]);

            // Ensure filters is an array
            if (!is_array($filters)) {
                \Log::warning('Filters is not an array, converting to empty array');
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
                // Ensure labels are strings and log them
                $label = (string) ($target->user->name . ' - ' . $target->event->name);
                \Log::info('Label being added:', ['label' => $label, 'type' => gettype($label)]);
                $labels[] = $label;

                $targetSpace = (float) $target->target_space;
                $achievedSpace = (float) $target->achieved_space;

                $targetData[] = $targetSpace;
                $achievedData[] = $achievedSpace;

                // Color based on completion percentage
                $completionPercentage = $targetSpace > 0 ? ($achievedSpace / $targetSpace) * 100 : 0;
                if ($completionPercentage >= 100) {
                    $backgroundColor[] = '#10b981'; // green
                } elseif ($completionPercentage >= 75) {
                    $backgroundColor[] = '#f59e0b'; // yellow
                } else {
                    $backgroundColor[] = '#ef4444'; // red
                }
            }

            $chartData = [
                'datasets' => [
                    [
                        'label' => 'Target Space',
                        'data' => $targetData,
                        'backgroundColor' => '#3b82f6',
                        'borderColor' => '#3b82f6',
                    ],
                    [
                        'label' => 'Achieved Space',
                        'data' => $achievedData,
                        'backgroundColor' => $backgroundColor,
                        'borderColor' => $backgroundColor,
                    ],
                ],
                'labels' => $labels,
            ];

            \Log::info('Chart data prepared:', ['labels_count' => count($labels), 'datasets_count' => count($chartData['datasets'])]);

            return $chartData;

        } catch (\Exception $e) {
            \Log::error('DashboardUserTargetSpaceChartWidget error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
