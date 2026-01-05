<?php

namespace App\Filament\Resources\SponsorPackageResource\Pages;

use App\Filament\Resources\SponsorPackageResource;
use App\Models\Settings\Currency;
use App\Models\SponsorOption;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSponsorPackage extends EditRecord
{
    protected static string $resource = SponsorPackageResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Pre-fill currency checkboxes and prices
        $record = $this->getRecord();
        $currencies = Currency::all();

        // Get existing currency prices
        $existingPrices = $record->Currencies->mapWithKeys(function ($currency) {
            return [$currency->id => $currency->pivot->total_price];
        })->toArray();

        foreach ($currencies as $currency) {
            $enabledKey = "currency_{$currency->id}_enabled";
            $priceKey = "currency_{$currency->id}_price";

            // Check if currency is already attached
            $data[$enabledKey] = isset($existingPrices[$currency->id]);

            // Set price if currency is attached
            if (isset($existingPrices[$currency->id])) {
                $data[$priceKey] = $existingPrices[$currency->id];
            } else {
                $data[$priceKey] = 0;
            }
        }

        // Pre-fill option checkboxes and pivot data
        $options = SponsorOption::all();

        // Get existing option pivot data
        $existingOptions = $record->SponsorOptions->mapWithKeys(function ($option) {
            return [
                $option->id => [
                    // 'quantity' => $option->pivot->quantity,
                    // 'is_included' => $option->pivot->is_included
                ]
            ];
        })->toArray();

        foreach ($options as $option) {
            $enabledKey = "option_{$option->id}_enabled";
            // $quantityKey = "option_{$option->id}_quantity";
            // $includedKey = "option_{$option->id}_included";

            // Check if option is already attached
            $data[$enabledKey] = isset($existingOptions[$option->id]);

            // Set pivot data if option is attached
            // if (isset($existingOptions[$option->id])) {
            //     $data[$quantityKey] = $existingOptions[$option->id]['quantity'];
            //     $data[$includedKey] = $existingOptions[$option->id]['is_included'];
            // } else {
            //     $data[$quantityKey] = 1;
            //     $data[$includedKey] = true;
            // }
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Collect currency selections
        $currencyPrices = [];
        $currencies = Currency::all();

        foreach ($currencies as $currency) {
            $enabledKey = "currency_{$currency->id}_enabled";
            $priceKey = "currency_{$currency->id}_price";

            if (isset($data[$enabledKey]) && $data[$enabledKey] && isset($data[$priceKey]) && $data[$priceKey] > 0) {
                $currencyPrices[$currency->id] = [
                    'total_price' => $data[$priceKey]
                ];
            }

            // Remove temporary form fields
            unset($data[$enabledKey], $data[$priceKey]);
        }

        // Collect option selections
        $optionPivotData = [];
        $options = SponsorOption::all();

        foreach ($options as $option) {
            $enabledKey = "option_{$option->id}_enabled";
            // $quantityKey = "option_{$option->id}_quantity";
            // $includedKey = "option_{$option->id}_included";

            if (isset($data[$enabledKey]) && $data[$enabledKey]) {
                $optionPivotData[$option->id] = [
                    // 'quantity' => $data[$quantityKey] ?? 1,
                    // 'is_included' => $data[$includedKey] ?? true
                ];
            }

            // Remove temporary form fields
            unset($data[$enabledKey]);//, $data[$quantityKey], $data[$includedKey]);
        }

        // Handle new options creation from repeater
        if (!empty($data['new_options']) && is_array($data['new_options'])) {
            foreach ($data['new_options'] as $newOptionData) {
                if (!empty($newOptionData['title']) ) {
                    $newOption = SponsorOption::create([
                        'title' => $newOptionData['title'],
                    ]);

                    $optionPivotData[$newOption->id] = [
                    ];
                }
            }
        }

        // Remove new options repeater field
        unset($data['new_options']);

        // Store for afterSave
        $this->currencyPrices = $currencyPrices;
        $this->optionPivotData = $optionPivotData;

        return $data;
    }

    protected function afterSave(): void
    {
        // Sync currencies with prices
        if (isset($this->currencyPrices)) {
            $this->record->Currencies()->sync($this->currencyPrices);
        }

        // Sync options with pivot data
        if (isset($this->optionPivotData)) {
            $this->record->SponsorOptions()->sync($this->optionPivotData);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->before(function ($record) {
                    if ($record->Contracts()->exists()) {
                        throw new \Exception('Cannot delete package with existing contracts. Delete the contracts first.');
                    }
                }),
        ];
    }
}


