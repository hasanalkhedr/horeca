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
        // Generate contract number
        $data['contract_no'] = Contract::generateContractNumber(
            new Contract($data)
        );
        return $data;
    }

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

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // If we have a merged stand, use it
        if (!empty($data['merged_stand_id'])) {
            $data['stand_id'] = $data['merged_stand_id'];
        }

        // Remove temporary merge data from database storage
        unset($data['merged_stand_id']);
        unset($data['merge_stands']);
        unset($data['merge_stand_count']);
        unset($data['suggested_merge_no']);

        return $data;
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
