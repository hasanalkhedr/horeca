<?php

namespace App\Filament\Resources\ReportResource\Pages;

use App\Filament\Resources\EventResource;
use App\Filament\Resources\ReportResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateReport extends CreateRecord
{
    protected static string $resource = EventResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Process components field - ensure it's stored properly
        if (isset($data['components']) && is_string($data['components'])) {
            $components = array_map('trim', explode(',', $data['components']));
            $data['components'] = $components;
        }

        return $data;
    }
}
