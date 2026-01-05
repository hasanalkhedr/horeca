<?php

namespace App\Filament\Resources\AdsPackageResource\Pages;

use App\Filament\Resources\AdsPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAdsPackages extends ListRecords
{
    protected static string $resource = AdsPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-o-plus-circle')
                ->label('New Package'),
        ];
    }
}
