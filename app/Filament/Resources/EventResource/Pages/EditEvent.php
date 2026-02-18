<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use App\Models\Settings\Currency;
use App\Models\Settings\Price;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditEvent extends EditRecord
{
    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\DeleteAction::make(),
        ];
    }


protected function mutateFormDataBeforeFill(array $data): array
{
    $record = $this->record;
    $currencies = Currency::all();

    // Fill event currencies data
    foreach ($currencies as $currency) {
        $currencyData = $record->Currencies->where('id', $currency->id)->first();
        $data["event_currency_{$currency->id}_enabled"] = $currencyData ? true : false;
        //$data["event_currency_{$currency->id}_min_price"] = $currencyData ? $currencyData->pivot->min_price : 0;
    }

    // Fill price packages data - FIXED VERSION
    $pricePackagesData = [];
    foreach ($record->Prices as $price) {
        $packageData = [
            'name' => $price->name,
            'description' => $price->description ?? '',
        ];

        // Load currency prices for this price package
        $price->load('Currencies'); // Ensure currencies are loaded
        $priceCurrencies = $price->Currencies->keyBy('id');

        foreach ($currencies as $currency) {
            $hasCurrency = $priceCurrencies->has($currency->id);
            $packageData["price_package_currency_{$currency->id}_enabled"] = $hasCurrency;
            $packageData["price_package_currency_{$currency->id}_price"] = $hasCurrency ? $priceCurrencies[$currency->id]->pivot->amount : 0;
        }

        $pricePackagesData[] = $packageData;
    }

    $data['prices'] = $pricePackagesData;

    // Load the event with its price packages
    $event = $this->record;

    // Manually set the IDs in the price_packages array
    if ($event->relationLoaded('Prices') || $event->Prices) {
        $data['prices'] = $event->Prices->map(function ($price) {
            $priceData = $price->toArray();

            // Add currency data for each price package
            foreach ($price->Currencies as $currency) {
                $priceData["price_package_currency_{$currency->id}_enabled"] = true;
                $priceData["price_package_currency_{$currency->id}_price"] = $currency->pivot->amount ?? 0;
            }

            return $priceData;
        })->toArray();
    }
    // Fill categories and packages data
    $data['categories'] = $record->Categories->pluck('id')->toArray();
    $data['sponsor_packages'] = $record->SponsorPackages->pluck('id')->toArray();
    $data['ads_packages'] = $record->AdsPackages->pluck('id')->toArray();
    $data['eff_ads_packages'] = $record->EffAdsPackages->pluck('id')->toArray();

    return $data;
}

    protected function handleRecordUpdate($record, array $data): Model
    {
        // Update basic event fields
        $record->update([
            'CODE' => $data['CODE'],
            'name' => $data['name'],
            'description' => $data['description'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'apply_start_date' => $data['apply_start_date'],
            'apply_deadline_date' => $data['apply_deadline_date'],
            'total_space' => $data['total_space'],
            'space_to_sell' => $data['space_to_sell'],
            'country' => $data['country'],
            'city' => $data['city'],
            'address' => $data['address'],
            'vat_rate' => $data['vat_rate'],
        ]);

        // Sync event currencies
        $this->syncEventCurrencies($record, $data);

        // Sync price packages
        $this->syncPricePackages($record, $data);

        // Sync categories and packages
        $this->syncCategoriesAndPackages($record, $data);

        return $record;
    }

    private function syncEventCurrencies($event, $data): void
    {
        $currencies = Currency::all();
        $eventCurrenciesData = [];

        foreach ($currencies as $currency) {
            $enabledField = "event_currency_{$currency->id}_enabled";
            //$minPriceField = "event_currency_{$currency->id}_min_price";

            if (isset($data[$enabledField]) && $data[$enabledField]) {
                $eventCurrenciesData[$currency->id] = ['min_price' => 0];
            }
        }

        $event->Currencies()->sync($eventCurrenciesData);
    }

    // private function syncPricePackages($event, $data): void
    // {
    //     // Delete all existing price packages
    //     $event->Prices()->delete();

    //     // Create new price packages
    //     if (isset($data['prices'])) {
    //         foreach ($data['prices'] as $packageData) {
    //             if (!empty($packageData['name'])) {
    //                 // Create price package
    //                 $price = Price::create([
    //                     'event_id' => $event->id,
    //                     'name' => $packageData['name'],
    //                     'description' => $packageData['description'] ?? null,
    //                 ]);

    //                 // Set currency prices for this package
    //                 $currencies = Currency::all();
    //                 $priceCurrenciesData = [];

    //                 foreach ($currencies as $currency) {
    //                     $enabledField = "price_package_currency_{$currency->id}_enabled";
    //                     $priceField = "price_package_currency_{$currency->id}_price";

    //                     if (isset($packageData[$enabledField]) && $packageData[$enabledField]) {
    //                         $priceAmount = $packageData[$priceField] ?? 0;
    //                         $priceCurrenciesData[$currency->id] = ['amount' => $priceAmount];
    //                     }
    //                 }

    //                 if (!empty($priceCurrenciesData)) {
    //                     $price->Currencies()->sync($priceCurrenciesData);
    //                 }
    //             }
    //         }
    //     }
    // }


    private function syncPricePackages($event, $data): void
{
    if (!isset($data['prices'])) {
        return;
    }

    // Get existing price packages
    $existingPrices = $event->Prices()->get()->keyBy('id');
    $processedIds = [];

    foreach ($data['prices'] as $packageData) {
        if (empty($packageData['name'])) {
            continue;
        }

        // Check if this package has an ID (existing) or is new
        $priceId = $packageData['id'] ?? null;
        $price = null;

        if ($priceId && $existingPrices->has($priceId)) {
            // Update existing price package
            $price = $existingPrices->get($priceId);
            $price->update([
                'name' => $packageData['name'],
                'description' => $packageData['description'] ?? null,
            ]);
            $processedIds[] = $priceId;
        } else {
            // Create new price package
            $price = Price::create([
                'event_id' => $event->id,
                'name' => $packageData['name'],
                'description' => $packageData['description'] ?? null,
            ]);
        }

        // Handle currency prices for this package
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

        // Sync currencies for this price package
        if (!empty($priceCurrenciesData)) {
            $price->Currencies()->sync($priceCurrenciesData);
        } else {
            // If no currencies selected, detach all
            $price->Currencies()->detach();
        }
    }
    // Delete price packages that were not in the submitted data
    $pricesToDelete = $existingPrices->whereNotIn('id', $processedIds);
    foreach ($pricesToDelete as $priceToDelete) {
        // This will also detach related currencies due to database cascade or you can explicitly detach
        $priceToDelete->Currencies()->detach();
        $priceToDelete->delete();
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
