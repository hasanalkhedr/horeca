<?php

namespace App\Filament\Resources\ContractResource\Pages;

use App\Filament\Resources\ContractResource;
use App\Filament\Resources\ContractResource\Widgets\ContractStatisticsWidget;
use App\Filament\Resources\ContractResource\Widgets\ContractTypeChartWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContracts extends ListRecords
{
    protected static string $resource = ContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ContractStatisticsWidget::class,
            ContractTypeChartWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 7;
    }

    public function updatedTableFilters(): void
    {
        $filters = $this->tableFilters ?? [];
        $hasActiveFilters = false;

        $normalize = function (mixed $payload): mixed {
            if (is_array($payload) && isset($payload[0]) && is_array($payload[0])) {
                return $payload[0];
            }

            return $payload;
        };

        foreach ($filters as $filter) {
            $filter = $normalize($filter);

            if (!is_array($filter)) {
                continue;
            }

            if (isset($filter['values']) && !empty($filter['values'])) {
                $hasActiveFilters = true;
                break;
            }

            if (isset($filter['value']) && $filter['value'] !== null && $filter['value'] !== '' && $filter['value'] !== []) {
                $hasActiveFilters = true;
                break;
            }

            if (isset($filter['isActive']) && $filter['isActive'] === true) {
                $hasActiveFilters = true;
                break;
            }

            if ((isset($filter['from']) && !empty($filter['from'])) || (isset($filter['until']) && !empty($filter['until']))) {
                $hasActiveFilters = true;
                break;
            }
        }

        if ($hasActiveFilters) {
            $this->dispatch('refresh-widget', $filters);
        } else {
            $this->dispatch('refresh-widget', null);
        }
    }

    public function updatedTableSearch(): void
    {
        // Search affects table results; keep widget in sync with the current filters.
        $this->dispatch('refresh-widget', $this->tableFilters ?? []);
    }

    protected function tableResetFilters(): void
    {
        // Dispatch event to notify widget of filter reset
        $this->dispatch('refresh-widget', null);
    }

    protected function tableClearedFilters(): void
    {
        // Dispatch event to notify widget of filter clear
        $this->dispatch('refresh-widget', null);
    }
}
