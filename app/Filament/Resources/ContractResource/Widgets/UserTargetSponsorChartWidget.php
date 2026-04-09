<?php

namespace App\Filament\Resources\ContractResource\Widgets;

use App\Models\UserTarget;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class UserTargetSponsorChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Sponsor Targets Progress';

    protected static ?int $sort = 5;

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
        // Get current user's targets or all targets if admin
        $user = Auth::user();
        $query = UserTarget::with(['user', 'event']);

        // If not admin, show only current user's targets
        if (!$user->hasRole('super_admin')) {
            $query->where('user_id', $user->id);
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
            $labels[] = $target->user->name . ' - ' . $target->event->name;

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
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
