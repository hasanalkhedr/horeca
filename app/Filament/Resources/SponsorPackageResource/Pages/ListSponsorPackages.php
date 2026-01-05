<?php

namespace App\Filament\Resources\SponsorPackageResource\Pages;

use App\Filament\Resources\SponsorPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSponsorPackages extends ListRecords
{
    protected static string $resource = SponsorPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-o-plus-circle')
                ->label('New Package'),
        ];
    }
}
