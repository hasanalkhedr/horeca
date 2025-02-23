<?php

namespace App\Livewire;

use App\Models\Contract;
use App\Models\Settings\Currency;
use App\Models\SponsorPackage;
use Livewire\Component;

class SponsorSection extends Component
{
    public Contract $contract;
    public Currency $currency;
    public function mount($contract = null, $currency = null)
    {
        if($contract) {
            $this->contract = $contract;
        } else {
            $sp = new SponsorPackage([
                'title' => 'GOLDEN USD Package',
            ]);
            $this->contract = new Contract([

            ]);
            $this->contract->SponsorPackage = $sp;
        }
        $this->currency = $currency;
    }
    public function render()
    {
        return view('livewire.sponsor-section');
    }
}
