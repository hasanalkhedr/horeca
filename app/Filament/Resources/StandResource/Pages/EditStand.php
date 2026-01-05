<?php

namespace App\Filament\Resources\StandResource\Pages;

use App\Filament\Resources\StandResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStand extends EditRecord
{
    protected static string $resource = StandResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
