<?php

namespace App\Filament\Resources\ContractResource\Widgets;

use App\Models\UserTarget;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class UserTargetWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        // Get current user's targets or all targets if admin
        $user = Auth::user();
        $query = UserTarget::with(['user', 'event']);

        // If not admin, show only current user's targets
        if (!$user->hasRole('super_admin')) {
            $query->where('user_id', $user->id);
        }

        // Update achieved values for all targets
        foreach ($query->get() as $target) {
            if ($target instanceof \App\Models\UserTarget) {
                $target->updateAchievedValues();
            }
        }

        // Refresh the targets after updating to get the latest values
        $targets = $query->get();

        // Calculate overall statistics
        $totalTargets = $targets->count();
        $activeTargets = $targets->where('status', 'active')->count();
        $completedTargets = $targets->where('status', 'completed')->count();

        // Calculate totals
        $totalTargetSpace = $targets->sum('target_space');
        $totalAchievedSpace = $targets->sum('achieved_space');
        $totalTargetAmount = $targets->sum('target_space_amount');
        $totalAchievedAmount = $targets->sum('achieved_space_amount');
        $totalTargetSponsor = $targets->sum('target_sponsor_amount');
        $totalAchievedSponsor = $targets->sum('achieved_sponsor_amount');

        // Calculate percentages
        $spacePercentage = $totalTargetSpace > 0 ? ($totalAchievedSpace / $totalTargetSpace) * 100 : 0;
        $amountPercentage = $totalTargetAmount > 0 ? ($totalAchievedAmount / $totalTargetAmount) * 100 : 0;
        $sponsorPercentage = $totalTargetSponsor > 0 ? ($totalAchievedSponsor / $totalTargetSponsor) * 100 : 0;

        // Get top performer
        $topPerformer = $targets->sortByDesc('completion_percentage')->first();
        $topPerformerName = $topPerformer ? $topPerformer->user->name : 'N/A';
        $topPerformerPercentage = $topPerformer ? $topPerformer->completion_percentage : 0;

        return [
            // Overall Target Statistics
            Stat::make('Total Targets', $totalTargets)
                ->description("{$completedTargets} completed • {$activeTargets} active")
                ->descriptionIcon('heroicon-o-eye')
                ->color('primary')
                ->chart($this->getTargetTrend()),

            Stat::make('Completion Rate', number_format($totalTargets > 0 ? ($completedTargets / $totalTargets) * 100 : 0, 1) . '%')
                ->description("{$completedTargets} of {$totalTargets} targets")
                ->descriptionIcon('heroicon-o-check-circle')
                ->color($completedTargets === $totalTargets ? 'success' : ($completedTargets > 0 ? 'warning' : 'danger'))
                ->chart($this->getCompletionTrend()),

            Stat::make('Top Performer', $topPerformerName)
                ->description(number_format($topPerformerPercentage, 1) . '% completion')
                ->descriptionIcon('heroicon-o-trophy')
                ->color($topPerformerPercentage >= 100 ? 'success' : ($topPerformerPercentage >= 75 ? 'warning' : 'info'))
                ->chart($this->getTopPerformerTrend()),

            // Space Statistics
            Stat::make('Space Achievement', number_format($totalAchievedSpace, 1) . ' / ' . number_format($totalTargetSpace, 1) . ' sqm')
                ->description(number_format($spacePercentage, 1) . '% of target')
                ->descriptionIcon('heroicon-o-square-3-stack-3d')
                ->color($spacePercentage >= 100 ? 'success' : ($spacePercentage >= 75 ? 'warning' : 'danger'))
                ->chart($this->getSpaceTrend()),

            // Amount Statistics
            Stat::make('Amount Achievement', '$' . number_format($totalAchievedAmount, 0) . ' / $' . number_format($totalTargetAmount, 0))
                ->description(number_format($amountPercentage, 1) . '% of target')
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color($amountPercentage >= 100 ? 'success' : ($amountPercentage >= 75 ? 'warning' : 'danger'))
                ->chart($this->getAmountTrend()),

            // Sponsor Statistics
            Stat::make('Sponsor Achievement', '$' . number_format($totalAchievedSponsor, 0) . ' / $' . number_format($totalTargetSponsor, 0))
                ->description(number_format($sponsorPercentage, 1) . '% of target')
                ->descriptionIcon('heroicon-o-star')
                ->color($sponsorPercentage >= 100 ? 'success' : ($sponsorPercentage >= 75 ? 'warning' : 'danger'))
                ->chart($this->getSponsorTrend()),

            // Individual User Stats (if not too many users)
            ...$this->getUserStats($targets),
        ];
    }

    /**
     * Get individual user statistics (limit to top 5 performers)
     */
    protected function getUserStats($targets): array
    {
        $userStats = [];
        $topUsers = $targets->groupBy('user.id')
            ->map(function ($userTargets) {
                $user = $userTargets->first()->user;
                $totalTargetSpace = $userTargets->sum('target_space');
                $totalAchievedSpace = $userTargets->sum('achieved_space');
                $completionPercentage = $totalTargetSpace > 0 ? ($totalAchievedSpace / $totalTargetSpace) * 100 : 0;

                return [
                    'user' => $user,
                    'completion_percentage' => $completionPercentage,
                    'targets_count' => $userTargets->count(),
                    'achieved_space' => $totalAchievedSpace,
                    'target_space' => $totalTargetSpace,
                ];
            })
            ->sortByDesc('completion_percentage')
            ->take(5);

        foreach ($topUsers as $userStat) {
            $userStats[] = Stat::make($userStat['user']->name, number_format($userStat['completion_percentage'], 1) . '%')
                ->description("{$userStat['targets_count']} targets • " . number_format($userStat['achieved_space'], 1) . '/' . number_format($userStat['target_space'], 1) . ' sqm')
                ->descriptionIcon('heroicon-o-user')
                ->color($userStat['completion_percentage'] >= 100 ? 'success' : ($userStat['completion_percentage'] >= 75 ? 'warning' : 'info'))
                ->chart([0, $userStat['completion_percentage']]);
        }

        return $userStats;
    }

    /**
     * Get trend data for total targets
     */
    protected function getTargetTrend(): array
    {
        // For demo purposes, return a simple increasing trend
        // In a real implementation, you would query historical data
        return [10, 12, 15, 14, 18, 20, 22];
    }

    /**
     * Get trend data for completion rate
     */
    protected function getCompletionTrend(): array
    {
        return [20, 25, 30, 28, 35, 40, 45];
    }

    /**
     * Get trend data for top performer
     */
    protected function getTopPerformerTrend(): array
    {
        return [60, 65, 70, 75, 80, 85, 90];
    }

    /**
     * Get trend data for space achievement
     */
    protected function getSpaceTrend(): array
    {
        return [100, 120, 150, 140, 180, 200, 220];
    }

    /**
     * Get trend data for amount achievement
     */
    protected function getAmountTrend(): array
    {
        return [1000, 1200, 1500, 1400, 1800, 2000, 2200];
    }

    /**
     * Get trend data for sponsor achievement
     */
    protected function getSponsorTrend(): array
    {
        return [500, 600, 750, 700, 900, 1000, 1100];
    }
}
