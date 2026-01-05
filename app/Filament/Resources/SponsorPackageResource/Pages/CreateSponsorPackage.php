<?php

namespace App\Filament\Resources\SponsorPackageResource\Pages;

use App\Filament\Resources\SponsorPackageResource;
use App\Models\Settings\Currency;
use App\Models\SponsorOption;
use Filament\Resources\Pages\CreateRecord;

class CreateSponsorPackage extends CreateRecord
{
    protected static string $resource = SponsorPackageResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
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
                if (!empty($newOptionData['title'])) {
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

        // Store for afterCreate
        $this->currencyPrices = $currencyPrices;
        $this->optionPivotData = $optionPivotData;

        return $data;
    }

    protected function afterCreate(): void
    {
        // Attach currencies with prices
        if (!empty($this->currencyPrices)) {
            $this->record->Currencies()->sync($this->currencyPrices);
        }

        // Attach options with pivot data
        if (!empty($this->optionPivotData)) {
            $this->record->SponsorOptions()->sync($this->optionPivotData);
        }
    }
}
