<?php

namespace App\Filament\Resources\ContractResource\Pages;

use App\Filament\Resources\ContractResource;
use App\Models\Contract;
use App\Models\Stand;
use App\Models\Event;
use App\Models\Settings\Price;
use App\Models\SponsorPackage;
use App\Models\AdsPackage;
use App\Models\EffAdsPackage;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateContract extends CreateRecord
{
    protected static string $resource = ContractResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Process arrays
        $data = $this->processArrays($data);

        // // Calculate amounts
        // $data = $this->calculateAllAmounts($data);

        // Generate contract number
        $data['contract_no'] = Contract::generateContractNumber(
            new Contract($data)
        );
        return $data;
    }

    private function processArrays(array $data): array
    {
        // Process ads_check array
        $adsCheck = [];
        $packageId = $data['ads_package_id'];
        if (isset($data['ads_selections'])) {
            foreach ($data['ads_selections'] as $selection) {
                $adsCheck[] = $packageId . '_' . $selection;
            }
        }
        $data['ads_check'] = $adsCheck;

        // Process eff_ads_check array
        $effAdsCheck = [];
        $effPackageId = $data['eff_ads_package_id'];
        if (isset($data['eff_ads_selections'])) {
            foreach ($data['eff_ads_selections'] as $selection) {
                $effAdsCheck[] = $effPackageId . '_' . $selection;
            }
        }
        $data['eff_ads_check'] = $effAdsCheck;

        // Handle special price
        if (isset($data['use_special_price']) && $data['use_special_price']) {
            $data['price_id'] = null;
        } else {
            $data['price_amount'] = null;
        }
        unset($data['use_special_price']);

        // Remove temporary fields
        unset($data['ads_selections']);
        unset($data['eff_ads_selections']);

        return $data;
    }

    // private function calculateAllAmounts(array $data): array
    // {
    //     // Calculate space amount
    //     $data = $this->calculateSpaceAmount($data);

    //     // Calculate space net
    //     $data['space_net'] = max(0, ($data['space_amount'] ?? 0) - ($data['space_discount'] ?? 0));

    //     // Calculate sponsor amount if selected
    //     if (isset($data['sponsor_package_id']) && $data['sponsor_package_id']) {
    //         $data = $this->calculateSponsorAmount($data);
    //     } else {
    //         $data['sponsor_amount'] = 0;
    //     }
    //     $data['sponsor_net'] = max(0, ($data['sponsor_amount'] ?? 0) - ($data['sponsor_discount'] ?? 0));

    //     // Calculate ads amount if selected
    //     if (isset($data['ads_check']) && is_array($data['ads_check']) && !empty($data['ads_check'])) {
    //         $data = $this->calculateAdsAmountFromCheck($data);
    //     } else {
    //         $data['advertisment_amount'] = 0;
    //     }
    //     $data['ads_net'] = max(0, ($data['advertisment_amount'] ?? 0) - ($data['ads_discount'] ?? 0));

    //     // Calculate eff ads amount if selected
    //     if (isset($data['eff_ads_check']) && is_array($data['eff_ads_check']) && !empty($data['eff_ads_check'])) {
    //         $data = $this->calculateEffAdsAmountFromCheck($data);
    //     } else {
    //         $data['eff_ads_amount'] = 0;
    //     }
    //     $data['eff_ads_net'] = max(0, ($data['eff_ads_amount'] ?? 0) - ($data['eff_ads_discount'] ?? 0));

    //     // Calculate special design amount if applicable
    //     if (isset($data['special_design_price']) && $data['special_design_price'] > 0 && isset($data['stand_id'])) {
    //         $stand = Stand::find($data['stand_id']);
    //         if ($stand) {
    //             $data['special_design_amount'] = $stand->space * $data['special_design_price'];
    //         }
    //     }

    //     // Calculate totals
    //     $data['sub_total_1'] =
    //         ($data['space_net'] ?? 0) +
    //         ($data['sponsor_net'] ?? 0) +
    //         ($data['ads_net'] ?? 0) +
    //         ($data['eff_ads_net'] ?? 0) +
    //         ($data['water_electricity_amount'] ?? 0) +
    //         ($data['special_design_amount'] ?? 0);

    //     $data['d_i_a'] =
    //         ($data['space_discount'] ?? 0) +
    //         ($data['sponsor_discount'] ?? 0) +
    //         ($data['ads_discount'] ?? 0) +
    //         ($data['eff_ads_discount'] ?? 0);

    //     $data['sub_total_2'] = ($data['sub_total_1'] ?? 0) - ($data['d_i_a'] ?? 0);

    //     // Calculate VAT
    //     $event = Event::find($data['event_id']);
    //     $vatRate = $event?->vat_rate ?? 0;
    //     $data['vat_amount'] = ($data['sub_total_2'] ?? 0) * ($vatRate / 100);

    //     $data['net_total'] = ($data['sub_total_2'] ?? 0) + ($data['vat_amount'] ?? 0);

    //     return $data;
    // }

    // private function calculateSpaceAmount(array $data): array
    // {
    //     if (empty($data['stand_id'])) {
    //         $data['space_amount'] = 0;
    //         return $data;
    //     }

    //     $stand = Stand::find($data['stand_id']);
    //     if (!$stand) {
    //         $data['space_amount'] = 0;
    //         return $data;
    //     }

    //     $space = $stand->space;

    //     // Check if using special price
    //     if (isset($data['price_id']) && !$data['price_id'] && isset($data['price_amount'])) {
    //         $data['space_amount'] = $space * ($data['price_amount'] ?? 0);
    //     } elseif (!empty($data['price_id'])) {
    //         // Get price from database
    //         $price = Price::with(['Currencies' => function($query) use ($data) {
    //             $query->where('currencies.id', $data['currency_id'] ?? null);
    //         }])->find($data['price_id']);

    //         $priceAmount = $price?->Currencies->first()?->pivot->amount ?? 0;
    //         $data['space_amount'] = $space * $priceAmount;
    //     } else {
    //         $data['space_amount'] = 0;
    //     }

    //     return $data;
    // }

    // private function calculateSponsorAmount(array $data): array
    // {
    //     $package = SponsorPackage::with(['Currencies' => function($query) use ($data) {
    //         $query->where('currencies.id', $data['currency_id'] ?? null);
    //     }])->find($data['sponsor_package_id']);

    //     $data['sponsor_amount'] = $package?->Currencies->first()?->pivot->total_price ?? 0;
    //     return $data;
    // }

    // private function calculateAdsAmountFromCheck(array $data): array
    // {
    //     $adsCheck = $data['ads_check'] ?? [];
    //     $currencyId = $data['currency_id'] ?? null;
    //     $total = 0;

    //     foreach ($adsCheck as $item) {
    //         if (strpos($item, '_') !== false) {
    //             [$packageId, $optionId] = explode('_', $item);

    //             $package = AdsPackage::with(['AdsOptions.Currencies' => function($query) use ($currencyId) {
    //                 $query->where('currencies.id', $currencyId);
    //             }])->find($packageId);

    //             if ($package) {
    //                 $option = $package->AdsOptions->find($optionId);
    //                 if ($option) {
    //                     $price = $option->Currencies
    //                         ->where('id', $currencyId)
    //                         ->first()?->pivot->price ?? 0;
    //                     $total += $price;
    //                 }
    //             }
    //         }
    //     }

    //     $data['advertisment_amount'] = $total;
    //     return $data;
    // }

    // private function calculateEffAdsAmountFromCheck(array $data): array
    // {
    //     $effAdsCheck = $data['eff_ads_check'] ?? [];
    //     $currencyId = $data['currency_id'] ?? null;
    //     $total = 0;

    //     foreach ($effAdsCheck as $item) {
    //         if (strpos($item, '_') !== false) {
    //             [$packageId, $optionId] = explode('_', $item);

    //             $package = EffAdsPackage::with(['EffAdsOptions.Currencies' => function($query) use ($currencyId) {
    //                 $query->where('currencies.id', $currencyId);
    //             }])->find($packageId);

    //             if ($package) {
    //                 $option = $package->EffAdsOptions->find($optionId);
    //                 if ($option) {
    //                     $price = $option->Currencies
    //                         ->where('id', $currencyId)
    //                         ->first()?->pivot->price ?? 0;
    //                     $total += $price;
    //                 }
    //             }
    //         }
    //     }

    //     $data['eff_ads_amount'] = $total;
    //     return $data;
    // }

    protected function handleRecordCreation(array $data): Model
    {
        // Create contract
        $contract = parent::handleRecordCreation($data);

        // Mark stand as sold
        if ($contract->stand_id) {
            Stand::where('id', $contract->stand_id)->update(['status' => 'Sold']);
        }

        return $contract;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
