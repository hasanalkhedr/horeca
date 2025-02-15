<?php

namespace App\Livewire;

use App\Models\Event;
use App\Models\Client;
use App\Models\Company;
use App\Models\Contract;
use Livewire\Component;

class SignatureSection extends Component
{
    public Contract $contract;
    public function mount($contract = null)
    {
        if ($contract) {
            $this->contract = $contract;
        } else {
            $e = new Event([
                'name' => 'EVENT NAME',
                'start_date' => '01/01/2025',
                'end_date' => '01/01/2025'
            ]);
            $p = new Client([
                'name' => 'Contact Person',
            ]);
            $p1 = new Client([
                'name' => 'Exhabition Coordinator',
            ]);
            $c = new Company([
                'name' => 'Sample Company',
            ]);
            $this->contract = new Contract([

            ]);
            $this->contract->Event = $e;
            $this->contract->Company = $c;
            $this->contract->ContactPerson = $p;
            $this->contract->ExhabitionCoordinator = $p1;
        }
    }
    public function render()
    {
        return view('livewire.signature-section');
    }
}
