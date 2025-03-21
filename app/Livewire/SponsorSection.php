<?php

namespace App\Livewire;

use App\Models\Contract;
use App\Models\Settings\Currency;
use App\Models\SponsorPackage;
use App\Models\SponsorOption;
use Livewire\Component;

class SponsorSection extends Component
{
    public Contract $contract;
    public Currency $currency;
    public bool $with_options = false;
    public function mount($contract = null, $currency = null, $with_options = false)
    {
        if($contract) {
            $this->contract = $contract;
        } else {
            $sp = new SponsorPackage([
                'title' => 'Sponsor Package Title',
            ]);
            $this->contract = new Contract([

            ]);
            $this->contract->SponsorPackage = $sp;
        }
        $this->currency = $currency;
        $this->with_options = $with_options;
    }
    public function render()
    {
        return view('livewire.sponsor-section');
    }
}
