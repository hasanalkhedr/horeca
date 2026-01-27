<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use App\Filament\Resources\EventResource\Widgets\EventSelectionStatsWidget;
use App\Filament\Resources\EventResource\Widgets\EventStatsWidget;
use App\Filament\Resources\EventResource\Widgets\LiveEventSelectionStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEvents extends ListRecords
{
    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            //EventStatsWidget::class,
        ];
    }
}
