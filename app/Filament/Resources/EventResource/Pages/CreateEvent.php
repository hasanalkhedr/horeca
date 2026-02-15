<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use App\Models\Settings\Currency;
use App\Models\Settings\Price;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateEvent extends CreateRecord
{
    protected static string $resource = EventResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // Create the basic event record
        $event = parent::handleRecordCreation($data);

        // Process event currencies
        $this->syncEventCurrencies($event, $data);

        // Process price packages
        $this->syncPricePackages($event, $data);

        // Process categories and packages
        $this->syncCategoriesAndPackages($event, $data);

        return $event;
    }

    private function syncEventCurrencies($event, $data): void
    {
        $currencies = Currency::all();
        $eventCurrenciesData = [];

        foreach ($currencies as $currency) {
            $enabledField = "event_currency_{$currency->id}_enabled";
            //$minPriceField = "event_currency_{$currency->id}_min_price";

            if (isset($data[$enabledField]) && $data[$enabledField]) {
                //$minPrice = $data[$minPriceField] ?? 0;
                $eventCurrenciesData[$currency->id] = ['min_price' => 0];
            }
        }

        $event->Currencies()->sync($eventCurrenciesData);
    }

    private function syncPricePackages($event, $data): void
    {
        if (isset($data['price_packages'])) {
            foreach ($data['price_packages'] as $packageData) {
                if (!empty($packageData['name'])) {
                    // Create price package
                    $price = Price::create([
                        'event_id' => $event->id,
                        'name' => $packageData['name'],
                        'description' => $packageData['description'] ?? null,
                    ]);

                    // Set currency prices for this package
                    $currencies = Currency::all();
                    $priceCurrenciesData = [];

                    foreach ($currencies as $currency) {
                        $enabledField = "price_package_currency_{$currency->id}_enabled";
                        $priceField = "price_package_currency_{$currency->id}_price";

                        if (isset($packageData[$enabledField]) && $packageData[$enabledField]) {
                            $priceAmount = $packageData[$priceField] ?? 0;
                            $priceCurrenciesData[$currency->id] = ['amount' => $priceAmount];
                        }
                    }

                    if (!empty($priceCurrenciesData)) {
                        $price->Currencies()->sync($priceCurrenciesData);
                    }
                }
            }
        }
    }

    private function syncCategoriesAndPackages($event, $data): void
    {
        // Sync Categories
        if (isset($data['categories'])) {
            $event->Categories()->sync($data['categories']);
        }

        // Sync Sponsor Packages
        if (isset($data['sponsor_packages'])) {
            $event->SponsorPackages()->sync($data['sponsor_packages']);
        }

        // Sync Ads Packages
        if (isset($data['ads_packages'])) {
            $event->AdsPackages()->sync($data['ads_packages']);
        }

        // Sync Eff Ads Packages
        if (isset($data['eff_ads_packages'])) {
            $event->EffAdsPackages()->sync($data['eff_ads_packages']);
        }
    }
}
