<?php

namespace App\Livewire;

use App\Models\Contract;
use Livewire\Component;

class WaterSection extends Component
{
    public Contract $contract;

    public function mount($contract = null)
    {
        if($contract) {
            $this->contract = $contract;
        } else {
            // $e = new Event([
            //     'name' => 'EVENT NAME',
            //     'start_date' => '01/01/2025',
            //     'end_date' => '01/01/2025'
            // ]);
            $this->contract = new Contract([

            ]);
            // $this->contract->Event = $e;
        }
    }
    public function render()
    {
        return view('livewire.water-section');
    }
}
