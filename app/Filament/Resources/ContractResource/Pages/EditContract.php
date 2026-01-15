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
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditContract extends EditRecord
{
    protected static string $resource = ContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $contract = $this->record;

        // $data['category_id'] = [$contract->category_id];
        $data['currency_id'] = $contract->Report?->currency_id;

        $data['use_special_price'] = $contract->price_id === null;

        //Process ads_check into ads_selections
        $adsSelections = [];
        $currentAds = $contract->ads_check ?? [];

        if (!empty($currentAds) && is_array($currentAds)) {
            foreach ($currentAds as $item) {
                if (strpos($item, '_') !== false) {
                    [$packageId, $optionId] = explode('_', $item);
                    $adsSelections[] = $optionId;
                }
            }
        }
        $data['ads_options_display'] = $adsSelections;

        //Process eff_ads_check into eff_ads_selections
        $effAdsSelections = [];
        $currentEffAds = $contract->eff_ads_check ?? [];

        if (!empty($currentEffAds) && is_array($currentEffAds)) {
            foreach ($currentEffAds as $item) {
                if (strpos($item, '_') !== false) {
                    [$packageId, $optionId] = explode('_', $item);
                    $effAdsSelections[] = $optionId;
                }
            }
        }
        $data['eff_ads_selections'] = $effAdsSelections;

        return $data;
    }

    protected function handleRecordUpdate($record, array $data): Model
    {
        $oldStandId = $record->stand_id;

        // Update contract
        $record->update($data);

        // Update stand status if changed
        $newStandId = $record->stand_id;
        if ($oldStandId != $newStandId) {
            // Mark old stand as available
            if ($oldStandId) {
                Stand::where('id', $oldStandId)->update(['status' => 'Available']);
            }

            // Mark new stand as sold
            if ($newStandId) {
                Stand::where('id', $newStandId)->update(['status' => 'Sold']);
            }
        }

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
