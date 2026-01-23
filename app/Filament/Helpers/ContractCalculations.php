<?php

namespace App\Filament\Helpers;

use App\Models\AdsOption;
use App\Models\EffAdsOption;
use App\Models\Stand;
use App\Models\Event;
use App\Models\Settings\Price;
use App\Models\SponsorPackage;
use App\Models\AdsPackage;
use App\Models\EffAdsPackage;
use Illuminate\Support\Facades\Cache;

trait ContractCalculations
{
    /**
     * Get cache key for calculation data
     */
    protected static function getCalculationCacheKey($type, $id, $currencyId = null): string
    {
        $key = "calc_{$type}_{$id}";
        if ($currencyId) {
            $key .= "_curr_{$currencyId}";
        }
        return $key;
    }

    /**
     * Get cached data or store it
     */
    protected static function getCached($key, $callback, $ttl = 60)
    {
        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Load stand with caching
     */
    protected static function getStandWithCache($standId)
    {
        if (!$standId) {
            return null;
        }

        return self::getCached(
            self::getCalculationCacheKey('stand', $standId),
            fn() => Stand::find($standId)
        );
    }

    /**
     * Load price with currency relationship (cached)
     */
    protected static function getPriceWithCurrency($priceId, $currencyId)
    {
        if (!$priceId || !$currencyId) {
            return null;
        }

        return self::getCached(
            self::getCalculationCacheKey('price', $priceId, $currencyId),
            fn() => Price::with([
                'Currencies' => fn($q) => $q->where('currencies.id', $currencyId)
            ])->find($priceId)
        );
    }

    /**
     * Load sponsor package with currency relationship (cached)
     */
    protected static function getSponsorPackageWithCurrency($packageId, $currencyId)
    {
        if (!$packageId || !$currencyId) {
            return null;
        }

        return self::getCached(
            self::getCalculationCacheKey('sponsor', $packageId, $currencyId),
            fn() => SponsorPackage::with([
                'Currencies' => fn($q) => $q->where('currencies.id', $currencyId)
            ])->find($packageId)
        );
    }

    /**
     * Load ads option with currency relationship (cached)
     */
    protected static function getAdsOptionWithCurrency($optionId, $currencyId)
    {
        if (!$optionId || !$currencyId) {
            return null;
        }

        return self::getCached(
            self::getCalculationCacheKey('ads_option', $optionId, $currencyId),
            fn() => AdsOption::with([
                'Currencies' => fn($q) => $q->where('currencies.id', $currencyId)
            ])->find($optionId)
        );
    }

    /**
     * Load eff ads option with currency relationship (cached)
     */
    protected static function getEffAdsOptionWithCurrency($optionId, $currencyId)
    {
        if (!$optionId || !$currencyId) {
            return null;
        }

        return self::getCached(
            self::getCalculationCacheKey('eff_ads_option', $optionId, $currencyId),
            fn() => EffAdsOption::with([
                'Currencies' => fn($q) => $q->where('currencies.id', $currencyId)
            ])->find($optionId)
        );
    }

    /**
     * Load event VAT rate with caching
     */
    protected static function getEventVatRate($eventId)
    {
        if (!$eventId) {
            return 0;
        }

        return self::getCached(
            self::getCalculationCacheKey('event_vat', $eventId),
            fn() => Event::find($eventId)?->vat_rate ?? 0
        );
    }

    /**
     * Clear relevant cache when data changes
     */
    protected static function clearCalculationCache($type, $id = null, $currencyId = null): void
    {
        if ($id) {
            // Clear specific cache
            Cache::forget(self::getCalculationCacheKey($type, $id, $currencyId));

            // Also clear related caches
            switch ($type) {
                case 'event_vat':
                    // Clear all calculations that might use this event
                    Cache::forget(self::getCalculationCacheKey('event_vat', $id));
                    break;
            }
        }
    }

//     public static function calculateSpaceAmount(callable $set, callable $get): void
// {
//     // Check if we have a merged stand from the merge component
//     $mergedStandId = $get('merged_stand_id') ?? null;

//     // Use merged stand if available, otherwise use regular stand_id
//     $standId = $mergedStandId ?? $get('stand_id');

//     // Update the stand_id field if needed
//     if ($standId && $standId !== $get('stand_id')) {
//         $set('stand_id', $standId);
//     }

//     if (!$standId) {
//         $set('space_amount', 0);
//         return;
//     }

//     $stand = self::getStandWithCache($standId);
//     if (!$stand) {
//         $set('space_amount', 0);
//         return;
//     }

//     $space = $stand->space;
//     $priceId = $get('price_id');
//     $useSpecialPrice = $get('use_special_price');
//     $specialPrice = $get('price_amount');
//     $currencyId = $get('currency_id');

//     if ($useSpecialPrice) {
//         $amount = $space * ((float) $specialPrice ?? 0);
//     } elseif ($priceId && $currencyId) {
//         $price = self::getPriceWithCurrency($priceId, $currencyId);
//         $priceAmount = $price?->Currencies->first()?->pivot->amount ?? 0;
//         $amount = $space * $priceAmount;
//     } else {
//         $amount = 0;
//     }

//     $set('space_amount', $amount);
//     self::calculateSpaceNet($set, $get);
// }


public static function calculateSpaceAmount(callable $set, callable $get): void
{
    // Check if we have a merged stand from the merge component
    $mergedStandId = $get('merged_stand_id') ?? null;

    // Use merged stand if available, otherwise use regular stand_id
    $standId = $mergedStandId ?? $get('stand_id');

    // Update the stand_id field if needed
    if ($standId && $standId !== $get('stand_id')) {
        $set('stand_id', $standId);
    }

    if (!$standId) {
        $set('space_amount', 0);
        $set('tax_per_sqm_total', 0);
        $set('base_space_amount', 0);
        return;
    }

    $stand = self::getStandWithCache($standId);
    if (!$stand) {
        $set('space_amount', 0);
        $set('tax_per_sqm_total', 0);
        $set('base_space_amount', 0);
        return;
    }

    $space = $stand->space;
    $priceId = $get('price_id');
    $useSpecialPrice = $get('use_special_price');
    $specialPrice = $get('price_amount');
    $currencyId = $get('currency_id');

    // Calculate base space amount (without tax)
    $baseAmount = 0;
    if ($useSpecialPrice) {
        $baseAmount = $space * ((float) $specialPrice ?? 0);
    } elseif ($priceId && $currencyId) {
        $price = self::getPriceWithCurrency($priceId, $currencyId);
        $priceAmount = $price?->Currencies->first()?->pivot->amount ?? 0;
        $baseAmount = $space * $priceAmount;
    }

    // Calculate tax per sqm if enabled
    $taxPerSqmTotal = 0;
    $enableTaxPerSqm = $get('enable_tax_per_sqm') ?? false;
    if ($enableTaxPerSqm) {
        $taxPerSqmAmount = (float) ($get('tax_per_sqm_amount') ?? 0);
        $taxPerSqmTotal = $space * $taxPerSqmAmount;
    }

    // Total space amount = base amount + tax per sqm
    $totalAmount = $baseAmount + $taxPerSqmTotal;

    $set('space_amount', $totalAmount);
    $set('base_space_amount', $baseAmount);
    $set('tax_per_sqm_total', $taxPerSqmTotal);
    self::calculateSpaceNet($set, $get);
}
    public static function calculateSpaceNet(callable $set, callable $get): void
    {
        $spaceAmount = (float) ($get('space_amount') ?? 0);
        $spaceDiscount = (float) ($get('space_discount') ?? 0);
        $spaceNet = $spaceAmount - $spaceDiscount;
        $set('space_net', max(0, $spaceNet));
        self::calculateTotal($set, $get);
    }

    public static function calculateSpecialDesignAmount(callable $set, callable $get): void
    {
        $standId = $get('stand_id');
        $pricePerSqm = (float) ($get('special_design_price') ?? 0);

        if (!$standId || !$pricePerSqm) {
            $set('special_design_amount', 0);
            return;
        }

        $stand = self::getStandWithCache($standId);
        if (!$stand) {
            $set('special_design_amount', 0);
            return;
        }

        $space = $stand->space;
        $amount = $space * $pricePerSqm;

        $set('special_design_amount', $amount);
        self::calculateTotal($set, $get);
    }

    public static function calculateSponsorAmount(callable $set, callable $get): void
    {
        $packageId = $get('sponsor_package_id');
        $currencyId = $get('currency_id');

        if (!$packageId || !$currencyId) {
            $set('sponsor_amount', 0);
            return;
        }

        $package = self::getSponsorPackageWithCurrency($packageId, $currencyId);
        $amount = $package?->Currencies->first()?->pivot->total_price ?? 0;

        $set('sponsor_amount', $amount);
        self::calculateSponsorNet($set, $get);
    }

    public static function calculateSponsorNet(callable $set, callable $get): void
    {
        $sponsorAmount = (float) ($get('sponsor_amount') ?? 0);
        $sponsorDiscount = (float) ($get('sponsor_discount') ?? 0);
        $sponsorNet = $sponsorAmount - $sponsorDiscount;
        $set('sponsor_net', max(0, $sponsorNet));
        self::calculateTotal($set, $get);
    }

    protected static function calculateAdsAmount(callable $set, callable $get, array $selectedOptions): void
    {
        $currencyId = $get('currency_id');

        if (!$currencyId || empty($selectedOptions)) {
            $set('advertisment_amount', 0);
            $set('ads_net', 0);
            return;
        }

        $total = 0;

        foreach ($selectedOptions as $selection) {
            if (str_contains($selection, '_')) {
                [$packageId, $optionId] = explode('_', $selection, 2);

                if ($optionId) {
                    $option = self::getAdsOptionWithCurrency($optionId, $currencyId);
                    if ($option) {
                        $price = $option->Currencies
                            ->firstWhere('id', $currencyId)?->pivot->price ?? 0;
                        $total += $price;
                    }
                }
            }
        }

        $set('advertisment_amount', $total);
        self::calculateAdsNet($set, $get);
    }

    public static function calculateAdsNet(callable $set, callable $get): void
    {
        $adsAmount = (float) ($get('advertisment_amount') ?? 0);
        $adsDiscount = (float) ($get('ads_discount') ?? 0);
        $adsNet = $adsAmount - $adsDiscount;
        $set('ads_net', max(0, $adsNet));
        self::calculateTotal($set, $get);
    }

    protected static function calculateEffAdsAmount(callable $set, callable $get, array $selectedOptions): void
    {
        $currencyId = $get('currency_id');

        if (!$currencyId || empty($selectedOptions)) {
            $set('eff_ads_amount', 0);
            $set('eff_ads_net', 0);
            return;
        }

        $total = 0;

        foreach ($selectedOptions as $selection) {
            if (str_contains($selection, '_')) {
                [$packageId, $optionId] = explode('_', $selection, 2);

                if ($optionId) {
                    $option = self::getEffAdsOptionWithCurrency($optionId, $currencyId);
                    if ($option) {
                        $price = $option->Currencies
                            ->firstWhere('id', $currencyId)?->pivot->price ?? 0;
                        $total += $price;
                    }
                }
            }
        }

        $set('eff_ads_amount', $total);
        self::calculateEffAdsNet($set, $get);
    }

    public static function calculateEffAdsNet(callable $set, callable $get): void
    {
        $effAdsAmount = (float) ($get('eff_ads_amount') ?? 0);
        $effAdsDiscount = (float) ($get('eff_ads_discount') ?? 0);
        $effAdsNet = $effAdsAmount - $effAdsDiscount;

        // Remove the dd() call for production
        // dd($effAdsAmount, $effAdsDiscount, $effAdsNet);

        $set('eff_ads_net', max(0, $effAdsNet));
        self::calculateTotal($set, $get);
    }

    // public static function calculateTotal(callable $set, callable $get): void
    // {
    //     $spaceAmount = (float) ($get('space_amount') ?? 0);
    //     $sponsorAmount = (float) ($get('sponsor_amount') ?? 0);
    //     $adsAmount = (float) ($get('advertisment_amount') ?? 0);
    //     $effAdsAmount = (float) ($get('eff_ads_amount') ?? 0);
    //     $waterElectricity = (float) ($get('water_electricity_amount') ?? 0);
    //     $specialDesign = (float) ($get('special_design_amount') ?? 0);

    //     $subTotal1 = $spaceAmount + $sponsorAmount + $adsAmount + $effAdsAmount + $waterElectricity + $specialDesign;
    //     $set('sub_total_1', $subTotal1);

    //     $spaceDiscount = (float) ($get('space_discount') ?? 0);
    //     $sponsorDiscount = (float) ($get('sponsor_discount') ?? 0);
    //     $adsDiscount = (float) ($get('ads_discount') ?? 0);
    //     $effAdsDiscount = (float) ($get('eff_ads_discount') ?? 0);

    //     $d_i_a = $spaceDiscount + $sponsorDiscount + $adsDiscount + $effAdsDiscount;
    //     $set('d_i_a', $d_i_a);

    //     $subTotal2 = $subTotal1 - $d_i_a;
    //     $set('sub_total_2', $subTotal2);

    //     $eventId = $get('event_id');
    //     $vatRate = self::getEventVatRate($eventId);
    //     $vatAmount = $subTotal2 * ($vatRate / 100);
    //     $set('vat_amount', $vatAmount);

    //     $netTotal = $subTotal2 + $vatAmount;
    //     $set('net_total', $netTotal);
    // }

    public static function calculateTotal(callable $set, callable $get): void
{
    $spaceAmount = (float) ($get('space_amount') ?? 0);
    $sponsorAmount = (float) ($get('sponsor_amount') ?? 0);
    $adsAmount = (float) ($get('advertisment_amount') ?? 0);
    $effAdsAmount = (float) ($get('eff_ads_amount') ?? 0);
    $waterElectricity = (float) ($get('water_electricity_amount') ?? 0);
    $specialDesign = (float) ($get('special_design_amount') ?? 0);

    // Get tax per sqm total
    $taxPerSqmTotal = (float) ($get('tax_per_sqm_total') ?? 0);

    // Note: space_amount already includes tax_per_sqm_total, so we don't add it again here
    $subTotal1 = $spaceAmount + $sponsorAmount + $adsAmount + $effAdsAmount + $waterElectricity + $specialDesign;
    $set('sub_total_1', $subTotal1);

    $spaceDiscount = (float) ($get('space_discount') ?? 0);
    $sponsorDiscount = (float) ($get('sponsor_discount') ?? 0);
    $adsDiscount = (float) ($get('ads_discount') ?? 0);
    $effAdsDiscount = (float) ($get('eff_ads_discount') ?? 0);

    $d_i_a = $spaceDiscount + $sponsorDiscount + $adsDiscount + $effAdsDiscount;
    $set('d_i_a', $d_i_a);

    $subTotal2 = $subTotal1 - $d_i_a;
    $set('sub_total_2', $subTotal2);

    $eventId = $get('event_id');
    $vatRate = self::getEventVatRate($eventId);
    $vatAmount = $subTotal2 * ($vatRate / 100);
    $set('vat_amount', $vatAmount);

    $netTotal = $subTotal2 + $vatAmount;
    $set('net_total', $netTotal);
}
    /**
     * Batch clear all calculation caches for a contract
     * Useful when contract is updated or deleted
     */
    public static function clearAllCalculationCaches($contract): void
    {
        if (!$contract) return;

        // Clear stand cache
        if ($contract->stand_id) {
            self::clearCalculationCache('stand', $contract->stand_id);
        }

        // Clear price cache
        if ($contract->price_id && $contract->currency_id) {
            self::clearCalculationCache('price', $contract->price_id, $contract->currency_id);
        }

        // Clear sponsor cache
        if ($contract->sponsor_package_id && $contract->currency_id) {
            self::clearCalculationCache('sponsor', $contract->sponsor_package_id, $contract->currency_id);
        }

        // Clear event VAT cache
        if ($contract->event_id) {
            self::clearCalculationCache('event_vat', $contract->event_id);
        }

        // Clear form data cache
        if ($contract->event_id && $contract->report_id) {
            // Assuming you have this method in your resource class
            if (method_exists(self::class, 'clearFormCache')) {
                self::clearFormCache($contract->event_id, $contract->report_id);
            }
        }
    }
}
