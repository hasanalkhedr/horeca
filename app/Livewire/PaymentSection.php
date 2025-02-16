<?php

namespace App\Livewire;

use App\Models\Contract;
use App\Models\Report;
use Livewire\Component;

class PaymentSection extends Component
{
    public Contract $contract;
    public Report $report;
    public function mount($contract = null, $report = null) {
        if($contract) {
            $this->contract = $contract;
        } else {
            $c = new Contract([]);
            $this->contract = $c;
        }
        if($report) {
            $this->report = $report;
        } else {
            $this->report = new Report([]);
        }
    }
    public function render()
    {
        return view('livewire.payment-section');
    }
}
