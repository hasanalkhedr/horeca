<?php

namespace App\Livewire;

use App\Http\Controllers\ContractController;
use App\Models\Client;
use App\Models\Company;
use App\Models\Contract;
use App\Models\Event;
use App\Models\Settings\Category;
use Livewire\Component;

class CompanyDetailsComponent extends Component
{
    public Contract $contract;

    public function mount($contract = null, $event = null)
    {
        if ($contract) {
            $this->contract = $contract;
        } else {
            if($event == null) {
            $event = new Event([
                'name' => 'EVENT NAME',
                'start_date' => '01/01/2025',
                'end_date' => '01/01/2025'
            ]);}
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
            $this->contract->Event = $event;
            $this->contract->Company = $c;
            $this->contract->ContactPerson = $p;
            $this->contract->ExhabitionCoordinator = $p1;
        }
    }
    public function render()
    {
        return view('livewire.company-details-component');
    }
}
