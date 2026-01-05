<?php
// App\Filament\Resources\AdsPackageResource\Pages\EditAdsPackage.php

namespace App\Filament\Resources\AdsPackageResource\Pages;

use App\Filament\Resources\AdsPackageResource;
use App\Models\AdsOption;
use App\Models\Settings\Currency;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditAdsPackage extends EditRecord
{
    protected static string $resource = AdsPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->record;
        $currencies = Currency::all();

        // Fill package currencies data as checkboxes
        foreach ($currencies as $currency) {
            $currencyPrice = $record->Currencies->where('id', $currency->id)->first();
            $data["package_currency_{$currency->id}_enabled"] = $currencyPrice ? true : false;
            $data["package_currency_{$currency->id}_price"] = $currencyPrice ? $currencyPrice->pivot->total_price : 0;
        }

        // Fill existing options checkboxes
        foreach ($record->AdsOptions as $option) {
            $data["existing_option_{$option->id}"] = true;
        }

        return $data;
    }

    protected function handleRecordUpdate($record, array $data): Model
    {
        // Update basic fields
        $record->update([
            'title' => $data['title'],
            'description' => $data['description'],
        ]);

        // Sync package currencies with base prices
        $currencies = Currency::all();
        $packageCurrenciesData = [];

        foreach ($currencies as $currency) {
            $enabledField = "package_currency_{$currency->id}_enabled";
            $priceField = "package_currency_{$currency->id}_price";

            if (isset($data[$enabledField]) && $data[$enabledField]) {
                $price = $data[$priceField] ?? 0;
                $packageCurrenciesData[$currency->id] = ['total_price' => $price];
            }
        }

        $record->Currencies()->sync($packageCurrenciesData);

        // Process existing options (from checkbox list)
        $existingOptionIds = [];
        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'existing_option_') && $value === true) {
                $optionId = str_replace('existing_option_', '', $key);
                if (is_numeric($optionId)) {
                    $existingOptionIds[] = (int)$optionId;
                }
            }
        }

        // Create new options with their currency prices
        $newOptionIds = [];
        if (isset($data['new_options'])) {
            foreach ($data['new_options'] as $newOptionData) {
                if (!empty($newOptionData['title'])) {
                    // Create new option
                    $option = AdsOption::create([
                        'title' => $newOptionData['title'],
                        'description' => $newOptionData['description'] ?? null,
                    ]);

                    // Store the new option ID
                    $newOptionIds[] = $option->id;

                    // Set option currency prices
                    $optionCurrenciesData = [];

                    foreach ($currencies as $currency) {
                        $enabledField = "new_currency_{$currency->id}_enabled";
                        $priceField = "new_currency_{$currency->id}_price";

                        if (isset($newOptionData[$enabledField]) && $newOptionData[$enabledField]) {
                            $price = $newOptionData[$priceField] ?? 0;
                            $optionCurrenciesData[$currency->id] = ['price' => $price];
                        }
                    }

                    if (!empty($optionCurrenciesData)) {
                        $option->Currencies()->sync($optionCurrenciesData);
                    }
                }
            }
        }

        // Combine existing and new option IDs
        $allOptionIds = array_merge($existingOptionIds, $newOptionIds);

        // Sync all options to the package
        $record->AdsOptions()->sync($allOptionIds);

        return $record;
    }
}
