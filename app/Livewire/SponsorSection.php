<?php

namespace App\Livewire;

use App\Models\Contract;
use App\Models\Event;
use App\Models\Settings\Currency;
use App\Models\SponsorPackage;
use App\Models\SponsorOption;
use Livewire\Component;

class SponsorSection extends Component
{
    public Contract $contract;
    public Currency $currency;
    public string $with_options = '';
    public Event $event;
    public function mount($contract = null, $currency = null, $event = null, $with_options = '')
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
        $this->event = $event;
        $this->with_options = $with_options;
    }
    public function render()
    {
        return view('livewire.sponsor-section');
    }
}
