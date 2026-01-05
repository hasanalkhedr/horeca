<?php

namespace App\Filament\Resources\EffAdsPackageResource\Pages;

use App\Filament\Resources\EffAdsPackageResource;
use App\Models\EffAdsOption;
use App\Models\Settings\Currency;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateEffAdsPackage extends CreateRecord
{
    protected static string $resource = EffAdsPackageResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // Create the package first
        $package = static::getModel()::create([
            'title' => $data['title'],
            'description' => $data['description'],
        ]);

        // Attach package currencies with base prices
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

        if (!empty($packageCurrenciesData)) {
            $package->Currencies()->sync($packageCurrenciesData);
        }

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

        // Attach existing options
        if (!empty($existingOptionIds)) {
            $package->EffAdsOptions()->attach($existingOptionIds);
        }

        // Create new options with their currency prices
        if (isset($data['new_options'])) {
            $newOptionIds = [];

            foreach ($data['new_options'] as $newOptionData) {
                if (!empty($newOptionData['title'])) {
                    // Create new option
                    $option = EffAdsOption::create([
                        'title' => $newOptionData['title'],
                        'description' => $newOptionData['description'] ?? null,
                    ]);

                    // Store the option ID to attach later
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

            // Attach all newly created options to the package
            if (!empty($newOptionIds)) {
                $package->EffAdsOptions()->attach($newOptionIds);
            }
        }

        return $package;
    }
}
