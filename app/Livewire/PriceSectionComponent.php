<?php

namespace App\Livewire;

use App\Models\Contract;
use App\Models\Event;
use App\Models\Settings\Price;
use App\Models\Stand;
use Livewire\Component;

class PriceSectionComponent extends Component
{
    public Contract $contract;
    public function mount($contract = null)
    {
        if ($contract) {
            $this->contract = $contract;
        } else {
            $s = new Stand([
                'no' => 'stand_numer',
            ]);
            $p = new Price([
                'name' => 'Space Only',
                'amount' => 100,
            ]);
            $e = new Event([

            ]);
            $p->Event = $e;
            $this->contract = new Contract([

            ]);
            $this->contract->Stand = $s;
            $this->contract->Event = $e;
        }
    }
    public function render()
    {
        return view('livewire.price-section-component');
    }
}
