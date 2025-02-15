<?php

namespace App\Livewire;

use App\Models\Contract;
use App\Models\SponsorPackage;
use Livewire\Component;

class SponsorSection extends Component
{
    public Contract $contract;
    public function mount($contract = null)
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
    }
    public function render()
    {
        return view('livewire.sponsor-section');
    }
}
