<?php

namespace App\Livewire;

use App\Models\Contract;
use Livewire\Component;

class PaymentSection extends Component
{
    public Contract $contract;
    public string $bankAccount;
    public function mount($contract = null, $bankAccount = '') {
        if($contract) {
            $this->contract = $contract;
        } else {
            $c = new Contract([]);
            $this->contract = $c;
        }
        $this->bankAccount = $bankAccount;
    }
    public function render()
    {
        return view('livewire.payment-section');
    }
}
