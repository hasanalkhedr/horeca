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

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
