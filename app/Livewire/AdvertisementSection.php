<?php

namespace App\Livewire;

use App\Models\Contract;
use App\Models\Settings\Currency;
use Livewire\Component;

class AdvertisementSection extends Component
{
    public Contract $contract;
    public Currency $currency;
    public function mount($contract = null, $currency = null)
    {
        if($contract) {
            $this->contract = $contract;
        } else {

            $this->contract = new Contract([

            ]);
        }
        $this->currency = $currency;
    }
    public function render()
    {
        return view('livewire.advertisement-section');
    }
}
