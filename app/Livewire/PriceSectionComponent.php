<?php

namespace App\Livewire;

use App\Models\Contract;
use App\Models\Event;
use App\Models\Settings\Currency;
use App\Models\Settings\Price;
use App\Models\Stand;
use Livewire\Component;

class PriceSectionComponent extends Component
{
    public bool $special_price;
    public Contract $contract;
    public Currency $currency;
    public function mount($contract = null, $currency = null, $event = null, $special_price = null)
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
            if($event==null) {
            $event = new Event([

            ]);}
            $p->Event = $event;
            $this->contract = new Contract([

            ]);
            $this->contract->Stand = $s;
            $this->contract->Event = $event;
        }
        $this->currency = $currency;
        $this->special_price = $special_price;
    }
    public function render()
    {
        return view('livewire.price-section-component');
    }
}
