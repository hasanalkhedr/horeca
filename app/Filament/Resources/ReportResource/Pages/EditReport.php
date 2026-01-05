<?php

namespace App\Filament\Resources\ReportResource\Pages;

use App\Filament\Resources\ReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReport extends EditRecord
{
    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Convert components array to string for display
        if (isset($data['components']) && is_array($data['components'])) {
            $data['components'] = implode(', ', $data['components']);
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Process components field - convert from string to array
        if (isset($data['components']) && is_string($data['components'])) {
            $components = array_map('trim', explode(',', $data['components']));
            $data['components'] = $components;
        }

        return $data;
    }
}
