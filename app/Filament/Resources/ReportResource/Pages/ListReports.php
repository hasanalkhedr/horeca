<?php

namespace App\Filament\Resources\ReportResource\Pages;

use App\Filament\Resources\ReportResource;
use App\Models\Report;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReports extends ListRecords
{
    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
            Actions\Action::make('New Contract Template')
                    ->label('New Contract Template')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->color('primary')
                    ->url(route('report.builder'))
                    ->openUrlInNewTab(),
        ];
    }
}
