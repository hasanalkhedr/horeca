<?php

namespace App\Livewire;

use App\Models\Contract;
use App\Models\Event;
use App\Models\Settings\Currency;
use Livewire\Component;

class EffectiveAdvertisementSection extends Component
{
    public Contract $contract;
    public Currency $currency;
    public Event $event;
    public function mount($contract = null, $currency = null, $event = null)
    {
        if($contract) {
            $this->contract = $contract;
        } else {

            $this->contract = new Contract([

            ]);
        }
        $this->currency = $currency;
        $this->event = $event;
    }
    public function render()
    {
        return view('livewire.effective-advertisement-section');
    }
}
