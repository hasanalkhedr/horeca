<?php

namespace App\Filament\Resources\EffAdsPackageResource\Pages;

use App\Filament\Resources\EffAdsPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEffAdsPackages extends ListRecords
{
    protected static string $resource = EffAdsPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-o-plus-circle')
                ->label('New Package'),
        ];
    }
}
