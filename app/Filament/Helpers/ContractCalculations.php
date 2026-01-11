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

trait ContractCalculations
{
    public static function calculateSpaceAmount(callable $set, callable $get): void
    {
        $standId = $get('stand_id');
        $priceId = $get('price_id');
        $useSpecialPrice = $get('use_special_price');
        $specialPrice = $get('price_amount');
        $currencyId = $get('currency_id');

        if (!$standId) {
            $set('space_amount', 0);
            return;
        }

        $stand = Stand::find($standId);
        if (!$stand) {
            $set('space_amount', 0);
            return;
        }

        $space = $stand->space;

        if ($useSpecialPrice) {
            $amount = $space * ($specialPrice ?? 0);
        } elseif ($priceId) {
            $price = Price::with(['Currencies' => function($query) use ($currencyId) {
                $query->where('currencies.id', $currencyId);
            }])->find($priceId);

            $priceAmount = $price?->Currencies()->where('currencies.id', $currencyId)->first()?->pivot->amount ?? 0;
            $amount = $space * $priceAmount;
        } else {
            $amount = 0;
        }

        $set('space_amount', $amount);
        self::calculateSpaceNet($set, $get);
    }

    public static function calculateSpaceNet(callable $set, callable $get): void
    {
        $spaceAmount = $get('space_amount') ?? 0;
        $spaceDiscount = $get('space_discount') ?? 0;
        $spaceNet = $spaceAmount - $spaceDiscount;
        $set('space_net', max(0, $spaceNet));
        self::calculateTotal($set, $get);
    }

    public static function calculateSpecialDesignAmount(callable $set, callable $get): void
    {
        $standId = $get('stand_id');
        $pricePerSqm = $get('special_design_price') ?? 0;

        if (!$standId || !$pricePerSqm) {
            $set('special_design_amount', 0);
            return;
        }

        $stand = Stand::find($standId);
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

        $package = SponsorPackage::with(['Currencies' => function($query) use ($currencyId) {
            $query->where('currencies.id', $currencyId);
        }])->find($packageId);

        $amount = $package?->Currencies->first()?->pivot->total_price ?? 0;

        $set('sponsor_amount', $amount);
        self::calculateSponsorNet($set, $get);
    }

    public static function calculateSponsorNet(callable $set, callable $get): void
    {
        $sponsorAmount = $get('sponsor_amount') ?? 0;
        $sponsorDiscount = $get('sponsor_discount') ?? 0;
        $sponsorNet = $sponsorAmount - $sponsorDiscount;
        $set('sponsor_net', max(0, $sponsorNet));
        self::calculateTotal($set, $get);
    }

    public static function calculateAdsAmount(callable $set, callable $get, ?array $adsSelections = null): void
    {
        $currencyId = $get('currency_id');
        $total = 0;
        foreach ($adsSelections as $selection) {
            $option = AdsOption::find($selection);
            if ($option) {
                $price = $option->Currencies
                    ->where('id', $currencyId)
                    ->first()?->pivot->price ?? 0;
                $total += $price;
            }
        }
        $set('advertisment_amount', $total);
        self::calculateAdsNet($set, $get);
    }

    public static function calculateAdsNet(callable $set, callable $get): void
    {
        $adsAmount = $get('advertisment_amount') ?? 0;
        $adsDiscount = $get('ads_discount') ?? 0;
        $adsNet = $adsAmount - $adsDiscount;
        $set('ads_net', max(0, $adsNet));
        self::calculateTotal($set, $get);
    }

    public static function calculateEffAdsAmount(callable $set, callable $get, ?array $effAdsSelections = null): void
    {
        $currencyId = $get('currency_id');
        $total = 0;

        foreach ($effAdsSelections as $selection) {
            $option = EffAdsOption::find($selection);
            if ($option) {
                $price = $option->Currencies
                    ->where('id', $currencyId)
                    ->first()?->pivot->price ?? 0;
                $total += $price;
            }
        }

        $set('eff_ads_amount', $total);
        self::calculateEffAdsNet($set, $get);
    }

    public static function calculateEffAdsNet(callable $set, callable $get): void
    {
        $effAdsAmount = $get('eff_ads_amount') ?? 0;
        $effAdsDiscount = $get('eff_ads_discount') ?? 0;
        $effAdsNet = $effAdsAmount - $effAdsDiscount;
        $set('eff_ads_net', max(0, $effAdsNet));
        self::calculateTotal($set, $get);
    }

    public static function calculateTotal(callable $set, callable $get): void
    {
        $spaceAmount = $get('space_amount') ?? 0;
        $sponsorAmount = $get('sponsor_amount') ?? 0;
        $adsAmount = $get('advertisment_amount') ?? 0;
        $effAdsAmount = $get('eff_ads_amount') ?? 0;
        $waterElectricity = $get('water_electricity_amount') ?? 0;
        $specialDesign = $get('special_design_amount') ?? 0;

        // $spaceNet = $get('space_net') ?? 0;
        // $sponsorNet = $get('sponsor_net') ?? 0;
        // $adsNet = $get('ads_net') ?? 0;
        // $effAdsNet = $get('eff_ads_net') ?? 0;

        $subTotal1 = $spaceAmount + $sponsorAmount + $adsAmount + $effAdsAmount + $waterElectricity + $specialDesign;
        $set('sub_total_1', $subTotal1);

        $spaceDiscount = $get('space_discount') ?? 0;
        $sponsorDiscount = $get('sponsor_discount') ?? 0;
        $adsDiscount = $get('ads_discount') ?? 0;
        $effAdsDiscount = $get('eff_ads_discount') ?? 0;

        $d_i_a = $spaceDiscount + $sponsorDiscount + $adsDiscount + $effAdsDiscount;
        $set('d_i_a', $d_i_a);

        $subTotal2 = $subTotal1 - $d_i_a;
        $set('sub_total_2', $subTotal2);

        $eventId = $get('event_id');
        $vatRate = Event::find($eventId)?->vat_rate ?? 0;
        $vatAmount = $subTotal2 * ($vatRate / 100);
        $set('vat_amount', $vatAmount);

        $netTotal = $subTotal2 + $vatAmount;
        $set('net_total', $netTotal);
    }
}
